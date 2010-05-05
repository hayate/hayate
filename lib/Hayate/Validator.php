<?php

class Validator extends ArrayObject
{
    protected $valid_rules = array('required','email','url','numeric','boolean','(size)\[(\d+)\]','(range)\[([+-]?\d+)-([+-]?\d+)\]');
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
     * @param $field string The field name
     * @param $rule string|array If Strings the method accept a
     * variable list of rules, otherwise an array of rules
     */
    public function add_rule($field, $rule)
    {
        if (is_array($rule)) {
            $this->_add_rule($field, array_unique($rule));
        }
        else {
            // get this method args
            $args = func_get_args();
            // remove $field
            array_shift($args);
            $this->_add_rule($field, array_unique($args));
        }
    }

    protected function _add_rule($field, array $rules)
    {
        foreach ($rules as $rule)
        {
            foreach ($this->valid_rules as $valid_rule)
            {
                $match = array();
                if (preg_match('#'.$valid_rule.'#', $rule, $match) == 1)
                {
                    if (count($match) == 1)
                    {
                        $this->vals[$field][] = array('rule' => $match[0],
                                                      'param' => null);
                    }
                    else if (count($match) == 3)
                    {
                        $this->vals[$field][] = array('rule' => $match[1],
                                                      'param' => $match[2]);
                    }
                    else if (count($match) == 4)
                    {
                        $this->vals[$field][] = array('rule' => $match[1],
                                                      'param' => array($match[2],$match[3]));
                    }
                }
            }
        }
    }

    public function add_callback($field, $callback, $param = NULL)
    {
        $this->vals[$field][] = array('callback' => array($callback, $param));
    }

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
                        $this->add_error_field($field, $rule['rule']);
                        break;
                    }
                }
                else if (isset($rule['callback'])) {
                    $params = array(&$this, $field, $rule['callback'][1]);
                    call_user_func_array($rule['callback'][0], $params);
                }
            }
        }
        return ($this->errors == array());
    }

    public function add_error_field($field, $rule)
    {
        $this->errors[$field][$rule] = 'failed';
    }

    public function pre_filter($field, $callback, $params = array())
    {
        if ((null === $params) || (is_string($params) && empty($params))) {
            $params == array();
        }
        if (! is_array($params)) {
            $params = array($params);
        }
        $this->prefilters[$field] = array($callback, $params);
    }

    /**
     * @param array|string $map If array it should be:
     * <pre>$errmsg = array('field_1' => array('rule_1' => mixed type implementation dependent,
     *                                         'rule_2' => mixed type implementation dependent),
     *                      'field_2' => array('rule_1' => mixed type implementation dependent,
     *                                         'rule_2' => mixed type implementation dependent))</pre>
     * if string, it should be a path to a php file containing the above described array format with out extension
     * @param bool $rules If true include rules in returned array
     */
    public function errors($map = null, $rules = false)
    {
        switch (true) {
        case is_string($map):
            require_once $map.'.php';
            break;
        case is_array($map):
            $errmsg = $map;
            break;
        default:
            return $this->errors;
        }

        $msgs = array_intersect_key($errmsg, $this->errors);
        foreach ($msgs as $field => $value) {
            $msgs[$field] = array_intersect_key($value, $this->errors[$field]);
        }
        if (true === $rules) {
            return $msgs;
        }
        foreach ($msgs as $field => $ins)
        {
            foreach ($ins as $in) {
                $msgs[$field] = $in;
            }
        }
        return $msgs;
    }

    public function as_array()
    {
        return $this->getArrayCopy();
    }

    public function __isset($name)
    {
        if (array_key_exists($name, $this)) {
            return (false === empty($this[$name]));
        }
        return false;
    }

    protected function _pre_filter()
    {
        foreach ($this->prefilters as $field => $filter)
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
            return (filter_var($this[$field], FILTER_VALIDATE_BOOLEAN, array('flags'=>FILTER_NULL_ON_FAILURE)) !== NULL);
        }
        return true;
    }

    protected function size($field, $params = null)
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
