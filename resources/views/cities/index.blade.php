@extends('layouts.app')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <h1 class="page-title mb-0">Австралия и Океания</h1>
    <a href="{{ route('cities.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1" aria-hidden="true"></i>Добавить</a>
</div>
<div class="row row-cols-1 row-cols-sm-2 row-cols-md-2 row-cols-lg-3 row-cols-xl-3 row-cols-xxl-4 row-cols-xxxl-5 g-4" id="cards">
@forelse($cities as $city)
<div class="col"><article class="card h-100">
    <a href="{{ route('cities.show', $city) }}"><img src="{{ asset('storage/'.$city->image_path) }}" class="card-img-top" alt="{{ $city->name }}"></a>
    <div class="card-body d-flex flex-column">
        <span class="card-tag mb-2">{{ $city->country }}</span>
        <h2 class="card-title h5"><a class="text-decoration-none" href="{{ route('cities.show', $city) }}">{{ $city->name }}</a></h2>
        <p class="card-text flex-grow-1">{{ $city->summary }}</p>
        <a class="btn btn-danger mt-2" href="{{ route('cities.show', $city) }}">Подробнее</a>
        <a class="btn btn-outline-primary mt-2" href="{{ route('cities.edit', $city) }}">Изменить</a>
        @include('cities._delete')
    </div>
</article></div>
@empty
<div class="col-12"><p>Каталог пуст. Добавьте первый город.</p></div>
@endforelse
</div>
@endsection
