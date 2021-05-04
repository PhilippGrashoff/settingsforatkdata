<?php

declare(strict_types=1);

namespace settingsforatk;

use Atk4\Data\Model;
use traitsforatkdata\CreatedDateAndLastUpdatedTrait;


class SettingGroup extends Model
{

    use CreatedDateAndLastUpdatedTrait;

    public $table = 'setting_group';


    protected function init(): void
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

        $this->hasMany(Setting::class, ['model' => [Setting::class]]);
    }
}
