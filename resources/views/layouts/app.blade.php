<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Столицы мира — Австралия и Океания')</title>
    <link rel="stylesheet" href="{{ mix('css/styles.css') }}">
    <script src="{{ mix('js/app.js') }}" defer></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('cities.index') }}">
            <img src="{{ asset('storage/site/logo.png') }}" class="logo-img" alt="Логотип">
            <span>Столицы мира</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Меню"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('cities.index') }}">Главная</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('cities.create') }}">Добавить город</a></li>
            </ul>
        </div>
    </div>
</nav>
<main class="container my-4 main-content">
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @yield('content')
</main>
<footer class="site-footer container"><p class="mb-0">Elsova Ekaterina Pavlovna 2026</p></footer>
</body>
</html>
