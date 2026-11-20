# Лабораторная работа 3 Использование MVC фреймворка

Приложение Laravel 12 «Столицы мира — Австралия и Океания». Выполнены базовый и расширенный уровни: список, детальная страница, добавление, редактирование, загрузка изображений, валидация и мягкое удаление.

Использованы пять объектов предыдущей работы: Апиа, Веллингтон, Канберра, Нукуалофа и Сува. Сохранены фотографии, тексты, цветовые переменные, адаптивная сетка с дополнительной точкой `xxxl` и подпись автора. Исходники предыдущих работ находятся в `previous-labs/`; приложение их не исполняет.

## Быстрый запуск из архива

Нужны PHP 8.3 или новее, Composer, Node.js и npm. Для PHP включите расширения `pdo_sqlite`, `sqlite3`, `gd`, `fileinfo`, `mbstring`, `openssl`, `curl`, `zip` и стандартные расширения Laravel. В php.ini установите `upload_max_filesize=8M`, `post_max_size=10M`, `memory_limit=512M`.

В терминале из папки проекта:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item -ItemType File -Path database/database.sqlite -ErrorAction SilentlyContinue
php artisan migrate --seed
php artisan storage:link
npm ci
npm run build
php artisan serve
```

Откройте http://127.0.0.1:8000/cities. Команда запуска Laravel — `php artisan serve`.

В подготовленной папке рядом с архивом зависимости уже установлены, база заполнена, ресурсы собраны. Для запуска на этом компьютере можно выполнить `./Start.ps1` из PowerShell. Скрипт использует доступный PHP либо переносимый PHP из рабочей папки подготовки.

Если Windows не разрешает создать ссылку `public/storage`, выполните `php artisan storage:link` из терминала с правами администратора либо включите режим разработчика Windows. Фотографии должны оставаться в `storage/app/public`, копировать их в `public` не нужно.

## Сборка и наблюдение за изменениями

```powershell
npm run dev
npm run watch
```

`dev` выполняет одну сборку, `watch` пересобирает ресурсы после изменения SCSS/JS. После пересборки обновите страницу браузера. Для изменений Blade достаточно обновить страницу. `build` собирает сжатую production-версию. Laravel Mix использует Webpack; CSS и JS помещаются в `public/css` и `public/js`, шрифты — в `public/fonts`. Blade подключает ресурсы через `mix()` и `public/mix-manifest.json`.

Вместо отдельных загрузчиков и HtmlWebpackPlugin из прошлой лабораторной применён Laravel Mix 6. HTML теперь формирует Blade. Bootstrap, jQuery, Font Awesome и собственные стили включаются в локальную сборку. Версии Webpack и CLI закреплены для совместимости с Mix; `package-lock.json` обеспечивает воспроизводимую установку.

## Структура решения

| Требование | Реализация |
| --- | --- |
| Миграция SQLite и snake_case | `database/migrations/2026_01_01_000000_create_cities_table.php`, таблица `cities` |
| Eloquent и мутаторы | `app/Models/City.php`, `name` и `visited_at` |
| Семь действий CRUD | `app/Http/Controllers/CityController.php` |
| Именованные маршруты | `Route::resource('cities', CityController::class)` в `routes/web.php` |
| Список из базы | `resources/views/cities/index.blade.php` |
| Сведения из Modal на отдельной странице | `resources/views/cities/show.blade.php` |
| Одна форма создания и редактирования | `resources/views/cities/_form.blade.php` |
| HTML5 и Bootstrap validation | `required`, `maxlength`, маска ДД.ММ.ГГГГ, `accept`, `checkValidity`, `was-validated` |
| Validation Rules на сервере | метод `validated` контроллера, русские сообщения об ошибках |
| Загрузка и изменение размера | `app/Services/CityImage.php`, GD, центральная обрезка 960 × 540, JPEG |
| Soft Deletes | trait `SoftDeletes`, поле `deleted_at`, вызов `delete()` |
| Пять начальных объектов | `database/seeders/cities.json`, `DatabaseSeeder` |
| Laravel Mix | `webpack.mix.js`, `resources/scss`, `resources/js` |

В `cities` хранятся `id`, `name`, `country`, `summary`, `description`, `visited_at`, `image_path`, `created_at`, `updated_at`, `deleted_at`. Необязательное поле `visited_at` означает дату посещения; оно добавлено для демонстрации мутатора даты, начальные значения пустые. Это не историческая дата основания города.

При добавлении фото обязательно, при редактировании можно оставить старое. Сервер проверяет реальный тип изображения, расширение, размер до 5 МБ и габариты до 6000 × 6000 px. При замене старая фотография удаляется после успешного сохранения записи. При мягком удалении фотография сохраняется, чтобы запись можно было восстановить.

## Маршруты

| Метод | URL | Имя и действие |
| --- | --- | --- |
| GET | /cities | cities.index — список |
| GET | /cities/create | cities.create — форма добавления |
| POST | /cities | cities.store — создание |
| GET | /cities/{city} | cities.show — детали |
| GET | /cities/{city}/edit | cities.edit — форма редактирования |
| PUT или PATCH | /cities/{city} | cities.update — сохранение |
| DELETE | /cities/{city} | cities.destroy — мягкое удаление |

Все изменяющие формы используют `@csrf`; PUT и DELETE передаются через `@method`. Данные выводятся экранированным синтаксисом `{{ }}`. Входные данные `image_path` и `deleted_at` из формы не принимаются. Удалённые записи исключаются из списка и недоступны через привязку модели в show/edit/update.

## Проверка через Tinker

```powershell
php artisan tinker
```

В интерактивной оболочке:

```php
$city = App\Models\City::create([
    'name' => '  Тестовый город  ',
    'country' => 'Самоа',
    'summary' => 'Пример для проверки модели',
    'description' => 'Создание записи через Eloquent в Laravel Tinker.',
    'visited_at' => '2026-11-06 12:30:00',
    'image_path' => App\Models\City::firstOrFail()->image_path,
]);
$city->name; // Тестовый город — пробелы убраны мутатором
$city->getRawOriginal('visited_at'); // 2026-11-06
$city->visited_at->format('d.m.Y'); // 06.11.2026
$city->delete(); // устанавливает deleted_at
App\Models\City::withTrashed()->find($city->id)->restore();
```

В примере используется существующая фотография. Для обычного создания с загрузкой новой фотографии используйте форму приложения. Начальные пять записей восстанавливаются повторным созданием БД через миграции только если можно удалить все имеющиеся данные; `migrate:fresh --seed` удаляет таблицы и не нужен для обычного запуска. Обычный `db:seed` не дублирует города и не восстанавливает удалённые записи.

## Проверка работы

```powershell
php artisan test
php artisan route:list --path=cities
php artisan view:cache
```

Feature-тесты в `tests/Feature/CityTest.php` проверяют страницы, создание, мутаторы, редактирование с заменой фото и без неё, отклонение неправильных данных, размер изображения, Soft Deletes и повторный запуск сидера. Используется отдельная SQLite в памяти; рабочая база не очищается.

После обновления формы выполнены 15 тестов, все прошли, всего 129 проверок. Проверены связанные списки стран и городов, запрет дубликатов, формат даты, календарная корректность даты, запрет будущих дат, наличие факта для каждого города, CRUD и изображения. Также выполнена production-сборка Mix. Отдельно проверены обработчики JavaScript: обновление списка городов, блокировка занятых городов, счётчик, маска даты и подстановка факта.

В форме сначала выбирается страна из справочника `config/oceania.php`, затем город или населённый пункт этой страны. Города, уже имеющиеся в активном каталоге, отмечены «уже в каталоге» и недоступны для выбора. При редактировании текущая запись исключается из этой проверки. Сервер также запрещает дубли пары «страна — город». После мягкого удаления город снова доступен для добавления.

Дата в форме вводится как ДД.ММ.ГГГГ — максимум восемь цифр и два разделителя. Невозможные даты отклоняются браузером и сервером. В базе дата сохраняется в формате ГГГГ-ММ-ДД. Краткое описание ограничено 500 символами, подробное — 10000; под каждым полем счётчик обновляется при вводе и при открытии заполненной формы.

Дата посещения не может быть позже сегодняшней. Клиент и сервер используют часовой пояс приложения `APP_TIMEZONE` (по умолчанию `Asia/Yekaterinburg`). При выборе города в подробное описание подставляется строка `Интересный факт: ...` из `config/city_facts.php`. При смене города заменяется строка факта, остальной введённый текст сохраняется. При открытии редактирования существующее описание автоматически не меняется. Поле подробного описания доступно для редактирования, когда в кратком описании введено хотя бы 5 символов без внешних пробелов.

Справочные материалы о городах: [Tourism Australia](https://www.australia.com/en-us/facts-and-planning/about-australia/cities-states-and-territories.html), [National Capital Authority](https://www.nca.gov.au/education/canberras-history/canberra-seat-government), [Tourism New Zealand](https://www.newzealand.com/au/wellington/), [Samoa Tourism Authority](https://www.samoa.travel/plan-book/activities/vailima-national-reserve/).

Ручная проверка для защиты: откройте пять городов, создайте шестой с фотографией, измените его название и фото, отправьте форму с пустыми обязательными полями, удалите созданный город. В Tinker покажите `City::withTrashed()->get()` и значение `deleted_at`.

## Git

В подготовленной рабочей папке инициализирован локальный Git. Архив не содержит `.git`, поэтому после распаковки выполните:

```powershell
git init
git add .
git commit -m "Complete Laravel 12 MVC laboratory"
```

Для отправки преподавателю добавьте адрес своего репозитория через `git remote add origin АДРЕС_РЕПОЗИТОРИЯ` и выполните `git push -u origin HEAD`. `vendor`, `node_modules`, `.env`, SQLite, пользовательские загрузки и собранные ресурсы исключены из Git; исходные изображения для сидера включены.

## Материалы

- Laravel 12: https://laravel.com/docs/12.x
- Мутаторы: https://laravel.com/docs/12.x/eloquent-mutators
- Laravel Mix 6: https://laravel-mix.com/docs/6.0/installation

Лабораторная использует локальное учебное приложение без авторизации: все посетители имеют доступ к CRUD.
