<?php
/**
* Hayate Framework
* Copyright 2010 Andrea Belvedere
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
class Hayate_Session_Database implements Hayate_Session_Interface
{
    protected static $instance = null;
    protected $crypto;
    protected $config;
    protected $db;

    protected function __construct()
    {
        $this->config = Hayate_Config::load('session');
        $connName = $this->config->get('session.connection', 'default');
        $this->db = Hayate_Database::getInstance($connName);
        $this->crypto = null;
        // if we want the session encrypted, and the cookie does not
        // encrypt then we encrypt here otherwise we let the cookie class encrypt
        if ($this->config->get('session.encrypt', false))
        {
            $this->crypto = Hayate_Crypto::getInstance();
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

    /**
     * Executed when the session is being opened
     *
     * @param string $path The save path
     * @param string $name The session name
     * @return boolean
     */
    public function open($path, $name)
    {
        Hayate_Log::info(__METHOD__);
        return true;
    }

    /**
     * Executed when the session operation is done
     *
     * @return boolean
     */
    public function close()
    {
        return true;
    }

    /**
     * Read session
     *
     * @return string An empty string if there is no data to read
     */
    public function read($id)
    {
        Hayate_Log::info(__METHOD__);
        try {
            $ses = $this->getSession($id);
            if (is_object($ses) && isset($ses->data))
            {
                return is_null($this->crypto) ? $ses->data : $this->crypto->decrypt($ses->data);
            }
        }
        catch (Exception $ex) {
            Hayate_Log::error($ex, true);
        }
        return '';
    }

    /**
     * Write session, executed after the output stream is closed
     *
     * @param string $id Session id
     * @param string $data Session data
     * @return boolean
     */
    public function write($id, $data)
    {
        try {
            $ses = $this->getSession($id);
            // prepare fields
            $data = is_null($this->crypto) ? $data : $this->crypto->encrypt($data);
            $access = gmdate('U', time());
            // do update
            if (is_object($ses) && isset($ses->session_id))
            {
                $this->db->update('sessions',
                                  array('data' => $data, 'access' => $access),
                                  array('session_id' => $id));
            }
            // do insert
            else {
                $this->db->insert('sessions', array('data' => $data,
                                                    'access' => $access,
                                                    'session_id' => $id));
            }
            return true;
        }
        catch (Exception $ex) {
            error_log("{$ex}");
        }
        return false;
    }

    /**
     * Executed when the session is destroyed
     *
     * @param string $id The Session id
     * @return boolean
     */
    public function destroy($id)
    {
        Hayate_Log::info(__METHOD__);
        try {
            $this->db->delete('sessions', array('session_id' => $id));
            return true;
        }
        catch (Exception $ex) {
            Hayate_Log::error($ex, true);
        }
        return false;
    }

    /**
     * @param integer $maxlifetime Max session lifetime
     * @return boolean
     */
    public function gc($maxlifetime)
    {
        Hayate_Log::info(__METHOD__);
        try {

            $lifetime = gmdate('U', time()) - $maxlifetime;
            $this->db->delete('sessions', array('access <' => $lifetime));
            return true;
        }
        catch (Exception $ex) {
            Hayate_Log::error($ex, true);
        }
        return false;
    }

    protected function getSession($id, $mode = PDO::FETCH_OBJ)
    {
        try {
            $stm = $this->db->prepare('SELECT * FROM sessions WHERE session_id=:id LIMIT 1');
            $stm->bindValue(':id', $id, PDO::PARAM_STR);
            $stm->execute();
            $ses = $stm->fetch($mode);
            $stm->closeCursor();
            return $ses;
        }
        catch (Exception $ex) {
            Hayate_Log::error($ex, true);
        }
        return false;
    }
}
