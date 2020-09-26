<?php

declare(strict_types=1);

namespace settingsforatk\tests;

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
        $setting = new Setting($app->db);
        $setting->set('ident', 'LALADU');
        $app->addSetting($setting);
        self::assertEquals(
            $initialCount + 1,
            (new Setting($app->db))->action('count')->getOne()
        );
        $app->addSetting($setting);
        self::assertEquals(
            $initialCount + 1,
            (new Setting($app->db))->action('count')->getOne()
        );
    }

    public function testSettingsAreLoadedIfNot()
    {
        $app = $this->getAppWithSettingsAndDb();
        $setting = new Setting($app->db);
        $setting->set('ident', 'RERERERE');
        $setting->set('value', 'PIRIDI');

        $app->addSetting($setting);
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
        $setting = new Setting($app->db);
        $setting->set('ident', 'SOMEEXISTINGSETTING');
        $setting->set('value', 'HALLOHALLO');
        $app->addSetting($setting);
        self::assertTrue($app->settingExists('SOMEEXISTINGSETTING'));
        self::assertFalse($app->settingExists('SOMEOTHERNONEXISTINGSETTING'));
    }
    
    public function testGetSTDSettings()
    {
        $app = $this->getAppWithSettingsAndDb();
        
        $setting = new Setting($app->db);
        $setting->set('ident', 'STD_NAME');
        $setting->set('value', 'HALLOHALLO');
        $app->addSetting($setting);
        
        $setting = new Setting($app->db);
        $setting->set('ident', 'SOMENONSTDSETTING');
        $setting->set('value', 'PIRIDA');
        $app->addSetting($setting);
        
        $std = $app->getAllSTDSettings();
        self::assertArrayHasKey('STD_NAME', $std);
        self::assertArrayNotHasKey('SOMENONSTDSETTING', $std);
    }

    protected function getAppWithSettingsAndDb(): AppWithSettings {
        $persistence = $this->getSqliteTestPersistence();
        $app = new AppWithSettings();
        $app->db = $persistence;

        return $app;
    }
}