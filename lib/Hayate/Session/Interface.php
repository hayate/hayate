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
interface Hayate_Session_Interface
{
    /**
     * Executed when the session is being opened
     *
     * @param string $path The save path
     * @param string $name The session name
     * @return boolean
     */
    public function open($path, $name);

    /**
     * Executed when the session operation is done
     *
     * @return boolean
     */
    public function close();

    /**
     * Read session
     *
     * @return string An empty string if there is no data to read
     */
    public function read($id);

    /**
     * Write session, executed after the output stream is closed
     *
     * @param string $id Session id
     * @param string $data Session data
     * @return boolean
     */
    public function write($id, $data);

    /**
     * Executed when the session is destroyed
     *
     * @param string $id The Session id
     * @return boolean
     */
    public function destroy($id);

    /**
     * @param integer $maxlifetime Max session lifetime
     * @return boolean
     */
    public function gc($maxlifetime);
}