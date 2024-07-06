<?php

declare(strict_types=1);

namespace PhilippR\Atk4\Settings;

use Atk4\Data\Persistence;

class SettingInstaller
{

    protected Persistence $persistence;

    protected int $stdSystem = 1;

    protected int $stdEncryptValue = 0;

    protected int $stdHidden = 0;


    /**
     * @param Persistence $persistence
     */
    public function __construct(Persistence $persistence)
    {
        $this->persistence = $persistence;
    }

    /**
     * @param string $name
     * @param string $description
     * @return SettingGroup
     * @throws \Atk4\Core\Exception
     * @throws \Atk4\Data\Exception
     */
    public function installSettingGroup(string $name, string $description): SettingGroup
    {
        $settingGroup = new SettingGroup($this->persistence);
        $settingGroup->tryLoadBy('name', $name);
        if ($settingGroup->loaded()) {
            return $settingGroup;
        }
        $settingGroup->set('name', $name);
        $settingGroup->set('description', $description);
        $settingGroup->save();

        return $settingGroup;
    }

    /**
     * @param array $settings
     * @param SettingGroup|null $settingGroup
     * @return void
     */
    public function installSettings(array $settings, SettingGroup $settingGroup = null): void
    {
        foreach ($settings as $settingIdent => $values) {
            $this->installSetting($settingIdent, $values, $settingGroup);
        }
    }

    /**
     * @param string $settingIdent
     * @param array $values
     * @param SettingGroup|null $settingGroup
     * @return Setting
     * @throws \Atk4\Core\Exception
     * @throws \Atk4\Data\Exception
     */
    public function installSetting(string $settingIdent, array $values, SettingGroup $settingGroup = null): Setting
    {
        $setting = new Setting($this->persistence);
        $setting->tryLoadBy('ident', $settingIdent);
        if ($setting->loaded()) {
            return $setting;
        }
        $setting->set('ident', $settingIdent);
        $setting->setMulti($values);
        if (!array_key_exists('system', $values)) {
            $setting->set('system', $this->stdSystem);
        }
        if (!array_key_exists('encrypt_value', $values)) {
            $setting->set('encrypt_value', $this->stdEncryptValue);
        }
        if (!array_key_exists('hidden', $values)) {
            $setting->set('hidden', $this->stdHidden);
        }
        if ($settingGroup) {
            $setting->set('setting_group_id', $settingGroup->get('id'));
        }
        $setting->save();

        return $setting;
    }
}
