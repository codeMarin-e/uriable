<?php
    namespace Marinar\Uriable\Database\Seeders;

    use Illuminate\Database\Seeder;
    use Marinar\Uriable\MarinarUriable;

    class MarinarUriableInstallSeeder extends Seeder {

        use \Marinar\Marinar\Traits\MarinarSeedersTrait;

        public static function configure() {
            static::$packageName = 'marinar_uriable';
            static::$packageDir = MarinarUriable::getPackageMainDir();
        }

        public function run() {
            if(!in_array(env('APP_ENV'), ['dev', 'local'])) return;

            $this->autoInstall();

            $this->refComponents->info("Done!");
        }

    }
