<!DOCTYPE html>
<html>

<head>
    <title>Ledger Report</title>
    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 12px;
        }

        h2 {
            text-align: center;
            margin-bottom: 2px;
        }

        .company-name {
            text-align: center;
            font-weight: bold;
            font-size: 15px;
            margin-bottom: 10px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 5px;
        }

        .info-table td {
            border: none;
            padding: 2px 0;
            vertical-align: top;
        }

        .left {
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .closing {
            width: 100%;
            margin-top: 5px;
            margin-bottom: 10px;
        }

        .closing td {
            border: none;
        }

        hr {
            margin: 10px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            word-wrap: break-word;
        }

        th {
            background: #f2f2f2;
        }

        .sn {
            width: 40px;
        }

        .date {
            width: 90px;
        }

        .miti {
            width: 90px;
        }

        .status {
            width: 120px;
        }

        .amt {
            width: 80px;
        }

        .balance {
            width: 100px;
        }

        .remark {
            width: auto;
            text-align: left;
        }
    </style>
</head>

<body>

    <h2>Ledger Report</h2>

    <div class="company-name">
        {{ $party->name ?? '-' }}
    </div>

    <table class="info-table">
        <tr>
            <td class="left">
                <strong>Email:</strong> {{ $party->email ?? '-' }}<br>
                <strong>Mobile:</strong> {{ $party->phone ?? '-' }}
            </td>

            <td class="right">
                <strong>PAN:</strong> {{ $party->pan ?? '-' }}<br>
                <strong>Report Date:</strong>
                {{ $dateFrom ?? 'Start' }} to {{ $dateTo ?? 'End' }}
            </td>
        </tr>
    </table>

    @php
        $closingBalance = $ledgers->first()?->balance ?? 0;
    @endphp

    <table class="closing">
        <tr>
            <td></td>
            <td class="right">
                <strong>Closing Balance:</strong>
                @if ($closingBalance >= 0)
                    Cr. {{ number_format($closingBalance, 2) }}
                @else
                    Dr. {{ number_format(abs($closingBalance), 2) }}
                @endif
            </td>
        </tr>
    </table>

    <hr>

    <table>
        <thead>
            <tr>
                <th class="sn">SN</th>
                <th class="date">Date</th>
                <th class="miti">Miti</th>
                <th class="status">Status/Invoice</th>
                <th class="amt">Debit</th>
                <th class="amt">Credit</th>
                <th class="balance">Balance</th>
                <th class="remark">Remark</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($ledgers as $index => $ledger)
                <tr>

                    <td>{{ $index + 1 }}</td>

                    <td>{{ $ledger->date?->format('Y-m-d') }}</td>

                    <td>
                        {{ match ($ledger->reference_type) {
                            'invoice_return' => $ledger->reference?->return_miti,
                            'purchase_return' => $ledger->reference?->return_miti,
                            'invoice' => $ledger->reference?->invoice_miti,
                            'purchase_order' => $ledger->reference?->received_date_miti,
                            'cheque' => $ledger->reference?->miti,
                            'credit' => $ledger->reference?->miti,
                            'payment' => $ledger->reference?->miti,
                            default => null,
                        } ?? '-' }}
                    </td>

                    <td>
                        {{ optional($ledger->reference)->sales_return_number ??
                            (optional($ledger->reference)->cheque_number ??
                                (optional($ledger->reference)->transaction_id ??
                                    (optional($ledger->reference)->invoice_no ??
                                        (optional($ledger->reference)->purchase_invoice_number ??
                                            (optional($ledger->reference)->purchase_return_number ?? ($ledger->reference_id ?? '-')))))) }}
                    </td>

                    <td>{{ number_format($ledger->debit, 2) }}</td>

                    <td>{{ number_format($ledger->credit, 2) }}</td>

                    <td>
                        @if ($ledger->balance >= 0)
                            Cr. {{ number_format($ledger->balance, 2) }}
                        @else
                            Dr. {{ number_format(abs($ledger->balance), 2) }}
                        @endif
                    </td>

                    <td class="remark">
                        {{ $ledger->remarks ?? '-' }}
                    </td>

                </tr>
            @endforeach
        </tbody>

        <tfoot>
            <tr>
                <td colspan="4"><strong>Total</strong></td>
                <td>{{ number_format($ledgers->sum('debit'), 2) }}</td>
                <td>{{ number_format($ledgers->sum('credit'), 2) }}</td>
                <td></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

</body>

</html>
