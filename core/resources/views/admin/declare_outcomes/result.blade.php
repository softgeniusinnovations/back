@extends('admin.layouts.app')

@section('panel')

    <div class="row">

        <div class="col-md-7">
            <div class="row">
                <div class="col-lg-12">

                    <button id="download-pdf" class="btn btn-primary">Download PDF</button>

                    <div class="card b-radius--10" id="pdf-content">
                        <table class="table table-bordered">
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
                                    <td>{{ @$match['league']['name'] }}</td>
                                    <td>{{ @$match['home']['name'] }}</td>
                                    <td>{{ @$match['away']['name'] }}</td>
                                    <td>{{ @$match['ss'] }}</td>
                                    <td>{{ @$match['stats']['possession_rt'][0] }}%
                                        - {{ @$match['stats']['possession_rt'][1] }}%
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <h2 class="mt-4">Match Statistics</h2>
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>Stat</th>
                                <th>{{ @$match['home']['name']}}</th>
                                <th
                                {{ $match['away']['name'] }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>Goals</td>
                                <td>{{ @$results[0]['stats']['goals'][0] }}</td>
                                <td>{{ @$results[0]['stats']['goals'][1] }}</td>
                            </tr>
                            <tr>
                                <td>Corners</td>
                                <td>{{ @$results[0]['stats']['corners'][0] }}</td>
                                <td>{{ @$results[0]['stats']['corners'][1] }}</td>
                            </tr>
                            <tr>
                                <td>Yellow Cards</td>
                                <td>{{ @$results[0]['stats']['yellowcards'][0] }}</td>
                                <td>{{ @$results[0]['stats']['yellowcards'][1] }}</td>
                            </tr>
                            <tr>
                                <td>Red Cards</td>
                                <td>{{ @$results[0]['stats']['redcards'][0] }}</td>
                                <td>{{ @$results[0]['stats']['redcards'][1] }}</td>
                            </tr>
                            <tr>
                                <td>Shots On Target</td>
                                <td>{{ @$results[0]['stats']['on_target'][0] }}</td>
                                <td>{{ @$results[0]['stats']['on_target'][1] }}</td>
                            </tr>
                            <tr>
                                <td>Possession</td>
                                <td>{{ @$results[0]['stats']['possession_rt'][0] }}%</td>
                                <td>{{ @$results[0]['stats']['possession_rt'][1] }}%</td>
                            </tr>
                            </tbody>
                        </table>

                        <h2 class="mt-4">Match Sts</h2>
                        <ul class="list-group">
                            <li class="list-group-item">
                                {{ @$results[0]['sts'] }}
                            </li>
                        </ul>

                        <h2 class="mt-4">Match Events</h2>
                        <ul class="list-group">
                            @foreach (@$results[0]['events'] as $event)
                                <li class="list-group-item">
                                    {{ @$event['text'] }}
                                </li>
                            @endforeach
                        </ul>

                        <div class="col-md-5">
                            <pre>{{ json_encode(@$results, JSON_PRETTY_PRINT) }}</pre>
                        </div>


                    </div>

                </div>
            </div>
        </div>



    </div>




    <x-confirmation-modal/>

@endsection



@push('breadcrumb-plugins')

    <x-search-form/>

@endpush



@push('style')



@endpush



@push('script')
    <!-- Add this in your Blade template's <head> section or before the closing </body> tag -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <script>
        document.getElementById('download-pdf').addEventListener('click', function () {
            // Select the content to capture
            const content = document.getElementById('pdf-content');

            // Use html2pdf to generate the PDF and trigger download
            html2pdf(content, {
                margin: 0.5,
                filename: 'results.pdf',
                image: {type: 'jpeg', quality: 0.98},
                html2canvas: {scale: 2},
                jsPDF: {unit: 'in', format: 'a4', orientation: 'portrait'}
            });
        });
    </script>

    {{--    <script>--}}
    {{--        document.getElementById('download-pdf').addEventListener('click', function () {--}}
    {{--            const results = @json($results); // Convert PHP $results to JavaScript object--}}

    {{--            // Send the results data to the backend--}}
    {{--            fetch('{{ route('download.pdf') }}', {--}}
    {{--                method: 'POST',--}}
    {{--                headers: {--}}
    {{--                    'Content-Type': 'application/json',--}}
    {{--                    'X-CSRF-TOKEN': '{{ csrf_token() }}'--}}
    {{--                },--}}
    {{--                body: JSON.stringify({ results })--}}
    {{--            })--}}
    {{--                .then(response => response.blob()) // Expect a PDF file in response--}}
    {{--                .then(blob => {--}}
    {{--                    // Create a link to download the PDF--}}
    {{--                    const link = document.createElement('a');--}}
    {{--                    link.href = window.URL.createObjectURL(blob);--}}
    {{--                    link.download = 'results.pdf';--}}
    {{--                    link.click();--}}
    {{--                })--}}
    {{--                .catch(error => console.error('Error downloading PDF:', error));--}}
    {{--        });--}}
    {{--    </script>--}}

@endpush

