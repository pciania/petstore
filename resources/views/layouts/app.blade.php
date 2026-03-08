<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Petstore') — Petstore</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-brand span { color: #0d6efd; }
        .status-badge-available  { background-color: #198754; }
        .status-badge-pending    { background-color: #ffc107; color: #000; }
        .status-badge-sold       { background-color: #dc3545; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ route('pets.index') }}">
                <i class="bi bi-box2-heart-fill me-1"></i>
                <span>Pet</span>store
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="{{ route('pets.index') }}">
                    <i class="bi bi-list-ul me-1"></i>All Pets
                </a>
                <a class="nav-link" href="{{ route('pets.create') }}">
                    <i class="bi bi-plus-circle me-1"></i>Add Pet
                </a>
            </div>
        </div>
    </nav>

    <main class="container">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-1"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="text-center text-muted py-4 mt-5 border-top">
        <small>Powered by <a href="https://petstore.swagger.io/" target="_blank" rel="noopener">Swagger Petstore API</a></small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

