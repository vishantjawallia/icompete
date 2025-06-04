@extends('admin.layouts.auth')
@section('title', 'Change Password')
@section('content')
<div class="auth-form">
    <div class="text-center mb-3">
        <a href="{{route('admin.login')}}">
            <img src="{{my_asset(get_setting('logo'))}}" alt="" style="width: 90px;">
        </a>
    </div>
    <h4 class="text-center mb-4">Change Password</h4>
    <form action="{{route('admin.password.confirm')}}" class="ajaxForm autoR" method="POST">
        @csrf
        <div class="mb-3" hidden>
            <label class="mb-1 form-label">Email Address</label>
            <input type="email" name="email" value="{{$email ?? '' }}" class="form-control" placeholder="Email Address" readonly>
        </div>

        <!-- Reset Code -->
        <div class="mb-3 form-group">
            <label class="mb-1 form-label" for="resetCode">{{ __('Reset Code') }}</label>
            <input id="resetCode" type="text" integer maxlength="6" name="code" required class="form-control" placeholder="Reset Code">
        </div>

        <!-- Password -->
        <div class="mb-3 form-group">
            <label class="mb-1 form-label" for="password">{{ __('Password') }}</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" class="form-control" placeholder="Password">
        </div>

        <!-- Confirm Password -->
        <div class="mb-3 form-group">
            <label class="mb-1 form-label" for="password_confirmation">{{ __('Confirm Password') }}</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="form-control" placeholder="Confirm Password">
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-primary btn-block">Submit</button>
        </div>
    </form>
    <div class="new-account mt-3">
        <p>Expired Code?
        <a class="text-primary" type="button" id="resendCodeLink" >Resend Code</a></p>
    </div>

</div>
@endsection

@push('scripts')
<script>
    const resendLink = document.getElementById('resendCodeLink');
    resendLink.addEventListener('click', function (event) {
        event.preventDefault();

        const email = '{{ $email }}'; // Email value
        const url = '{{ route('admin.password.resend') }}'; // Resend route

        // Show loading overlay if applicable
        JDLoader.open();

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ email })
        })
        .then(response => response.json())
        .then(data => {
            // Handle success or error response
            JDLoader.close();
            if (data.status === 'success') {
                toastr.success(data.message || 'Code resent successfully');
            } else if (data.status === "error") {
                toastr.error(data.message || 'An error occurred while resending the code');
            }
        })
        .catch(error => {
            JDLoader.close();
            toastr.error(error.message || 'Something went wrong');
        });
    });

</script>
@endpush
