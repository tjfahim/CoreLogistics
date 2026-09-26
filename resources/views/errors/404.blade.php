<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Page Not Found</title>

    <link rel="stylesheet" href="{{ asset('admin/vendors/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/vendors/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/css/style.css') }}">

    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Open Sans', sans-serif;
        }
        .error-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .error-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 50px 40px;
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        .error-code {
            font-size: 100px;
            font-weight: 800;
            line-height: 1;
            color: #dc3545;
            margin-bottom: 10px;
        }
        .error-title {
            font-size: 24px;
            font-weight: 700;
            color: #212529;
            margin-bottom: 10px;
        }
        .error-message {
            font-size: 15px;
            color: #6c757d;
            margin-bottom: 30px;
        }
        .error-actions .btn {
            min-width: 160px;
            margin: 5px;
            padding: 10px 20px;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="error-wrapper">
    <div class="error-card">
        <div class="error-code">404</div>
        <div class="error-title">Page Not Found</div>
        <div class="error-message">
            The page you are looking for might have been removed,
            had its name changed, or is temporarily unavailable.
        </div>

        <div class="error-actions">
            @auth
                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                    <i class="fa fa-dashboard"></i> Go to Dashboard
                </a>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                    <i class="fa fa-arrow-left"></i> Go Back
                </a>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary">
                    <i class="fa fa-sign-in"></i> Go to Login
                </a>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                    <i class="fa fa-arrow-left"></i> Go Back
                </a>
            @endauth
        </div>
    </div>
</div>

</body>
</html>