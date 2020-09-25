<?php

declare(strict_types=1);

namespace settingsforatk\tests\testclasses;

use atk4\ui\App;
use settingsforatk\SettingsTrait;

class AppWithSettings extends App
{

    use SettingsTrait;

    public $always_run = false;
}