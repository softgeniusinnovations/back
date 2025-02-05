@extends($activeTemplate . 'layouts.frontend')
@section('frontend')
<div class="container">
    <div class="row">
        <div class="col-md-12 mb-2">
            <h5>Casino bet history</h5>
            <table class="table-responsive-sm table-sm custom--table table table-striped table-bordered">
              <thead>
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">Date time</th>
                  <th scope="col">Session ID</th>
                  <th scope="col">Game Name</th>
                  <th scope="col">Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($data as $key=>$item)
                    <tr>
                      <td>{{++$key}}</td>
                      <td>{{$item->created_at}}</td>
                      <td>{{$item->session_id}}</td>
                      <td>{{$item->game_name}}</td>
                      <td><a href="{{route('casino.session', $item->session_id)}}" class="btn-primary">Details</a></td>
                    </tr>
                @empty
                <tr>
                    <td colspan="3">No data found</td>
                    <td></td>
                </tr>
                @endforelse
              </tbody>
            </table>
            <div class="float-end mt-2">
                {{ $data->links() }}
            </div>
        </div>
    </div>
</div>
@endsection