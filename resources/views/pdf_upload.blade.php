<!DOCTYPE html>
<html>
<head>
    <title>Upload PDF or Image</title>
</head>
<body>
    <h2>Upload PDF or Image File</h2>

    <!-- Success Message -->
    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <!-- Validation Errors -->
    @if($errors->any())
        <p style="color: red;">{{ implode(', ', $errors->all()) }}</p>
    @endif

    <!-- Upload Form -->
    <form action="/upload-pdf" method="POST" enctype="multipart/form-data">
        @csrf
        <label>Select File:</label>
        <input type="file" name="file" accept="application/pdf,image/png,image/jpeg,image/jpg" required>

        <button type="submit">Upload & Import</button>
    </form>
</body>
</html>
