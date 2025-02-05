@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-4 mx-auto">
            <div class="card b-radius--10">
                <div class="card-body p-5">
                	<div class="text-center mb-5">
                		<h5>Team Image upload</h5>
                		<p>Only jpeg file supported</p>
                	</div>

                    <form action="{{route('admin.goal.team.image.upload')}}" enctype="multipart/form-data" method="POST" class="text-center">
                    	@csrf
                    	<label for="team">Team Name ( Don't change the team name )</label>
                    	<input type="text" name="team" id="team" placeholder="Team name" class="form-control mb-3" />
                    	<input type="file" class="form-control" name="image" accept="jpeg" />
                    	<button class="btn btn-primary mt-3">Upload</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

