<?php

declare(strict_types=1);

namespace settingsforatk\tests;

use atk4\data\Exception;
use settingsforatk\Setting;
use settingsforatk\SettingGroup;
use traitsforatkdata\tests\TestCase;
use settingsforatk\UserException;


class SettingTest extends TestCase
{

    protected $sqlitePersistenceModels = [
        Setting::class,
        SettingGroup::class
    ];

    public function testSystemSettingNotDeletable()
    {
        $s = new Setting($this->getSqliteTestPersistence());
        $s->set('system', 1);
        $s->save();
        $this->expectException(UserException::class);
        $s->delete();
    }

    public function testSystemSettingIdentNotEditable()
    {
        $s = new Setting($this->getSqliteTestPersistence());
        $s->set('system', 1);
        $s->set('ident', 'SOMEIDENT');
        $s->save();
        $this->expectException(Exception::class);
        $s->set('ident', 'SOMEOTHERIDENT');
    }
}
