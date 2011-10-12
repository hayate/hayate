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
 * @package Hayate_View
 */
abstract class Hayate_View_Abstract
{
    protected $style = array();
    protected $jscript = array();
    protected $meta = array();
    protected $vars = array();

    public function assign(array $vars)
    {
        $this->vars = $vars;
    }

    public function style($href = null, $media = 'screen', $type = 'text/css')
    {
        if(null === $href)
        {
            return implode("\n", $this->style);
        }
        $this->style[] = '<link rel="stylesheet" type="' . $type .
                '" media="' . $media . '" href="' . $href . '" />';
        return true;
    }

    public function jscript($src = null, $type = 'text/javascript', $charset = 'UTF-8')
    {
        if(null === $src)
        {
            return implode("\n", $this->jscript);
        }
        $this->jscript[] = '<script type="' . $type . '" src="' . $src .
                '" charset="' . $charset . '"></script>';
        return true;
    }

    public function meta($name = null, $content = null, $scheme = null)
    {
        if(null === $name)
        {
            return implode("\n", $this->meta);
        }
        $meta = '<meta name="' . $name . '" content="' . $content . '"';
        $meta .= is_null($scheme) ? ' />' : ' scheme="' . $scheme . '" />';
        $this->meta[] = $meta;
    }

    public function hequiv($name = null, $content = null)
    {
        if(null === $name)
        {
            return implode("\n", $this->hequiv);
        }
        $this->meta[] = '<meta http-equiv="'.$name.'" content="'.$content.'" />';
    }
}