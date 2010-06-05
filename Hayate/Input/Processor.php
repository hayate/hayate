<?php
/**
 * Hayate Framework
 * Copyright 2009-2010 Andrea Belvedere
 *
 * Hayate is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 */
abstract class Hayate_Input_Processor
{
    protected $val;
    protected $props;

    public function __construct(array $input = array())
    {
	$this->val = new Hayate_Validator($input);
	$this->props = array();
    }

    abstract function process($action = null);

    /**
     * @param string $field The field errors, if null return all the
     * errors
     * @param bool $first If true only return the first error, else
     * return all field errors
     * @return array|string An array of errors or a string with the
     * first error
     */
    public function errors($field = null, $first = false)
    {
	return $this->val->errors($field, $first);
    }

    public function addError($error)
    {
	$this->val->addError($error);
    }

    public function getProperty($name)
    {
	if (array_key_exists($name, $this->props))
	{
	    return $this->props[$name];
	}
	return null;
    }

    public function setProperty($name, &$prop)
    {
	$this->props[$name] = $prop;
    }

    public function __get($name)
    {
	return $this->val->$name;
    }

    public function __isset($name)
    {
	return isset($this->val->$name);
    }

    public function asArray()
    {
	return $this->val->asArray();
    }
}