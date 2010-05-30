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
class Hayate_Upload
{
    const EXT_ERROR = -1;
    const UNKNOWN_ERROR = -2;
    const UNWRITABLE_ERROR = -3;
    const OVERSIZE_ERROR = -4;
    protected $item;
    protected $name;
    protected $error;
    protected $status;

    public function __construct($name, array $files)
    {
	if (! isset($files[$name]))
	{
	    throw new Hayate_Exception(sprintf(_('Item name %s not found.'), $name));
	}
	$this->item = $files[$name];
	$this->name = $name;
	$this->error = '';
	$this->status = UPLOAD_ERR_OK;
    }

    public function status()
    {
	return $this->status;
    }

    public function error()
    {
	return $this->error;
    }

    public function uploaded()
    {
	return ($this->item['error'] != UPLOAD_ERR_NO_FILE);
    }

    public function save($path, $sanitize = true, $prefix = null)
    {
	$filename = $this->item['name'];
	if ($sanitize)
	{
	    $filename = preg_replace(array('/[?:\/*""<>|&]/', '/\s+/'),
				     array('', '_'), $this->item['name']);
	}
	if (null === $prefix)
	{
	    $prefix = time().'_';
	}
	$filename = $prefix.$filename;
	if (! is_dir($path) && !@mkdir($path, 0755, true))
	{
	    $this->status = self::UNWRITABLE_ERROR;
	    $this->error = sprintf(_('%s is not writable by the web server.'), $path);
	    return false;
	}
	if (! is_writable($path))
	{
	    $this->status = self::UNWRITABLE_ERROR;
	    $this->error = sprintf(_('%s is not writable by the web server.'), $path);
	    return false;
	}
	$filepath = rtrim($path, '\//') . DIRECTORY_SEPARATOR . $filename;
	if (move_uploaded_file($this->item['tmp_name'], $filepath))
	{
	    @chmod($filepath, 0644);
	    return $filepath;
	}
	$this->status = self::UNKNOWN_ERROR;
	$this->error = sprintf(_('File could not be saved in: %s'), $filepath);
	return false;
    }

    public function validate(array $exts = array(), $size = null)
    {
	if ($exts !== array())
	{
	    $exts = array_map('strtolower', $exts);
	    $ext = strtolower(pathinfo($this->item['name'], PATHINFO_EXTENSION));
	    if (! in_array($ext, $exts))
	    {
		$this->status = self::EXT_ERROR;
		$this->error = sprintf(_('Invalid extension %s. '.
					 'Supported extensions: %s'),
				       $ext,
				       implode(', ', $exts));
		return false;
	    }
	}
	if (is_numeric($size))
	{
	    if ($this->item['size'] > $size)
	    {
		$this->status = self::OVERSIZE_ERROR;
		$this->error = sprintf(_('File size is over allowed limit of %d.'), $size);
		return false;
	    }
	}
	switch ($this->item['error'])
	{
	case UPLOAD_ERR_OK:
	    return true;
	case UPLOAD_ERR_INI_SIZE:
	    $this->status = UPLOAD_ERR_INI_SIZE;
	    $this->error =
		_('The uploaded file exceeds the upload_max_filesize directive in php.ini.');
	    break;
	case UPLOAD_ERR_FORM_SIZE:
	    $this->status = UPLOAD_ERR_FORM_SIZE;
	    $this->error =
		_('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.');
	    break;
	case UPLOAD_ERR_PARTIAL:
	    $this->status = UPLOAD_ERR_PARTIAL;
	    $this->error = _('The uploaded file was only partially uploaded.');
	    break;
	case UPLOAD_ERR_NO_FILE:
	    $this->status = UPLOAD_ERR_NO_FILE;
	    $this->error = _('No file was uploaded.');
	    break;
	case UPLOAD_ERR_NO_TMP_DIR:
	    $this->status = UPLOAD_ERR_NO_TMP_DIR;
	    $this->error = _('Missing a temporary folder.');
	    break;
	case UPLOAD_ERR_CANT_WRITE:
	    $this->status = UPLOAD_ERR_CANT_WRITE;
	    $this->error = _('Failed to write file to disk.');
	    break;
	case UPLOAD_ERR_EXTENSION:
	    $this->status = UPLOAD_ERR_EXTENSION;
	    $this->error = _('A PHP extension stopped the file upload.');
	    break;
	default:
	    $this->status = self::UNKNOWN_ERROR;
	    $this->error = _('Unknown error while uploading file, please try again.');
	}
	return ($this->status === UPLOAD_ERR_OK);
    }
}