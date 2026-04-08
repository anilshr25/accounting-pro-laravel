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
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }

        th {
            background: #f2f2f2;
        }

        .text-left {
            text-align: left;
        }
    </style>
</head>

<body>

    <h2>Ledger Report</h2>

    <p><strong>Name:</strong> {{ $party->name ?? '-' }}</p>
    <p><strong>Email:</strong> {{ $party->email ?? '-' }}</p>
    <p><strong>Mobile:</strong> {{ $party->phone ?? '-' }}</p>
    <p><strong>Closing Balance:</strong>
        @if ($ledgers->last()?->balance >= 0)
            Cr. {{ number_format($ledgers->first()->balance, 2) }}
        @else
            Dr. {{ number_format(abs($ledgers->first()->balance), 2) }}
        @endif
    </p>

    @if ($dateFrom || $dateTo)
        <p>
            <strong>Period:</strong>
            {{ $dateFrom ?? 'Start' }} to {{ $dateTo ?? 'End' }}
        </p>
    @endif

    <hr>

    <table>
        <thead>
            <tr>
                <th>SN</th>
                <th>Date</th>
                <th>Status/Invoice</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Balance</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ledgers as $index => $ledger)
                <tr>
                    <td>{{ $index + 1 }}</td>

                    <td>{{ $ledger->date->format('Y-m-d') }}</td>

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

                    <td class="text-left">
                        {{ $ledger->remarks ?? '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>

        <tfoot>
            <tr>
                <td colspan="3"><strong>Total</strong></td>
                <td>{{ number_format($ledgers->sum('debit'), 2) }}</td>
                <td>{{ number_format($ledgers->sum('credit'), 2) }}</td>
                <td></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

</body>

</html>
