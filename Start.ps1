$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot
$availablePhp = Get-Command php -ErrorAction SilentlyContinue
$portablePhp = Join-Path $PSScriptRoot '../../work/runtime/php/php.exe'
if ($availablePhp) { $labPhp = $availablePhp.Source }
elseif (Test-Path $portablePhp) { $labPhp = (Resolve-Path $portablePhp).Path }
else { throw 'Install PHP 8.3+ and follow README.md first.' }
if (!(Test-Path vendor/autoload.php)) { throw 'Run composer install first. See README.md.' }
if (!(Test-Path .env)) { throw 'Configure .env first. See README.md.' }
Write-Host 'Open http://127.0.0.1:8000/cities (Ctrl+C to stop)'
& $labPhp artisan serve --host=127.0.0.1 --port=8000 --no-reload
