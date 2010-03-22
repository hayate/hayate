<?php

class Model_Base extends ORM
{
    public function set_fields()
    {
        $this->add_field('id', 0);
        $this->add_field('relation_id', 0);
        $this->add_field('name', '');
        $this->add_field('surname', '');
        $this->add_field('emails', '');
        $this->add_field('dataora', 0);
    }
}