<?php
/**
 * Hayate Framework
 * Copyright 2010 Andrea Belvedere
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
class Hayate_Util_Text
{
    public static function limitChars($text, $size = 80, $end = '&#8230;', $fullword = false)
    {
        $end = is_null($end) ? '&#8230;' : $end;

        if ($size > mb_strlen($text, 'UTF-8')) {
            return $text;
        }
        if ($size <= 0) {
            return $end;
        }
        if (!$fullword) {
            return rtrim(mb_substr($text, 0, $size, 'UTF-8')) . $end;
        }

        $matches = array();
        preg_match('/.{0,' . preg_quote($size - 1) . '}\S*/us', $text, $matches);

        return rtrim($matches[0]).(mb_strlen($matches[0], 'UTF-8') < mb_strlen($text, 'UTF-8') ? $end : '');
    }
}