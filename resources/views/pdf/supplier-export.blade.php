<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <title>Supplier Export</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        h2 {
            text-align: center;
            margin-bottom: 5px;
        }

        .fiscal-year {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
        }

        th {
            background: #f2f2f2;
        }

        .amount {
            text-align: right;
        }
    </style>
</head>

<body>

    <h2>Supplier Report</h2>

    <div class="fiscal-year">
        Fiscal Year: {{ $fiscalYear }}
    </div>

    <table>
        <thead>
            <tr>
                <th>SN</th>
                <th>Party Name</th>
                <th>Date</th>
                <th>Miti</th>
                <th>Closing Balance</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($suppliers as $supplier)
                <tr>
                    <td>{{ $loop->iteration }}</td>

                    <td>{{ $supplier['party_name'] }}</td>

                    <td>{{ $supplier['date'] ?? '-' }}</td>

                    <td>{{ $supplier['miti'] ?? '-' }}</td>

                    <td class="amount">
                        {{ number_format((float) $supplier['amount'], 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
