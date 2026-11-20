<?php
namespace App\Services;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class CityImage {
    public function store(UploadedFile $file): string {
        return $this->fromPath($file->getRealPath());
    }
    public function fromPath(string $path): string {
        $source = @imagecreatefromstring(file_get_contents($path));
        if (!$source) throw ValidationException::withMessages(['image' => 'Не удалось прочитать изображение.']);
        $target = imagecreatetruecolor(960, 540);
        $background = imagecolorallocate($target, 241, 250, 238);
        imagefill($target, 0, 0, $background);
        $width=imagesx($source); $height=imagesy($source);
        $scale=max(960 / $width, 540 / $height);
        $cropWidth=(int) round(960 / $scale); $cropHeight=(int) round(540 / $scale);
        imagecopyresampled($target, $source, 0, 0,
            (int) (($width-$cropWidth)/2), (int) (($height-$cropHeight)/2),
            960, 540, $cropWidth, $cropHeight);
        ob_start(); imagejpeg($target, null, 88); $bytes=ob_get_clean();
        imagedestroy($source); imagedestroy($target);
        $relative='cities/'.Str::uuid().'.jpg';
        Storage::disk('public')->put($relative, $bytes);
        return $relative;
    }
}
