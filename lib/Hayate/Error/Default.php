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
 * @package Hayate_Error
 *
 * Application default Exception/Error Reporter
 */
class Hayate_Error_Default extends Hayate_Error_Abstract
{
    protected function __construct()
    {
        parent::__construct();
    }

    public static function getInstance()
    {
        if (null === self::$instance)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function format()
    {
        ob_start();
        echo <<<___ERROR_HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Hayate Debug</title>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
<style type="text/css">
            body {color:#333;font-family:"Courier New",Courier,monospace;}
        table {border-collapse:collapse;border-color:#bbbbbb;width:100%;}
        th {text-align:left;background-color:#ff6600;vertical-align:top;}
        td {background-color:#eeeeee;}
        pre {word-wrap:break-word;padding:0;margin:0;}
        small {font-weight:normal;}
        ul#trace {margin:0;padding:0;list-style:none;}
        ul#trace li {margin-bottom:1.0em;}
</style>
</head>
<body>
<div id="wrap">
<table border="1" cellpadding="5" cellspacing="0">
<tr>
<th>Error:</th>
<td><pre><b><big>{$this->getMessage()}</big></b></pre></td>
</tr>
<tr>
<th>Exception:</th>
<td><pre>{$this->getException()}</pre></td>
</tr>
<tr>
<th>Trace:</th>
<td><ul id="trace">{$this->getTrace()}</ul></td>
</tr>
</table>
</div>
</body>
</html>
___ERROR_HTML;
        return ob_get_flush();
    }
}