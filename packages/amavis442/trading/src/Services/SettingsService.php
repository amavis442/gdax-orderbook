<?php
declare(strict_types=1);

namespace Amavis442\Trading\Services;

use Amavis442\Trading\Contracts\SettingsServiceInterface;

use Illuminate\Database\Capsule\Manager as DB;

class SettingsService implements SettingsServiceInterface {

    public function getSettings() : array {
        $settings = DB::table('settings')->orderby('id','desc')->limit(1)->first();

        return (array)$settings;
    }

}
