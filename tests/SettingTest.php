<?php

declare(strict_types=1);

namespace settingsforatk\tests;

use atk4\data\Exception;
use settingsforatk\Setting;
use settingsforatk\SettingGroup;
use settingsforatk\tests\testclasses\SettingWithoutEncryption;
use traitsforatkdata\TestCase;
use traitsforatkdata\UserException;


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
        $this->expectException(UserException::class);
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

    public function testOptionalEncryption()
    {
        $persistence = $this->getSqliteTestPersistence();
        $setting = new Setting($persistence);
        $setting->set('system', 1);
        $setting->set('ident', 'SOMEIDENT');
        $setting->set('value', 'Bla');
        $setting->set('encrypt_value', 0);
        $setting->save();

        $withoutEncrpytion = new SettingWithoutEncryption($persistence);
        $withoutEncrpytion->load($setting->get('id'));
        self::assertSame(
            $setting->get('value'),
            $withoutEncrpytion->get('value')
        );

        $setting->set('encrypt_value', 1);
        $setting->save();

        $withoutEncrpytion->reload();
        self::assertNotSame(
            $setting->get('value'),
            $withoutEncrpytion->get('value')
        );

        self::assertGreaterThan(
            30,
            strlen($withoutEncrpytion->get('value'))
        );

        $setting->set('encrypt_value', 0);
        $setting->save();
        $withoutEncrpytion->reload();
        self::assertSame(
            $setting->get('value'),
            $withoutEncrpytion->get('value')
        );
    }
}
