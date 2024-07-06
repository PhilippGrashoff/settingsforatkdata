<?php

declare(strict_types=1);

namespace PhilippR\Atk4\Settings\Tests\TestClasses;

use PhilippR\Atk4\Settings\Setting;

class SettingWithoutEncryption extends Setting
{

    protected function encryptValue(): void
    {
    }

    protected function decryptValue(): void
    {
    }
}