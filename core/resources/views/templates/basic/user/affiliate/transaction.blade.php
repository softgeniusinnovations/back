@extends($activeTemplate . 'layouts.master')
@section('master')
<section>
<div>
        <form action="" method="GET">
            <div class="row">
            {{-- <div class="col-3 form-group">
                    <label for="formGroupExampleInput">Currency</label>
                    <select id="inputState" class="form-control" name="currency">
                        <option selected value="">Choose Currency</option>
                         @foreach ($currency as $item)
                        <option value="{{ $item->currency_code }}" {{ request()->input('currency') == $item->currency_code ? 'selected' : '' }}>
                            {{ $item->currency_code }}
                        </option>
                        @endforeach
                    </select>
                </div>  --}}

                <div class="col-sm-5 form-group">
                <br>
                    <input type="text" class="form-control" name="dates" value="{{ request()->input('dates') }}">
                </div>

                <div class="col-sm-3 form-group">
                <br>
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </div>

            </div>
        </form>
    </div>
    <div>
        <table class="table-responsive--md custom--table custom--table-separate table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>@lang('User ID')</th>
                    <th>@lang('Promo Code')</th>
                    <th>@lang('Amount')</th>
                    <th>@lang('Date/Time')</th>
                </tr>
            </thead>

            <tbody>
                @php
                    $i=0;
                @endphp
                @forelse ($transaction as $item)
                    <tr>
                        <td>{{ ++$i }}</td>
                        <td>{{ optional($item->better_details)->username }}</td>
                        <td>{{ optional($item->promo_details)->promo_code }}</td>
                        <td>
                            @if($item->result == 1)
                                <span class="text-success">+{{ showAmount($item->amount) }}</span>
                            @elseif ($item->result == 2)
                                <span class="text-danger">-{{ showAmount($item->amount) }}</span>
                            @endif
                        </td>
                        <td>{{ $item->created_at }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                    </tr>
                @endforelse
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
