@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="row">
                        <!-- Table to Display Event Data -->
                        <div class="col-lg-12 p-3">
                            <h4 class="mb-3">Event Data</h4>
                            @if(isset($events))
                                <form action="{{ route('admin.storeLiveGame') }}" method="POST" id="event-form">
                                    @csrf
                                    <input type="hidden" name="category" value="{{ $category ?? '' }}">
                                    @if($category=="Cricket")
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                <tr>
                                                    <th>Select</th> <!-- Checkbox header -->
                                                    <th>Match ID</th>
                                                    <th>League ID</th>
                                                    <th>Home Team</th>
                                                    <th>Away Team</th>
{{--                                                    <th>Status</th>--}}
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($events['data']['results'] as $index=> $match)
                                                    <tr>

                                                        <td>
                                                            <input type="checkbox" class="event-checkbox" name="event_ids[]" value="{{ $match['id']??'' }}">
                                                        </td>
                                                        {{--                                                        <td>--}}
                                                        {{--                                                            <input type="checkbox" name="match_ids[]" value="{{ $match['id'] }}">--}}
                                                        {{--                                                        </td>--}}
                                                        <td>{{ $match['id'] }}</td>
                                                        <td>{{ $match['league']['id'] }}</td>
                                                        <td>{{ $match['home']['name'] }}</td>
                                                        <td>{{ $match['away']['name'] }}</td>
{{--                                                        <td>{{ $match['time_status'] }}</td>--}}
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                    @else
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                <tr>
                                                    <th>
                                                        <input type="checkbox" id="select-all">
                                                    </th>
                                                    <th>Category</th>
                                                    <th>Event ID</th>
                                                    <th>Event Name</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($events as $eventId => $eventData)
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" class="event-checkbox" name="event_ids[]" value="{{ $eventId }}">
                                                        </td>
                                                        <td>{{ $category ?? 'N/A' }}</td>
                                                        <td>{{ $eventId }}</td>
                                                        <td>{{ $eventData['info']['name'] }}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif

                                    <button type="submit" class="btn btn-primary mt-3">Submit</button>
                                </form>
                            @else
                                <p>No event data available.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-confirmation-modal />
@endsection

@push('style')
    <style>
        .table {
            margin-top: 20px;
        }
    </style>
@endpush

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Select All Checkboxes
            const selectAll = document.getElementById('select-all');
            const checkboxes = document.querySelectorAll('.event-checkbox');

            selectAll.addEventListener('change', function () {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = selectAll.checked;
                });
            });
        });
    </script>
@endpush
