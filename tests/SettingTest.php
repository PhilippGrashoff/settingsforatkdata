<?php

declare(strict_types=1);

namespace settingsforatk\tests;

use atk4\data\Exception;
use settingsforatk\Setting;
use settingsforatk\SettingGroup;
use traitsforatkdata\TestCase;
use settingsforatk\UserException;


class SettingTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        if (!defined('ENCRYPTFIELD_KEY')) {
            define('ENCRYPTFIELD_KEY', '12003456789abcdef123456789abcdef');
        }
    }

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
