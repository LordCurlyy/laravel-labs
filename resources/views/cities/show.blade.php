@extends('layouts.app')
@section('title', $city->name.' — '.$city->country)
@section('content')
<a href="{{ route('cities.index') }}">← Все города</a>
<h1 class="page-title mt-3">{{ $city->name }} — {{ $city->country }}</h1>
<img src="{{ asset('storage/'.$city->image_path) }}" class="detail-img rounded mb-4" alt="{{ $city->name }}">
<p class="lead">{{ $city->summary }}</p>
<p class="description">{{ $city->description }}</p>
<p>Дата посещения: {{ $city->visited_at?->format('d.m.Y') ?? 'Не указана' }}</p>
<div class="col-md-5 col-lg-3">
    <a class="btn btn-primary w-100" href="{{ route('cities.edit', $city) }}">Редактировать</a>
    @include('cities._delete')
</div>
@endsection
