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
     * @param float $changeOrderTotal Total value of approved change orders (implement later).
     * @return array Calculated summary values.
     */
    public static function calculateBillingSummary(
        array $billingHeader,
        array $project,
        array $billingDetails,
        float $previousPaymentsTotal = 0.0,
        float $changeOrderTotal = 0.0 // Added for future use
    ): array {
        $summary = [
            'original_contract_sum' => 0.0,
            'net_change_orders' => 0.0,
            'contract_sum_to_date' => 0.0,
            'total_completed_stored' => 0.0,
            'retainage_work_percent' => 0.0,
            'retainage_stored_percent' => 0.0,
            'retainage_work_amount' => 0.0,
            'retainage_stored_amount' => 0.0,
            'total_retainage' => 0.0,
            'total_earned_less_retainage' => 0.0,
            'less_previous_payments' => 0.0,
            'current_payment_due' => 0.0,
            'balance_to_finish' => 0.0,
        ];

        // Grand totals from detail lines
        $grandTotals = [
            'total_completed_stored' => 0.0,
            'total_retainage' => 0.0,
            'total_work_completed' => 0.0, // Need this for separate retainage calc
            'total_stored_materials' => 0.0, // Need this for separate retainage calc
        ];
        foreach ($billingDetails as $detail) {
            $grandTotals['total_completed_stored'] += (float)($detail['total_completed_stored'] ?? 0.0);
            $grandTotals['total_retainage'] += (float)($detail['retainage'] ?? 0.0);
            // These might need to be calculated/passed explicitly if not already in $billingDetails
            $grandTotals['total_work_completed'] += (float)($detail['total_work_completed'] ?? 0.0); // Sum of (prev_work + this_work)
            $grandTotals['total_stored_materials'] += (float)($detail['this_materials_stored'] ?? 0.0); // Sum of current stored materials
        }

        // 1. Original Contract Sum
        $summary['original_contract_sum'] = (float)($project['contract_amount'] ?? 0.0);

        // 2. Net change by Change Orders
        $summary['net_change_orders'] = $changeOrderTotal; // Use parameter

        // 3. Contract Sum to Date
        $summary['contract_sum_to_date'] = $summary['original_contract_sum'] + $summary['net_change_orders'];

        // 4. Total Completed & Stored to Date
        $summary['total_completed_stored'] = $grandTotals['total_completed_stored'];

        // 5. Retainage
        // Use specific rate from billing if set, otherwise project rate
        $retainageRate = isset($billingHeader['retainage_rate']) && $billingHeader['retainage_rate'] !== null
            ? (float)$billingHeader['retainage_rate']
            : (float)($project['retainage_percentage'] ?? 0.0);

        // Assuming same rate for work and stored unless specified differently (e.g., via UI inputs)
        // For calculation *within this helper*, we might use the single rate. The UI can override.
        $summary['retainage_work_percent'] = $retainageRate * 100;
        $summary['retainage_stored_percent'] = $retainageRate * 100; // Default same as work

        // Calculate amounts based on the *single* rate for backend calculation consistency
        // Note: This might differ slightly from UI if UI uses separate inputs.
        // We prioritize the Column G total from details for overall consistency.
        $summary['retainage_work_amount'] = $grandTotals['total_work_completed'] * $retainageRate;
        $summary['retainage_stored_amount'] = $grandTotals['total_stored_materials'] * $retainageRate; // Adjust if logic differs
        $summary['total_retainage'] = $grandTotals['total_retainage']; // Use sum from detail lines

        // 6. Total Earned Less Retainage
        $summary['total_earned_less_retainage'] = $summary['total_completed_stored'] - $summary['total_retainage'];

        // 7. Less Previous Certificates For Payment
        $summary['less_previous_payments'] = $previousPaymentsTotal; // Use parameter

        // 8. Current Payment Due
        $summary['current_payment_due'] = $summary['total_earned_less_retainage'] - $summary['less_previous_payments'];

        // 9. Balance To Finish, Including Retainage
        $summary['balance_to_finish'] = $summary['contract_sum_to_date'] - $summary['total_completed_stored'];

        // Ensure no negative values where inappropriate (e.g., payment due)
        $summary['current_payment_due'] = max(0, $summary['current_payment_due']);

        return $summary;
    }

    /**
     * Calculates detail line values (G703 style).
     *
     * @param array $sov SOV item data.
     * @param array $currentDetail Current billing detail input/data for this SOV item.
     * @param array $previousTotals Totals from previous billings for this SOV item.
     * @param float $retainageWorkPercent Retainage rate for completed work (0 to 1).
     * @param float $retainageStoredPercent Retainage rate for stored materials (0 to 1).
     * @return array Calculated values for the detail line.
     */
    public static function calculateDetailLine(
        array $sov,
        array $currentDetail,
        array $previousTotals,
        float $retainageWorkPercent,
        float $retainageStoredPercent
    ): array {
        $line = [];
        $line['sov_id'] = $sov['id'];
        $line['item_number'] = $sov['item_number'];
        $line['description'] = $sov['description'];
        $line['scheduled_value'] = (float)($sov['scheduled_value'] ?? 0.0);
        $line['prev_work_completed'] = (float)($previousTotals['work_completed'] ?? 0.0);
        // Previous stored materials logic might need refinement based on business rules
        $line['prev_materials_stored'] = (float)($previousTotals['materials_stored'] ?? 0.0);
        $line['this_work_completed'] = (float)($currentDetail['work_completed_this_period'] ?? 0.0);
        $line['this_materials_stored'] = (float)($currentDetail['materials_stored_this_period'] ?? 0.0);

        $line['total_work_completed'] = $line['prev_work_completed'] + $line['this_work_completed'];
        // Total completed and stored TO DATE
        $line['total_completed_stored'] = $line['total_work_completed'] + $line['this_materials_stored'];

        $line['percent_complete'] = $line['scheduled_value'] > 0
            ? ($line['total_completed_stored'] / $line['scheduled_value']) * 100
            : 0.0;

        $line['balance_to_finish'] = $line['scheduled_value'] - $line['total_completed_stored'];

        // Retainage calculation for this line
        $retainageOnWork = $line['total_work_completed'] * $retainageWorkPercent;
        $retainageOnStored = $line['this_materials_stored'] * $retainageStoredPercent;
        $line['retainage'] = $retainageOnWork + $retainageOnStored;

        // Prevent total completed > scheduled value (adjust 'this period' if needed?)
        // Or just cap values for calculation? Capping is simpler here.
        $line['total_completed_stored'] = min($line['total_completed_stored'], $line['scheduled_value']);
        $line['balance_to_finish'] = max(0, $line['balance_to_finish']);
        $line['percent_complete'] = min(100, $line['percent_complete']);
        // Recalculate retainage based on capped total? Depends on rules. Assuming retainage applies to actual earned value.
        // If total_completed_stored was capped, retainage might need recalculation based on capped amounts.
        // For simplicity, we use the retainage calculated before capping here.

        return $line;
    }
}