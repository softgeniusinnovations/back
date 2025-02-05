@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                @if($name == "baseball")
                <div class="card-body p-4">
                <h5>Category: Baseball</h5>
                 @foreach($xmlData['category'] as $category)
                    <div class="mb-5 table-responsive">
                        <h5 class="text-lg font-bold mb-0 mt-4">{{ $category['name'] }}</h5>
                        <table class="table table-bordered table-striped w-full">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Local Team</th>
                                <th>Total Score</th>
                                <th>In1</th>
                                <th>In2</th>
                                <th>In3</th>
                                <th>In4</th>
                                <th>In5</th>
                                <th>In6</th>
                                <th>In7</th>
                                <th>In8</th>
                                <th>In9</th>
                                <th>Hits</th>
                                <th>Errors</th>
                                <th>Away Team</th>
                                <th>Total Score</th>
                                <th>Hits</th>
                                <th>Errors</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($category['match'] as $match)
                                <tr>
                                    <td>{{ $match['id'] }}</td>
                                    <td>{{ $match['date'] }}</td>
                                    <td>{{ $match['time'] }}</td>
                                    <td>{{ $match['status'] }}</td>
                                    <td>{{ $match['localteam']['name'] }}</td>
                                    <td>{{ $match['localteam']['totalscore'] }}</td>
                                    <td>{{ @$match['localteam']['in1'] }} - {{ @$match['awayteam']['in1'] }}</td>
                                    <td>{{ @$match['localteam']['in2'] }} - {{ @$match['awayteam']['in2'] }}</td>
                                    <td>{{ @$match['localteam']['in3'] }} - {{ @$match['awayteam']['in3'] }}</td>
                                    <td>{{ @$match['localteam']['in4'] }} - {{ @$match['awayteam']['in4'] }}</td>
                                    <td>{{ @$match['localteam']['in5'] }} - {{ @$match['awayteam']['in5'] }}</td>
                                    <td>{{ @$match['localteam']['in6'] }} - {{ @$match['awayteam']['in6'] }}</td>
                                    <td>{{ @$match['localteam']['in7'] }} - {{ @$match['awayteam']['in7'] }}</td>
                                    <td>{{ @$match['localteam']['in8'] }} - {{ @$match['awayteam']['in8'] }}</td>
                                    <td>{{ @$match['localteam']['in9'] }} - {{ @$match['awayteam']['in9'] }}</td>
                                    <td>{{ $match['localteam']['hits'] }}</td>
                                    <td>{{ $match['localteam']['errors'] }}</td>
                                    <td>{{ $match['awayteam']['name'] }}</td>
                                    <td>{{ $match['awayteam']['totalscore'] }}</td>
                                    <td>{{ $match['awayteam']['hits'] }}</td>
                                    <td>{{ $match['awayteam']['errors'] }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
                </div>
                @endif
                
              
                @if($name == "bsktbl")
                    <div class="card-body p-4">
                    <h5>Category: Basket ball</h5>
                    @foreach($xmlData['category'] as $category)
                        <div class="mb-5 table-responsive">
                            <h5 class="text-lg font-bold mb-0 mt-4">{{ $category['name'] }}</h5>
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                        <th>Local Team</th>
                                        <th>Total Score</th>
                                        <th>Q1</th>
                                        <th>Q2</th>
                                        <th>Q3</th>
                                        <th>Q4</th>
                                        <th>OT</th>
                                        <th>Away Team</th>
                                        <th>Total Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        // Check if 'match' is an array or a single object
                                        $matches = $category['match'] ?? null;
                                        
                                        if ($matches && @$matches['awayteam']) {
                                            $matches = [(array) $matches];
                                        }
                                    
                                        if ($matches && !is_array($matches)) {
                                            $matches = [$matches];
                                        }
                                    @endphp
                    
                                    @foreach($matches as $match)
                                        @if (is_array($match))
                                            <tr>
                                                <td>{{ @$match['id'] ?? '-' }}</td>
                                                <td>{{ @$match['date'] ?? '-' }}</td>
                                                <td>{{ @$match['time'] ?? '-' }}</td>
                                                <td>{{ @$match['status'] ?? '-' }}</td>
                                                <td>{{ @$match['localteam']['name'] ?? '-' }}</td>
                                                <td>{{ @$match['localteam']['totalscore'] ?? '-' }}</td>
                                                <td>{{ @$match['localteam']['q1'] ?? '-' }}</td>
                                                <td>{{ @$match['localteam']['q2'] ?? '-' }}</td>
                                                <td>{{ @$match['localteam']['q3'] ?? '-' }}</td>
                                                <td>{{ @$match['localteam']['q4'] ?? '-' }}</td>
                                                <td>{{ @$match['localteam']['ot'] ?? '-' }}</td>
                                                <td>{{ @$match['awayteam']['name'] ?? '-' }}</td>
                                                <td>{{ @$match['awayteam']['totalscore'] ?? '-' }}</td>
                                            </tr>
                                        @else
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                    </div>
                @endif
                
                @if($name == "soccernew")
                <div class="card-body p-4">
                    <h5>Category: {{@$xmlData['sport']}}</h5>
                    <h5>Update at: {{@$xmlData['updated']}}</h5>
                    
                    
                    
                    @foreach ($xmlData['category'] as $category)
                        <div style="margin-bottom: 20px;">
                            <h5 class="text-lg font-bold mb-0 mt-4" style="color: #000;">{{ $category['name'] }}</h5>
                    
                            <table class="table table-bordered table-striped border border-gray-300 w-full" style="color: #000;">
                                <thead>
                                    <tr>
                                        <th class="border border-gray-300 px-4 py-2">ID</th>
                                        <th class="border border-gray-300 px-4 py-2">Date</th>
                                        <th class="border border-gray-300 px-4 py-2">Time</th>
                                        <th class="border border-gray-300 px-4 py-2">Local Team</th>
                                        <th class="border border-gray-300 px-4 py-2">Score</th>
                                        <th class="border border-gray-300 px-4 py-2">Visitor Team</th>
                                        <th class="border border-gray-300 px-4 py-2">Events</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $matches = $category['matches']['match'] ?? null;
                                        if ($matches && !is_array(reset($matches))) {
                                            $matches = [$matches]; // Ensure it's wrapped in an array only if it's a single match object
                                        }
                                    @endphp
                                    @foreach ($matches as $match)
                                        @if (is_array($match)) {{-- Ensure $match is an array --}}
                                            <tr>
                                                <td class="border border-gray-300 px-4 py-2">{{ $match['id'] ?? 'N/A' }}</td>
                                                <td class="border border-gray-300 px-4 py-2">{{ $match['formatted_date'] ?? 'N/A' }}</td>
                                                <td class="border border-gray-300 px-4 py-2">{{ $match['time'] ?? 'N/A' }}</td>
                                                <td class="border border-gray-300 px-4 py-2">{{ $match['localteam']['name'] ?? 'Unknown' }}</td>
                                                <td class="border border-gray-300 px-4 py-2">
                                                    @if (($match['localteam']['goals'] ?? '?') !== '?')
                                                        [{{ $match['localteam']['goals'] ?? 'N/A' }} - {{ $match['visitorteam']['goals'] ?? 'N/A' }}]
                                                    @else
                                                        ?
                                                    @endif
                                                </td>
                                                <td class="border border-gray-300 px-4 py-2">{{ $match['visitorteam']['name'] ?? 'Unknown' }}</td>
                                                <td class="border border-gray-300 px-4 py-2">
                                                    @if (!empty($match['events']['event']))
                                                        <ul>
                                                            @php
                                                                $events = $match['events']['event'];
                                                    
                                                                // Wrap single event in an array only if it's not already an array
                                                                if (!isset($events[0])) {
                                                                    $events = [$events];
                                                                }
                                                            @endphp
                                                            @foreach ($events as $event)
                                                                @if (is_array($event)) {{-- Ensure $event is an array --}}
                                                                    <li>
                                                                        <strong>{{ ucfirst($event['type'] ?? 'Unknown') }}</strong>: 
                                                                        {{ $event['minute'] ?? '0' }}' 
                                                                        @if (!empty($event['player']))
                                                                            by {{ $event['player'] }}
                                                                        @endif
                                                                        @if (!empty($event['assist']))
                                                                            (Assist: {{ $event['assist'] }})
                                                                        @endif
                                                                    </li>
                                                                @else
                                                                    <li>Invalid event data</li>
                                                                @endif
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        No events
                                                    @endif
                    
                    
                                                </td>
                    
                                            </tr>
                                        @else
                                            {{-- Handle unexpected $match type --}}
                                            <tr>
                                                <td colspan="6" class="border border-gray-300 px-4 py-2 text-center">Invalid match data</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach

                </div>
                
                @endif
                
                @if($name == "tennis_scores" )
                    <div class="card-body p-4">
                    <h5>Category: Tennis</h5>
                    
                            @foreach ($xmlData['category'] as $category)
                             <div class="table-responsive">
                                <h5 class="text-lg font-bold mb-0 mt-4">{{ $category['name'] }}</h5>
                                <table class="table table-bordered table-striped w-full">
                                    <thead>
                                        <tr>
                                            <th class="border border-gray-300 px-4 py-2">Match ID</th>
                                            <th class="border border-gray-300 px-4 py-2">Match Date</th>
                                            <th class="border border-gray-300 px-4 py-2">Match Time</th>
                                            <th class="border border-gray-300 px-4 py-2">Match Status</th>
                                            <th class="border px-4 py-2">Player 1</th>
                                            <th class="border px-4 py-2">Player 1 Score</th>
                                            <th class="border px-4 py-2">Player 1 Winner</th>
                                            <th class="border px-4 py-2">S1</th>
                                            <th class="border px-4 py-2">S2</th>
                                            <th class="border px-4 py-2">S3</th>
                                            <th class="border px-4 py-2">S4</th>
                                            <th class="border px-4 py-2">S5</th>
                                            <th class="border px-4 py-2">Player 2</th>
                                            <th class="border px-4 py-2">Player 2 Score</th>
                                            <th class="border px-4 py-2">Player 2 Winner</th>
                                        </tr>
                                    </thead>
                                    @if(is_array($category['match']))
                                        @foreach ($category['match'] as $match)
                                            @if(is_array($match))
                                                <tr>
                                                    <td>{{@$match['id']}}</td>
                                                    <td>{{@$match['date']}}</td>
                                                    <td>{{@$match['time']}}</td>
                                                    <td>{{@$match['status']}}</td>
                                                    <td>{{@$match['player'][0]['name']}}</td>
                                                    <td>{{@$match['player'][0]['totalscore']}}</td>
                                                    <td>{{@$match['player'][0]['winner']}}</td>
                                                    
                                                    <td>{{@$match['player'][0]['s1']}} - {{@$match['player'][1]['s1']}}</td>
                                                    <td>{{@$match['player'][0]['s2']}} - {{@$match['player'][1]['s2']}}</td>
                                                    <td>{{@$match['player'][0]['s3']}} - {{@$match['player'][1]['s3']}}</td>
                                                    <td>{{@$match['player'][0]['s4']}} - {{@$match['player'][1]['s4']}}</td>
                                                    <td>{{@$match['player'][0]['s5']}} - {{@$match['player'][1]['s5']}}</td>
                                                    
                                                    <td>{{@$match['player'][1]['name']}}</td>
                                                    <td>{{@$match['player'][1]['totalscore']}}</td>
                                                    <td>{{@$match['player'][1]['winner']}}</td>
                                                </tr>
                                            
                                            @else
                                                <tr>
                                                    <td>{{@$category['match']['id']}}</td>
                                                    <td>{{@$category['match']['date']}}</td>
                                                    <td>{{@$category['match']['time']}}</td>
                                                    <td>{{@$category['match']['status']}}</td>
                                                    <td>{{@$category['match']['player'][0]['name']}}</td>
                                                    <td>{{@$category['match']['player'][0]['totalscore']}}</td>
                                                    <td>{{@$category['match']['player'][0]['winner']}}</td>
                                                    
                                                    <td>{{@$category['match']['player'][0]['s1']}} - {{@$category['match']['player'][1]['s1']}}</td>
                                                    <td>{{@$category['match']['player'][0]['s2']}} - {{@$category['match']['player'][1]['s2']}}</td>
                                                    <td>{{@$category['match']['player'][0]['s3']}} - {{@$category['match']['player'][1]['s3']}}</td>
                                                    <td>{{@$category['match']['player'][0]['s4']}} - {{@$category['match']['player'][1]['s4']}}</td>
                                                    <td>{{@$category['match']['player'][0]['s5']}} - {{@$category['match']['player'][1]['s5']}}</td>
                                                    
                                                    <td>{{@$category['match']['player'][1]['name']}}</td>
                                                    <td>{{@$category['match']['player'][1]['totalscore']}}</td>
                                                    <td>{{@$category['match']['player'][1]['winner']}}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @endif
                                </table>
                            </div>
                            @endforeach

                    </div>
                @endif
                
                @if($name == "volleyball")
                    <div class="card-body p-4">
                    <h5>Category: Volleyball</h5>
                    
                            @foreach ($xmlData['category'] as $category)
                             <div class="table-responsive">
                                <h5 class="text-lg font-bold mb-0 mt-4">{{ $category['name'] }}</h5>
                                <table class="table table-bordered table-striped w-full">
                                    <thead>
                                        <tr>
                                            <th class="border border-gray-300 px-4 py-2">Match ID</th>
                                            <th class="border border-gray-300 px-4 py-2">Match Date</th>
                                            <th class="border border-gray-300 px-4 py-2">Match Time</th>
                                            <th class="border border-gray-300 px-4 py-2">Match Status</th>
                                            <th class="border px-4 py-2">Localteam</th>
                                            <th class="border px-4 py-2">Totalscore</th>
                                            <th class="border px-4 py-2">S1</th>
                                            <th class="border px-4 py-2">S2</th>
                                            <th class="border px-4 py-2">S3</th>
                                            <th class="border px-4 py-2">S4</th>
                                            <th class="border px-4 py-2">S5</th>
                                            <th class="border px-4 py-2">Awayteam</th>
                                            <th class="border px-4 py-2">Totalscore</th>
                                        </tr>
                                    </thead>
                                    
                                     @php
                                        // Check if 'match' is an array or a single object
                                        $matches = $category['match'] ?? null;
                                        
                                        if ($matches && @$matches['awayteam']) {
                                            $matches = [(array) $matches];
                                        }
                                    
                                        if ($matches && !is_array($matches)) {
                                            $matches = [$matches];
                                        }
                                    @endphp
                    
                                       @foreach($matches as $match)
                                            @if(is_array($match))
                                                <tr>
                                                    <td>{{@$match['id']}}</td>
                                                    <td>{{@$match['date']}}</td>
                                                    <td>{{@$match['time']}}</td>
                                                    <td>{{@$match['status']}}</td>
                                                    <td>{{@$match['localteam']['name']}}</td>
                                                    <td>{{@$match['localteam']['totalscore']}}</td>
                                                    
                                                    <td>{{@$match['localteam']['s1']}} - {{@$match['awayteam']['s1']}}</td>
                                                    <td>{{@$match['localteam']['s2']}} - {{@$match['awayteam']['s2']}}</td>
                                                    <td>{{@$match['localteam']['s3']}} - {{@$match['awayteam']['s3']}}</td>
                                                    <td>{{@$match['localteam']['s4']}} - {{@$match['awayteam']['s4']}}</td>
                                                    <td>{{@$match['localteam']['s5']}} - {{@$match['awayteam']['s5']}}</td>
                                                    
                                                    <td>{{@$match['awayteam']['name']}}</td>
                                                    <td>{{@$match['awayteam']['totalscore']}}</td>
                                                </tr>
                                            
                                            @else
                                                <tr>
                                                    <td>{{@$category['match']['id']}}</td>
                                                    <td>{{@$category['match']['date']}}</td>
                                                    <td>{{@$category['match']['time']}}</td>
                                                    <td>{{@$category['match']['status']}}</td>
                                                    <td>{{@$category['match']['localteam']['name']}}</td>
                                                    <td>{{@$category['match']['localteam']['totalscore']}}</td>
                                                    
                                                    <td>{{@$category['match']['localteam']['s1']}} - {{@$category['match']['awayteam']['s1']}}</td>
                                                    <td>{{@$category['match']['localteam']['s2']}} - {{@$category['match']['awayteam']['s2']}}</td>
                                                    <td>{{@$category['match']['localteam']['s3']}} - {{@$category['match']['awayteam']['s3']}}</td>
                                                    <td>{{@$category['match']['localteam']['s4']}} - {{@$category['match']['awayteam']['s4']}}</td>
                                                    <td>{{@$category['match']['localteam']['s5']}} - {{@$category['match']['awayteam']['s5']}}</td>
                                                    
                                                    <td>{{@$category['match']['awayteam']['name']}}</td>
                                                    <td>{{@$category['match']['awayteam']['totalscore']}}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                </table>
                            </div>
                            @endforeach

                    </div>
                @endif
                
                
                @if($name == "football")
                    <div class="card-body p-4">
                        <h5>Category: Football</h5>
                
                        @foreach ($xmlData['category'] as $category)
                        <div class="table-responsive">
                            <h5 class="text-lg font-bold mb-0 mt-4">{{ $category['name'] }}</h5>
                            <table class="table table-bordered table-striped w-full">
                                <thead>
                                    <tr>
                                        <th class="border border-gray-300 px-4 py-2">Match ID</th>
                                        <th class="border border-gray-300 px-4 py-2">Match Date</th>
                                        <th class="border border-gray-300 px-4 py-2">Match Time</th>
                                        <th class="border border-gray-300 px-4 py-2">Match Status</th>
                                        <th class="border px-4 py-2">Localteam</th>
                                        <th class="border px-4 py-2">Totalscore</th>
                                        <th class="border px-4 py-2">Awayteam</th>
                                        <th class="border px-4 py-2">Totalscore</th>
                                        <th class="border px-4 py-2">Events</th>
                                    </tr>
                                </thead>
                
                                @php
                                    // Check if 'match' is an array or a single object
                                    $matches = $category['match'] ?? null;
                
                                    if ($matches && @$matches['awayteam']) {
                                        $matches = [(array) $matches];
                                    }
                
                                    if ($matches && !is_array($matches)) {
                                        $matches = [$matches];
                                    }
                                @endphp
                
                                @foreach($matches as $match)
                                <tr>
                                    <td>{{ @$match['id'] }}</td>
                                    <td>{{ @$match['date'] }}</td>
                                    <td>{{ @$match['time'] }}</td>
                                    <td>{{ @$match['status'] }}</td>
                                    <td>{{ @$match['localteam']['name'] }}</td>
                                    <td>{{ @$match['localteam']['totalscore'] }}</td>
                                    <td>{{ @$match['awayteam']['name'] }}</td>
                                    <td>{{ @$match['awayteam']['totalscore'] }}</td>
                                    <td>
                                        @if(isset($match['events']))
                                            @foreach($match['events'] as $quarter => $eventData)
                                                @if(isset($eventData['event']))
                                                    <strong>{{ ucfirst($quarter) }}:</strong>
                                                    @foreach($eventData['event'] as $event)
                                                        <div>
                                                            {{ @$event['min'] }}' - 
                                                            {{ @$event['player'] }} 
                                                            ({{ @$event['team'] }}) 
                                                            - {{ @$event['type'] }}
                                                        </div>
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        @else
                                            No events available
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                        </div>
                        @endforeach
                    </div>
                @endif

                
                
                
                <pre>
                    {{ json_encode($xmlData, JSON_PRETTY_PRINT) }}
                </pre>
            </div>
        </div>
    </div>
   
@endsection
