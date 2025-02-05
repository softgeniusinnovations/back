@extends($activeTemplate . 'layouts.master')
@section('master')
<section>
    <div>
        <form action="{{ route('affiliate.report.summery') }}" method="GET">
            <div class="row">
                <div class="col-4 form-group">
                    <label for="formGroupExampleInput">Currency</label>
                    <select id="inputState" class="form-control" name="currency">
                        <option selected value="">Choose Currency</option>
                        @foreach ($currency as $item)
                        <option value="{{ $item->currency_code }}" {{ request()->input('currency') == $item->currency_code ? 'selected' : '' }}>
                            {{ $item->currency_code }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-4 form-group">
                    <label for="formGroupExampleInput">Website</label>
                    <select id="inputState" class="form-control" value="website">
                        <option selected value="">Choose website</option>
                            @foreach ($website as $item)
                                <option value="{{ $item->website }}">{{ $item->website }}</option>
                            @endforeach
                    </select>
                </div>
                <div class="col-sm-4">
                    <label for="formGroupExampleInput">Marketing tool ID</label>
                    <input type="text" class="form-control" name="marketingId">
                </div>
                {{-- <div class="col-sm-3 form-group">
                    <label for="formGroupExampleInput">Time interval</label>
                    <select id="inputState" class="form-control" name="interval">
                        <option value="">Choose one Option</option>
                        <option value="today" {{ request()->input('interval') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ request()->input('interval') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="last7days" {{ request()->input('interval') == 'last7days' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="last30days" {{ request()->input('interval') == 'last30days' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="thismonth" {{ request()->input('interval') == 'thismonth' ? 'selected' : '' }}>This Month</option>
                        <option value="lastmonth" {{ request()->input('interval') == 'lastmonth' ? 'selected' : '' }}>Last Month</option>
                    </select>
                </div> --}}

                <div class="col-sm-5 form-group">
                    <input type="text" class="form-control" name="dates" value="{{ request()->input('dates') }}">
                </div>

                <div class="col-sm-3 form-group">
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </div>

            </div>
        </form>
    </div>

    <div>
        <table class="table table-striped">
            <tbody>
                <tr>
                    <td>Views</td>
                    <td>{{$view}}</td>
                </tr>
                <tr>
                    <td>Clicks</td>
                    <td>{{$view}}</td>
                </tr>
                <tr>
                    <td>Driect links</td>
                    <td>{{ $webCount }}</td>
                </tr>
                <tr>
                    <td>Click/Views</td>
                    <td>{{ $view != 0 ? ($view / $view) * 100 : 0 }} %</td>
                </tr>
                <tr>
                    <td>Registrations</td>
                    <td>{{ $registration }}</td>
                </tr>
                <tr>
                    <td>Registrations/Click ratio</td>
                    <td>{{ $view != 0 ? number_format(($registration / $view) * 100, 2) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Registrations with deposit</td>
                    <td>{{ $newDepositor }}</td>
                </tr>
                <tr>
                    <td>Registrations with deposit/Registration ratio</td>
                    <td>{{ $registration != 0 ? number_format(($newDepositor / $registration) * 100, 2) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Total new deposit amount</td>
                    <td>{{ number_format($newDepositorAmount), 2}}</td>
                </tr>
                <tr>
                    <td>New depositors</td>
                    <td>{{ $newDepositor }}</td>
                </tr>
                <tr>
                    <td>Accounts with deposits</td>
                    <td>{{number_format($allDepositAmount), 2}}</td>
                </tr>
                <tr>
                    <td>Total Profit</td>
                    <td class="{{ $profit > 0 ? 'text-success' : '' }}">{{number_format($profit, 2)}}</td>
                </tr>
                <tr>
                    <td>Total Loss</td>
                    <td class="{{ $loss < 0 ? 'text-danger' : 'text-danger' }}">{{number_format($loss, 2)}}</td>
                </tr>
                <tr>
                    <td>Revenue</td>
                    <td class="{{ $revenue < 0 ? 'text-danger' : '' }}">{{ number_format($revenue, 2) }}</td>
                </tr>
                <!-- <tr>
                    <td>Number of Revenue</td>
                    <td>0</td>
                </tr> -->
                <tr>
                    <td>Active Player</td>
                    <td>{{$activepalyer}}</td>
                </tr>
                <tr>
                    <td>Bonus Amount</td>
                    <td class="{{ $bonus >= 0 ? 'text-success' : 'text-danger' }}">{{number_format($bonus, 2)}}</td>
                </tr>
                <tr>
                    <td>Referral Commission</td>
                    <td class="{{ $revenue < 0 ? 'text-danger' : '' }}">{{number_format($revenue , 2 )}}</td>
                </tr>

            </tbody>
        </table>
    </div>
</section>
@endsection
@push('style')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    .daterangepicker td.in-range {
        background-color: #357ebd;
        border-color: transparent;
        color: #ffffff;
        border-radius: 0;
    }

</style>
@endpush
@push('script')
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    $('input[name="dates"]').daterangepicker();

</script>
@endpush
