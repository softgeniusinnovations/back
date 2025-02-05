<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Results PDF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
        }
        .mt-4 {
            margin-top: 1.5rem;
        }
    </style>
</head>
<body>

@if(is_array($results) && count($results) > 0)
    <h2>Match Data</h2>
    <table>
        <thead>
        <tr>
            <th>League</th>
            <th>Home Team</th>
            <th>Away Team</th>
            <th>Score</th>
            <th>Possession</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($results as $match)
            <tr>
                <td>{{ $match['league']['name'] }}</td>
                <td>{{ $match['home']['name'] }}</td>
                <td>{{ $match['away']['name'] }}</td>
                <td>{{ $match['ss'] }}</td>
                <td>{{ $match['stats']['possession_rt'][0] }}% - {{ $match['stats']['possession_rt'][1] }}%</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <h2 class="mt-4">Match Statistics</h2>
    <table>
        <thead>
        <tr>
            <th>Stat</th>
            <th>{{ $results[0]['home']['name'] }}</th>
            <th>{{ $results[0]['away']['name'] }}</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Goals</td>
            <td>{{ $results[0]['stats']['goals'][0] }}</td>
            <td>{{ $results[0]['stats']['goals'][1] }}</td>
        </tr>
        <tr>
            <td>Corners</td>
            <td>{{ $results[0]['stats']['corners'][0] }}</td>
            <td>{{ $results[0]['stats']['corners'][1] }}</td>
        </tr>
        <tr>
            <td>Yellow Cards</td>
            <td>{{ $results[0]['stats']['yellowcards'][0] }}</td>
            <td>{{ $results[0]['stats']['yellowcards'][1] }}</td>
        </tr>
        <tr>
            <td>Red Cards</td>
            <td>{{ $results[0]['stats']['redcards'][0] }}</td>
            <td>{{ $results[0]['stats']['redcards'][1] }}</td>
        </tr>
        <tr>
            <td>Shots On Target</td>
            <td>{{ $results[0]['stats']['on_target'][0] }}</td>
            <td>{{ $results[0]['stats']['on_target'][1] }}</td>
        </tr>
        <tr>
            <td>Possession</td>
            <td>{{ $results[0]['stats']['possession_rt'][0] }}%</td>
            <td>{{ $results[0]['stats']['possession_rt'][1] }}%</td>
        </tr>
        </tbody>
    </table>

    <h2 class="mt-4">Match Sts</h2>
    <ul>
        <li>{{ $results[0]['sts'] }}</li>
    </ul>

    <h2 class="mt-4">Match Events</h2>
    <ul>
        @foreach ($results[0]['events'] as $event)
            <li>{{ $event['text'] }}</li>
        @endforeach
    </ul>

@else
    <p>No match data available.</p>
@endif

</body>
</html>