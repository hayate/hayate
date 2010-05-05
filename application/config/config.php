<?php
/**
* Hayate Framework
* Copyright 2009 Andrea Belvedere
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
* General application configuration file, modify as required
*/

/**
* site hostname
*/
$config['hostname'] = 'hayate';

/**
* hayate base path
* is the site served from document_root or a sub-folder
*/
$config['base_path'] = '/';

/**
* set log level
* 0 = no log
* 1 = error
* 2 = debug
* 3 = info
*/
$config['error_level'] = 3;

/**
 * Directory to store log files
 */
$config['log_directory'] = APPPATH . 'logs';

/**
* set to true, to protect against Cross Site Scripting attacks
*/
$config['xss_clean'] = true;

/**
* Set the default module
*/
$config['default_module'] = 'default';

/**
 * Installed modules (directories names)
 */
$config['modules'] = array();

/**
* Internal charset
*/
$config['charset'] = 'UTF-8';

/**
 * Application timezone
 *
 * @see http://www.php.net/manual/en/timezones.php
 */
$config['timezone'] = 'Asia/Tokyo';

/**
 * Application default locale
 * this can be a string or an array, on linux check installed locale
 * by running "locale -a" on the comman line
 *
 * @see http://php.net/manual/en/function.setlocale.php
 */
$config['locale'] = array('en_US', 'en_US.utf8');

/**
* Set view options,
* current support options are "native" for php templates or "smarty"
*/
$config['view'] = array('name' => 'smarty',
                        'smarty_dir' => APPPATH.'/libs/Smarty-2.6.26/libs/',
                        'template_dir' => dirname(dirname(__FILE__)).'/templates',
                        'compile_dir' => dirname(dirname(__FILE__)).'/templates_c',
                        'compile_check' => true,
                        'use_sub_dirs' => true);
