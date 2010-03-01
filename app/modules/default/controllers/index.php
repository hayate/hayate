<?php

class Default_Index extends Controller
{
    protected $template = 'index.html';

    public function index($var = null)
    {
	$view = new View($this->template);
	$view->name = 'andrea';
	$view->render();
    }

    public function test()
    {
        echo '<h1>'.__METHOD__.'</h1>';
    }

    public function __call($method, array $args)
    {
	$this->forward('test');
    }
}