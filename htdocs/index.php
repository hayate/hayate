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
 * relative or absolute path of application directory
 */
$application = '../application';

/**
 * relative or absolute path to application libraries
 */
$library = '../lib';

/**
 * relative or absolute path of modules directory
 */
$modules = $application . '/modules';

define('APPPATH', realpath($application) . DIRECTORY_SEPARATOR);
define('LIBPATH', realpath($library) . DIRECTORY_SEPARATOR);
define('MODPATH', realpath($modules) . DIRECTORY_SEPARATOR);
define('DOCROOT', $_SERVER['DOCUMENT_ROOT']);

unset($application);
unset($library);
unset($modules);

require_once LIBPATH . 'Hayate/Bootstrap.php';
Hayate_Bootstrap::getInstance()->run();