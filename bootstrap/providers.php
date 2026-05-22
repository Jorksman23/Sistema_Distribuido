<?php

use App\Providers\AppServiceProvider;
LaravelPdoOdbc\OdbcServiceProvider::class;

return [
    AppServiceProvider::class,
    App\Providers\AppServiceFooter::class,
];
