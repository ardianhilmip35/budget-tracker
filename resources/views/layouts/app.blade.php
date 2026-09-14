<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0d6efd">
    <title>{{ $title ?? 'Budget Tracker' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f6f8fb; padding-bottom:80px; }
        .navbar-brand { font-weight:700; }
        .money { font-variant-numeric: tabular-nums; }
        .budget-card { border:0; border-radius:16px; }
        .progress { height:8px; }
        .bottom-nav { position:fixed; bottom:0; left:0; right:0; z-index:1030; background:white; border-top:1px solid #e6e9ef; }
        .bottom-nav a { color:#6c757d; text-decoration:none; font-size:.82rem; padding:12px 8px; display:block; text-align:center; }
        .bottom-nav a.active { color:#0d6efd; font-weight:600; }
        @media (min-width: 768px) { .bottom-nav { display:none; } body { padding-bottom:24px; } }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-md bg-white border-bottom sticky-top">
    <div class="container">
        <a class="navbar-brand" href="{{ route('dashboard') }}">Budget Jakarta</a>
        <div class="d-none d-md-flex gap-2 align-items-center">
            <a class="btn btn-sm btn-outline-primary" href="{{ route('history') }}">Riwayat</a>
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('settings.edit') }}">Pengaturan</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-danger">Keluar</button></form>
        </div>
    </div>
</nav>

<main class="container py-3 py-md-4">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger"><strong>Periksa input:</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @yield('content')
</main>

<nav class="bottom-nav d-md-none">
    <div class="row g-0">
        <div class="col"><a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a></div>
        <div class="col"><a class="{{ request()->routeIs('history') ? 'active' : '' }}" href="{{ route('history') }}">Riwayat</a></div>
        <div class="col"><a class="{{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.edit') }}">Pengaturan</a></div>
    </div>
</nav>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
