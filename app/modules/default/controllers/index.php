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

        $name = '%s%"';

        $db = Database::instance();
        $ret = $db->select(array('name','surname'))
            ->from('base')
            ->where('name', $name)
            ->get();

        var_dump($ret);


        $ret = $db->set('surname', 'belvedere')
            ->where(array('name' => 'simona'))
            ->from('base')
            ->update();

        var_dump($ret);

        $ret = $db->select(array('name','surname'))
            ->from('base')
            ->where('name', 'simona')
            ->get();

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