<?php

declare(strict_types=1);

namespace settingsforatk;


trait SettingsTrait
{

    protected $_settings = [];
    protected $_settingsLoaded = false;

    public function getSetting(string $ident)
    {
        $this->_loadSettings();

        if (isset($this->_settings[$ident])) {
            return $this->_settings[$ident];
        }

        return null;
    }

    protected function _loadSettings(): void
    {
        if ($this->_settingsLoaded) {
            return;
        }
        foreach (new Setting(isset($this->db) ? $this->db : $this->persistence) as $setting) {
            $this->_settings[$setting->get('ident')] = $setting->get('value');
        }
        $this->_settingsLoaded = true;
    }

    /**
     * returns all STD_ settings, which are typically used in templates
     */
    public function getAllSTDSettings(): array
    {
        $return = [];
        $this->_loadSettings();

        foreach ($this->_settings as $key => $value) {
            if (substr($key, 0, 4) == 'STD_') {
                $return[$key] = $value;
            }
        }
        return $return;
    }

    public function settingExists(string $ident): bool
    {
        $this->_loadSettings();
        return array_key_exists($ident, $this->_settings);
    }

    /**
     * For "installers": Add a setting if it does not exist yet
     */
    public function addSetting(Setting $setting): void
    {
        $this->_loadSettings();
        if (!array_key_exists($setting->get('ident'), $this->_settings)) {
            $setting->save();
            $this->_settingsLoaded = false;
        }
    }
}