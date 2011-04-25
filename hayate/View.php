<?php
/**
 * @author Andrea belvedere <scieck@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 * date: Mon Apr 25 18:21:19 JST 2011
 */
namespace Hayate\View {

    class Exception extends \Exception {}

    class Config
    {
        protected static $config;

        public static function config(array $config)
        {
            self::$config = $config;
        }

        public static function get($name, $default = NULL)
        {
            $pos = strpos($name, '.');
            if (FALSE === $pos)
            {
                return isset(self::$config[$name]) ? self::$config[$name] : $default;
            }
            $keys = array();
            while ($pos !== FALSE)
            {
                $key = substr($name, 0, $pos);
                if (! empty($key))
                {
                    $keys[] = $key;
                }
                $name = substr($name, ++$pos);
                $pos = strpos($name, '.');
                if (FALSE === $pos && $name !== FALSE)
                {
                    $keys[] = $name;
                }
            }
            $value = isset(self::$config[$keys[0]]) ? self::$config[$keys[0]] : NULL;
            if (NULL === $value) return $default;

            for ($i = 1; $i < count($keys); $i++)
            {
                $value = (is_array($value) && isset($value[$keys[$i]])) ? $value[$keys[$i]] : NULL;
            }
            return (NULL !== $value) ? $value : $default;
        }
    }
}

namespace Hayate {

    interface IView
    {
        /**
         * @return void
         */
        public function render($template);

        /**
         * @return string
         */
        public function fetch($template);
    }

    class View
    {
        protected $view;

        public function __construct($template)
        {
            $this->view = self::factory();
        }

        /**
         * @return void
         */
        public function render()
        {

        }

        /**
         * @return string
         */
        public function fetch()
        {

        }

        public function __get($name)
        {
            return $this->view->get($name);
        }

        public function __set($name, $value)
        {
            $this->view->set($name, $value);
        }

        public function __toString()
        {
            return $this->fetch();
        }

        protected static function factory()
        {
            $name = \Hayate\View\Config::get('view.name');
            $classname = "\Hayate\View\\$name";
            if (! class_exists($classname, false))
            {
                throw new \Hayate\View\Exception(_("Could not find class: {$classname}."));
            }
            return $classname::getInstance();
        }
    }
}

namespace Hayate\View {

    class Native implements \Hayate\IView
    {
        protected static $instance = NULL;
        protected $var;

        protected function __construct()
        {
            $this->var = array();
        }

        public static function getInstance()
        {
            if (NULL === self::$instance)
            {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * @return void
         */
        public function render($template)
        {

        }

        /**
         * @return string
         */
        public function fetch($template)
        {

        }

        public function set($name, $value)
        {
            $this->var[$name] = $value;
        }

        public function get($name, $default = '')
        {
            if (isset($this->var[$name]))
            {
                return $this->var[$name];
            }
            return $default;
        }
    }
}