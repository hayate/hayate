<?php
/**
 * Hayate Framework
 * Copyright 2009-2011 Andrea Belvedere (scieck [at] gmail [dot] com)
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
 /**
  * @package Hayate_Util
  */
class Hayate_Util_SimpleLayout implements Hayate_Util_PageLayout
{
    public function fetchNavigation(Hayate_Util_Paginate $paginate)
    {
        if ($paginate->getPages() <= 1) return '';

        $items = $paginate->getItems();
        $basepath = $paginate->getBasepath();

        $ret = '<div id="paginate"><ul>';
        if ($paginate->hasPrevious())
        {
            $ret .= sprintf('<li><a href="%s%d">%s</a></li>', $paginate->getBasepath(), $paginate->getPrevious(), _('Previous'));
        }

        for ($i = 1; $i <= $paginate->getPages(); $i++)
        {
            if ($i == $paginate->getPage())
            {
                $ret .= sprintf('<li><span>%d</span></li>', $i);
            }
            else {
                $ret .= sprintf('<li><a href="%s%d">%d</a></li>', $paginate->getBasepath(), $i, $i);
            }
        }

        if ($paginate->hasNext())
        {
            $ret .= sprintf('<li><a href="%s%d">%s</a></li>', $paginate->getBasepath(), $paginate->getNext(), _('Next'));
        }

        $ret .= '</ul></div>';
        return $ret;
    }
}
