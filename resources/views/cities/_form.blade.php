@php
    $places = config('oceania');
    $selectedCountry = old('country', $city->country);
    $selectedCity = old('name', $city->name);
    $usedCities = \App\Models\City::query()->when($city->exists, fn ($query) => $query->where('id', '!=', $city->id))->get(['country', 'name'])->groupBy('country')->map(fn ($rows) => $rows->pluck('name')->all())->all();
    $canDescribe = in_array($selectedCity, $places[$selectedCountry] ?? [], true) && !in_array($selectedCity, $usedCities[$selectedCountry] ?? [], true);
@endphp
@if($errors->any())
<div class="alert alert-danger" role="alert"><p>Исправьте ошибки в форме.</p><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<form action="{{ $city->exists ? route('cities.update', $city) : route('cities.store') }}" method="post" enctype="multipart/form-data" class="needs-validation" novalidate id="cityForm">
@csrf
@if($city->exists) @method('PUT') @endif
<div class="row g-3">
<div class="col-md-6">
    <label for="country" class="form-label">Страна</label>
    <select id="country" name="country" class="form-select @error('country') is-invalid @enderror" required>
        <option value="">Выберите страну</option>
        @foreach($places as $country => $cities)
        <option value="{{ $country }}" @selected($selectedCountry === $country)>{{ $country }}</option>
        @endforeach
    </select>
    <div class="invalid-feedback">Выберите страну из списка.</div>
</div>
<div class="col-md-6">
    <label for="name" class="form-label">Город или населённый пункт</label>
    <select id="name" name="name" class="form-select @error('name') is-invalid @enderror" required @disabled(!isset($places[$selectedCountry]))>
        <option value="">{{ isset($places[$selectedCountry]) ? 'Выберите город' : 'Сначала выберите страну' }}</option>
        @foreach($places[$selectedCountry] ?? [] as $name)
        <option value="{{ $name }}" @selected($selectedCity === $name) @disabled(in_array($name, $usedCities[$selectedCountry] ?? [], true))>{{ $name }}{{ in_array($name, $usedCities[$selectedCountry] ?? [], true) ? ' — уже в каталоге' : '' }}</option>
        @endforeach
    </select>
    <div class="invalid-feedback">Выберите город выбранной страны.</div>
</div>
<div class="col-12">
    <label for="summary" class="form-label">Краткое описание (от 5 до 500 символов)</label>
    <textarea id="summary" name="summary" class="form-control @error('summary') is-invalid @enderror" maxlength="500" minlength="5" required rows="3" aria-describedby="summaryCount summaryHelp" data-character-count @disabled(!$canDescribe)>{{ old('summary', $city->summary) }}</textarea>
    <div id="summaryHelp" class="form-text">Сначала выберите страну и город.</div>
    <div class="invalid-feedback">Введите от 5 до 500 символов.</div>
    <div id="summaryCount" class="form-text" aria-live="polite">Написано <span data-count-for="summary">0</span> из 500 символов</div>
</div>
<div class="col-12">
    <label for="description" class="form-label">Подробное описание (максимум 10000 символов)</label>
    <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" maxlength="10000" minlength="10" required rows="5" aria-describedby="descriptionHelp descriptionCount" data-character-count @disabled(!$canDescribe || mb_strlen(trim(old('summary', $city->summary) ?? '')) < 5)>{{ old('description', $city->description) }}</textarea>
    <div id="descriptionHelp" class="form-text" aria-live="polite">Сначала заполните краткое описание: минимум 5 символов.</div>
    <div class="invalid-feedback">Введите от 10 до 10000 символов.</div>
    <div id="descriptionCount" class="form-text" aria-live="polite">Написано <span data-count-for="description">0</span> из 10000 символов</div>
</div>
<div class="col-md-6">
    <label for="visited_at" class="form-label">Дата посещения (необязательно)</label>
    <input type="text" inputmode="numeric" autocomplete="off" id="visited_at" name="visited_at" class="form-control @error('visited_at') is-invalid @enderror" maxlength="10" pattern="[0-9]{2}\.[0-9]{2}\.[0-9]{4}" placeholder="ДД.ММ.ГГГГ" aria-describedby="dateHelp dateError" value="{{ old('visited_at', $city->visited_at?->format('d.m.Y')) }}">
    <div id="dateError" class="invalid-feedback" aria-live="polite">@error('visited_at'){{ $message }}@enderror</div>
    <div id="dateHelp" class="form-text">ДД.ММ.ГГГГ.</div>
</div>
<div class="col-md-6">
    <label for="image" class="form-label">Изображение{{ $city->exists ? ' (для замены)' : '' }}</label>
    <input type="file" id="image" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/jpeg,image/png,image/webp" @required(!$city->exists) aria-describedby="imageHelp">
    <div id="imageHelp" class="form-text">JPG, PNG, WebP до 2 МБ и 6000 × 6000 px. Будет обрезано по центру до 960 × 540 px.</div>
    <div class="invalid-feedback">Выберите изображение до 2 МБ.</div>
</div>
@if($city->exists)<div class="col-12"><img src="{{ asset('storage/'.$city->image_path) }}" class="rounded" width="240" height="135" alt="Текущее изображение города"></div>@endif
<div class="col-12 d-flex gap-2"><button type="submit" id="submitBtn" class="btn btn-primary">Сохранить</button><a href="{{ route('cities.index') }}" class="btn btn-outline-secondary">Отмена</a></div>
</div></form>
<script type="application/json" id="oceaniaPlaces">{!! json_encode($places, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/json" id="usedCities">{!! json_encode($usedCities, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/json" id="cityFacts">{!! json_encode(['facts' => config('city_facts'), 'timezone' => config('app.timezone')], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>
