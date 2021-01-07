<?php

declare(strict_types=1);

namespace settingsforatk\tests;

use settingsforatk\Setting;
use settingsforatk\SettingGroup;
use settingsforatk\SettingInstaller;
use traitsforatkdata\TestCase;


class SettingInstallerTest extends TestCase
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

    public function testInstallNewSettingGroup()
    {
        $persistence = $this->getSqliteTestPersistence();
        $initialCount = (new SettingGroup($persistence))->action('count')->getOne();
        $settingInstaller = new SettingInstaller($persistence);
        $settingInstaller->installSettingGroup('SomeSG', 'Blabla');
        self::assertEquals(
            $initialCount + 1,
            (new SettingGroup($persistence))->action('count')->getOne()
        );
    }

    public function testExistigSettingGroupNotInstalledAgain()
    {
        $persistence = $this->getSqliteTestPersistence();
        $settingGroup = new SettingGroup($persistence);
        $settingGroup->set('name', 'a');
        $settingGroup->set('description', 'b');
        $settingGroup->save();

        $initialCount = (new SettingGroup($persistence))->action('count')->getOne();
        $settingInstaller = new SettingInstaller($persistence);
        $returnedSettingGroup = $settingInstaller->installSettingGroup('a', 'Blabla');
        self::assertSame(
            $initialCount,
            (new SettingGroup($persistence))->action('count')->getOne()
        );
        self::assertSame(
            'b',
            $returnedSettingGroup->get('description')
        );
        self::assertSame(
            $settingGroup->get('id'),
            $returnedSettingGroup->get('id')
        );
    }

    public function testInstallNewSettings()
    {
        $persistence = $this->getSqliteTestPersistence();
        $initialSettingCount = (new Setting($persistence))->action('count')->getOne();
        $settingInstaller = new SettingInstaller($persistence);
        $settingsToInstall = [
            'SOMESETTING' => ['name' => 'lala', 'description' => 'blabla'],
            'SOMEOTHERSETTING' => ['name' => 'lulu', 'description' => 'blublub'],
        ];
        $settingInstaller->installSettings($settingsToInstall);
        self::assertEquals(
            $initialSettingCount + 2,
            (new Setting($persistence))->action('count')->getOne()
        );

        //installing again shoudn't change anything
        $settingInstaller->installSettings($settingsToInstall);
        self::assertEquals(
            $initialSettingCount + 2,
            (new Setting($persistence))->action('count')->getOne()
        );

        //will throw exception if Setting does not exist
        $setting = new Setting($persistence);
        $setting->loadBy('ident', 'SOMESETTING');
        $setting = new Setting($persistence);
        $setting->loadBy('ident', 'SOMEOTHERSETTING');
    }

    public function testSettingGroupIsUsedOnInstallSettings()
    {
        $persistence = $this->getSqliteTestPersistence();
        $settingInstaller = new SettingInstaller($persistence);
        $settingGroup = $settingInstaller->installSettingGroup('SOMESG', 'LALA');
        $setting = $settingInstaller->installSetting(
            'SOMEMORE',
            ['value' => 3, 'description' => 'dada'],
            $settingGroup
        );

        self::assertEquals(
            $settingGroup->get('id'),
            $setting->get('setting_group_id')
        );
    }

    public function testStdValuesAreUsed()
    {
        $persistence = $this->getSqliteTestPersistence();
        $settingInstaller = new SettingInstaller($persistence);
        $settingInstaller->stdEncryptValue = 0;
        $settingInstaller->stdHidden = 0;
        $settingInstaller->stdSystem = 0;

        $setting = $settingInstaller->installSetting(
            'ALLZERO',
            ['value' => 3, 'description' => 'dada'],
        );
        self::assertSame(
            0,
            $setting->get('system')
        );
        self::assertSame(
            0,
            $setting->get('hidden')
        );
        self::assertSame(
            0,
            $setting->get('encrypt_value')
        );

        $settingInstaller->stdEncryptValue = 1;
        $settingInstaller->stdHidden = 1;
        $settingInstaller->stdSystem = 1;
        $setting = $settingInstaller->installSetting(
            'ALLONE',
            ['value' => 3, 'description' => 'dada'],
        );
        self::assertSame(
            1,
            $setting->get('system')
        );
        self::assertSame(
            1,
            $setting->get('hidden')
        );
        self::assertSame(
            1,
            $setting->get('encrypt_value')
        );
    }
}
