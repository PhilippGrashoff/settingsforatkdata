<?php

declare(strict_types=1);

namespace settingsforatk\tests\testclasses;

use Atk4\Data\Model;
use Atk4\Ui\Form\Control\Dropdown;

class SettingWithoutEncryption extends Model
{

    public $table = 'setting';

    protected function init(): void
    {
        parent::init();

        $this->addFields(
            [
                [
                    'ident',
                    'type' => 'string',
                    'caption' => 'Schlüssel'
                    ,
                    'ui' => ['readonly' => true]
                ],
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
                    'system',
                    'type' => 'integer',
                    'system' => true
                ],
                [
                    'value',
                    'type' => 'string',
                    'system' => true,
                    'caption' => 'Wert',
                    'ui' => ['editable' => true]
                ],
                [
                    'hidden',
                    'type' => 'integer',
                    'system' => true,
                ],
                [
                    'encrypt_value',
                    'type' => 'integer',
                    'values' => [0 => 'Nein', 1 => 'Ja'],
                    'caption' => 'Wert verschlüsselt speichern',
                    'ui' => ['form' => [Dropdown::class]]
                ]
            ]
        );
    }
}