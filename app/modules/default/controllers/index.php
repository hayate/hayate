<?php

class Default_Index extends Controller
{
    protected $template = 'index';

    public function index($var = null)
    {
        //Log::error(__METHOD__);
        if (null !== $var)
        {
            echo '<h3>'.$var.'</h3>';
        }
        else {
            echo '<h3>'.__METHOD__.'</h3>';
        }

        //Log::error($_POST, true);
        echo print_r($_POST, true);
        echo print_r($_GET, true);
        echo print_r($_SERVER['REQUEST_METHOD'], true);

        $put = Input::instance()->put();

        echo print_r($put, true);

        //echo print_r($put['city'], true);
        //echo print_r($put['country'], true);

        /*
        $view = new View($this->template);
        $view->name = 'andrea';
        $view->content = new View('inner');
        $view->render();
        */
    }

    public function test()
    {
        echo '<h3>'.__METHOD__.'</h3>';
    }

    public function __call($method, array $args)
    {
        $this->forward('test');
    }
}