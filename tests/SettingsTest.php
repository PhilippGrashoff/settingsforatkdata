<?php

declare(strict_types=1);

namespace PhilippR\Atk4\Settings\Tests;

use Atk4\Data\Persistence\Sql;
use Atk4\Data\Schema\TestCase;
use PhilippR\Atk4\Settings\Setting;
use PhilippR\Atk4\Settings\SettingGroup;
use PhilippR\Atk4\Settings\Settings;

class SettingsTest extends TestCase
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
            define('ENCRYPTFIELD_KEY', '00123456789abcdef123456789abcdef');
        }
    }

    public function testGetNonExistentSetting()
    {
        self:$this->expectExceptionMessage('');
        Settings::getInstance()->getSetting('SOMENONEXISTANTSETTING');
    }

    public function testSettingExists(): void
    {
        $this->addSetting('SOMEEXISTINGSETTING', 'HALLOHALLO');
        self::assertTrue(Settings::getInstance()->settingExists('SOMEEXISTINGSETTING'));
        self::assertFalse(Settings::getInstance()->settingExists('SOMEOTHERNONEXISTINGSETTING'));
    }

    public function testGetSettingsIdentStartWith(): void
    {
        $this->addSetting('STD_NAME', 'HALLOHALLO');
        $this->addSetting('SOMENONSTDSETTING', 'PIRIDA');

        $result = Settings::getInstance()->getSettingsIdentStartWith('STD_');
        self::assertArrayHasKey('STD_NAME', $result);
        self::assertArrayNotHasKey('SOMENONSTDSETTING', $result);
    }


    public function testSettingsDeleteEmptiesSettingsCache(): void
    {
    }

    public function testSettingCreationEmptiesSettingsCache(): void
    {
    }

    public function testSettingUpdateEmptiesSettingsCache(): void
    {
    }

    protected function addSetting(string $ident, mixed $value): Setting
    {
        $setting = new Setting($this->db);
        $setting->set('ident', $ident);
        $setting->set('value', $value);
        $setting->save();

        return $setting;
    }
}