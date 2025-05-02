<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIA G702 - Application and Certificate for Payment</title>
    <!-- Add CSS links here -->
    <style>
        /* Basic styling for layout - replace with your CSS */
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header-info, .contract-summary, .payment-calc { margin-bottom: 20px; }
        .signatures { margin-top: 30px; }
        /* Add more specific styles as needed */
    </style>
</head>
<body>
    <h1>AIA Document G702™ – Application and Certificate for Payment</h1>

    <div class="header-info">
        <table>
            <tr>
                <td><strong>TO OWNER:</strong> <?= htmlspecialchars($owner_name ?? 'N/A') ?></td>
                <td><strong>FROM CONTRACTOR:</strong> <?= htmlspecialchars($contractor_name ?? 'N/A') ?></td>
                <td><strong>APPLICATION NO:</strong> <?= htmlspecialchars($application_number ?? 'N/A') ?></td>
            </tr>
            <tr>
                <td><strong>PROJECT:</strong> <?= htmlspecialchars($project_name ?? 'N/A') ?><br><?= htmlspecialchars($project_address ?? 'N/A') ?></td>
                <td><strong>VIA ARCHITECT:</strong> <?= htmlspecialchars($architect_name ?? 'N/A') ?></td>
                <td><strong>PERIOD TO:</strong> <?= htmlspecialchars($period_to_date ?? 'N/A') ?></td>
            </tr>
            <tr>
                <td><strong>CONTRACT FOR:</strong> <?= htmlspecialchars($contract_for ?? 'General Construction') ?></td>
                <td><strong>CONTRACT DATE:</strong> <?= htmlspecialchars($contract_date ?? 'N/A') ?></td>
                <td><strong>PROJECT NOS:</strong> <?= htmlspecialchars($project_numbers ?? 'N/A') ?></td>
            </tr>
        </table>
    </div>

    <div class="contract-summary">
        <h2>CONTRACTOR'S APPLICATION FOR PAYMENT</h2>
        <p>Application is made for payment, as shown below, in connection with the Contract. Continuation Sheet, AIA Document G703™, is attached.</p>
        <table>
            <tr><td>1. ORIGINAL CONTRACT SUM</td><td style="text-align:right;"><?= number_format($original_contract_sum ?? 0, 2) ?></td></tr>
            <tr><td>2. Net change by Change Orders</td><td style="text-align:right;"><?= number_format($net_change_orders ?? 0, 2) ?></td></tr>
            <tr><td>3. CONTRACT SUM TO DATE (Line 1 ± 2)</td><td style="text-align:right;"><?= number_format($contract_sum_to_date ?? 0, 2) ?></td></tr>
            <tr><td>4. TOTAL COMPLETED & STORED TO DATE (Column G on G703)</td><td style="text-align:right;"><?= number_format($total_completed_stored_to_date ?? 0, 2) ?></td></tr>
            <tr><td>5. RETAINAGE:</td><td></td></tr>
            <tr><td> &nbsp; a. <?= number_format(($retainage_percent_completed ?? 0) * 100, 1) ?>% of Completed Work (Column D + E on G703)</td><td style="text-align:right;"><?= number_format($retainage_on_completed ?? 0, 2) ?></td></tr>
            <tr><td> &nbsp; b. <?= number_format(($retainage_percent_stored ?? 0) * 100, 1) ?>% of Stored Material (Column F on G703)</td><td style="text-align:right;"><?= number_format($retainage_on_stored ?? 0, 2) ?></td></tr>
            <tr><td> &nbsp; Total Retainage (Lines 5a + 5b or Total in Column I of G703)</td><td style="text-align:right;"><?= number_format($total_retainage ?? 0, 2) ?></td></tr>
            <tr><td>6. TOTAL EARNED LESS RETAINAGE (Line 4 Less Line 5 Total)</td><td style="text-align:right;"><?= number_format($total_earned_less_retainage ?? 0, 2) ?></td></tr>
            <tr><td>7. LESS PREVIOUS CERTIFICATES FOR PAYMENT (Line 6 from prior Certificate)</td><td style="text-align:right;"><?= number_format($less_previous_certificates ?? 0, 2) ?></td></tr>
            <tr><td>8. CURRENT PAYMENT DUE</td><td style="text-align:right;"><?= number_format($current_payment_due ?? 0, 2) ?></td></tr>
            <tr><td>9. BALANCE TO FINISH, INCLUDING RETAINAGE (Line 3 Less Line 6)</td><td style="text-align:right;"><?= number_format($balance_to_finish ?? 0, 2) ?></td></tr>
        </table>
    </div>

    <div class="change-order-summary">
        <table>
            <thead>
                <tr><th>CHANGE ORDER SUMMARY</th><th>ADDITIONS</th><th>DEDUCTIONS</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total changes approved in previous months by Owner</td>
                    <td style="text-align:right;"><?= number_format($co_additions_prev ?? 0, 2) ?></td>
                    <td style="text-align:right;"><?= number_format($co_deductions_prev ?? 0, 2) ?></td>
                </tr>
                <tr>
                    <td>Total approved this Month</td>
                    <td style="text-align:right;"><?= number_format($co_additions_this ?? 0, 2) ?></td>
                    <td style="text-align:right;"><?= number_format($co_deductions_this ?? 0, 2) ?></td>
                </tr>
                <tr>
                    <td>TOTALS</td>
                    <td style="text-align:right;"><?= number_format(($co_additions_prev ?? 0) + ($co_additions_this ?? 0), 2) ?></td>
                    <td style="text-align:right;"><?= number_format(($co_deductions_prev ?? 0) + ($co_deductions_this ?? 0), 2) ?></td>
                </tr>
                 <tr>
                    <td>NET CHANGES by Change Order</td>
                    <td colspan="2" style="text-align:right;"><?= number_format($net_change_orders ?? 0, 2) ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="signatures">
        <p>
            The undersigned Contractor certifies that to the best of the Contractor's knowledge, information and belief the Work covered by this Application for Payment has been completed in accordance with the Contract Documents, that all amounts have been paid by the Contractor for Work for which previous Certificates for Payment were issued and payments received from the Owner, and that current payment shown herein is now due.
        </p>
        <p><strong>CONTRACTOR:</strong> _________________________ By: _________________________ Date: __________</p>
        <p>State of: __________ County of: __________</p>
        <p>Subscribed and sworn to before me this __________ day of __________ year __________</p>
        <p>Notary Public: _________________________ My Commission Expires: __________</p>
    </div>

    <div class="architect-certification">
        <h2>ARCHITECT'S CERTIFICATE FOR PAYMENT</h2>
        <p>
            In accordance with the Contract Documents, based on on-site observations and the data comprising this application, the Architect certifies to the Owner that to the best of the Architect's knowledge, information and belief the Work has progressed as indicated, the quality of the Work is in accordance with the Contract Documents, and the Contractor is entitled to payment of the AMOUNT CERTIFIED.
        </p>
        <p><strong>AMOUNT CERTIFIED</strong> $ <?= number_format($amount_certified ?? 0, 2) ?></p>
        <p>(Attach explanation if amount certified differs from the amount applied for. Initial all figures on this Application and on the Continuation Sheet that are changed to conform with the amount certified.)</p>
        <p><strong>ARCHITECT:</strong> _________________________ By: _________________________ Date: __________</p>
        <p>This Certificate is not negotiable. The AMOUNT CERTIFIED is payable only to the Contractor named herein. Issuance, payment and acceptance of payment are without prejudice to any rights of the Owner or Contractor under this Contract.</p>
    </div>

</body>
</html>