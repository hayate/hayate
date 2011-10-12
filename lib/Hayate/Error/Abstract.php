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
 * Error reporting abstract class
 */
abstract class Hayate_Error_Abstract
{
    protected static $instance = null;
    protected $headers;
    protected $status;
    protected $statuses;
    protected $exception;

    protected function __construct()
    {
        $this->headers = array();
        $this->status = 200;
        $this->exception = null;
        $this->statuses = array(200 => 'OK',
                                400 => 'Bad Request',
                                401 => 'Unauthorized',
                                402 => 'Payment Required',
                                403 => 'Forbidden',
                                404 => 'Not Found',
                                405 => 'Method Not Allowed',
                                406 => 'Not Acceptable',
                                407 => 'Proxy Authentication Required',
                                408 => 'Request Timeout',
                                409 => 'Conflict',
                                410 => 'Gone',
                                411 => 'Length Required',
                                412 => 'Precondition Failed',
                                413 => 'Request Entity Too Large',
                                414 => 'Request-URI Too Long',
                                415 => 'Unsupported Media Type',
                                416 => 'Request Range Not Satisfiable',
                                417 => 'Expectation Failed',
                                500 => 'Internal Server Error',
                                501 => 'Not Implemented',
                                502 => 'Bad Gateway',
                                503 => 'Service Unavailable',
                                504 => 'Gateway Timeout',
                                505 => 'HTTP Version Not Supported');
    }

    /**
     * @return string The packaged error
     */
    abstract public function format();

    /**
     * @param string $name The name of the http header or the whole
     * header if $value is not set
     * @param string $value The value of the header
     * @return void
     *
     * If $value is not set $name should be the whole header
     * i.e. 'Content-Type: application/pdf' otherwise $name should be
     * 'Content-Type' and $value should be 'application/pdf'
     *
     */
    public function addHeader($name, $value = null)
    {
        if (null === $value)
        {
            $this->headers[] = $name;
        }
        else {
            $this->headers[] = $name.': '.$value;
        }
    }

    public function setStatus($status)
    {
        if (array_key_exists($status, $this->statuses))
        {
            $this->status = $status;
        }
    }

    public function setException(Exception $ex)
    {
        $this->exception = $ex;
    }

    public function getException()
    {
        return isset($this->exception) ? $this->exception : _('Not Available.');
    }

    public function getMessage()
    {
        return isset($this->exception) ? $this->exception->getMessage() : _('Not Available.');
    }

    public function getTrace()
    {
        $trace = debug_backtrace();
        // remove first item as it is the error_handler caller
        array_shift($trace);
        $trace = count($trace) ? $trace : isset($this->exception) ? $this->exception->getTrace() : array();

        $items = array();
        foreach ($trace as $entry)
        {
            $tmp = '<li><pre>';
            if (isset($entry['file']))
            {
                $tmp .= $entry['file'] . ': <b>'.$entry['line'].'</b><br />';
            }
            if (isset($entry['class']))
            {
                $tmp .= $entry['class'].$entry['type'];
            }
            $tmp .= $entry['function'].'(';
            if (isset($entry['args']) && is_array($entry['args']))
            {
                $sep = '';
                while (null !== ($arg = array_shift($entry['args'])))
                {
                    $tmp .= $sep . gettype($arg);
                    $sep = ', ';
                }
            }
            $tmp .= ')</pre></li>';
            $items[] = $tmp;
        }
        return count($items) ? implode("\n", $items) : '<li><pre>'._('Not Available.').'</pre></li>';
    }

    public function report()
    {
        header('HTTP/1.1 '.$this->status.' '.$this->statuses[$this->status]);
        foreach ($this->headers as $header)
        {
            header($header);
        }
        $output = $this->format();
        header('Content-Length: '.strlen($output));
        return $output;
    }
}
