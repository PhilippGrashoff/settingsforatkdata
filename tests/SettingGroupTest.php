<?php

declare(strict_types=1);

namespace settingsforatk\tests;

use settingsforatk\Setting;
use settingsforatk\SettingGroup;
use traitsforatkdata\tests\TestCase;

class SettingGroupTest extends TestCase
{

    protected $sqlitePersistenceModels = [
        Setting::class,
        SettingGroup::class
    ];

    public function testInit()
    {
        $s = new SettingGroup(self::$app->db);
        $s->save();
        self::assertTrue(true);
    }
}
