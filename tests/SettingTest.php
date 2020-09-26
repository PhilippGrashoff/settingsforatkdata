<?php

declare(strict_types=1);

namespace settingsforatk\tests;

use atk4\data\Exception;
use settingsforatk\Setting;
use settingsforatk\SettingGroup;
use traitsforatkdata\TestCase;


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
        $setting = new Setting($this->getSqliteTestPersistence());
        $setting->set('system', 1);
        $setting->save();
        $this->expectException(Exception::class);
        $setting->delete();
    }
    
    public function testExceptionClassCanBeDefined()
    {
        $setting = new Setting($this->getSqliteTestPersistence());
        $setting->set('system', 1);
        $setting->exceptionClassForUserMessageInSetting = \PHPUnit\Util\Exception::class;
        $setting->save();
        $this->expectException(\PHPUnit\Util\Exception::class);
        $setting->delete();
    }

    public function testSystemSettingIdentNotEditable()
    {
        $setting = new Setting($this->getSqliteTestPersistence());
        $setting->set('system', 1);
        $setting->set('ident', 'SOMEIDENT');
        $setting->save();
        $this->expectException(Exception::class);
        $setting->set('ident', 'SOMEOTHERIDENT');
    }
}
