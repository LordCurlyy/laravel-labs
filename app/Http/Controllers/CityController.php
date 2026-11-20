<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Services\CityImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CityController extends Controller {

    public function index() { 
        return view('cities.index', ['cities' => City::orderBy('id')->get()]); 
    }

    public function create() { 
        return view('cities.create', ['city' => new City]); 
    }

    public function show(City $city) { 
        return view('cities.show', compact('city')); 
    }

    public function edit(City $city) { 
        return view('cities.edit', compact('city')); 
    }

    public function store(Request $request, CityImage $images) {
        $data = $this->validated($request, true);
        unset($data['image']);
        $path = $images->store($request->file('image'));
        
        try { 
            $city = City::create([...$data, 'image_path' => $path]); 
        } catch (\Throwable $e) { 
            Storage::disk('public')->delete($path); 
            throw $e; 
        }
        
        return redirect()->route('cities.show', $city)->with('success', 'Город добавлен.');
    }

    public function update(Request $request, City $city, CityImage $images) {
        $data = $this->validated($request, false, $city);
        unset($data['image']);
        $old = $city->image_path; 
        $new = null;
        
        if ($request->hasFile('image')) {
            $data['image_path'] = $new = $images->store($request->file('image'));
        }
        
        try { 
            $city->update($data); 
        } catch (\Throwable $e) { 
            if ($new) Storage::disk('public')->delete($new); 
            throw $e; 
        }
        
        if ($new && $old) Storage::disk('public')->delete($old);
        
        return redirect()->route('cities.show', $city)->with('success', 'Изменения сохранены.');
    }

    public function destroy(City $city) {
        $city->delete(); // Изображение сохраняется для возможного восстановления записи.
        return redirect()->route('cities.index')->with('success', 'Город удалён из каталога.');
    }

    private function validated(Request $request, bool $creating, ?City $city = null): array {
        $data = $request->validate([
            'country' => ['required', 'string', Rule::in(array_keys(config('oceania')))],
            'name' => ['required', 'string', Rule::in(config('oceania')[is_string($request->input('country')) ? $request->input('country') : ''] ?? []),
                Rule::unique('cities', 'name')->where('country', is_string($request->input('country')) ? $request->input('country') : '')->whereNull('deleted_at')->ignore($city?->id)],
            'summary' => ['required', 'string', 'min:5', 'max:500'],
            'description' => ['required', 'string', 'min:10', 'max:10000'],
            'visited_at' => ['nullable', 'regex:/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/', 'date_format:d.m.Y', 'before_or_equal:today'],
            'image' => [
                $creating ? 'required' : 'nullable', 
                'image', 
                'mimes:jpg,jpeg,png,webp', 
                'max:2048', // 2048 КБ = 2 МБ
                'dimensions:max_width=6000,max_height=6000'
            ],
        ], [
            'required' => 'Заполните поле «:attribute».',
            'min' => 'Поле «:attribute» должно содержать минимум :min символов.',
            'max' => 'Превышен допустимый размер поля «:attribute» (:max).',
            'regex' => 'Поле «:attribute» должно начинаться с заглавной буквы и содержать только буквы, пробелы или дефисы.',
            'image' => 'Выберите корректное изображение.',
            'mimes' => 'Разрешены JPG, PNG и WebP.',
            'date_format' => 'Введите существующую дату в формате ДД.ММ.ГГГГ.',
            'visited_at.regex' => 'Дата должна содержать 2 цифры дня, 2 цифры месяца и 4 цифры года.',
            'visited_at.before_or_equal' => 'Дата посещения не может быть позже сегодняшней.',
            'country.in' => 'Выберите страну из списка.',
            'name.in' => 'Выберите город, относящийся к выбранной стране.',
            'name.unique' => 'Этот город уже есть в каталоге. Выберите другой.',
            'dimensions' => 'Размер изображения не должен превышать 6000 × 6000 пикселей.',
        ], [
            'name' => 'Город', 
            'country' => 'Страна', 
            'summary' => 'Краткое описание',
            'description' => 'Подробное описание', 
            'visited_at' => 'Дата посещения', 
            'image' => 'Изображение'
        ]);
        if (!empty($data['visited_at'])) {
            $data['visited_at'] = \Carbon\CarbonImmutable::createFromFormat('!d.m.Y', $data['visited_at'])->format('Y-m-d');
        }
        return $data;
    }
}
