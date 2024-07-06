<?php

declare(strict_types=1);

namespace PhilippR\Atk4\Settings\Tests;

use Atk4\Data\Persistence\Sql;
use Atk4\Data\Schema\TestCase;
use PhilippR\Atk4\Settings\Setting;
use PhilippR\Atk4\Settings\SettingGroup;

class SettingGroupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->db = new Sql('sqlite::memory:');
        $this->createMigrator(new Setting($this->db))->create();
        $this->createMigrator(new SettingGroup($this->db))->create();
    }

    public function testInit(): void
    {
        $settingGroup = (new SettingGroup($this->db))->createEntity();
        $settingGroup->save();
        self::assertTrue($settingGroup->hasReference(Setting::class));
    }
}
