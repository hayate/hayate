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

        $db = Database::instance();
        $ret = $db->from('relations')
            ->join('base', array('relations.id' => 'base.relation_id'), 'RIGHT OUTER')
            ->where('relation', 'nipote')->get();

        var_dump($ret);

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