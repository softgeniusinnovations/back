@extends('admin.layouts.app')

@section('panel')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Activity Logs</h5>
        </div>
        <div class="card-body">
            @if ($logs->isEmpty())
                <div class="alert alert-info text-center">
                    No activity logs found.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark">
                        <tr>
                            <th>Log name</th>
                            <th>Action</th>
                            <th>Performed By</th>
                            <th>Performed On</th>
                            <th>Description</th>
                            <th>Time</th>
                            <th>Modified Colum</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($logs as $log)
                            <tr class="{{ $log->event === 'deleted' ? 'table-danger' : '' }}">
                                <td>{{ $log->log_name }}</td>
                                <td>{{ $log->event }}</td>
                                <td>{{ $log->causer_id ?? 'System' }}</td>
                                <td>{{  'N/A' }}</td>


                                <td>{{ $log->description ?? 'N/A' }}</td>

                                <td>{{ $log->created_at }}</td>
                                <td>
                                    @if(isset($log->properties))
                                        <ul>
                                            @foreach ($log->properties->toArray() as $key => $value)
                                                <li><strong>{{ ucfirst($key) }}:</strong>
                                                    {{ is_array($value) ? json_encode($value) : $value }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        N/A
                                    @endif
                                </td>
{{--                                <td>{{ json_encode($log->properties->toArray()) }}</td>--}}


                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('style')
    <style>
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            background: #f8f9fa;
            padding: 5px;
            border-radius: 5px;
        }

        .table-danger {
            background-color: #f8d7da;
        }
    </style>
@endpush

@push('script')
    <script>
        // Add any necessary JavaScript if needed
    </script>
@endpush
