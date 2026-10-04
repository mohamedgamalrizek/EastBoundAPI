<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\Receipt;

/**
 * Demo receipts, each attached to the invoice it actually pays.
 *
 * The invoice is looked up by number, and amounts stay within what that
 * invoice is worth — a receipt for more than the invoice would leave the
 * customer showing as overpaid and push receivables negative.
 *
 * Saving a receipt re-derives its invoice's paid amount and status, so the mix
 * below is what produces the paid / partial / unpaid / overdue spread.
 */
class ReceiptSeeder extends Seeder
{
    public function run(): void
    {
        $invoices = Invoice::pluck('id', 'invoice_no');

        // receipt_no, invoice_no, received_on, amount, method
        $rows = [
            // Settled in full.
            ['RCP-2041', 'INV-1040', '2026-06-04', 48000,  'bKash'],
            ['RCP-2040', 'INV-1039', '2026-06-03', 9000,   'Cash'],
            ['RCP-2039', 'INV-1037', '2026-06-01', 86000,  'Bank'],
            ['RCP-2038', 'INV-1036', '2026-05-30', 27000,  'Card'],
            ['RCP-2037', 'INV-1031', '2026-05-29', 97000,  'Bank'],
            ['RCP-2036', 'INV-1030', '2026-05-26', 21000,  'Cash'],

            // Part-paid: these stay 'partial' until the rest comes in.
            ['RCP-2035', 'INV-1038', '2026-06-05', 150000, 'Bank'],
            ['RCP-2034', 'INV-1035', '2026-06-02', 250000, 'Bank'],
            ['RCP-2033', 'INV-1033', '2026-06-06', 70000,  'Card'],
            ['RCP-2032', 'INV-1034', '2026-06-01', 32000,  'bKash'],
            ['RCP-2031', 'INV-1032', '2026-05-30', 18500,  'Cash'],
            ['RCP-2030', 'INV-1041', '2026-06-08', 60000,  'Bank'],
        ];

        foreach ($rows as $r) {
            if (! isset($invoices[$r[1]])) {
                continue;
            }

            $invoice = Invoice::find($invoices[$r[1]]);

            Receipt::updateOrCreate(
                ['receipt_no' => $r[0]],
                [
                    'invoice_id'    => $invoice->id,
                    'customer_id'   => $invoice->customer_id,
                    'customer_name' => $invoice->customer_name,
                    'received_on'   => $r[2],
                    // Never more than the invoice is worth.
                    'amount'        => min($r[3], (float) $invoice->amount),
                    'method'        => $r[4],
                    'reference'     => $invoice->invoice_no,
                ]
            );
        }
    }
}
