<?php
/**
 * @author Andrea Belvedere <scieck@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 * date: Mon Apr 25 18:21:19 JST 2011
 */
namespace Hayate {

    class View
    {
        protected $view;
        protected $template;

        public function __construct($template)
        {
            $config = \Hayate\Registry::getInstance()->get('config');
            $this->view = self::factory($config->view);
            $this->template = $template;
        }

        /**
         * @return void
         */
        public function render()
        {
            $this->view->render($this->template);
        }

        /**
         * @return string
         */
        public function fetch()
        {
            return $this->view->fetch($this->template);
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
            return $this->view->fetch($this->template);
        }

        protected static function factory(array $config)
        {
            $name = $config['name'];
            $classname = "\Hayate\View\\$name";
            if (! class_exists($classname, false))
            {
                throw new \Hayate\Exception(_("Could not find class: {$classname}."));
            }
            return $classname::getInstance();
        }
    }
}

namespace Hayate\View {

    abstract class AbstractView
    {
        protected $config;
        protected $vars;

        protected function __construct()
        {
            $this->config = \Hayate\Registry::getInstance()->get('config')->view;
            $this->vars = array();
        }

        /**
         * @return void
         */
        abstract public function render($template);

        /**
         * @return string
         */
        abstract public function fetch($template);
    }

    class Native extends AbstractView
    {
        protected static $instance = NULL;
        protected $router;

        protected function __construct()
        {
            parent::__construct();
            $this->router = \Hayate\Registry::getInstance()->get('router');
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
            extract($this->vars, EXTR_SKIP);
            ob_start();
            try {
                require($this->template($template));
            }
            catch (Exception $ex) {
                ob_end_clean();
                throw $ex;
            }
            ob_end_flush();
        }

        /**
         * @return string
         */
        public function fetch($template)
        {
            extract($this->vars, EXTR_SKIP);
            ob_start();
            try {
                require($this->template($template));
            }
            catch (Exception $ex) {
                ob_end_clean();
                throw $ex;
            }
            return ob_get_clean();
        }

        public function set($name, $value)
        {
            $this->vars[$name] = $value;
        }

        public function get($name, $default = '')
        {
            if (isset($this->vars[$name]))
            {
                return $this->vars[$name];
            }
            return $default;
        }

        protected function template($template)
        {
            return $this->router->modulesPath() .'/'. $this->router->module() .
                '/view/'. $template . $this->config['ext'];
        }
    }

    abstract class Controller extends \Hayate\Controller
    {
        protected $template = 'template';
        protected $render = TRUE;

        public function __construct()
        {
            parent::__construct();

            if (TRUE === $this->render)
            {
                $this->template = new \Hayate\View($this->template);
                $this->register(\Hayate\Controller::PostAction, array($this, 'render'));
            }
        }

        protected function render()
        {
            if ($this->render)
            {
                $this->template->render();
            }
        }

        public function __get($name)
        {
            if ($this->template instanceof \Hayate\View)
            {
                return $this->template->$name;
            }
        }

        public function __set($name, $value)
        {
            if ($this->template instanceof \Hayate\View)
            {
                $this->template->$name = $value;
            }
        }
    }
}