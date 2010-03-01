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
 * @version 1.0
 */
class Log
{
    const HAYATE_LOG_OFF = 0;
    const HAYATE_LOG_ERROR = 1;
    const HAYATE_LOG_DEBUG = 2;
    const HAYATE_LOG_INFO = 3;

    protected static $log_types = array(self::HAYATE_LOG_OFF => '',
					self::HAYATE_LOG_ERROR => 'ERROR',
					self::HAYATE_LOG_DEBUG => 'DEBUG',
					self::HAYATE_LOG_INFO => 'INFO');

    private function __construct() {}

    public static function error($msg, $print_r = true)
    {
	self::write(self::HAYATE_LOG_ERROR, $msg, $print_r);
    }

    public static function info($msg, $print_r = true)
    {
	self::write(self::HAYATE_LOG_INFO, $msg, $print_r);
    }

    public static function debug($msg, $print_r = true)
    {
	self::write(self::HAYATE_LOG_DEBUG, $msg, $print_r);
    }

    protected static function write($type, $msg, $print_r)
    {
	require_once 'Config.php';
	$error_level = Config::instance()->get('error_level', 0);
	if ($type == $error_level)
	{
	    $logdir = APPPATH.'logs/';
	    if (! is_writable($logdir))
	    {
		require_once 'HayateException.php';
		throw new HayateException(sprintf(_('"logs" directory "%s" is not writable.'), $logdir));
	    }
	    $filename = $logdir.'log-'.date('d-m-Y').'.log';
	    $logfile = new SplFileObject($filename, 'a');
	    self::header($type, $logfile);
	    if (true === $print_r) {
		$msg = print_r($msg, true);
	    }
	    $logfile->fwrite($msg);
	    self::footer($logfile);
	}
    }

    protected static function header($type, SplFileObject $logfile)
    {
	$logfile->fwrite(self::$log_types[$type].' - '.date('r').' --> ');
    }

    protected static function footer(SplFileObject $logfile)
    {
	$logfile->fwrite("\n");
    }
}