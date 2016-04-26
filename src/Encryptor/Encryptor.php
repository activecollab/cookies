<?php

/*
 * This file is part of the Active Collab Cookies project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Cookies\Encryptor;

use InvalidArgumentException;

/**
 * Encrypt and decrypt values.
 *
 * Built on Nelmio Security Bundle encryptor:
 * https://github.com/nelmio/NelmioSecurityBundle/blob/master/Encrypter.php
 *
 * @package ActiveCollab\Cookies\Encryptor
 */
class Encryptor implements EncryptorInterface
{
    /**
     * @var resource
     */
    private $module;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var string
     */
    private $algorithm;

    /**
     * @var int
     */
    private $iv_size;

    /**
     * @param string $secret
     * @param string $algorithm
     */
    public function __construct($secret, $algorithm = MCRYPT_RIJNDAEL_256)
    {
        if (empty($secret)) {
            throw new InvalidArgumentException('Encryption secret not provided');
        }

        $this->secret = substr($secret, 0, 32);
        $this->algorithm = $algorithm;

        $this->module = @mcrypt_module_open($this->algorithm, '', MCRYPT_MODE_CBC, '');
        if ($this->module === false) {
            throw new InvalidArgumentException("The supplied encryption algorithm '$this->algorithm' is not supported by this system");
        }
        $this->iv_size = mcrypt_enc_get_iv_size($this->module);
    }

    /**
     * @param  mixed  $value
     * @return string
     */
    public function encrypt($value)
    {
        if (empty($value)) {
            return null;
        }

        $iv = mcrypt_create_iv($this->iv_size, MCRYPT_RAND);
        mcrypt_generic_init($this->module, $this->secret, $iv);

        return rtrim(base64_encode($iv . mcrypt_generic($this->module, (string) $value)), '=');
    }

    /**
     * @param  string $value
     * @return mixed
     */
    public function decrypt($value)
    {
        if (empty($value)) {
            return null;
        }

        $encrypted_data = base64_decode($value, true);
        $iv = substr($encrypted_data, 0, $this->iv_size);
        if (strlen($iv) < $this->iv_size) {
            return null;
        }

        $encrypted_data = substr($encrypted_data, $this->iv_size);
        $init = mcrypt_generic_init($this->module, $this->secret, $iv);
        if ($init === false || $init < 0) {
            return null;
        }

        return rtrim(mdecrypt_generic($this->module, $encrypted_data), "\0");
    }
}
