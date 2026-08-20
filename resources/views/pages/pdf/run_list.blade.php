<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Run List</title>
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            margin: 8mm 8mm;
            font-size: 16px;
            color: #222;
        }

        h1 {
            text-align: center;
            margin: 0 0 14px 0;
            font-size: 20px;
            letter-spacing: 0.5px;
        }

        table.run-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #333;
        }

        table.run-table thead th {
            background-color: #333333;
            color: #ffffff;
            padding: 6px 8px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #333333;
        }

        table.run-table thead th.text-left {
            text-align: left;
        }

        table.run-table tbody td {
            padding: 6px 8px;
            border: 1px solid #cccccc;
            text-align: center;
            vertical-align: top;
        }

        table.run-table tbody td.text-left {
            text-align: left;
        }

        table.run-table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table.run-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
    </style>
</head>

<body>
    <main>
        <div class="container">
            <h1>RUN LIST - {{ strtoupper(now()->format('F d, Y')) }}</h1>

            <table class="run-table">
                <thead>
                    <tr>
                        <th width="7%">Item #</th>
                        <th width="10%">Lot #</th>
                        <th width="12%">Claim #</th>
                        <th width="38%" class="text-left">Description</th>
                        <th width="10%"># of Runs</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($run_lists as $run_list)
                        <tr>
                            <td>{{ $run_list->item_number }}</td>
                            <td>{{ $run_list->lot_number }}</td>
                            <td>{{ $run_list->claim_number }}</td>
                            <td class="text-left">{{ $run_list->description }}</td>
                            <td>{{ $run_list->number_of_runs }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </main>
</body>

</html>
