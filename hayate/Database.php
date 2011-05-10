<?php
/**
 * @author Andrea Belvedere <scieck@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 * date: Fri May  6 21:14:51 JST 2011
 */
namespace Hayate {

    use Hayate\Registry;

    class Database
    {
        protected static $db = array();

        protected function __construct() {}

        /**
         * @param string $name The name of a connection
         * @return
         */
        public static function getInstance($name = 'default')
        {
            if (isset(static::$db[$name]))
            {
                return static::$db[$name];
            }
            $config = Registry::getInstance()->get('config')->database;

            if (! is_array($config))
            {
                throw new \Hayate\Exception(_('Missing database configuration parameter.'));
            }
            static::$db[$name] = new \Hayate\Database\Model($config[$name]);
            return static::$db[$name];
        }

        /**
         * Call this method to explicitly close a connection
         *
         * @param string $name The name of a connection to close
         * @return void
         */
        public function close($name = 'default')
        {
            if (isset(static::$db[$name]))
            {
                static::$db[$name] = NULL;
                unset(static::$db[$name]);
            }
        }
    }
}

namespace Hayate\Database {

    class Model extends \PDO
    {
        protected $fetchMode;

        public function __construct(array $args)
        {
            try {
                parent::__construct($args['dsn'], $args['username'], $args['password'], $args['options']);

                $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $this->fetchMode = isset($args['object']) ? ($args['object'] ? \PDO::FETCH_OBJ : \PDO::FETCH_ASSOC) : \PDO::FETCH_ASSOC;

                if (isset($args['charset']))
                {
                    $driver = $this->getAttribute(\PDO::ATTR_DRIVER_NAME);
                    switch ($driver)
                    {
                    case 'mysql':
                        $stm = $this->prepare("SET NAMES ?");
                        break;
                    case 'pgsql':
                        $stm = $this->prepare("SET NAMES '?'");
                        break;
                    // not sure about sqlite or sqlite2
                    case 'sqlite':
                    case 'sqlite2':
                        $stm = $this->prepare("PRAGMA encoding='?'");
                        break;
                    }
                }
                if (isset($stm))
                {
                    $stm->bindValue(1, $args['charset'], \PDO::PARAM_STR);
                    $stm->execute();
                }
            }
            catch (PDOException $ex)
            {
                throw new \Hayate\Exception($ex->getMessage(), $ex->getCode(), $ex);
            }
        }
    }

    class ORM
    {
        protected $tableName;
        protected $primaryKey = 'id';
        protected $field;
        protected $changed;
        protected $db;

        protected function __construct($tableName)
        {
            $this->tableName = $tableName;
            $this->field = array();
            $this->changed = array();
            $this->db = \Hayate\Database::getInstance();
        }

        public static function factory($tableName, $id = NULL)
        {
            if (NULL === $id)
            {
                return new ORM($tableName);
            }
            $orm = new ORM($tableName);
            $orm->set($this->primaryKey($id), $id);
            $orm->read();
            return $orm;
        }

        public function save()
        {

        }

        public function find($id = NULL)
        {
            $pk = $this->primaryKey;
            if (NULL !== $id)
            {
                $pk = $this->primaryKey($id);
                $this->field[$pk] = $id;
            }
            $stm = $this->db->prepare('SELECT * FROM ' .$this->tableName. ' WHERE ' .$pk. '=?');
            $stm->bindValue(1, $this->field[$pk]);
            if (! $stm->execute())
            {

            }

        }

        public function delete($id = NULL)
        {

        }

        public function findAll($limit = NULL, $offset = NULL)
        {

        }

        public function __get($name)
        {
            if (isset($this->field[$name]))
            {
                return $this->field[$name];
            }
            return NULL;
        }

        public function __set($name, $value)
        {
            $this->field[$name] = $value;
            if (! in_array($name, $this->changed))
            {
                $this->changed[] = $name;
            }
        }

        protected function set($name, $value)
        {
            $this->field[$name] = $value;
        }

        /**
         * This method can be overwritten to return a primary field name that is not 'id'
         * for example if a table has a unique field called 'username' then this method
         * can be overwritten as follow:
         * <code>
         * protected function primaryKey($value = NULL)
         * {
         *     if (is_string($value))
         *     {
         *         return 'username';
         *     }
         *     return parent::primaryKey($value);
         * }
         * </code>
         */
        protected function primaryKey($value = NULL)
        {
            return $this->primaryKey;
        }
    }
}