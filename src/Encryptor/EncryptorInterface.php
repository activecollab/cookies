<?php

/*
 * This file is part of the Active Collab Authentication project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

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
