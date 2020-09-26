<?php

declare(strict_types=1);

namespace settingsforatk\tests;

use settingsforatk\Setting;
use settingsforatk\SettingGroup;
use traitsforatkdata\TestCase;

class SettingGroupTest extends TestCase
{

    protected $sqlitePersistenceModels = [
        SettingGroup::class
    ];

    public function testInit()
    {
        $settingGroup = new SettingGroup($this->getSqliteTestPersistence());
        $settingGroup->save();
        self::assertTrue($settingGroup->hasRef(Setting::class));
    }
}
