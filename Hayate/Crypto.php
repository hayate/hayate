<?php
/**
 * Hayate Framework
 * Copyright 2009 Andrea Belvedere
 *
 * Hayate is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library. If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * This class uses php mcrypt extension and is inspired by "Pro PHP
 * Security" Appress book
 *
 * @see http://php.net/manual/en/mcrypt.requirements.php
 */
class Hayate_Crypto
{
    private static $instance = null;

    // possible choices are: aes, tripledes, blowfish
    const ALGO = 'aes';
    private $mcrypt;
    private $key;
    private $ivsize;
    private $maxKeysize;
    private $keysize;
    private $iv;


    private function __construct()
    {
        if (! function_exists('mcrypt_module_open'))
        {
            throw new Hayate_Exception(sprintf(_('%s: mcrypt extension is missing.'), __CLASS__));
        }
        switch (self::ALGO)
        {
        case 'aes':
            $algo = MCRYPT_RIJNDAEL_256;
            break;
        case 'tripledes':
            $algo = MCRYPT_TRIPLEDES;
            break;
        case 'blowfish':
            $algo = MCRYPT_BLOWFISH;
            break;
        default:
            throw new Hayate_Exception(sprintf(_('%s is not supported, please use "aes", "tripledes" or "blowfish"'), self::ALGO));
        }
        // initialize mcrypt
        $this->mcrypt = mcrypt_module_open($algo, '', MCRYPT_MODE_CBC, '');

        // calculate IV size
        $this->ivsize = mcrypt_enc_get_iv_size($this->mcrypt);

        // calculate key max key length
        $this->maxKeysize = mcrypt_enc_get_key_size($this->mcrypt);

        $config = Hayate_Config::getInstance();
        if (isset($config->core->secret_key))
        {
            $this->setKey($config->core->secret_key);
        }
    }

    public static function getInstance()
    {
        if (null === self::$instance)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function setKey($secret)
    {
        $key = '';
        $keyblocks = ceil(($this->maxKeysize * 2) / 32);

        // obfuscate secret key
        for ($i = 0; $i < $keyblocks; $i++)
        {
            $key .= md5($i.$secret, true);
        }
        // resize key to correct length
        $this->key = substr($key, 0, $this->maxKeysize);
        $this->keysize = strlen($this->key);
    }

    public function encrypt($data)
    {
        if (empty($data)) return;

        // generate initialization vector
        $this->iv = mcrypt_create_iv($this->ivsize, MCRYPT_RAND);

        // initialize mcrypt
        $ret = mcrypt_generic_init($this->mcrypt, $this->key, $this->iv);
        if ((false === $ret) || ($ret < 0))
        {
            throw new Hayate_Exception(_('Could initialize mcrypt.'));
        }

        // encrypt
        $ciphertext = mcrypt_generic($this->mcrypt, $data);

        // de-initialize mcrypt
        mcrypt_generic_deinit($this->mcrypt);

        // prepend IV
        // (IV is only used to create entropy, and is of no use to an attacker);
        $ans = $this->iv.$ciphertext;

        // base64 encode
        $ans = chunk_split(base64_encode($ans), 64);

        return $ans;
    }

    /**
     * @param string $data The base 64 encrypted data
     */
    public function decrypt($data)
    {
        if (empty($data)) return;

        $in = base64_decode($data);
        // retrieve IV from decoded $data
        $this->iv = substr($in, 0, $this->ivsize);
        $ciphertext = substr($in, $this->ivsize);

        // initialize mcrypt
        $ret = mcrypt_generic_init($this->mcrypt, $this->key, $this->iv);
        if ((false === $ret) || ($ret < 0))
        {
            throw new Hayate_Exception(_('Could initialize mcrypt.'));
        }

        // decrypt
        $ans = mdecrypt_generic($this->mcrypt, $ciphertext);

        // de-initialize mcrypt
        mcrypt_generic_deinit($this->mcrypt);

        return rtrim($ans, "\0");
    }
}