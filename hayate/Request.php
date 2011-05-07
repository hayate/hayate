<?php
/**
 * @author Andrea Belvedere <scieck@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 * date: Sat May  7 23:06:14 JST 2011
 */
namespace Hayate;

class Request
{
    protected static $instance = NULL;
    protected $method;

    protected function __construct()
    {
        $this->method = strtolower($_SERVER['REQUEST_METHOD']);
    }

    public static function getInstance()
    {
        if (NULL === self::$instance)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function method()
    {
        return $this->method;
    }

    public function isHead()
    {
        return 'head' == $this->method;
    }

    public function isGet()
    {
        return 'get' == $this->method;
    }

    public function isPost()
    {
        return 'post' == $this->method;
    }

    public function isPut()
    {
        return 'put' == $this->method;
    }

    public function isAjax()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }

    public function redirect($location)
    {
        if (false === stripos($location, 'http', 0))
        {
            $location = URI::getInstance()->scheme() .
                '://' . URI::getInstance()->hostname() .
                '/' . ltrim($location, '/');
        }
        header('Location: '.$location);

        if (! $this->isHead())
        {
            exit('<h1>302 - Found</h1><p><a href="'.$location.'">'.$location.'</a>');
        }
        exit();
    }

    public function refresh()
    {
        $this->redirect(URI::getInstance()->current());
    }
}
