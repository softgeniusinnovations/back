@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('ID')</th>
                                    <th>@lang('Identity')</th>
                                    <th>@lang('Agent')</th>
                                    <th>@lang('Email')</th>
                                    <th>@lang('Phone')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Mail Send')</th>
                                    <th>@lang('Arrived')</th>
                                    <th>@lang('Updated')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $index=>$user)
                                    <tr>
                                        <td>{{++$index}}</td>
                                        <td> {{@$user->agents->identity}}</td>
                                        <td> {{@$user->agents->username}}</td>
                                        <td> {{@$user->email}}</td>
                                        <td> {{@$user->agents->phone}}</td>
                                        <td>
                                            <button class="btn btn-sm 
                                                {{ $user->status == 0 ? 'btn-danger' : '' }}
                                                {{ $user->status == 1 ? 'btn-primary' : '' }}
                                                {{ $user->status == 2 ? 'btn-success' : '' }}">
                                                {{ $user->status == 0 ? 'General' : '' }}
                                                {{ $user->status == 1 ? 'Requested' : '' }}
                                                {{ $user->status == 2 ? 'Password Changed' : '' }}
                                             </button>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm 
                                                {{ $user->is_mail_send == 0 ? 'btn-warning' : '' }}
                                                {{ $user->is_mail_send == 1 ? 'btn-success' : '' }}">
                                                {{ $user->is_mail_send == 0 ? 'Pending' : '' }}
                                                {{ $user->is_mail_send == 1 ? 'Send' : '' }}
                                             </button>
                                        </td>
                                        <td> {{ \Carbon\Carbon::parse($user->created_at)->diffForHumans() }}</td>
                                        <td> {{ \Carbon\Carbon::parse($user->updated_at)->diffForHumans() }}</td>
                                        <td>
                                            <a class="btn btn-sm btn-primary" href="{{ route('admin.agent.password.request.edit', $user->id) }}">Change Password</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($data->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($data) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

