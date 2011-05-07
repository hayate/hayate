<?php
/**
 * @author Andrea Belvedere <scieck@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 * date: Fri May  6 21:14:51 JST 2011
 */
namespace Hayate\Database {

    class Exception extends \Exception {}
}

namespace Hayate {

    require_once 'Util.php';

    use Hayate\Util\Registry;

    class Database
    {
        protected static $db = array();

        protected function __construct() {}


        public function __destruct()
        {
            foreach (self::$db as &$con)
            {
                $con = NULL;
            }
        }

        public static function getInstance($name = 'default')
        {
            if (isset(self::$db[$name])) return self::$db[$name];

            $config = Registry::getInstance()->get('config');


        }
    }
}