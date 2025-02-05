@extends($activeTemplate . 'layouts.master')
@section('master')

<div>
    <form action="{{ route('affiliate.report.affiliatelinkgenarate') }}" method="post">
        @csrf
        <div class="row">
            <div class="col-sm-3 form-group">
                <label for="formGroupExampleInput">Website</label>
                <select id="inputState" class="form-control" name="website">
                    <option selected value="">Choose website</option>
                    @foreach ($website as $item)
                    <option value="{{ $item->website }} | {{ $item->id }}">{{ $item->website }}</option>
                    @endforeach
                </select>
            </div>

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

            <div class="col-sm-6 form-group">
                <label for="formGroupExampleInput">Campaign</label>
                <select id="campaign" class="form-control" name="campaign">
                    <option value="">Choose one Option</option>
                    <option value="Asia-EU">Asia-EU</option>
                    <option value="Asia-US">Asia-US</option>
                    <option value="EU-US">EU-US</option>
                    <option value="EU-Africa">EU-Africa</option>
                    <option value="US-Africa">US-Africa</option>
                    <option value="Asia-Africa">Asia-Africa</option>
                </select>
            </div>

            <div class="col-sm-3 form-group">
                <label for="landingpage">Landing page</label>
                <input type="text" class="form-control" id="landingpage" name="landingpage" value="{{ request()->input('landingpage') }}">
            </div>

            <div class="col-sm-3 form-group">
                <label for="subid">Sub ID</label>
                <select id="subid" class="form-control" name="subid" value="{{ request()->input('subid') }}">
                    <option value="{{$promo->promo_code}}">{{$promo->promo_code}}</option>
                </select>
            </div>

            <div class="col-sm-3 form-group">
                <br>
                <button type="submit" class="btn btn-primary">Generate Link</button>
            </div>

        </div>
    </form>
</div>

<div class="table-responsive">
    <table class="table-responsive table-sm custom--table table table-striped table-bordered border-dark">
        <thead>
            <tr>
                <th>No</th>
                <th>Website</th>
                <th>Status</th>
                <th>Landing Page</th>
                <th>SubId</th>
                <th>Generated link</th>
                <th>Currency</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($websiteList as $key => $item)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $item->aff_website }}</td>
                <td>
                    @if ($item->status == 1)
                    <span class="badge badge--success">Active</span>
                    @else
                    <span class="badge badge--danger">Inactive</span>
                    @endif
                </td>
                <td>{{ $item->landing_page }}</td>
                <td>{{ $item->subid }}</td>
                <td id="linkgenarate{{ $item->id }}" class="text-primary">{{ $item->linkgenarate }}</td>
                <td>{{ $item->currency }}</td>
                <td>
                    <div>
                        {{-- <a href="javascript:void(0);" class="btn-sm btn-outline-danger delete_btn" data-id="{{ $item->id }}"><i class="las la-trash"></i></a> --}}
                        <button onclick="copyLink({{ $item->id }})" class="btn btn-sm btn-outline-info"><i class="fas fa-copy"></i></button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
@push('script')
<script>
    $(document).ready(function() {
        window.copyLink = function(id) {
            /* Get the link field */
            var copyText = $("#linkgenarate" + id).text();

            /* Create a temporary text area element for the copy operation */
            var tempElement = $("<textarea></textarea>");
            tempElement.val(copyText);
            $("body").append(tempElement);

            /* Select the text inside the temporary text area */
            tempElement.select();

            /* Copy the text */
            document.execCommand("copy");

            /* Remove the temporary element */
            tempElement.remove();

            /* Show the copied text using SweetAlert */
            Swal.fire({
                icon: "success",
                title: "Link Copied",
                text: "Link copied to clipboard",
                timer: 1500,
                showConfirmButton: false,
            });
        }
    });

</script>
@endpush
