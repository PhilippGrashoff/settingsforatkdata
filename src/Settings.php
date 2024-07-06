<?php

declare(strict_types=1);

namespace PhilippR\Atk4\Settings;

use Atk4\Core\Exception;

class Settings
{

    protected static ?Settings $instance = null;

    protected array $_settings = [];
    protected bool $_settingsLoaded = false;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    protected function __construct()
    {
    }

    protected function __clone()
    {
    }

    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    /**
     * @param string $ident
     * @return Setting|null
     */
    public function getSetting(string $ident): ?Setting
    {
        $this->_loadSettings();

        if (isset($this->_settings[$ident])) {
            return $this->_settings[$ident];
        }

        return null;
    }

    /**
     * @return void
     */
    protected function _loadSettings(): void
    {
        if ($this->_settingsLoaded) {
            return;
        }
        $this->_settings = [];
        foreach (new Setting($this->getPersistence()) as $setting) {
            $this->_settings[$setting->get('ident')] = $setting->get('value');
        }
        $this->_settingsLoaded = true;
    }

    /**
     * returns all settings where the ident starts with the given string
     *
     * @param string $startsWith
     * @return array
     */
    public function getSettingsThatStartWith(string $startsWith): array
    {
        $return = [];
        $this->_loadSettings();

        foreach ($this->_settings as $key => $value) {
            if (str_starts_with($key, $startsWith)) {
                $return[$key] = $value;
            }
        }
        return $return;
    }

    /**
     * @param string $ident
     * @return bool
     */
    public function settingExists(string $ident): bool
    {
        $this->_loadSettings();
        return array_key_exists($ident, $this->_settings);
    }

    /**
     * Forces reload of settings on next setting loading. Is called from Setting hooks to ensure the setting cache is
     * never outdated.
     *
     * @return void
     */
    public function emptySettingsCache(): void
    {
        $this->_settingsLoaded = false;
        $this->_settings = [];
    }
}