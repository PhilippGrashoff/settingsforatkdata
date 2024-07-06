<?php

declare(strict_types=1);

namespace PhilippR\Atk4\Settings;

use Atk4\Data\Exception;

class Settings
{

    protected static ?Settings $instance = null;

    protected array $settings = [];
    protected bool $settingsLoaded = false;

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
     * @return Setting
     */
    public function getSetting(string $ident): Setting
    {
        $this->loadSettings();
        if (!isset($this->settings[$ident])) {
            throw new Exception('The setting "' . $ident . '" does not exist.');
        }

        return $this->settings[$ident];
    }

    /**
     * Loads all settings and stores the ident => value in $settings array
     *
     * @return void
     */
    protected function loadSettings(): void
    {
        if ($this->settingsLoaded) {
            return;
        }
        $this->settings = [];
        foreach (new Setting($this->getPersistence()) as $setting) {
            $this->settings[$setting->get('ident')] = $setting->get('value');
        }
        $this->settingsLoaded = true;
    }

    /**
     * returns all settings where the ident starts with the given string
     *
     * @param string $startsWith
     * @return array
     */
    public function getSettingsIdentStartWith(string $startsWith): array
    {
        $return = [];
        $this->loadSettings();

        foreach ($this->settings as $ident => $value) {
            if (str_starts_with($ident, $startsWith)) {
                $return[$ident] = $value;
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
        $this->loadSettings();
        return array_key_exists($ident, $this->settings);
    }

    /**
     * Forces reload of settings on next setting loading. Is called from Setting hooks to ensure the setting cache is
     * never outdated.
     *
     * @return void
     */
    public function emptySettingsCache(): void
    {
        $this->settingsLoaded = false;
        $this->settings = [];
    }
}