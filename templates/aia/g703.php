<?php
// This file provides the structure for the G703 Continuation Sheet.
// Assumes $schedule_items, $application_*, $architect_project_no are passed.
// Assumes $retainage_rate_work and $retainage_rate_stored are passed (e.g., 0.10 for 10%)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIA G703 - Continuation Sheet</title>
    <!-- Add CSS links here -->
    <style>
        /* Basic styling for layout - replace with your CSS */
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 10px; /* Smaller font for G703 */}
        th, td { border: 1px solid #ccc; padding: 4px; text-align: right; vertical-align: top; }
        th { background-color: #f2f2f2; text-align: center; font-weight: bold; }
        td.item-description { text-align: left; }
        .header-info { margin-bottom: 10px; font-size: 12px; }
        /* Add more specific styles as needed */
    </style>
</head>
<body>
    <h1>AIA Document G703™ – Continuation Sheet</h1>

    <div class="header-info">
        <span><strong>APPLICATION NO:</strong> <?= htmlspecialchars($application_number ?? 'N/A') ?></span> |
        <span><strong>APPLICATION DATE:</strong> <?= htmlspecialchars($application_date ?? 'N/A') ?></span> |
        <span><strong>PERIOD TO:</strong> <?= htmlspecialchars($period_to_date ?? 'N/A') ?></span> |
        <span><strong>ARCHITECT'S PROJECT NO:</strong> <?= htmlspecialchars($architect_project_no ?? 'N/A') ?></span>
    </div>

    <table>
        <thead>
            <tr>
                <th>A<br>ITEM NO.</th>
                <th>B<br>DESCRIPTION OF WORK</th>
                <th>C<br>SCHEDULED VALUE</th>
                <th>D<br>WORK COMPLETED FROM PREVIOUS APPLICATION</th>
                <th>E<br>WORK COMPLETED THIS PERIOD</th>
                <th>F<br>MATERIALS PRESENTLY STORED (NOT IN D OR E)</th>
                <th>G<br>TOTAL COMPLETED AND STORED TO DATE (D+E+F)</th>
                <th>H<br>%<br>(G ÷ C)</th>
                <th>I<br>BALANCE TO FINISH (C-G)</th>
                <th>J<br>RETAINAGE<br>(Specify Rate)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Initialize Totals
            $total_c = 0; // Scheduled Value
            $total_d = 0; // Work Completed Previous
            $total_e = 0; // Work Completed This Period
            $total_f = 0; // Materials Stored
            $total_g = 0; // Total Completed & Stored
            $total_i = 0; // Balance to Finish
            $total_j = 0; // Retainage

            // Define retainage rates (These should ideally be passed from controller/config)
            $retainage_rate_work = $retainage_rate_work ?? 0.10; // Example: 10% on completed work
            $retainage_rate_stored = $retainage_rate_stored ?? 0.10; // Example: 10% on stored materials

            if (!empty($schedule_items)): // Check if $schedule_items exists and is not empty
                foreach ($schedule_items as $item):
                    // Get values from item, default to 0 if not set
                    $item_c = $item['scheduled_value'] ?? 0;
                    $item_d = $item['work_completed_previous'] ?? 0; // Work completed in prior apps
                    $item_e = $item['work_completed_this_period'] ?? 0; // Work completed this app period
                    $item_f = $item['materials_stored'] ?? 0; // Materials stored this period

                    // --- Calculate Columns ---
                    // G: Total Completed and Stored to Date
                    $item_g = $item_d + $item_e + $item_f;

                    // H: Percentage Complete
                    $item_h = ($item_c != 0) ? ($item_g / $item_c) * 100 : 0;

                    // I: Balance to Finish
                    $item_i = $item_c - $item_g;

                    // J: Retainage Calculation (Example: Fixed rate on work (D+E) and stored (F))
                    // Implement your specific retainage logic here.
                    // This example uses different rates for work and stored materials.
                    $retainage_on_work = ($item_d + $item_e) * $retainage_rate_work;
                    $retainage_on_stored = $item_f * $retainage_rate_stored;
                    $item_j = $retainage_on_work + $retainage_on_stored;
                    // --- End Calculations ---

                    // Accumulate Totals
                    $total_c += $item_c;
                    $total_d += $item_d;
                    $total_e += $item_e;
                    $total_f += $item_f;
                    $total_g += $item_g;
                    $total_i += $item_i;
                    $total_j += $item_j;
            ?>
            <tr>
                <td style="text-align: center;"><?= htmlspecialchars($item['item_no'] ?? '') ?></td>
                <td class="item-description"><?= htmlspecialchars($item['description'] ?? '') ?></td>
                <td><?= number_format($item_c, 2) ?></td>
                <td><?= number_format($item_d, 2) ?></td>
                <td><?= number_format($item_e, 2) ?></td>
                <td><?= number_format($item_f, 2) ?></td>
                <td><?= number_format($item_g, 2) ?></td>
                <td style="text-align: center;"><?= number_format($item_h, 1) ?>%</td>
                <td><?= number_format($item_i, 2) ?></td>
                <td><?= number_format($item_j, 2) ?></td>
            </tr>
            <?php
                endforeach;
            endif;

            // Calculate overall percentage H for the total line
            $total_h_percent = ($total_c != 0) ? ($total_g / $total_c) * 100 : 0;
            ?>
            <!-- Total Row -->
            <tr>
                <th colspan="2" style="text-align: right;">TOTALS</th>
                <th><?= number_format($total_c, 2) ?></th>
                <th><?= number_format($total_d, 2) ?></th>
                <th><?= number_format($total_e, 2) ?></th>
                <th><?= number_format($total_f, 2) ?></th>
                <th><?= number_format($total_g, 2) ?></th>
                <th style="text-align: center;"><?= number_format($total_h_percent, 1) ?>%</th>
                <th><?= number_format($total_i, 2) ?></th>
                <th><?= number_format($total_j, 2) ?></th>
            </tr>
        </tbody>
    </table>

</body>
</html>