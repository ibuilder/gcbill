<?php

namespace App\Helpers;

class CalculationHelper {

    /**
     * Calculates the full summary (G702 style) for a given set of billing details and project info.
     *
     * @param array $billingHeader The billing header record.
     * @param array $project The project record.
     * @param array $billingDetails Array of calculated detail lines (each including sov_id, scheduled_value, total_completed_stored, retainage, etc.).
     * @param float $previousPaymentsTotal Total Earned Less Retainage from the prior approved/paid billing.
     * @param float $changeOrderTotal Total value of approved change orders.
     * @return array Calculated summary values.
     */
    public static function calculateBillingSummary(
        array $billingHeader,
        array $project,
        array $billingDetails,
        float $previousPaymentsTotal = 0.0,
        float $changeOrderTotal = 0.0
    ): array {
        $summary = [
            'original_contract_sum' => 0.0,
            'net_change_orders' => 0.0,
            'contract_sum_to_date' => 0.0,
            'total_completed_stored' => 0.0,
            'retainage_work_percent' => 0.0, // Informational
            'retainage_stored_percent' => 0.0, // Informational
            'retainage_work_amount' => 0.0, // Informational/Breakdown
            'retainage_stored_amount' => 0.0, // Informational/Breakdown
            'total_retainage' => 0.0, // Calculated from details
            'total_earned_less_retainage' => 0.0,
            'less_previous_payments' => 0.0,
            'current_payment_due' => 0.0,
            'balance_to_finish' => 0.0,
        ];

        // Grand totals from detail lines
        $grandTotals = [
            'total_completed_stored' => 0.0,
            'total_retainage' => 0.0,
            'total_work_completed' => 0.0,
            'total_stored_materials' => 0.0,
        ];
        foreach ($billingDetails as $detail) {
            $grandTotals['total_completed_stored'] += (float)($detail['total_completed_stored'] ?? 0.0);
            $grandTotals['total_retainage'] += (float)($detail['retainage'] ?? 0.0);
            // Ensure these keys exist in your detail line calculation result
            $grandTotals['total_work_completed'] += (float)($detail['total_work_completed'] ?? 0.0);
            $grandTotals['total_stored_materials'] += (float)($detail['this_materials_stored'] ?? 0.0); // Assuming this represents total stored to date for retainage calc? Or just this period? Clarify logic if needed.
        }

        // 1. Original Contract Sum
        $summary['original_contract_sum'] = (float)($project['contract_amount'] ?? 0.0);

        // 2. Net change by Change Orders
        $summary['net_change_orders'] = (float)$changeOrderTotal;

        // 3. Contract Sum to Date
        $summary['contract_sum_to_date'] = $summary['original_contract_sum'] + $summary['net_change_orders'];

        // 4. Total Completed & Stored to Date (From Detail Sum)
        $summary['total_completed_stored'] = $grandTotals['total_completed_stored'];

        // 5. Retainage
        // Use specific rate from billing if set, otherwise project rate
        // Note: Retainage rates are often complex (e.g., tiered, different for work/stored).
        // This assumes a simplified model based on a single rate or potentially separate rates passed in.
        $retainageWorkRate = isset($billingHeader['retainage_work_rate']) && $billingHeader['retainage_work_rate'] !== null
            ? (float)$billingHeader['retainage_work_rate'] / 100 // Assuming rate stored as percentage
            : (float)($project['retainage_percentage'] ?? 0.0) / 100; // Default from project

        $retainageStoredRate = isset($billingHeader['retainage_stored_rate']) && $billingHeader['retainage_stored_rate'] !== null
            ? (float)$billingHeader['retainage_stored_rate'] / 100
            : $retainageWorkRate; // Default stored rate to work rate if not specified

        $summary['retainage_work_percent'] = $retainageWorkRate * 100;
        $summary['retainage_stored_percent'] = $retainageStoredRate * 100;

        // Calculate breakdown amounts for information, but use the sum from details for the official total.
        $summary['retainage_work_amount'] = $grandTotals['total_work_completed'] * $retainageWorkRate;
        $summary['retainage_stored_amount'] = $grandTotals['total_stored_materials'] * $retainageStoredRate; // Check if total_stored_materials is correct basis
        $summary['total_retainage'] = $grandTotals['total_retainage']; // Use sum from detail lines as the definitive total

        // 6. Total Earned Less Retainage
        $summary['total_earned_less_retainage'] = $summary['total_completed_stored'] - $summary['total_retainage'];

        // 7. Less Previous Certificates For Payment
        $summary['less_previous_payments'] = (float)$previousPaymentsTotal;

        // 8. Current Payment Due
        $summary['current_payment_due'] = $summary['total_earned_less_retainage'] - $summary['less_previous_payments'];

        // 9. Balance To Finish, Including Retainage
        // Balance = Contract Sum To Date - Total Earned (Completed & Stored) + Total Retainage
        // Simplified: Contract Sum To Date - Total Earned Less Retainage
        $summary['balance_to_finish'] = $summary['contract_sum_to_date'] - $summary['total_earned_less_retainage'];

        // Ensure no negative values where inappropriate
        $summary['current_payment_due'] = max(0.0, $summary['current_payment_due']);
        $summary['balance_to_finish'] = max(0.0, $summary['balance_to_finish']); // Balance shouldn't go below zero

        // Rounding: Consider applying rounding consistently (e.g., to 2 decimal places) at the end
        foreach ($summary as $key => $value) {
            if (is_float($value)) {
                $summary[$key] = round($value, 2);
            }
        }

        return $summary;
    }

    /**
     * Calculates detail line values (G703 style).
     *
     * @param array $sov SOV item data (must include 'id', 'item_number', 'description', 'scheduled_value').
     * @param array $currentDetail Current billing detail input/data for this SOV item (must include 'work_completed_this_period', 'materials_stored_this_period').
     * @param array $previousTotals Totals from previous billings for this SOV item (must include 'work_completed', 'materials_stored').
     * @param float $retainageWorkRate Retainage rate for completed work (e.g., 0.05 for 5%).
     * @param float $retainageStoredRate Retainage rate for stored materials (e.g., 0.05 for 5%).
     * @return array Calculated values for the detail line.
     */
    public static function calculateDetailLine(
        array $sov,
        array $currentDetail,
        array $previousTotals,
        float $retainageWorkRate, // Pass rate (0.0 to 1.0)
        float $retainageStoredRate // Pass rate (0.0 to 1.0)
    ): array {
        $line = [];
        $line['sov_id'] = (int)($sov['id'] ?? 0);
        $line['item_number'] = $sov['item_number'] ?? '';
        $line['description'] = $sov['description'] ?? '';
        $line['scheduled_value'] = (float)($sov['scheduled_value'] ?? 0.0);

        // Previous values
        $line['prev_work_completed'] = (float)($previousTotals['work_completed'] ?? 0.0);
        // Previous stored materials logic needs care: Is it cumulative stored value, or just what was stored previously?
        // Assuming 'materials_stored' in previousTotals means the *value* stored as of the end of the last period.
        $line['prev_materials_stored'] = (float)($previousTotals['materials_stored'] ?? 0.0);

        // Current period values from input/current detail record
        $line['this_work_completed'] = (float)($currentDetail['work_completed_this_period'] ?? 0.0);
        $line['this_materials_stored'] = (float)($currentDetail['materials_stored_this_period'] ?? 0.0);

        // --- Calculations ---

        // D: Total Work Completed to Date
        $line['total_work_completed'] = $line['prev_work_completed'] + $line['this_work_completed'];

        // E: Materials Presently Stored (Not Previously Billed For)
        // This is typically the value input for this period.
        // The G703 form is slightly ambiguous here. Often interpreted as "Total materials stored on site now".
        // We'll use 'this_materials_stored' as the value for Column E based on common usage.
        $line['materials_presently_stored'] = $line['this_materials_stored'];

        // F: Total Completed and Stored to Date (D + E)
        $line['total_completed_stored'] = $line['total_work_completed'] + $line['materials_presently_stored'];

        // --- Constraints & Percentage ---
        // Ensure Total Completed & Stored does not exceed Scheduled Value
        $line['total_completed_stored'] = min($line['total_completed_stored'], $line['scheduled_value']);
        // Ensure Total Work Completed doesn't exceed (Total Completed & Stored - Materials Presently Stored) after capping
        $line['total_work_completed'] = min($line['total_work_completed'], $line['total_completed_stored'] - $line['materials_presently_stored']);
        $line['total_work_completed'] = max(0.0, $line['total_work_completed']); // Ensure non-negative

        // G: Percentage Complete ((F / C) * 100)
        $line['percent_complete'] = $line['scheduled_value'] > 0
            ? ($line['total_completed_stored'] / $line['scheduled_value']) * 100
            : ($line['total_completed_stored'] > 0 ? 100.0 : 0.0); // If scheduled is 0, 100% if any value completed
        $line['percent_complete'] = min(100.0, $line['percent_complete']); // Cap at 100%

        // H: Balance to Finish (C - F)
        $line['balance_to_finish'] = $line['scheduled_value'] - $line['total_completed_stored'];
        $line['balance_to_finish'] = max(0.0, $line['balance_to_finish']); // Ensure non-negative

        // I: Retainage (on Completed Work D * rate + on Stored Materials E * rate)
        // Use the capped/adjusted values for calculation
        $retainageOnWork = $line['total_work_completed'] * $retainageWorkRate;
        $retainageOnStored = $line['materials_presently_stored'] * $retainageStoredRate;
        $line['retainage'] = $retainageOnWork + $retainageOnStored;

        // Rounding: Apply rounding to calculated float values
        $floatKeys = [
            'scheduled_value', 'prev_work_completed', 'prev_materials_stored',
            'this_work_completed', 'this_materials_stored', 'total_work_completed',
            'materials_presently_stored', 'total_completed_stored', 'percent_complete',
            'balance_to_finish', 'retainage'
        ];
        foreach ($floatKeys as $key) {
            if (isset($line[$key])) {
                // Round percentages to maybe 1 or 2 decimals, currency to 2
                $decimals = ($key === 'percent_complete') ? 2 : 2; // Adjust precision if needed
                $line[$key] = round((float)$line[$key], $decimals);
            }
        }

        return $line;
    }
}