<?php

declare(strict_types=1);

namespace PhilippR\Atk4\Settings\Tests;

use Atk4\Data\Persistence\Sql;
use Atk4\Data\Schema\TestCase;
use PhilippR\Atk4\Settings\Setting;
use PhilippR\Atk4\Settings\SettingGroup;
use PhilippR\Atk4\Settings\SettingInstaller;

class SettingInstallerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->db = new Sql('sqlite::memory:');
        $this->createMigrator(new Setting($this->db))->create();
        $this->createMigrator(new SettingGroup($this->db))->create();
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        if (!defined('ENCRYPTFIELD_KEY')) {
            define('ENCRYPTFIELD_KEY', '12003456789abcdef123456789abcdef');
        }
    }

    public function testInstallNewSettingGroup()
    {
        $initialCount = (new SettingGroup($this->db))->action('count')->getOne();
        $settingInstaller = new SettingInstaller($this->db);
        $settingInstaller->installSettingGroup('SomeSG', 'Blabla');
        self::assertEquals(
            $initialCount + 1,
            (new SettingGroup($this->db))->action('count')->getOne()
        );
    }

    public function testExistigSettingGroupNotInstalledAgain()
    {
        $settingGroup = new SettingGroup($this->db);
        $settingGroup->set('name', 'a');
        $settingGroup->set('description', 'b');
        $settingGroup->save();

        $initialCount = (new SettingGroup($this->db))->action('count')->getOne();
        $settingInstaller = new SettingInstaller($this->db);
        $returnedSettingGroup = $settingInstaller->installSettingGroup('a', 'Blabla');
        self::assertSame(
            $initialCount,
            (new SettingGroup($this->db))->action('count')->getOne()
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
        $initialSettingCount = (new Setting($this->db))->action('count')->getOne();
        $settingInstaller = new SettingInstaller($this->db);
        $settingsToInstall = [
            'SOMESETTING' => ['name' => 'lala', 'description' => 'blabla'],
            'SOMEOTHERSETTING' => ['name' => 'lulu', 'description' => 'blublub'],
        ];
        $settingInstaller->installSettings($settingsToInstall);
        self::assertEquals(
            $initialSettingCount + 2,
            (new Setting($this->db))->action('count')->getOne()
        );

        //installing again shouldn't change anything
        $settingInstaller->installSettings($settingsToInstall);
        self::assertEquals(
            $initialSettingCount + 2,
            (new Setting($this->db))->action('count')->getOne()
        );

        //will throw exception if Setting does not exist
        $setting = new Setting($this->db);
        $setting->loadBy('ident', 'SOMESETTING');
        $setting = new Setting($this->db);
        $setting->loadBy('ident', 'SOMEOTHERSETTING');
    }

    public function testSettingGroupIsUsedOnInstallSettings()
    {
        $settingInstaller = new SettingInstaller($this->db);
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
        $settingInstaller = new SettingInstaller($this->db);
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
