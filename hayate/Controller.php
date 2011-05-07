<?php
/**
 * @author Andrea Belvedere <scieck@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 * date: Mon Apr 25 05:32:28 JST 2011
 */
namespace Hayate {

    abstract class Controller extends Event
    {
        const PreAction = 'PreAction';
        const PostAction = 'PostAction';

        public function __construct()
        {
            var_dump(__METHOD__);
            $this->register(self::PreAction, array($this, 'preAction'));
            $this->register(self::PostAction, array($this, 'postAction'));
        }
        protected function preAction() {}
        protected function postAction() {}
    }
}

namespace Hayate\View {

    abstract class Controller extends \Hayate\Controller
    {
        protected $template = 'template';
        protected $render = TRUE;

        public function __construct()
        {
            parent::__construct();
            require_once 'View.php';

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

