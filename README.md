
<p align="center"><img src="/art/header.jpg" alt="Social Card of Laravel Lighty"></p>

# Laravel Lighty ⚡️

Набор инструментов для быстрого создания CRUD REST API

## Описание

Данная библиотека предоставляет набор инструментов для быстрого создания REST API для базовых CRUD операций над сущностями.

## Установка

Для установки данной библиотеки требуется использование Composer:

```bash
composer require "khazhinov/laravel-lighty:^1.7 -W"
```

При необходимости опубликуйте файл конфигурации:

```bash
php artisan vendor:publish --provider="Khazhinov\LaravelLighty\LaravelLightyServiceProvider" --tag="config"
```

И шаблон для экспорта в XSLX:

```bash
php artisan vendor:publish --provider="Khazhinov\LaravelLighty\LaravelLightyServiceProvider" --tag="views"
```

## Обработчик ошибок

Библиотека предоставляет базовый класс обработчика ошибок, который будет форматировать ответ от сервера в соответствии с принятой структурой данных.

Для внедрения обработчика унаследуйте класс ```App\Exceptions\Handler``` (app/Exception/Handler.php) от ```Khazhinov\LaravelLighty\Exceptions\ExceptionHandler```:

```php
namespace App\Exceptions;

use Khazhinov\LaravelLighty\Exceptions\ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];
}

```

## Использование

Данная библиотека предоставляет набор Artisan команд для быстрой генерации всех необходимых при создании REST API классов.

Просто используйте:

```bash
php artisan lighty:generator TestEntity v1.0 --migration
```

В результате выполнения данной команды будет сгенерирована следующая структура файлов:


| Путь к файлу                                                          | Назначение                                                  |
|-----------------------------------------------------------------------|-------------------------------------------------------------|
| app/Http/Controllers/Api/V1_0/TestEntity/TestEntityCRUDController.php | Базовый CRUD контроллер                                     |
| app/Http/Requests/TestEntity/TestEntityStoreRequest.php               | Класс запроса для проверки данных метода создания сущности  |
| app/Http/Requests/TestEntity/TestEntityUpdateRequest.php              | Класс запроса для проверки данных метода изменения сущности |
| app/Http/Resources/TestEntity/TestEntityResource.php                  | Класс ресурса сущности                                      |
| app/Http/Resources/TestEntity/TestEntityCollection.php                | Класс ресурса коллекции сущностей                           |
| app/Models/TestEntity.php                                             | Модель сущности                                             |
| database/migrations/TIMESTAMP_create_test_entities_table.php          | Файл миграции                                               |

Также в терминал будет выведена информация, необходимая для добавления в роутер:

```php
#/api/v1.0/testEntities
Route::group([
    "namespace" => "TestEntity",
    "prefix" => "/testEntities",
    "as" => "test_entities.",
], static function () {
    Route::get("/validations/{method?}", "TestEntityCRUDController@getValidations")->name("validations");

    Route::get("/", "TestEntityCRUDController@index")->name("index");
    Route::post("/search", "TestEntityCRUDController@index")->name("search");
    Route::post("/setPosition", "TestEntityCRUDController@setPosition")->name("set-position");

    Route::post("/", "TestEntityCRUDController@store")->name("store");
    Route::delete("/", "TestEntityCRUDController@bulkDestroy")->name("bulk-destroy");

    #/api/v1.0/testEntities/:key
    Route::group([
        "prefix" => "/{key}",
    ], static function () {
        Route::get("/", "TestEntityCRUDController@show")->name("show");
        Route::put("/", "TestEntityCRUDController@update")->name("update");
        Route::delete("/", "TestEntityCRUDController@destroy")->name("destroy");
    });
});
```

Пример готового роутера API (routes/api.php):

```php
<?php

use Illuminate\Support\Facades\Route;


# /api/v1.0/
Route::group(["namespace" => "App\Http\Controllers\Api\V1_0", "prefix" => "/v1.0", "as" => "api.v1_0"], static function () {
    #/api/v1.0/testEntities
    Route::group([
        "namespace" => "TestEntity",
        "prefix" => "/testEntities",
        "as" => "test_entities.",
    ], static function () {
        Route::get("/validations/{method?}", "TestEntityCRUDController@getValidations")->name("validations");

        Route::get("/", "TestEntityCRUDController@index")->name("index");
        Route::post("/search", "TestEntityCRUDController@index")->name("search");
        Route::post("/setPosition", "TestEntityCRUDController@setPosition")->name("set-position");

        Route::post("/", "TestEntityCRUDController@store")->name("store");
        Route::delete("/", "TestEntityCRUDController@bulkDestroy")->name("bulk-destroy");

        #/api/v1.0/testEntities/:key
        Route::group([
            "prefix" => "/{key}",
        ], static function () {
            Route::get("/", "TestEntityCRUDController@show")->name("show");
            Route::put("/", "TestEntityCRUDController@update")->name("update");
            Route::delete("/", "TestEntityCRUDController@destroy")->name("destroy");
        });
    });
});
```

## Лицензия

Лицензия MIT. Для получения большей информации обращайтесь к [тексту лицензии](LICENSE.md).

