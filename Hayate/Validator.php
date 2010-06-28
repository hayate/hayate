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
class Hayate_Validator extends ArrayObject
{
    protected $valid_rules = array('(required)','(email)','(url)','(numeric)','(boolean)',
                                   '(length)\[(\d+)\]','(range)\[([+-]?\d+)-([+-]?\d+)\]');
    protected $vals;
    protected $errors;
    protected $prefilters;

    public function __construct(array $input = array())
    {
        parent::__construct($input, ArrayObject::ARRAY_AS_PROPS);
        $this->vals = array();
        $this->errors = array();
        $this->prefilters = array();
    }

    /**
     * @param string $field The field name
     * @param array $rule One or more rules
     * @param array $msg Error messages associated with rules
     */
    public function addRule($field, array $rule, array $msg = array())
    {
        for ($i = 0; $i < count($rule); $i++)
        {
            foreach ($this->valid_rules as $valid_rule)
            {
                $match = array();
                if ((preg_match('#'.$valid_rule.'#', $rule[$i], $match) == 1) && (count($match) >= 2))
                {
                    switch($match[1])
                    {
                    case 'required':
                        $error = sprintf(_('"%s" is a required field.'), $this->fieldName($field));
                        $this->vals[$field][] = array('rule' => $match[1],
                                                      'param' => null,
                                                      'error' => isset($msg[$i]) ? $msg[$i] : $error);
                        break;
                    case 'email':
                        $error = _('Invalid Email address.');
                        $this->vals[$field][] = array('rule' => $match[1],
                                                      'param' => null,
                                                      'error' => isset($msg[$i]) ? $msg[$i] : $error);
                        break;
                    case 'url':
                        $error = _('Invalid URL address.');
                        $this->vals[$field][] = array('rule' => $match[1],
                                                      'param' => null,
                                                      'error' => isset($msg[$i]) ? $msg[$i] : $error);
                        break;
                    case 'numeric':
                        $error = sprintf(_('%s must be a numeric field.'), $this->fieldName($field));
                        $this->vals[$field][] = array('rule' => $match[1],
                                                      'param' => null,
                                                      'error' => isset($msg[$i]) ? $msg[$i] : $error);
                        break;
                    case 'boolean':
                        $error = sprintf(_('Invalid "%s" value.'), $this->fieldName($field));
                        $this->vals[$field][] = array('rule' => $match[1],
                                                      'param' => null,
                                                      'error' => isset($msg[$i]) ? $msg[$i] : $error);
                    break;
                    case 'length':
                        $error = sprintf(_('%s must be at least %d characters long.'),
                                         $this->fieldName($field), $match[2]);
                        $this->vals[$field][] = array('rule' => $match[1],
                                                      'param' => $match[2],
                                                      'error' => isset($msg[$i]) ? $msg[$i] : $error);
                        break;
                    case 'range':
                        $error = sprintf(_('%s must be between %d and %d characters long.'),
                                         $this->fieldName($field), $match[2], $match[3]);
                        $this->vals[$field][] = array('rule' => $match[1],
                                                      'param' => array($match[2],$match[3]),
                                                      'error' => isset($msg[$i]) ? $msg[$i] : $error);
                        break;
                    }
                }
            }
        }
    }


    public function addCallback($field, $callback, $param = null)
    {
        if (isset($this[$field]))
        {
            $this->vals[$field][] = array('callback' => array($callback, $param));
        }
    }

    /**
     * @return bool, True if there are no erros, false otherwise.
     */
    public function validate()
    {
        // applying pre filters before validating
        $this->_pre_filter();

        foreach ($this->vals as $field => $rules)
        {
            foreach ($rules as $rule)
            {
                if (isset($rule['rule']))
                {
                    $method = $rule['rule'];
                    if (! $this->$method($field, $rule['param']))
                    {
                        $this->addError($field, $rule['error']);
                        break;
                    }
                }
                else if (isset($rule['callback']))
                {
                    $params = array(&$this, $field, $rule['callback'][1]);
                    call_user_func_array($rule['callback'][0], $params);
                }
            }
        }
        return ($this->errors == array());
    }

    public function addError($field, $error = null)
    {
	if (null === $error)
	{
	    $this->errors['errors'][] = $field;
	}
	else {
	    $this->errors[$field][] = $error;
	}
    }

    public function preFilter($field, $callback, $params = array())
    {
        if ((null === $params) || (is_string($params) && empty($params))) {
            $params == array();
        }
        if (! is_array($params)) {
            $params = array($params);
        }
        if (isset($this[$field]))
        {
            $this->prefilters[$field] = array($callback, $params);
        }
    }

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
	if (null === $field && !$first)
	{
	    return $this->errors;
	}
	else if (null === $field && $first)
	{
	    $keys = array_keys($this->errors);
	    if (count($keys))
	    {
		return $this->errors[$keys[0]][0];
	    }
	    return '';
	}
	else if (array_key_exists($field, $this->errors))
	{
	    if (false !== $first)
	    {
		return $this->errors[$field][0];
	    }
	    return $this->errors[$field];
	}
	return $first ? '' : array();
    }

    public function asArray()
    {
        return $this->getArrayCopy();
    }

    public function offsetExists($name)
    {
	if (parent::offsetExists($name))
	{
	    $value = $this->offsetGet($name);
	    return (false === empty($value));
	}
	return false;
    }

    public function get($name, $default = null)
    {
	if (isset($this->$name))
	{
	    return $this->$name;
	}
	return $default;
    }

    protected function fieldName($field)
    {
        $field = preg_replace('/[-_]+/', ' ', $field);
        return ucwords(strtolower(trim($field)));
    }

    protected function _pre_filter()
    {
        foreach ($this->prefilters as $field => &$filter)
        {
            if ('*' == $field) {
                foreach ($this as $key => $val)
                {
                    array_unshift($filter[1], $val);
                    $this->$key = call_user_func_array($filter[0], $filter[1]);
                }
            }
            else if (isset($this[$field]))
            {
                array_unshift($filter[1], $this[$field]);
                $this->$field = call_user_func_array($filter[0], $filter[1]);
            }
        }
    }

    protected function required($field, $param = null)
    {
        if (! array_key_exists($field, $this)) {
            return false;
        }
        if (is_numeric($this[$field]) && ($this[$field] == 0)) {
            return true;
        }
        return (empty($this[$field]) === false);
    }

    protected function email($field, $param = null)
    {
        if (array_key_exists($field, $this)) {
            return (filter_var($this[$field], FILTER_VALIDATE_EMAIL) !== false);
        }
        return true;
    }

    protected function url($field, $params = null)
    {
        if (array_key_exists($field, $this)) {
            return (filter_var($this[$field], FILTER_VALIDATE_URL) !== false);
        }
        return true;
    }

    protected function numeric($field, $param = null)
    {
        if (array_key_exists($field, $this)) {
            return is_numeric($this[$field]);
        }
        return true;
    }

    protected function boolean($field, $params = null)
    {
        if (array_key_exists($field, $this)) {
            return (filter_var($this[$field], FILTER_VALIDATE_BOOLEAN, array('flags'=>FILTER_NULL_ON_FAILURE))!==NULL);
        }
        return true;
    }

    protected function length($field, $params = null)
    {
        if (array_key_exists($field, $this)) {
            if (! is_numeric($params)) {
                trigger_error("Invalid non numeric param in ".__METHOD__);
                return false;
            }
            return (mb_strlen($this[$field], 'UTF-8') <= $params);
        }
        return true;
    }

    protected function range($field, array $params)
    {
        if (array_key_exists($field, $this))
        {
            $min = $params[0];
            $max = $params[1];
            return (($this[$field] >= $min) && ($this[$field] <= $max));
        }
        return true;
    }
}
