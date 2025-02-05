<div class="modal fade" id="registerModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Registration Process</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body class text-center">
                <a class="btn btn--signup" href="{{ route('user.register') }}"> @lang('Full Registration') </a>
                <a class="btn btn--signup" href="{{ route('user.oneclick.register') }}"> @lang('One Click Registration') </a>
            </div>
        </div>
    </div>
</div>