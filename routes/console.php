<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\YPReadBusinessByProvince;
use App\Console\Commands\YPReadBusinessByCategory;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('ypreadbusiness', function ( YPReadBusinessByCategory $byCategory ) {
    $this->info('Start reading YP Business by categories ...');
    $byCategory->handle();
});

Artisan::command('ypreadprovince', function ( YPReadBusinessByProvince $byProvince ) {
    $this->info('Start reading YP Business by provinces ...');
    $byProvince->handle();
});
