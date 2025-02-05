@extends($activeTemplate . 'layouts.frontend')
@section('frontend')
<div class="container">
    <div class="row">
        <div class="col-md-12 mb-2">
            <h5>Casino {{$session}} History</h5>
            <table class="table-responsive-sm table-sm custom--table table table-striped table-bordered">
              <thead>
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">Id</th>
                  <th scope="col">BetInfo</th>
                  <th scope="col">GameId</th>
                  <th scope="col">GameName</th>
                  <th scope="col">Before</th>
                  <th scope="col">Bet</th>
                  <th scope="col">Win</th>
                  <th scope="col">Date time</th>
                </tr>
              </thead>
              <tbody>
                 @isset($data['content']['log'])
                     @forelse($data['content']['log'] as $key=>$item)
                        <tr>
                          <td>{{++$key}}</td>
                          <td>{{$item['id']}}</td>
                          <td>{{$item['BetInfo']}}</td>
                          <td>{{$item['gameId']}}</td>
                          <td>{{$item['gameName']}}</td>
                          <td>{{$item['before']}}</td>
                          <td>{{$item['bet']}}</td>
                          <td>{{$item['win']}}</td>
                          <td>{{$item['dateTime']}}</td>
                        </tr>
                    @empty
                    <tr>
                        <td colspan="8">No data found</td>
                        <td></td>
                    </tr>
                    @endforelse
                 @endisset
              </tbody>
            </table>
        </div>
    </div>
</div>
@endsection