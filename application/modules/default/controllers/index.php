<?php
class Default_Index_Controller extends Hayate_Controller_Template
{
    protected $_template = 'index.html.php';

    public function index($var = null)
    {
        //$this->_template->name = 'Hayate';
        $this->_template->content = new Hayate_View('inner.html.php');
        /*
        $view = new Hayate_View('index.tpl');
        $view->name = 'Hayate';
        $view->render();
        */
        
        $db = Hayate_Database::getInstance();
        //$db->query("INSERT INTO base (name,surname) VALUES ('%s','%s')", 'andrea', 'belvedere');
        //$db->query("INSERT INTO base (name,surname) VALUES ('%s','%s')", 'andrea', 'belvedere');

        $ret = $db->where('name', 'andrea')
            ->from('base')
            ->get();

        //$db->update('base', array('name' => 'marco'), array('id' => 9));

        /*
        foreach ($ret as $name) {
            var_dump($name);
        }
        */

        //$db->from('base')->set('name', 'simona')->insert();
        //$db->set(array('name' => 'Aurora', 'surname' => 'Belvedere'))->insert('base');

        
        $base = Hayate_ORM::factory('base')->find_all();
        echo "<ul>\n";
        foreach ($base as $person) {
            echo "<li>{$person->name} {$person->surname}</li>\n";
        }
        echo "</ul>";

        //$base = new Base_Model();
        //$base->load(9);

        //var_dump($base);
    }

    public function test()
    {
        $this->_template->name = $this->_post('name', 'Hayate');
    }
}