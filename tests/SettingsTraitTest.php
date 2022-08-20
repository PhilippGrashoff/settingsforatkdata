<?php

declare(strict_types=1);

namespace settingsforatk\tests;

use Atk4\Data\Exception;
use settingsforatk\Setting;
use settingsforatk\SettingGroup;
use settingsforatk\tests\testclasses\AppWithSettings;
use traitsforatkdata\TestCase;

class SettingsTraitTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        if(!defined('ENCRYPTFIELD_KEY')) {
            define('ENCRYPTFIELD_KEY', '00123456789abcdef123456789abcdef');
        }
    }

    protected $sqlitePersistenceModels = [
        Setting::class,
        SettingGroup::class
    ];

    public function testAddSettingTwiceOnlyAddsOne()
    {
        $app = $this->getAppWithSettingsAndDb();
        $initialCount = (new Setting($app->db))->action('count')->getOne();
        $app->addSetting('LALADU', 'Somevalue');
        self::assertEquals(
            $initialCount + 1,
            (new Setting($app->db))->action('count')->getOne()
        );
        $app->addSetting('LALADU', 'Somevalue');
        self::assertEquals(
            $initialCount + 1,
            (new Setting($app->db))->action('count')->getOne()
        );
    }

    public function testSettingsAreLoadedIfNot()
    {
        $app = $this->getAppWithSettingsAndDb();
        $app->addSetting('RERERERE', 'PIRIDI');
        self::assertEquals(
            'PIRIDI',
            $app->getSetting('RERERERE')
        );
    }

    public function testGetNonExistantSetting()
    {
        $app = $this->getAppWithSettingsAndDb();
        self::assertNull($app->getSetting('SOMENONEXISTANTSETTING'));
    }

    public function testSettingExists()
    {
        $app = $this->getAppWithSettingsAndDb();
        $app->addSetting('SOMEEXISTINGSETTING', 'HALLOHALLO');
        self::assertTrue($app->settingExists('SOMEEXISTINGSETTING'));
        self::assertFalse($app->settingExists('SOMEOTHERNONEXISTINGSETTING'));
    }
    
    public function testGetSTDSettings()
    {
        $app = $this->getAppWithSettingsAndDb();
        $app->addSetting('STD_NAME', 'HALLOHALLO');
        $app->addSetting('SOMENONSTDSETTING', 'PIRIDA');
        
        $std = $app->getAllSTDSettings();
        self::assertArrayHasKey('STD_NAME', $std);
        self::assertArrayNotHasKey('SOMENONSTDSETTING', $std);
    }

    public function testUpdateSetting() {
        $app = $this->getAppWithSettingsAndDb();
        $app->addSetting('STD_NAME', 'HALLOHALLO');
        self::assertEquals(
            'HALLOHALLO',
            $app->getSetting('STD_NAME')
        );

        $app->updateSetting('STD_NAME', 'GEGE');
        self::assertEquals(
            'GEGE',
            $app->getSetting('STD_NAME')
        );
    }

    public function testReloadOfSettingsEmptiesSettingsArray() {
        $app = $this->getAppWithSettingsAndDb();
        $app->addSetting('STD_NAME', 'HALLOHALLO');
        self::assertTrue($app->settingExists('STD_NAME'));
        $setting = new Setting($app->db);
        $setting->loadBy('ident', 'STD_NAME');
        $setting->delete();
        self::assertTrue($app->settingExists('STD_NAME'));
        $app->reloadSettings();
        self::assertFalse($app->settingExists('STD_NAME'));
    }




    public function testUpdateSettingExceptionSettingNotExists() {
        $app = $this->getAppWithSettingsAndDb();
        self::expectException(Exception::class);
        $app->updateSetting('SOMENONEXISTANT', 'FDF');
    }

    protected function getAppWithSettingsAndDb(): AppWithSettings {
        $persistence = $this->getSqliteTestPersistence();
        $app = new AppWithSettings();
        $app->db = $persistence;

        return $app;
    }
}