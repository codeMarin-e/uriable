<?php
	return [
		'install' => [
            'php artisan db:seed --class="\Marinar\Uriable\Database\Seeders\MarinarUriableInstallSeeder"',
		],
        'remove' => [
            'php artisan db:seed --class="\Marinar\Uriable\Database\Seeders\MarinarUriableRemoveSeeder"',
        ]
	];
