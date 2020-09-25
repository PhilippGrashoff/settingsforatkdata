<?php

declare(strict_types=1);

namespace settingsforatk\tests;

use settingsforatk\Setting;
use settingsforatk\SettingGroup;
use settingsforatk\tests\testclasses\AppWithSettings;
use traitsforatkdata\tests\TestCase;

class SettingsTraitTest extends TestCase
{

    protected $sqlitePersistenceModels = [
        Setting::class,
        SettingGroup::class
    ];

    public function testAddSettingTwiceOnlyAddsOne()
    {
        $persistence = $this->getSqliteTestPersistence();
        $app = new AppWithSettings();
        $initialCount = (new Setting($persistence))->action('count')->getOne();
        $setting = new Setting($persistence);
        $setting->set('ident', 'LALADU');
        $app->addSetting($setting);
        self::assertEquals(
            $initialCount + 1,
            (new Setting($persistence))->action('count')->getOne()
        );
        $app->addSetting($setting);
        self::assertEquals(
            $initialCount + 1,
            (new Setting($persistence))->action('count')->getOne()
        );
    }

    public function testSettingsAreLoadedIfNot()
    {
        $app = new AppWithSettings();
        $setting = new Setting($this->getSqliteTestPersistence());
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
        $app = new AppWithSettings();
        self::assertNull($app->getSetting('SOMENONEXISTANTSETTING'));
    }

    public function testUnloadSettings()
    {
        $app = new AppWithSettings();
        $app->getSetting('LALA');

        self::assertThat(
            $app,
            self::attributeEqualTo('_settingsLoaded', true)
        );

        $app->unloadSettings();
        self::assertThat(
            $app,
            self::attributeEqualTo('_settingsLoaded', false)
        );
    }

    public function testSettingExists()
    {
        $app = new AppWithSettings();
        $setting = new Setting($this->getSqliteTestPersistence());
        $setting->set('ident', 'SOMEEXISTINGSETTING');
        $setting->set('value', 'HALLOHALLO');
        $app->addSetting($setting);
        self::assertTrue($app->settingExists('SOMEEXISTINGSETTING'));
        self::assertFalse($app->settingExists('SOMEOTHERNONEXISTINGSETTING'));
    }
    
    public function testGetSTDSettings()
    {
        $app = new AppWithSettings();
        
        $setting = new Setting($this->getSqliteTestPersistence());
        $setting->set('ident', 'STD_NAME');
        $setting->set('value', 'HALLOHALLO');
        $app->addSetting($setting);
        
        $setting = new Setting($this->getSqliteTestPersistence());
        $setting->set('ident', 'SOMENONSTDSETTING');
        $setting->set('value', 'PIRIDA');
        $app->addSetting($setting);
        
        $std = $app->getAllSTDSettings();
        self::assertArrayHasKey('STD_NAME', $std);
        self::assertArrayNotHasKey('SOMENONSTDSETTING', $std);
    }
    
    public function testSetSetting()
    {
        $app = new AppWithSettings();
        
        $setting = new Setting($this->getSqliteTestPersistence());
        $setting->set('ident', 'STD_NAME');
        $setting->set('value', 'HALLOHALLOHALLOHALLO');
        $app->setSetting($setting);
        
        self::assertEquals(
            'HALLOHALLOHALLOHALLO', 
            $app->getSetting('STD_NAME')
        );
    }
}