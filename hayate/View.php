<?php
/**
 * @author Andrea belvedere <scieck@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 * date: Mon Apr 25 18:21:19 JST 2011
 */
namespace Hayate\View {

    class Exception extends \Exception {}
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

    abstract class AView
    {
        protected $basepath;

        protected function __construct()
        {
            $this->basepath = \Hayate\View\Config::get('view.basepath',
                                                       dirname($_SERVER['DOCUMENT_ROOT']).'/view');
        }

        public function setBasePath($basepath)
        {
            $this->basepath = $basepath;
        }
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

    class Native extends \Hayate\AView implements \Hayate\IView
    {
        protected static $instance = NULL;
        protected $var;

        protected function __construct()
        {
            parent::__construct();
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