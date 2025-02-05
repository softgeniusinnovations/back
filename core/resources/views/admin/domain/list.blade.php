@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <a href="{{ route('admin.domain.create') }}" class="btn btn-sm btn-primary my-2">Add Domain</a>
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('Logo')</th>
                                    <th>@lang('Domain')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($domains as $domain)
                                    {{-- @dd($domains) --}}
                                    <tr>
                                        <td><img src="{{ asset('/core/public/storage/' . $domain->logo)}}" alt="Logo" style="max-width: 100px;"></td>
                                        <td>{{ $domain->domain_name }}</td>
                                        <td>
                                            <button
                                                class="btn {{ $domain->status == 1 ? 'btn-primary' : 'btn-danger' }}">{{ $domain->status == 1 ? 'Active' : 'Inactive' }}</button>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.domain.edit', $domain->id) }}" title="Edit"
                                                class="btn btn-sm btn-outline--primary"><i class="fa fa-pencil"></i></a>
                                             <form action="{{ route('admin.domain.delete', $domain->id) }}" method="POST" style="display:inline;" id="delete-form-{{ $domain->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline--danger" title="Delete" onclick="confirmDelete({{ $domain->id }})">
                                                   <i class="fa fa-trash"></i>
                                                </button>
                                             </form>

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

                @if ($domains->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($domains) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
       function confirmDelete(domainId) {
            // Show confirmation dialog
            if (confirm('Are you sure you want to delete this domain?')) {
                  // If confirmed, submit the form
                  document.getElementById('delete-form-' + domainId).submit();
            }
         }
    </script>
@endpush
