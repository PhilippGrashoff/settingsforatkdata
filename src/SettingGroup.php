<?php

declare(strict_types=1);

namespace PhilippR\Atk4\Settings;

use Atk4\Data\Model;
use PhilippR\Atk4\ModelTraits\CreatedDateAndLastUpdatedTrait;


class SettingGroup extends Model
{

    use CreatedDateAndLastUpdatedTrait;

    public $table = 'setting_group';


    protected function init(): void
    {
        parent::init();

        $this->addField(
            'name',
            [
                'type' => 'string'
            ]
        );

        $this->addField(
            'description',
            [
                'type' => 'text',
                'caption' => 'Beschreibung'
            ]
        );

        $this->addField(
            'order',
            [
                'type' => 'integer',
                'caption' => 'Sortierung'
            ]
        );

        $this->setOrder('order');

        $this->addCreatedDateAndLastUpdateFields();
        $this->addCreatedDateAndLastUpdatedHook();

        $this->hasMany(Setting::class, ['model' => [Setting::class]]);
    }
}
