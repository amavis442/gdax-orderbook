<?php
declare(strict_types=1);

namespace Amavis442\Trading\Services;

use Illuminate\Database\Capsule\Manager as DB;

class SettingsService {

    public function getSettings() : array {
        $settings = DB::table('settings')->orderby('id','desc')->limit(1)->first();

        return (array)$settings;
    }

}
