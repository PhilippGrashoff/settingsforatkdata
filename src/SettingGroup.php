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

        $this->addField('name');

        $this->addField(
            'description',
            [
                'type' => 'text',
            ]
        );

        $this->addField(
            'sort',
            [
                'type' => 'integer',
            ]
        );

        $this->setOrder('sort');

        $this->addCreatedDateFieldAndHook();
        $this->addLastUpdatedFieldAndHook();

        $this->hasMany(Setting::class, ['model' => [Setting::class]]);
    }
}
