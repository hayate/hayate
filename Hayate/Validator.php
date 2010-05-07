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
/**
 * @package Hayate
 * @version 1.0
 */
class Hayate_Validator
{
    protected $data;
    protected $missing;
    protected $errors;
    protected $checks;

    public function __construct(array $data, array $required = array())
    {
        $this->data = $data;
        $this->missing = array();
        $this->errors = array();
        $this->checks = array();
        if (! empty($required))
        {
            $this->setRequired($required);
        }
    }

    /**
     * @param array $required A list of required fields
     */
    public function setRequired(array $required)
    {
        $present = array();
        foreach ($this->data as $field => $value)
        {
            $value = is_string($value) ? trim($value) : $value;
            if (! empty($value))
            {
                $present[] = $field;
            }
        }
        $this->missing = array_diff($required, $present);
    }

    public function isString($field, $custom = null)
    {
        // don't do any checks if value is not present
        if (in_array($field, $this->missing)) return;

        if (array_key_exists($field, $this->data))
        {
            $msg = sprintf(_('%s: is not valid text.'), $this->fieldName($field));
            $obj = new stdObject();
            $obj->msg = is_null($custom) ? $msg : $custom;
            $this->checks[$field][__FUNCTION__] = $obj;
        }
    }

    public function maxLength($field, $max, $custom = null)
    {
        // don't do any checks if value is not present
        if (in_array($field, $this->missing)) return;

        if (array_key_exists($field, $this->data) && is_string($this->data[$field]))
        {
            if (! is_numeric($max))
            {
                throw new Hayate_Exception(_('"max" parameter must be numeric.'));
            }
            $msg = sprintf(_('%s: must be less or equal %d characters long.'), $this->fieldName($field), $max);
            $obj = new stdObject();
            $obj->msg = is_null($custom) ? $msg : $custom;
            $obj->max = $max;
            $this->checks[$field][__FUNCTION__] = $obj;
        }
    }

    public function minLength($field, $min, $custom = null)
    {
        // don't do any checks if value is not present
        if (in_array($field, $this->missing)) return;

        if (array_key_exists($field, $this->data) && is_string($this->data[$field]))
        {
            if (! is_numeric($min))
            {
                throw new Hayate_Exception(_('"min" parameter must be numeric.'));
            }
            $msg = sprintf(_('%s: must be greater or equal %d characters.'), $this->fieldName($field), $min);
            $obj = new stdObject();
            $obj->msg = is_null($custom) ? $msg : $custom;
            $obj->min = $min;
            $this->checks[$field][__FUNCTION__] = $obj;
        }
    }

    public function isArray($field, $custom = null)
    {
        // don't do any checks if value is not present
        if (in_array($field, $this->missing)) return;

        if (array_key_exists($field, $this->data))
        {
            $msg = sprintf(_('%s: must be an array.'), $this->fieldName($field));
            $obj = new stdObject();
            $obj->msg = is_null($custom) ? $msg : $custom;
            $this->checks[$field][__FUNCTION__] = $obj;
        }
    }

    public function maxArray($field, $max, $customMsg = null)
    {
        // don't do any checks if value is not present
        if (in_array($field, $this->missing)) return;

        if (array_key_exists($field, $this->data) && is_array($this->data[$field]))
        {
            $msg = sprintf(_('%s: must be an array.'), $this->fieldName($field));
        }
    }

    public function validate()
    {

    }

    public function fieldName($field)
    {
        $field = preg_replace('/_+/', ' ', $field);
        return ucwords(strtolower(trim($field)));
    }
}