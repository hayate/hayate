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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Main application configuration file, modify as required
 *
 * @version $Id: config.php 39 2010-02-08 08:47:53Z andrea $
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
$config['error_level'] = 1;

/**
 * set to true, to protect against Cross Site Scripting attacks
 */
$config['xss_clean'] = true;

/**
 * Set the default module
 */
$config['default_module'] = 'default';

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

/**
 * Set database options
 */
$config['database'] = array('connection' => array('driver' => 'mysql',
                                                  'user' => 'andrea',
                                                  'pass' => 'donkey',
                                                  'host' => 'localhost',
                                                  'port' => false,
                                                  'database' => 'hayate'),
                            'persistent' => false,
                            'charset' => 'utf8',
                            'object' => true,
                            'escape' => true,
                            'prefix' => '');

/**
 * routes
 */
$config['routes'] = array('author/(.*)' => 'index/index/$1',
                          'author/andrea-belvedere' => 'author/marco-belvedere');