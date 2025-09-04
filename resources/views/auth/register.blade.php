<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
<h2>Register</h2>

@if ($errors->any())
    <div style="color: red;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('register') }}">
    @csrf
    <input type="text" name="name" placeholder="Full Name" value="{{ old('name') }}" required><br><br>
    <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <input type="password" name="password_confirmation" placeholder="Confirm Password" required><br><br>
    <button type="submit">Register</button>
</form>

<p>Already have an account? <a href="{{ route('login.form') }}">Login here</a></p>
</body>
</html>
