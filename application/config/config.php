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
$config['error_level'] = 0;

/**
 * Directory to store log files
 */
$config['log_directory'] = dirname(DOCROOT) . '/logs';

/**
 * Display errors ?
 */
$config['display_errors'] = true;

/**
 * Error reporting
 *
 * PHP >= 5.3.0
 * development: E_ALL | E_STRICT
 * production : E_ALL & ~E_DEPRECATED
 *
 * PHP < 5.3.0
 * development: E_ALL | E_STRICT
 * production : E_ALL
 */
$config['error_reporting'] = E_ALL | E_STRICT;

/**
 * secret key
 *
 * key used for encryption (i.e. cookies and sessions)
 */
$config['secret_key'] = 'a very very secret key goes here';

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
 * directory (absolute path as string) or directories (array of
 * absolute paths) where db models are stored,
 */
$config['models_dir'] = array(APPPATH . 'models');

/**
 * enable hooks, true to enable false to disable
 *
 * When hooks are enabled, files called hook.php in application
 * directory and modules directories are loaded at startup, hook.php
 * files should contain Hayate_Event::add(...) statements to hook to
 * the events fired during application run time
 */
$config['enable_hooks'] = true;

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
$config['view'] = array('name' => 'native');

/**
 * smarty view config example
 */
/*
$config['view'] = array('name' => 'smarty',
                        'smarty_dir' => dirname(DOCROOT) . '/lib/Smarty-3.0.5/libs/',
                        'template_dir' => dirname(DOCROOT) . '/templates',
                        'compile_dir' => dirname(DOCROOT) . '/templates_c',
                        'compile_check' => true,
                        'use_sub_dirs' => true,
                        'allow_php_tag' => true
    );
*/
