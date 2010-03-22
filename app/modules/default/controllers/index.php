<?php

class Default_Index extends Controller
{
    protected $template = 'index';

    public function index($var = null)
    {
        if (null !== $var)
        {
            echo '<h3>'.$var.'</h3>';
        }
        else {
            echo '<h3>'.__METHOD__.'</h3>';
        }

        /*
        $bases = ORM::factory('base')->where('name', 'marco')->find_all();
        echo count($bases);
        echo "<br />";
        */

        $base = ORM::factory('base', 9);
        //echo nl2br($base);
        var_dump($base);

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