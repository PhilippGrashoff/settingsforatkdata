<?php

declare(strict_types=1);

namespace settingsforatk\tests\testclasses;

use Atk4\Ui\App;
use settingsforatk\SettingsTrait;

class AppWithSettings extends App
{

    use SettingsTrait;

    public $always_run = false;
}