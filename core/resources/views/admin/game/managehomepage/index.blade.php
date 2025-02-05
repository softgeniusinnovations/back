@extends('admin.layouts.app')

@section('panel')

    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="row">
                        <!-- Form for Managing Live Games -->
                        <div class="col-lg-12 p-3">
                            <form action="{{ route('admin.manageLiveGame') }}" method="POST">
                                @csrf
                                <!-- Select Category -->
                                <div class="mb-3">
                                    <label for="categorySelect" class="form-label">Select Category</label>
                                    <select name="category_name" id="categorySelect" class="form-control" required>
                                        <option value="" disabled selected>Select a category</option>
                                        @foreach($data as $category)
{{--                                            @if($category->in_play)--}}

                                                <option value="{{ json_encode(['in_play' => $category->in_play, 'name' => $category->name]) }}">{{ $category->name }}</option>
{{--                                            @endif--}}
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Text Field -->
{{--                                <div class="mb-3">--}}
{{--                                    <label for="entryText" class="form-label">Match Id</label>--}}
{{--                                    <input type="text" name="match_id" id="match_id" class="form-control" placeholder="Enter match id, comma-separated" required>--}}
{{--                                </div>--}}

                                <!-- Submit Button -->
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </form>
                        </div>

                        <!-- Table to Display Event Data -->
                        <div class="col-lg-12 p-3">
                            <h4 class="mb-3">Game Showing in Home page</h4>
                            @if(isset($homepagegames) && count($homepagegames) > 0)
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Category name</th>
                                        <th>Match id</th>
                                        <th>Match name</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($homepagegames as $homepagegamei => $homepagegame)
                                        <tr>
                                            <td>{{ $homepagegame->id }}</td>
                                            <td>{{ $homepagegame->category_name }}</td>
                                            <td>{{ $homepagegame->match_id }}</td>
                                            <td>{{ $homepagegame->match_name }}</td>
                                            <td>
                                                <form action="{{ route('admin.homepagegame.destroy', $homepagegame->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this game?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p>No event data available.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-confirmation-modal />
@endsection

@push('style')
    <style>
        .table {
            margin-top: 20px;
        }
    </style>
@endpush

@push('script')
    <script>
        document.getElementById("categorySelect").addEventListener("change", function() {
            let selectedData = JSON.parse(this.value);
            document.getElementById("categoryName").value = selectedData.name;
        });
    </script>
@endpush
