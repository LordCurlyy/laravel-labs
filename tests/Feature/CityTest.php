<?php
namespace Tests\Feature;

use App\Models\City;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CityTest extends TestCase
{
    use RefreshDatabase;

    private function data(): array
    {
        return ['name'=>'Апиа', 'country'=>'Самоа', 'summary'=>'Краткое описание',
            'description'=>'Подробная информация', 'visited_at'=>'06.11.2026'];
    }

    private function city(): City
    {
        return City::create([...$this->data(), 'image_path'=>'cities/original.jpg']);
    }

    public function test_list_detail_and_form_pages(): void
    {
        $city=$this->city();
        $this->get('/')->assertRedirect('/cities');
        $this->get('/cities')->assertOk()->assertSee($city->name);
        $this->get(route('cities.show',$city))->assertOk()->assertSee($city->description);
        $this->get(route('cities.create'))->assertOk()->assertSee('multipart/form-data',false);
        $this->get(route('cities.edit',$city))->assertOk()->assertSee($city->name);
    }

    public function test_create_stores_resized_image_and_date(): void
    {
        Storage::fake('public');
        $response=$this->post('/cities',[...$this->data(),
            'image'=>UploadedFile::fake()->image('city.png',1200,800)]);
        $city=City::firstOrFail();
        $response->assertRedirect(route('cities.show',$city));
        $this->assertSame('06.11.2026',$city->visited_at->format('d.m.Y'));
        Storage::disk('public')->assertExists($city->image_path);
        $size=getimagesize(Storage::disk('public')->path($city->image_path));
        $this->assertSame([960,540],[$size[0],$size[1]]);
        $this->assertSame('image/jpeg',$size['mime']);
    }

    public function test_update_without_image_preserves_existing_file(): void
    {
        $city=$this->city();
        $this->put(route('cities.update',$city),[...$this->data(),
            'name'=>'Ваителе', 'visited_at'=>''])->assertRedirect();
        $city->refresh();
        $this->assertSame('Ваителе',$city->name);
        $this->assertSame('cities/original.jpg',$city->image_path);
        $this->assertNull($city->visited_at);
    }

    public function test_replacing_image_removes_old_file(): void
    {
        Storage::fake('public');
        $city=$this->city();
        Storage::disk('public')->put($city->image_path,'old');
        $this->put(route('cities.update',$city),[...$this->data(),
            'image'=>UploadedFile::fake()->image('new.png',640,480)])->assertRedirect();
        Storage::disk('public')->assertMissing('cities/original.jpg');
        Storage::disk('public')->assertExists($city->fresh()->image_path);
    }

    public function test_invalid_fields_and_dates_are_rejected(): void
    {
        $this->post('/cities',['name'=>'', 'visited_at'=>'30.02.2026'])
            ->assertSessionHasErrors(['name','country','summary','description','visited_at','image']);
        $this->assertDatabaseCount('cities',0);
    }

    public function test_fake_image_is_rejected(): void
    {
        $this->post('/cities',[...$this->data(),
            'image'=>UploadedFile::fake()->create('script.jpg',10,'text/plain')])
            ->assertSessionHasErrors('image');
        $this->assertDatabaseCount('cities',0);
    }

    public function test_large_image_is_rejected(): void
    {
        $this->post('/cities',[...$this->data(),
            'image'=>UploadedFile::fake()->image('huge.png')->size(5121)])
            ->assertSessionHasErrors('image');
    }

    public function test_delete_is_soft_and_hides_all_city_routes(): void
    {
        Storage::fake('public');
        $city=$this->city();
        Storage::disk('public')->put($city->image_path,'retained');
        $this->delete(route('cities.destroy',$city))->assertRedirect(route('cities.index'));
        $this->assertSoftDeleted('cities',['id'=>$city->id]);
        Storage::disk('public')->assertExists($city->image_path);
        $this->get('/cities')->assertDontSee('Апиа');
        $this->get(route('cities.show',$city))->assertNotFound();
        $this->get(route('cities.edit',$city))->assertNotFound();
        $this->put(route('cities.update',$city),$this->data())->assertNotFound();
    }

    public function test_model_mutators(): void
    {
        $city=City::create([...$this->data(),'name'=>'  Апиа  ',
            'visited_at'=>'2026-11-06 15:30:00','image_path'=>'cities/test.jpg']);
        $this->assertSame('Апиа',$city->name);
        $this->assertDatabaseHas('cities',['id'=>$city->id,'visited_at'=>'2026-11-06']);
    }

    public function test_seeder_creates_exactly_five_cities_and_can_repeat(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);
        $this->assertDatabaseCount('cities',5);
        foreach(City::all() as $city) Storage::disk('public')->assertExists($city->image_path);
        $this->get('/cities')->assertOk()->assertSee('Апиа')->assertSee('Сува');
    }

    public function test_country_and_city_must_match_the_directory(): void
    {
        $city=$this->city();
        $this->put(route('cities.update',$city),[...$this->data(),'country'=>'Фиджи'])
            ->assertSessionHasErrors('name');
        $this->put(route('cities.update',$city),[...$this->data(),'country'=>'Неизвестная страна'])
            ->assertSessionHasErrors(['country','name']);
        $this->assertSame('Самоа',$city->fresh()->country);
    }

    public function test_date_has_exact_length_and_calendar_validation(): void
    {
        $city=$this->city();
        foreach (['06.11.20266','1.1.2026','31.04.2026','29.02.2025','2026-11-06'] as $date) {
            $this->put(route('cities.update',$city),[...$this->data(),'visited_at'=>$date])
                ->assertSessionHasErrors('visited_at');
        }
        $this->put(route('cities.update',$city),[...$this->data(),'visited_at'=>'29.02.2024'])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('2024-02-29',$city->fresh()->visited_at->format('Y-m-d'));
    }

    public function test_existing_city_cannot_be_added_again(): void
    {
        $city = $this->city();
        $this->post('/cities', [...$this->data(), 'image'=>UploadedFile::fake()->image('city.png')])
            ->assertSessionHasErrors('name');
        $this->assertDatabaseCount('cities', 1);
        $this->put(route('cities.update', $city), $this->data())
            ->assertSessionHasNoErrors()->assertRedirect();
        $other = City::create([...$this->data(), 'name'=>'Ваителе', 'image_path'=>'cities/other.jpg']);
        $this->put(route('cities.update', $other), $this->data())->assertSessionHasErrors('name');
        $this->assertSame('Ваителе', $other->fresh()->name);
    }

    public function test_visiting_date_cannot_be_in_the_future(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-11-06 23:30:00', 'Asia/Yekaterinburg'));
        $city = $this->city();
        $this->put(route('cities.update', $city), [...$this->data(), 'visited_at'=>'07.11.2026'])
            ->assertSessionHasErrors('visited_at');
        $this->put(route('cities.update', $city), [...$this->data(), 'visited_at'=>'06.11.2026'])
            ->assertSessionHasNoErrors();
        $this->travelBack();
    }

    public function test_each_city_has_an_interesting_fact(): void
    {
        foreach (config('oceania') as $cities) {
            foreach ($cities as $name) $this->assertNotEmpty(config('city_facts')[$name] ?? null, $name);
        }
    }
}
