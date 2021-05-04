<?php

declare(strict_types=1);

namespace settingsforatk;

use Atk4\Data\Exception;
use Atk4\Data\Model;
use Atk4\Ui\Form\Control\Dropdown;
use traitsforatkdata\CreatedDateAndLastUpdatedTrait;
use traitsforatkdata\UserException;


class Setting extends Model
{

    use CreatedDateAndLastUpdatedTrait;

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
                    'encrypt_value',
                    'type' => 'integer',
                    'values' => [0 => 'Nein', 1 => 'Ja'],
                    'caption' => 'Wert verschlüsselt speichern',
                    'ui' => ['form' => [Dropdown::class]]
                ],
                [
                    'value',
                    'type' => 'text',
                    'system' => true,
                    'caption' => 'Wert',
                    'ui' => ['editable' => true]
                ],
                [
                    'hidden',
                    'type' => 'integer',
                    'system' => true,
                ],
            ]
        );

        $this->addCreatedDateAndLastUpdateFields();
        $this->addCreatedDateAndLastUpdatedHook();

        $this->hasOne(
            'setting_group_id',
            [
                'model' => [SettingGroup::class],
                'type' => 'integer',
                'system' => true,
                'ui' => ['form' => ['DropDown']]
            ]
        )
            ->addFields(
                [
                    'setting_group_name' =>
                        [
                            'name',
                            'type' => 'string',
                            'system' => true
                        ]
                ]
            );

        //system settings cannot be deleted
        $this->onHook(
            Model::HOOK_BEFORE_DELETE,
            function (self $model) {
                if ($model->get('system')) {
                    throw new UserException(
                        'Diese Einstellung ist eine Systemeinstellung und kann nicht gelöscht werden.'
                    );
                }
            }
        );

        //ident of system setting cannot be edited if set
        $this->onHook(
            Model::HOOK_AFTER_LOAD,
            function (self $model) {
                if (
                    $model->get('system')
                    && $model->get('ident')
                ) {
                    $model->getField('ident')->read_only = true;
                }
            }
        );

        $this->onHook(
            Model::HOOK_AFTER_LOAD,
            function (self $model) {
                $model->decryptValue();
            },
            [],
            1
        );

        $this->onHook(
            Model::HOOK_BEFORE_SAVE,
            function (self $model) {
                $model->encryptValue();
            },
            [],
            999
        );
    }

    protected function decryptValue(): void
    {
        if ($this->get('encrypt_value') === 0) {
            return;
        }
        $key = ENCRYPTFIELD_KEY;
        $decoded = base64_decode((string)$this->get('value'));
        if (mb_strlen($decoded, '8bit') < (SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES)) {
            throw new Exception('An error occurred decrypting the field value');  //@codeCoverageIgnore
        }
        $nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
        $ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');

        $plain = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
        if ($plain === false) {
            throw new Exception('An error occurred decrypting the field value');  //@codeCoverageIgnore
        }
        sodium_memzero($ciphertext);
        sodium_memzero($key);

        $this->set('value', $plain);
    }

    protected function encryptValue()
    {
        if ($this->get('encrypt_value') === 0) {
            return;
        }
        //sodium needs string
        $key = ENCRYPTFIELD_KEY;
        $value = (string)$this->get('value');
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = base64_encode($nonce . sodium_crypto_secretbox($value, $nonce, $key));
        sodium_memzero($value);
        sodium_memzero($key);
        $this->set('value', $cipher);
    }
}
