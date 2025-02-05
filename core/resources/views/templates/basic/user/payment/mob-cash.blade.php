@extends($activeTemplate . 'layouts.master')
@section('master')
    <form action="{{ route('user.deposit.insert') }}" method="post">
        @csrf
        <input name="currency" type="hidden">
        <div class="card custom--card">
            <div class="card-header">
                <h5 class="card-title">@lang($pageTitle)</h5>
            </div>
            <div class="card-body">
                <div class="local">
                    <p class="p-4 d-flex" style="background: #dfe8f9; gap:15px; border-radius:5px; color:#6189d5"><i
                            class="fa fa-info"
                            style="display: flex; width: 26px; height: 26px; align-items:center; justify-content:center; border-radius:50%; background: #6189d5; color: #fff"></i>
                        Recomanded agents</p>
                </div>

                <div class="form-group">
                    <div class="row">
                        <div class="form-group">
                            <input type="text" name="search" placeholder="Search agent" class="form-control mb-2">
                        </div>
                        <ul class="agents" style="max-height: 400px; overflow: hidden; overflow-y:auto">
                           
                        </ul>
                    </div>

                    <div class="col-md-12 mob-cash" style="display: none">
                        <p class="p-4 d-flex" style="background: #dfe8f9; gap:15px; border-radius:5px; color:#6189d5"><i
                                class="fa fa-info"
                                style="display: flex; width: 26px; height: 26px; align-items:center; justify-content:center; border-radius:50%; background: #6189d5; color: #fff"></i>
                            <span class="choose-data"></span>
                        </p>
                        <div class="form-group">
                            <label for="uname">Your Name</label>
                            <input type="text" value="" id="uname" name="depositor_name" class="form-control">
                            <input type="hidden" name="payment_gateway" value="cash">
                            <input type="hidden" name="agent">
                            <input type="hidden" name="amount" value="300">
                        </div>
                        <div class="form-group">
                            <label for="ybw">Your Phone Number</label>
                            <input type="text" value="" id="ybw" name="phone" class="form-control"
                                required>
                        </div>
                    </div>

                    <!--<div class="text-end make-payment-btn">-->
                    <!--    <button class="btn btn--xl btn--base mt-3" type="submit">@lang('Make Payment')</button>-->
                    <!--</div>-->
                </div>
            </div>
    </form>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";
            // $(document).on('click', '.choose-btn', function() {
            //     $('.mob-cash').show();
            //     $('.choose-btn').text('Choose');
            //     $(this).text('Thanks');
            //     var data = $(this).data();
            //     $('.choose-data').text(
            //         `You have to choose the agent number: ${data.phone} and address: ${data.address} to connect with.`
            //     )
            //     $('[name=agent]').val(data.id);
            // });

            $('[name=search]').on('keyup', function() {
                var key = $(this).val();
                if (key) {
                    $.ajax({
                        url: "{{ route('user.deposit.mobcash.agents') }}",
                        method: 'GET',
                        data: {
                            admin_id: key
                        },
                        success: function(data) {
                            var output = data.map((item, index) => (
                                `<li class="d-flex align-items-center justify-content-between py-1 px-2 mb-2"
                                style="background: #f1f1f1">
                                <a href="https://${item.telegram_link}"><p style="font-size: 12px; margin-bottom: 1px">Agent: ${item.identity} <br> Address:
                                    ${item.address}</p></a>
                            </li>`
                            ));
                            $('.agents').html('');
                            $('.agents').append(output);
                        }
                    })
                }
            });
        })(jQuery);
    </script>
@endpush
