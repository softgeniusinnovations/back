@extends($activeTemplate . 'layouts.master')
@section('master')
<div class="card custom--card"">
    <h5 class=" card-header">
    <i class="las la-ticket-alt"></i>
    @lang('Events')
    </h5>
</div>

<div class="pt-4">
    <div class="row">
        @foreach($news as $key => $item)
        <div class="col-lg-4 mb-3">
            <a href="{{ route('user.deposit.index', ['id' => encrypt($item->id)]) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm" style="background: transparent">
                    <div class="card-body" style="background-image: url('{{ asset('assets/news/' . $item->image) }}'); background-size: cover;">
                        <div style="height: 120px"></div>
                    </div>
                    <div class="card-footer bg-dark border-0 m-0">
                        <p class="m-0 p-0
                        text-white">{{ __($item->title) }}</p>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>
</div>
@endsection
@push('script')
    <script>
        $(document).ready(function(){
            $('.delete_btn').on('click', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var url = "{{ route('user.news.destroy', ':id') }}";
                url = url.replace(':id', id);
                var csrf_token = $('meta[name="csrf-token"]').attr('content');
                
                Swal.fire({
                    title: "@lang('Are you sure to delete this news?')",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#276678",
                    confirmButtonText: "@lang('Yes')",
                    cancelButtonText: "@lang('No')",
                    reverseButtons: true
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: "DELETE",
                            data: {
                                "id": id,
                                "_token": csrf_token
                            },
                            success: function(response) {
                                Swal.fire({
                                icon: 'success'
                                , title: 'News Deleted Successfully!'
                                , showConfirmButton: false
                                , timer: 1500
                            });
                            window.location.reload();
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
