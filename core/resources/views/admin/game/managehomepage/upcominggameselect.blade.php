@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-lg-12 p-3">
                            <h4 class="mb-3">Match Data - {{$category_name}}</h4>
{{--                            @dd($data)--}}

                            <form action="{{ route('admin.storeUpcomingGame') }}" method="POST">
                                @csrf
                                <input type="hidden" name="category_name" value="{{ $category_name }}">
                                @if(isset($data))
{{--                                    @php--}}
{{--                                        $category = $data->scores->category;--}}
{{--                                    @endphp--}}

                                    @if($category_name=="Baseball")
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                <tr>
                                                    <th>Select</th> <!-- Checkbox header -->
                                                    <th>Match ID</th>
                                                    <th>League ID</th>
                                                    <th>Date</th>
                                                    <th>Time</th>
                                                    <th>Home Team</th>
                                                    <th>Away Team</th>
                                                    <th>Home Score</th>
                                                    <th>Away Score</th>
                                                    <th>Status</th>
                                                </tr>
                                                </thead>
{{--                                                @dd($data)--}}
                                                <tbody>
                                                @foreach ($data->scores->category as $index => $category)

                                                    @php
                                                        $match = $category->matches->match;
//                                                        dd($category)
                                                    @endphp
                                                    <tr>
                                                        <input type="hidden" name="category" value="Baseball">
                                                        <input type="hidden" name="league_ids[{{ $index }}]" value="{{ $category->id ?? '' }}">
                                                        <td>
                                                            <input type="checkbox" name="selected_match[{{ $index }}]" value="{{ $match->id ?? '' }}">
                                                        </td>
                                                        <td>{{ $match->id ?? 'N/A' }}</td>
                                                        <td>{{ $category->id ?? 'N/A' }}</td>
{{--                                                        <td>{{ $match->localteam->name ?? 'N/A' }}</td>--}}
{{--                                                        <td>{{ $match->awayteam->name ?? 'N/A' }}</td>--}}
{{--                                                        <td>{{ $match->status ?? 'N/A' }}</td>--}}



                                                        <td>{{ $match->date??'' }}</td>
                                                        <td>{{ $match->time??'' }}</td>
                                                        <td>{{ $match->localteam?->name ?? 'Unknown' }}</td>
                                                        <td>{{ $match->awayteam?->name ?? 'Unknown' }}</td>
                                                        <td>{{ $match->localteam?->totalscore ?? 'N/A' }}</td>
                                                        <td>{{ $match->awayteam?->totalscore ?? 'N/A' }}</td>
                                                        <td>{{ $match->status ?? 'N/A' }}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>

                                            </table>
                                        </div>
                                    @endif

                                    @if($category_name=="Basketball")
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                <tr>
                                                    <th>Select</th> <!-- Checkbox header -->
                                                    <th>Match ID</th>
                                                    <th>League ID</th>
                                                    <th>Home Team</th>
                                                    <th>Away Team</th>
                                                    <th>Status</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach ($data->scores->category as $category)
                                                    @foreach ($category->matches->match as $match)
                                                        <tr>
                                                            <input type="hidden" name="league_ids[{{ $category->{'@id'} }}]" value="{{ isset($category->{'@id'}) ? $category->{'@id'} : '' }}">
                                                            <td><input type="checkbox" name="selected_match[{{ $category->{'@id'} }}]" value="{{ isset($match->{'@id'}) ? $match->{'@id'} : '' }}"></td> <!-- Checkbox for each row -->
                                                            <td>{{ isset($match->{'@id'}) ? $match->{'@id'} : 'N/A' }}</td>
                                                            <td>{{ isset($category->{'@id'}) ? $category->{'@id'} : 'N/A' }}</td>
                                                            <td>{{ isset($match->localteam->{'@name'}) ? $match->localteam->{'@name'} : 'N/A' }}</td>
                                                            <td>{{ isset($match->awayteam->{'@name'}) ? $match->awayteam->{'@name'} : 'N/A' }}</td>
                                                            <td>{{ isset($match->{'@status'}) ? $match->{'@status'} : 'N/A' }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                    @if($category_name=="Cricket")
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                <tr>
                                                    <th>Select</th> <!-- Checkbox header -->
                                                    <th>Match ID</th>
                                                    <th>League ID</th>
                                                    <th>Home Team</th>
                                                    <th>Away Team</th>
                                                    <th>Status</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($data['data']['results'] as $index=> $match)
                                                    <tr>
                                                        <input type="hidden" name="category" value="Cricket">
                                                        <input type="hidden" name="league_ids[{{ $index }}]" value="{{ $match['league']['id'] ?? '' }}">

                                                        <td>
                                                            <input type="checkbox" name="selected_match[{{ $index }}]" value="{{ $match['id'] ?? '' }}">
                                                        </td>
{{--                                                        <td>--}}
{{--                                                            <input type="checkbox" name="match_ids[]" value="{{ $match['id'] }}">--}}
{{--                                                        </td>--}}
                                                        <td>{{ $match['id'] }}</td>
                                                        <td>{{ $match['league']['id'] }}</td>
                                                        <td>{{ $match['home']['name'] }}</td>
                                                        <td>{{ $match['away']['name'] }}</td>
                                                        <td>{{ $match['time_status'] }}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
{{--                                                <tbody>--}}
{{--                                                @foreach ($data->scores->category as $index => $category)--}}
{{--                                                    @php--}}
{{--                                                        // Ensure matches exist before accessing $match--}}
{{--                                                        $match = $category->matches->match ?? null;--}}
{{--                                                    @endphp--}}
{{--                                                    @if($match) <!-- Only render if $match exists -->--}}
{{--                                                    <tr>--}}
{{--                                                        <input type="hidden" name="category" value="Cricket">--}}
{{--                                                        <input type="hidden" name="league_ids[{{ $index }}]" value="{{ $category->id ?? '' }}">--}}

{{--                                                        <td>--}}
{{--                                                            <input type="checkbox" name="selected_match[{{ $index }}]" value="{{ $match->id ?? '' }}">--}}
{{--                                                        </td>--}}
{{--                                                        <td>{{ $match->id ?? 'N/A' }}</td>--}}
{{--                                                        <td>{{ $category->id ?? 'N/A' }}</td>--}}

{{--                                                        <td>{{ $match->date ?? 'N/A' }}</td>--}}
{{--                                                        <td>{{ $match->time ?? 'N/A' }}</td>--}}

{{--                                                        <!-- Safely accessing localteam and awayteam -->--}}
{{--                                                        <td>{{ $match->localteam?->name ?? 'Unknown' }}</td>--}}
{{--                                                        <td>{{ $match->awayteam?->name ?? 'Unknown' }}</td>--}}

{{--                                                        <td>{{ $match->localteam?->totalscore ?? 'N/A' }}</td>--}}
{{--                                                        <td>{{ $match->awayteam?->totalscore ?? 'N/A' }}</td>--}}
{{--                                                        <td>{{ $match->status ?? 'N/A' }}</td>--}}
{{--                                                    </tr>--}}
{{--                                                    @else--}}
{{--                                                        <!-- Optional: You could display a row with N/A values if no match exists -->--}}
{{--                                                        <tr>--}}
{{--                                                            <td colspan="10" class="text-center">No match data available</td>--}}
{{--                                                        </tr>--}}
{{--                                                    @endif--}}
{{--                                                @endforeach--}}
{{--                                                </tbody>--}}

                                            </table>
                                        </div>
                                    @endif

                                    @if($category_name=="Soccer")
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                <tr>
                                                    <th>Select</th> <!-- Checkbox header -->
                                                    <th>Match ID</th>
                                                    <th>league ID</th>
                                                    <th>Home Team</th>
                                                    <th>Away Team</th>
                                                    <th>Status</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach ($data->scores->categories as  $category)
                                                    @foreach ($category->matches as $match)
                                                        <tr>
                                                            <input type="hidden" name="category" value="Soccer">
                                                            <input type="hidden" name="league_id[{{ $category->id }}]" value="{{ $category->id ?? '' }}">
                                                            <td >
                                                                <input type="checkbox" name="selected_matches[{{ $category->id }}][]" value="{{ $match->id ?? '' }}">
                                                            </td>
                                                            <td>{{ $match->id }}</td>
                                                            <td>{{ $category->id }}</td>
                                                            <td>{{ $match->localteam->name }}</td>
                                                            <td>{{ $match->visitorteam->name }}</td>
                                                            <td>{{ $match->status }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                    @if($category_name=="Tennis")
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                <tr>
                                                    <th scope="col">Select</th>
                                                    <th scope="col">Match ID</th>
                                                    <th scope="col">League ID</th>
                                                    <th scope="col" colspan="2">Player Details</th>
                                                    <th scope="col">Status</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach ($data->scores->category as $category)
                                                    @php
                                                        $matches = is_array($category->matches->match) ? $category->matches->match : [$category->matches->match];
                                                    @endphp
                                                    @foreach ($matches as $match)
                                                        <tr>
                                                            <input type="hidden" name="category" value="Tennis">
                                                            <input type="hidden" name="league_id[{{ $category->id }}]" value="{{ $category->id ?? '' }}">
                                                            <td rowspan="{{ count($match->player) + 1 }}">
                                                                <input type="checkbox" name="selected_matches[{{ $category->id }}][]" value="{{ $match->id ?? '' }}">
                                                            </td>
                                                            <td rowspan="{{ count($match->player) + 1 }}">{{ $match->id }}</td>
                                                            <td rowspan="{{ count($match->player) + 1 }}">{{ $category->id ?? 'N/A' }}</td>
                                                            <td colspan="2">Match Date: {{ $match->date }} | Time: {{ $match->time }}</td>
                                                            <td>Status: {{ $match->status }}</td>
                                                        </tr>
                                                        @foreach ($match->player as $player)
                                                            <tr>
                                                                <td>{{ $player->name }}</td>
                                                                <td>S1: {{ $player->s1 }}</td>
                                                                <td>S2: {{ $player->s2 }}</td>
{{--                                                                <td>Winner: {{ $player->winner ? 'Yes' : 'No' }}</td>--}}
                                                            </tr>
                                                        @endforeach
                                                    @endforeach
                                                @endforeach
                                                </tbody>
                                            </table>

                                        </div>
                                    @endif

                                    @if($category_name=="Hockey")
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                <tr>
                                                    <th>Select</th> <!-- Checkbox header -->
                                                    <th>Match ID</th>
                                                    <th>League ID</th>
                                                    <th>Home team</th>
                                                    <th>Away team</th>
                                                    <th>Status</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach ($data->scores->category as $category)
                                                    @if (isset($category->matches->match) && is_array($category->matches->match))
                                                        @foreach ($category->matches->match as $match)
                                                            @if (is_object($match))
                                                                <tr>
                                                                    <input type="hidden" name="league_id[{{ $category->{'@id'} }}]" value="{{ $category->{'@id'} ?? '' }}">
                                                                    <input type="hidden" name="category" value="Hockey">

                                                                    <!-- Checkbox for selecting a match -->
                                                                    <td>
                                                                        <input type="checkbox" name="selected_matches[{{ $category->{'@id'} }}][]" value="{{ $match->{'@id'} ?? '' }}">
                                                                    </td>
                                                                    <td>{{ $match->{'@id'} ?? 'N/A' }}</td>
                                                                    <td>{{ $category->{'@id'} ?? 'N/A' }}</td>
                                                                    <td>{{ $match->localteam->{'@name'} ?? 'N/A' }}</td>
                                                                    <td>{{ $match->awayteam->{'@name'} ?? 'N/A' }}</td>
                                                                    <td>{{ $match->{'@status'} ?? 'N/A' }}</td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                @endforeach
                                                </tbody>


                                            </table>
                                        </div>
                                    @endif
                                    @if($category_name=="Handball")
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                <tr>
                                                    <th>Select</th> <!-- Checkbox header -->
                                                    <th>Match ID</th>
                                                    <th>League ID</th>
                                                    <th>Home team</th>
                                                    <th>Away team</th>
                                                    <th>Status</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach ($data->scores->category as $category)
                                                    @if (isset($category->matches->match) && is_array($category->matches->match))
                                                        @foreach ($category->matches->match as $match)
                                                            @if (is_object($match))
                                                                <tr>
                                                                    <input type="hidden" name="league_id[{{ $category->{'@id'} }}]" value="{{ $category->{'@id'} ?? '' }}">
                                                                    <input type="hidden" name="category" value="Handball">

                                                                    <!-- Checkbox for selecting a match -->
                                                                    <td>
                                                                        <input type="checkbox" name="selected_matches[{{ $category->{'@id'} }}][]" value="{{ $match->{'@id'} ?? '' }}">
                                                                    </td>
                                                                    <td>{{ $match->{'@id'} ?? 'N/A' }}</td>
                                                                    <td>{{ $category->{'@id'} ?? 'N/A' }}</td>
                                                                    <td>{{ $match->localteam->{'@name'} ?? 'N/A' }}</td>
                                                                    <td>{{ $match->awayteam->{'@name'} ?? 'N/A' }}</td>
                                                                    <td>{{ $match->{'@status'} ?? 'N/A' }}</td>
                                                                </tr>
                                                            @else
                                                                <!-- Handle the case where $match is not an object -->
                                                                <tr>
                                                                    <td colspan="5">Invalid match data</td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        <!-- Handle the case where no matches are available -->
                                                        <tr>
                                                            <td colspan="5">No matches available</td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                                </tbody>




                                            </table>
                                        </div>
                                    @endif
                                    @if($category_name=="Volleyball")
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                <tr>
                                                    <th>Select</th> <!-- Checkbox header -->
                                                    <th>Match ID</th>
                                                    <th>League ID</th>
                                                    <th>Home team</th>
                                                    <th>Away team</th>
                                                    <th>Status</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach ($data->scores->category as $category)
                                                    @if (isset($category->matches->match) && is_array($category->matches->match))
                                                        @foreach ($category->matches->match as $match)
                                                            @if (is_object($match))
                                                                <tr>
                                                                    <input type="hidden" name="league_id[{{ $category->{'@id'} }}]" value="{{ $category->{'@id'} ?? '' }}">
                                                                    <input type="hidden" name="category" value="Volleyball">

                                                                    <!-- Checkbox for selecting a match -->
                                                                    <td>
                                                                        <input type="checkbox" name="selected_matches[{{ $category->{'@id'} }}][]" value="{{ $match->{'@id'} ?? '' }}">
                                                                    </td>
                                                                    <td>{{ $match->{'@id'} ?? 'N/A' }}</td>
                                                                    <td>{{ $category->{'@id'} ?? 'N/A' }}</td>
                                                                    <td>{{ $match->localteam->{'@name'} ?? 'N/A' }}</td>
                                                                    <td>{{ $match->awayteam->{'@name'} ?? 'N/A' }}</td>
                                                                    <td>{{ $match->{'@status'} ?? 'N/A' }}</td>
                                                                </tr>
                                                            @else
                                                                <!-- Handle the case where $match is not an object -->
                                                                <tr>
                                                                    <td colspan="5">Invalid match data</td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        <!-- Handle the case where no matches are available -->
                                                        <tr>
                                                            <td colspan="5">No matches available</td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                                </tbody>





                                            </table>
                                        </div>
                                    @endif
                                    @if($category_name=="Football")
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                <tr>
                                                    <th>Select</th> <!-- Checkbox header -->
                                                    <th>Match ID</th>
                                                    <th>League ID</th>
                                                    <th>Home team</th>
                                                    <th>Away team</th>
                                                    <th>Status</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach ($data->scores->category as $category)
                                                    @if (isset($category->matches->match) && is_array($category->matches->match))
                                                        @foreach ($category->matches->match as  $match)
                                                            @if (is_object($match))
                                                                <tr>
                                                                    <input type="hidden" name="league_id[{{ $category->{'@id'} }}]" value="{{ $category->{'@id'} ?? '' }}">
                                                                    <input type="hidden" name="category" value="Football">

                                                                    <!-- Checkbox for selecting a match -->
                                                                    <td>
                                                                        <input type="checkbox" name="selected_matches[{{ $category->{'@id'} }}][]" value="{{ $match->{'@id'} ?? '' }}">
                                                                    </td>
                                                                    <td>{{ $match->{'@id'} ?? 'N/A' }}</td>
                                                                    <td>{{ $category->{'@id'} ?? 'N/A' }}</td>
                                                                    <td>{{ $match->localteam->{'@name'} ?? 'N/A' }}</td>
                                                                    <td>{{ $match->awayteam->{'@name'} ?? 'N/A' }}</td>
                                                                    <td>{{ $match->{'@status'} ?? 'N/A' }}</td>
                                                                </tr>
                                                            @else
                                                                <!-- Handle the case where $match is not an object -->
                                                                <tr>
                                                                    <td colspan="5">Invalid match data</td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        <!-- Handle the case where no matches are available -->
                                                        <tr>
                                                            <td colspan="5">No matches available</td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                                </tbody>

                                            </table>
                                        </div>
                                    @endif
                                    @if($category_name=="Rugby Union")
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                <tr>
                                                    <th>Select</th> <!-- Checkbox header -->
                                                    <th>Match ID</th>
                                                    <th>League ID</th>
                                                    <th>Home team</th>
                                                    <th>Away team</th>
                                                    <th>Status</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach ($data->scores->category as $category)
                                                    @if (isset($category->matches->match) && is_array($category->matches->match))
                                                        @foreach ($category->matches->match as $match)
                                                            @if (is_object($match))
                                                                <tr>
                                                                    <input type="hidden" name="league_id[{{ $category->id }}]" value="{{ $category->id ?? '' }}">
                                                                    <input type="hidden" name="category" value="Rugby Union">

                                                                    <!-- Checkbox for selecting a match -->
                                                                    <td>
                                                                        <input type="checkbox" name="selected_matches[{{ $category->id }}][]" value="{{ $match->id ?? '' }}">
                                                                    </td>
                                                                    <td>{{ $match->id ?? 'N/A' }}</td>
                                                                    <td>{{ $category->id ?? 'N/A' }}</td>
                                                                    <td>{{ $match->localteam->name ?? 'N/A' }}</td>
                                                                    <td>{{ $match->awayteam->name ?? 'N/A' }}</td>
                                                                    <td>{{ $match->status ?? 'N/A' }}</td>
                                                                </tr>
                                                            @else
                                                                <!-- Handle the case where $match is not an object -->
                                                                <tr>
                                                                    <td colspan="5">Invalid match data</td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        <!-- Handle the case where no matches are available -->
                                                        <tr>
                                                            <td colspan="5">No matches available</td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                                </tbody>


                                            </table>
                                        </div>
                                    @endif
                                    @if($category_name=="Esports")
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                <tr>
                                                    <th>Select</th> <!-- Checkbox header -->
                                                    <th>Match ID</th>
                                                    <th>Home team</th>
                                                    <th>Away team</th>
                                                    <th>Status</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach ($data->scores->match as $match)
                                                    @if (is_object($match))
                                                        <tr>
                                                            <input type="hidden" name="league_id" value="{{ $match->{'@league_id'} ?? ''  }}">
                                                            <input type="hidden" name="category" value="Esports">
                                                            <td>
                                                                <input type="checkbox" name="selected_match[{{ $match->{'@league_id'} }}]" value="{{ $match->{'@id'} ?? '' }}">
                                                            </td>
                                                            <td>{{ $match->{'@id'} ?? 'N/A' }}</td>
                                                            <td>{{ $match->localteam->{'@name'} ?? 'N/A' }}</td>
                                                            <td>{{ $match->awayteam->{'@name'} ?? 'N/A' }}</td>
                                                            <td>{{ $match->{'@status'} ?? 'N/A' }}</td>
                                                        </tr>
                                                    @else
                                                        <!-- Handle the case where $match is not an object -->
                                                        <tr>
                                                            <td colspan="5">Invalid match data</td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                                </tbody>



                                            </table>
                                        </div>
                                    @endif
                                    @if($category_name=="MMA")
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                <tr>
                                                    <th>Select</th> <!-- Checkbox header -->
                                                    <th>Match ID</th>
                                                    <th>League ID</th>
                                                    <th>Home team</th>
                                                    <th>Away team</th>
                                                    <th>Status</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach ($data->scores->category as $category)
                                                    @foreach ($category->matches->match as $match)
                                                        <tr>
                                                            <input type="hidden" name="league_id[{{ $category->{'@id'} }}]" value="{{ $category->{'@id'} ?? '' }}">
                                                            <input type="hidden" name="category" value="MMA">

                                                            <!-- Checkbox for selecting a match -->
                                                            <td>
                                                                <input type="checkbox" name="selected_matches[{{ $category->{'@id'} }}][]" value="{{ $match->{'@id'} ?? '' }}">
                                                            </td>
                                                            <td>{{ $match->{'@id'} ?? 'N/A' }}</td>
                                                            <td>{{ $category->{'@id'} ?? 'N/A' }}</td>
                                                            <td>{{ $match->localteam->{'@name'} ?? 'N/A' }}</td>
                                                            <td>{{ $match->awayteam->{'@name'} ?? 'N/A' }}</td>
                                                            <td>{{ $match->{'@status'} ?? 'N/A' }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                                </tbody>




                                            </table>
                                        </div>
                                    @endif
                                @else
                                    <p>No match data available for this category.</p>
                                @endif
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </form>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
