<?php
namespace App\Models;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class City extends Model {
    use SoftDeletes;
    protected $fillable = ['name', 'country', 'summary', 'description', 'visited_at', 'image_path'];
    protected function name(): Attribute {
        return Attribute::make(set: fn ($value) => trim($value));
    }
    // Поле даты посещения добавлено для демонстрации мутатора даты.
    protected function visitedAt(): Attribute {
        return Attribute::make(
            get: fn ($value) => $value ? CarbonImmutable::parse($value) : null,
            set: fn ($value) => $value ? CarbonImmutable::parse($value)->format('Y-m-d') : null,
        );
    }
}
