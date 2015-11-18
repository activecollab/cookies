<?php

namespace ActiveCollab\Cookies\Encryptor;

/**
 * @package ActiveCollab\Cookies\Encryptor
 */
interface EncryptorInterface
{
    /**
     * @param  mixed  $value
     * @return string
     */
    public function encrypt($value);

    /**
     * @param  string $value
     * @return mixed
     */
    public function decrypt($value);
}
