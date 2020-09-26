<?php

declare(strict_types=1);

namespace settingsforatk;

use atk4\data\Model;
use traitsforatkdata\CreatedDateAndLastUpdatedTrait;


class SettingGroup extends Model
{

    use CreatedDateAndLastUpdatedTrait;

    public $table = 'setting_group';


    public function init(): void
    {
        parent::init();

        $this->addFields(
            [
                [
                    'name',
                    'type' => 'string'
                ],
                [
                    'description',
                    'type' => 'text',
                    'caption' => 'Beschreibung'
                ],
                [
                    'order',
                    'type' => 'integer',
                    'caption' => 'Sortierung'
                ],
            ]
        );

        $this->addCreatedDateAndLastUpdateFields();
        $this->addCreatedDateAndLastUpdatedHook();

        $this->hasMany(Setting::class, [Setting::class]);
    }
}
