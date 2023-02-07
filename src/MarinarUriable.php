<?php
namespace Marinar\Uriable;

use Marinar\Uriable\Database\Seeders\MarinarUriableInstallSeeder;

class MarinarUriable {

    public static function getPackageMainDir() {
        return __DIR__;
    }

    public static function injects() {
        return MarinarUriableInstallSeeder::class;
    }
}
