@extends($activeTemplate . 'layouts.master')
@section('master')
<section>
    <div>
        <form action="{{ route('affiliate.report.fullreport') }}" method="GET">
            <div class="row">
                <div class="col-sm-4 form-group">
                    <label for="formGroupExampleInput">Currency</label>
                    <select id="inputState" class="form-control" name="currency">
                        <option selected value="BDT">BDT</option>
                        <!-- @foreach ($currency as $item)
                        <option value="{{ $item->currency_code }}" {{ request()->input('currency') == $item->currency_code ? 'selected' : '' }}>
                            {{ $item->currency_code }}
                        </option>
                        @endforeach -->
                    </select>
                </div>

                <div class="col-sm-3 form-group">
                    <label for="formGroupExampleInput">Website</label>
                    <select id="inputState" class="form-control" name="website">
                        <option selected value="">ALL</option>
                        @foreach ($website as $item)
                        <option value="{{ $item->website }}">{{ $item->website }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-3">
                    <label for="formGroupExampleInput">Marketing tool ID</label>
                    <input type="text" class="form-control" name="marketingId">
                </div>

                <div class="col-sm-4 form-group">
                    <label for="formGroupExampleInput">Date Interval</label>
                    <input type="text" class="form-control" name="dates" value="{{ request()->input('dates') }}">
                </div>

                <div class="col-sm-3 form-group">
                    <br>
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </div>

            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table-responsive-sm table-border-dark table-sm custom--table table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Website ID</th>
                    <th>Website</th>
                    <th>Registrations</th>
                    <th>New depositors</th>
                    <th>Total deposit amount</th>
                    <th>Bonus Amount</th>
                    <th>Company Profit</th>
                    <th>Commission amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($website1 as $key => $value)
                @php
                    $dates = explode(' - ', request()->input('dates'));
                    $from = isset($dates[0]) ? date('Y-m-d H:i:s', strtotime($dates[0])) : null;
                    $to = isset($dates[1]) ? date('Y-m-d H:i:s', strtotime($dates[1])) : null;

                $userData = App\Models\AffiliatePromos::where('affliate_user_id', auth()->id())
                            ->where('website', $value)->get();
                $userIds = $userData->pluck('better_user_id');

                $deposits = App\Models\Deposit::whereIn('user_id', $userIds)
                            ->where('status', 1)
                            ->when($from && $to, function ($query) use ($from, $to) {
                                return $query->whereBetween('created_at', [$from, $to]);
                            })
                            ->get();
                $newDepositors = $deposits->groupBy('user_id')->count();
                $totalAmount = $deposits->sum('final_amo');

            $totalBonus = App\Models\User::whereIn('id', $userIds)
                ->where('status', 1)
                ->when($from && $to, function ($query) use ($from, $to) {
                    return $query->whereBetween('created_at', [$from, $to]);
                })
                ->sum('bonus_account');

                $transactionSums = App\Models\AffiliateCommissionTransaction::whereIn('user_id', $userIds)
                ->whereIn('result', [1, 2])
                ->when($from && $to, function ($query) use ($from, $to) {
                    return $query->whereBetween('created_at', [$from, $to]);
                })
                ->groupBy('result')
                ->selectRaw('result, sum(amount) as sum')
                ->pluck('sum', 'result');
                $earn = $transactionSums[1] ?? 0;
                $loss = $transactionSums[2] ?? 0;

                $commission = $earn - $loss;


                $companyProfit = App\Models\Bet::whereIn('user_id', $userIds)
                ->where('status', [1, 3])
                ->when($from && $to, function ($query) use ($from, $to) {
                return $query->whereBetween('created_at', [$from, $to]);
                })
                ->groupBy('status')
                ->selectRaw('status, sum(stake_amount) as sum')
                ->pluck('sum', 'status');

                $companyProfitWin = $companyProfit[1] ?? 0;
                $companyProfitLoss = $companyProfit[2] ?? 0;
                $companyProf = $companyProfitLoss - $companyProfitWin;
                @endphp
                <tr>
                    <td>{{ $key }}</td>
                    <td>{{ $value }}</td>
                    <td>
                        {{ $userData->count() }}
                    </td>
                    <td>
                        {{ $newDepositors }}
                    </td>
                    <td>
                         {{ showAmount($totalAmount) }} 
                    </td>
                    <td>
                         {{ showAmount($totalBonus) }} 
                    </td>
                    <td>
                       <span class="text--{{ $companyProf >= 0 ? 'success' : 'danger' }}">
                            {{ $companyProf >= 0 ? '+' : '-' }}{{ showAmount(abs($companyProf)) }}
                        </span>
                    </td>
                    <td>
                       <span class="text--{{ $commission >= 0 ? 'success' : 'danger' }}">
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
