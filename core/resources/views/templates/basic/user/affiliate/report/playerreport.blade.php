@extends($activeTemplate . 'layouts.master')
@section('master')
<section>
    <div>
        <form action="{{ route('affiliate.report.playerreport') }}" method="GET">
            <div class="row">
                <div class="col-sm-3 form-group">
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

                <div class="col-sm-3 form-group">
                    <label for="formGroupExampleInput">Country</label>
                    <select id="country" class="form-control" name="country">
                        <option selected value="">Choose Country</option>
                        @foreach ($countries as $key => $item)
                        <option value="{{ $key }}" {{ request()->input('currency') == $key ? 'selected' : '' }}>
                            {{ $item->country }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-3">
                    <label for="formGroupExampleInput">Marketing tool ID</label>
                    <input type="text" class="form-control" name="marketingId">
                </div>

                <div class="col-sm-3 form-group">
                    <label for="formGroupExampleInput">Website</label>
                    <select id="inputState" class="form-control" value="website">
                        <option selected value="">Choose website</option>
                        @foreach ($website as $item)
                            <option value="{{ $item->website }}">{{ $item->website }}</option>
                        @endforeach
                    </select>
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

            <div class="col-sm-4 form-group">
                <label for="formGroupExampleInput">Date Interval</label>
                <input type="text" class="form-control" name="dates" value="{{ request()->input('dates') }}">
            </div>

            <div class="col-sm-3">
                <label for="formGroupExampleInput">Player Id</label>
                <input type="text" class="form-control" name="playerId" value="{{ request()->input('playerId') }}">
            </div>
            <div class="col-sm-3 form-group">
                <label for="playerType">Player Type</label>
                <select class="form-control" name="playerType" id="playerType">
                    <option value="">Select player type</option>
                    <option value="all">All</option>
                    <option value="new">New</option>
                    <option value="old">Old</option>
                </select>
            </div>
            <div class="col-sm-3 form-group">
                <br>
                <button type="submit" class="btn btn-primary">Generate Report</button>
            </div>

    </div>
    </form>
    </div>

    <div class="table-responsive">
        <table class="table-sm custom--table table table-striped table-bordered border-dark">
            <thead>
                <tr>
                    <th>Website Id</th>
                    <th>Website</th>
                    <th>SubId</th>
                    <th>Player ID</th>
                    <th>Registration Date</th>
                    <th>Country</th>
                    <th>Currency</th>
                    <th>Sum of all deposit</th>
                    <th>Company Profit Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($affiliatepromos as $key => $value)
                @php
                $dates = explode(' - ', request()->input('dates'));
                $from = isset($dates[0]) ? date('Y-m-d H:i:s', strtotime($dates[0])) : null;
                $to = isset($dates[1]) ? date('Y-m-d 23:59:59', strtotime($dates[1])) : null;
                    
                $deposits = App\Models\Deposit::where('user_id', $value->better_user_id)
                    ->where('status', 1)
                    ->when($from && $to, function ($query) use ($from, $to) {
                        return $query->whereBetween('created_at', [$from, $to]);
                    })
                    ->sum('final_amo');

                $transactionSums1 = App\Models\Bet::where('user_id', $value->better_user_id)
                ->where('status', 1)
                ->when($from && $to, function ($query) use ($from, $to) {
                        return $query->whereBetween('created_at', [$from, $to]);
                    })
                ->sum('stake_amount');

                $transactionSums2 = App\Models\Bet::where('user_id', $value->better_user_id)
                ->where('status', 3)
                ->when($from && $to, function ($query) use ($from, $to) {
                        return $query->whereBetween('created_at', [$from, $to]);
                    })
                ->sum('stake_amount');

                $commission = $transactionSums2 - $transactionSums1;
                @endphp
                <tr>
                    <td>{{ $value->websiteId ?? "-" }}</td>
                    <td>{{ $value->website ?? "-" }}</td>
                    <td>{{ optional($value->promo)->promo_code }}</td>
                    <td>{{optional($value->betterUser)->user_id}}</td>
                    <td>{{ $value->created_at->format('d-M-Y') }} <br />{{ $value->created_at->format('h:i A') }}</td>
                    <td>{{optional($value->betterUser)->country_code}}</td>
                    <td>{{optional($value->betterUser)->currency}}</td>
                    <td>
                        {{ showAmount($deposits) }}
                    </td>
                    <td> <span class="text--{{ $commission >= 0 ? 'success' : 'danger' }}">
                            {{ $commission >= 0 ? '+' : '-' }}{{ showAmount(abs($commission)) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            <tbody>
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
