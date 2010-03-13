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
 * @version 1.0
 */
class HayateException extends Exception
{
    /**
     * HayateExcetion
     *
     * @param string $message Error message
     * @param int $code Error code or number
     *
     * This constructor also optionally accept and Exception object as
     * the 3rd argument or $errfile (string) and $errline (int) as 3rd
     * and 4th parameters
     */
    public function __construct($message = '', $code = 0, Exception $prev = null)
    {
        if ($message instanceof Exception)
        {
            parent::__construct($message->getMessage(), $message->getCode());
        }
        else {
            parent::__construct($msg, $code);
            if ($prev instanceof Exception)
            {
                $this->setFile($prev->getFile());
                $this->setLine($prev->getLine());
            }
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