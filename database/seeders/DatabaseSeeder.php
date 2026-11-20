<?php
namespace Database\Seeders;
use App\Models\City;
use App\Services\CityImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
class DatabaseSeeder extends Seeder {
    public function run(): void {
        Storage::disk('public')->put('site/logo.png', file_get_contents(storage_path('app/seed-images/logo.png')));
        foreach (json_decode(file_get_contents(database_path('seeders/cities.json')), true) as $row) {
            if (City::withTrashed()->where('name',$row['name'])->where('country',$row['country'])->exists()) continue;
            $image=app(CityImage::class)->fromPath(storage_path('app/seed-images/'.$row['source_image']));
            unset($row['source_image']);
            City::create([...$row, 'image_path'=>$image]);
        }
    }
}
