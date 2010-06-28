<?php
/**
 * Hayate Framework
 * Copyright 2009-2010 Andrea Belvedere
 *
 * Hayate is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * @package Hayate
 */
class Hayate_Exception extends Exception
{
    /**
     * Hayate_Excetion
     *
     * @param string|Exception $message Error message or Wrapped exception
     * @param int $code Error code or number
     * @param Exception $prev From (php >= 5.3.0)
     *
     * This constructor also optionally accept and Exception object as
     * the 3rd argument (php >= 5.3.0)
     */
    public function __construct($message = '', $code = 0, Exception $prev = null)
    {
        if ($message instanceof Exception)
        {
            parent::__construct($message->getMessage(), (int)$message->getCode());
            $this->setFile($message->getFile());
            $this->setLine($message->getLine());
            require_once 'Hayate/Log.php';
            Hayate_Log::error($message->getMessage());
        }
        else {
            if (version_compare(PHP_VERSION, '5.3.0') >= 0)
            {
                parent::__construct($message, $code, $prev);
            }
            else {
                parent::__construct($message, $code);
                if ($prev instanceof Exception)
                {
                    $this->setFile($prev->getFile());
                    $this->setLine($prev->getLine());
                }
            }
            require_once 'Hayate/Log.php';
            Hayate_Log::error($message);
        }
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function setLine($line)
    {
        $this->line = $line;
    }
}