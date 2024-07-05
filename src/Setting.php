<?php

declare(strict_types=1);

namespace PhilippR\Atk4\Settings;

use Atk4\Data\Exception;
use Atk4\Data\Model;
use PhilippR\Atk4\ModelTraits\CreatedDateAndLastUpdatedTrait;


class Setting extends Model
{

    use CreatedDateAndLastUpdatedTrait;

    public $table = 'setting';


    protected function init(): void
    {
        parent::init();

        $this->addField(
            'ident',
            [
                'type' => 'string',
            ]
        );

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
            ]
        );

        $this->addField(

            'system',
            [
                'type' => 'integer',
                'system' => true
            ]
        );

        $this->addField(
            'encrypt_value',
            [
                'type' => 'boolean',
                'default' => false,
                'caption' => 'Save value encrypted',
            ]
        );

        $this->addField(
            'value',
            [
                'type' => 'text',
            ]
        );

        $this->addField(
            'hidden',
            [
                'type' => 'boolean',
                'system' => true,
            ]
        );

        $this->addCreatedDateFieldAndHook();
        $this->addLastUpdatedFieldAndHook();

        $this->hasOne(
            'setting_group_id',
            [
                'model' => [SettingGroup::class],
                'system' => true,
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
            function (self $settingEntity) {
                $settingEntity->assertNotSystem();
            }
        );

        //ident cannot be changed once set
        $this->onHook(
            Model::HOOK_AFTER_LOAD,
            function (self $settingEntity) {
                if ($settingEntity->get('ident')) {
                    $settingEntity->getField('ident')->readOnly = true;
                }
            }

        );

        $this->onHook(
            Model::HOOK_AFTER_LOAD,
            function (self $settingEntity) {
                $settingEntity->decryptValue();
            },
            [],
            1
        );

        $this->onHook(
            Model::HOOK_BEFORE_SAVE,
            function (self $settingEntity) {
                $settingEntity->encryptValue();
            },
            [],
            999
        );
    }

    protected function assertNotSystem(): void
    {
        if ($this->get('system')) {
            throw new Exception(
                'This is a system setting and cannot be deleted.'
            );
        }
    }

    protected function decryptValue(): void
    {
        if ($this->get('encrypt_value') !== true) {
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

    protected function encryptValue(): void
    {
        if ($this->get('encrypt_value') !== true) {
            return;
        }
        $key = ENCRYPTFIELD_KEY;
        //sodium needs string
        $value = (string)$this->get('value');
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = base64_encode($nonce . sodium_crypto_secretbox($value, $nonce, $key));
        sodium_memzero($value);
        sodium_memzero($key);
        $this->set('value', $cipher);
    }
}
