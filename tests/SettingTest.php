<?php

declare(strict_types=1);

namespace PhilippR\Atk4\Settings\Tests;

use Atk4\Data\Persistence\Sql;
use Atk4\Data\Schema\TestCase;
use PhilippR\Atk4\Settings\Setting;
use PhilippR\Atk4\Settings\SettingGroup;

class SettingTest extends TestCase
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
    
    public function testSystemSettingNotDeletable(): void
    {
        $setting = (new Setting($this->db))->createEntity();
        $setting->set('system', 1);
        $setting->save();
        $this->expectExceptionMessage('This is a system setting and cannot be deleted.');
        $setting->delete();
    }

    public function testSystemSettingIdentNotEditable()
    {
        $setting = (new Setting($this->db))->createEntity();
        $setting->set('system', 1);
        $setting->set('ident', 'SOMEIDENT');
        $setting->save();
        $this->expectException(Exception::class);
        $setting->set('ident', 'SOMEOTHERIDENT');
    }

    public function testOptionalEncryption()
    {
        $setting = (new Setting($this->db))->createEntity();
        $setting->set('system', 1);
        $setting->set('ident', 'SOMEIDENT');
        $setting->set('value', 'Bla');
        $setting->set('encrypt_value', 0);
        $setting->save();

        $withoutEncrpytion = new SettingWithoutEncryption($this->db);
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
