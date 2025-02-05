@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-4 mx-auto">
            <div class="card b-radius--10">
                <div class="card-body">
                    <!-- Header Line with User Details -->
                    <div class="text-center mb-4">
                        <h4>Password Reset</h4>
                        <p><strong>Username:</strong> {{ $user->agents->username }}</p>
                        <p><strong>Email:</strong> {{ $user->email }}</p>
                        <p><strong>Phone Number:</strong> {{ $user->agents->phone }}</p>
                        <p><strong>Identity:</strong> {{ $user->agents->identity }}</p>
                    </div>

                    <!-- Password Change Form -->
                    <form action="{{ route('admin.agent.password.request.update', $user->id) }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" class="form-control" name="new_password" required>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" class="form-control" name="confirm_password" required>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
