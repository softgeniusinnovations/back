@extends('admin.layouts.app')

@section('panel')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('Agent')</th>
                                    <th>@lang('User')</th>
                                    <th>@lang('Withdraw Amount | Withdraw Trx')</th>
                                    <th>@lang('Commission')</th>
                                    <th>@lang('Final Amount')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($comissions as $commission)
                                    @if(@$commission->user->id && @$commission->withdrawl->id)
                                    <tr>
                                        <td>
                                            {{ @$commission->agent->username }}
                                        </td>
                                        <td>
                                           @if( @$commission->user->id) <a href="{{ route('admin.users.detail', @$commission->user->id) }}">@
                                                {{ @$commission->user->username }}</a> @else Unknown @endif
                                        </td>

                                        <td>
                                            {{ @$commission->amount }} {{ $commission->user->currency }} <br />
                                            <small><a
                                                    href="{{ route('admin.withdraw.details', @$commission->withdrawl->id) }}">{{ @$commission->withdrawl->trx }}</a>
                                            </small>
                                        </td>
                                        <td>
                                            {{ @$commission->commision }} %
                                        </td>
                                        <td>
                                            {{ @$commission->final_amount }} {{ $commission->user->currency }}
                                        </td>
                                    @endif
                                    @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __('No data found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
                @if ($comissions->hasPages())
                    <div class="card-footer py-4">
                        @php echo paginateLinks($comissions) @endphp
                    </div>
                @endif
            </div><!-- card end -->
        </div>
    </div>
@endsection
