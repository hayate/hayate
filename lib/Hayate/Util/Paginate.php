<?php
/**
 * Hayate Framework
 * Copyright 2011 Andrea Belvedere (scieck [at] gmail [dot] com)
 *
 * Hayate is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library. If not, see <http://www.gnu.org/licenses/>.
 */
class Hayate_Util_Paginate
{
    protected $items;
    protected $total;
    protected $offset;
    protected $pages;
    protected $layout;
    protected $page;
    protected $basepath;
    protected $previous;
    protected $next;

    public function __construct(Hayate_ORM $orm, $items = 10, $page = 1, array $where = array())
    {
        if (! is_numeric($items) || ($items <= 0)) $items = 10;
        if (! is_numeric($page) || ($page <= 0)) $page = 1;

        $this->previous = false;
        $this->next = false;
        $this->total = $orm->count($where);

        if ($this->total <= $items)
        {
            $this->offset = 0;
            $this->pages = 1;
            $this->items = $orm->where($where)->findAll();
            $page = 1;
        }
        else {
            $this->pages = ceil($this->total / $items);
            if ($page > $this->pages)
            {
                $page = $this->pages;
            }
            $this->offset = ($page - 1) * $items;
            $this->items = $orm->where($where)->findAll($items, $this->offset);
            if (($page > 1) && ($this->pages > 1))
            {
                $this->previous = ($page - 1);
            }
            if ($page < $this->pages)
            {
                $this->next = ($page + 1);
            }
        }
        $this->page = $page;
        $this->layout = null;
    }

    public function __call($name, array $args = array())
    {
        $method = strtolower($name);
        if (substr_compare($method, 'get', 0, 3) == 0)
        {
            $attribute = substr($method, 3);
            if (property_exists($this, $attribute))
            {
                return $this->$attribute;
            }
        }
        throw new Hayate_Exception(sprintf(_('Method: %s not found in class: %s'), $name, __CLASS__));
    }


    public function setBasepath($basepath)
    {
        $this->basepath = $basepath;
    }

    public function setLayout(Hayate_Util_PageLayout $layout)
    {
        $this->layout = $layout;
    }

    public function hasNext()
    {
        return $this->next !== false;
    }

    public function hasPrevious()
    {
        return $this->previous !== false;
    }

    public function fetchNavigation()
    {
        if (empty($this->layout))
        {
            $sl = new Hayate_Util_SimpleLayout();
            return $sl->fetchNavigation($this);
        }
        else {
            return $this->layout->fetchNavigation($this);
        }
    }

    public function renderNavigation()
    {
        echo $this->fetchNavigation();
    }

    public function __toString()
    {
        return $this->fetchNavigation();
    }
}
