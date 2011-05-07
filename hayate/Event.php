<?php
/**
 * @author Andrea Belvedere <scieck@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 * date: Sat May  7 23:04:10 JST 2011
 */
namespace Hayate;

abstract class Event
{
    protected $events = array();

    public function register($name, $callback, array $args = array(), &$ret = NULL)
    {
        $event = new \stdClass();
        $event->callback = $callback;
        $event->args = $args;
        $event->ret = $ret;
        $this->events[$name][] = $event;
    }

    public function unregister($name)
    {
        if (isset($this->events[$name]))
        {
            unset($this->events[$name]);
        }
    }

    public function fire($name)
    {
        if (isset($this->events[$name]))
        {
            for ($i = 0; $i < count($this->events[$name]); $i++)
            {
                $event = $this->events[$name][$i];
                switch (count($event->args))
                {
                case 0:
                    $event->ret = call_user_func($event->callback);
                    break;
                case 1:
                    $event->ret = call_user_func($event->callback, $event->args[0]);
                    break;
                case 2:
                    $event->ret = call_user_func($event->callback, $event->args[0], $event->args[1]);
                    break;
                case 3:
                    $event->ret = call_user_func($event->callback, $event->args[0], $event->args[1], $event->args[2]);
                    break;
                case 4:
                    $event->ret = call_user_func($event->callback, $event->args[0], $event->args[1], $event->args[2], $event->args[3]);
                    break;
                case 5:
                    $event->ret = call_user_func($event->callback, $event->args[0], $event->args[1], $event->args[2], $event->args[3], $event->args[4]);
                    break;
                default:
                    $event->ret = call_user_func_array($event->callback, $event->args);
                }

            }
            unset($this->events[$name]);
        }
    }
}
