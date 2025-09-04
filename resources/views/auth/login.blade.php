<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
<h2>Login</h2>

@if ($errors->any())
    <div style="color: red;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('login') }}">
    @csrf
    <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit">Login</button>
</form>

<p>Don't have an account? <a href="{{ route('register.form') }}">Register here</a></p>
</body>
</html>
