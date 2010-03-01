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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *  
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * @file index.php
 * @version $Id: index.php 39 2010-02-08 08:47:53Z andrea $
 */

/**
 * the application directory path
 */
$app_path = '../application';

/**
 * hayate directory path
 */
$hayate_path = '../trunk/hayate';


/** below this line only make changes if you know what you are doing **/

define('APPPATH', realpath($app_path).DIRECTORY_SEPARATOR);
define('HAYATE', realpath($hayate_path).DIRECTORY_SEPARATOR);
unset($app_path);
unset($hayate_path);

require_once HAYATE.'Hayate.php';
$hayate = Hayate::instance();
$hayate->run();
