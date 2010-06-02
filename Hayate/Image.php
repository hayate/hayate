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
class Hayate_Image
{
    protected $filepath;
    protected $img;
    protected $resized;
    protected $width;
    protected $height;
    protected $ext;


    public function __construct($filepath)
    {
	if (! function_exists('getimagesize'))
	{
	    throw new Hayate_Exception(_('GD extension is missing.'));
	}
	if (! is_file($filepath))
	{
	    throw new Hayate_Exception(sprintf(_('Cannot find %s'), $filepath));
	}
	$this->filepath = $filepath;

	$info = getimagesize($this->filepath);
	if (false === $info)
	{
	    throw new Hayate_Exception(sprintf(_('Cannot read %s'), $filepath));
	}
	list ($this->width, $this->height) = $info;

	$mimes = array('image/jpeg' => 'jpg',
		       'image/pjpeg' => 'jpg',
		       'image/gif' => 'gif',
		       'image/png' => 'png');
	$this->ext = isset($mimes[$info['mime']]) ? $mimes[$info['mime']] : null;
	if (null === $this->ext)
	{
	    throw new Hayate_Exception(sprintf(_('Supported mime types are: %s'),
					       implode(',', array_keys($mimes))));
	}
	switch ($this->ext)
	{
	case 'jpg':
	    $this->img = imagecreatefromjpeg($filepath);
	    break;
	case 'gif':
	    $this->img = imagecreatefromgif($filepath);
	    break;
	case 'png':
	    $this->img = imagecreatefrompng($filepath);
	    break;
	}
    }

    public function __destruct()
    {
	if ($this->img)
	{
	    imagedestroy($this->img);
	}
	if ($this->resized)
	{
	    imagedestroy($this->resized);
	}
    }

    public function resize($width = 0, $height = 0, $keep_ratio = true)
    {
	$width = ((null === $width) || !is_numeric($width) || ($width < 0)) ? 0 : $width;
	$height = ((null === $height) || !is_numeric($height) || ($height < 0)) ? 0 : $height;
	if ($width == 0 && $height == 0)
	{
	    throw new Hayate_Exception(_('At least one dimension must be greater than 0.'));
	}
	// calculate width proportionally to height
	if ($width == 0)
	{
	    $width = round(($height * $this->width) / $this->height);
	}
	// calculate height proportionally to width
	else if ($height == 0)
	{
	    $height = round(($width * $this->height) / $this->width);
	}
	else if ($keep_ratio)
	{
	    $wratio = ($this->width / $width);
	    $hratio = ($this->height / $height);
	    if ($hratio > $wratio)
	    {
		$width = round(($height * $this->width) / $this->height);
	    }
	    else {
		$height = round(($width * $this->height) / $this->width);
	    }
	}
	$this->resized = imagecreatetruecolor($width, $height);
	imagealphablending($this->resized, false);
	imagesavealpha($this->resized, true);
	imagecopyresampled($this->resized, $this->img, 0, 0, 0, 0,
			   $width, $height, $this->width, $this->height);
	return $this;
    }

    public function resizeCrop($width, $height, $top = 'center', $left = 'center')
    {
	if (! is_numeric($width) || ($width <= 0) ||
	    ! is_numeric($height) || ($height <= 0))
	{
	    throw new Hayate_Exception(_('Width and height must be numeric values and greater than 0'));
	}
	$valid_top = array('top','center','bottom');
	$valid_left = array('left', 'center','right');
	if (! in_array($top, $valid_top, true))
	{
	    throw new Hayate_Exception(_('Valid top are: "top","center" and "bottom"'));
	}
	if (! in_array($left, $valid_left, true))
	{
	    throw new Hayate_Exception(_('Valid left are: "left","center" and "right"'));
	}
	//
	if (($this->height / $height) >= ($this->width / $width))
	{
	    $this->resize($width,0);
	    // retrieve height of resized image
	    $rh = imagesy($this->resized);
	    if ($rh > $height)
	    {
		$x = 0;
		if ($top == 'center')
		{
		    $y = round(($rh - $height) / 2);
		}
		else if ($top == 'top')
		{
		    $y = 0;
		}
		else {
		    $y = round($rh - $height);
		}
	    }
	    else {
		$height = $rh;
		$x = 0;
		$y = 0;
	    }
	}
	else {
	    $this->resize(0,$height);
	    // retrieve width of resized image
	    $rw = imagesx($this->resized);
	    if ($rw > $width)
	    {
		$y = 0;
		if ($left == 'center')
		{
		    $x = round(($rw - $width) / 2);
		}
		else if ($left == 'left')
		{
		    $x = 0;
		}
		else {
		    $x = round($rw - $width);
		}
	    }
	    else {
		$width = $rw;
		$x = 0;
		$y = 0;
	    }
	}

	$dst = imagecreatetruecolor($width, $height);
	imagealphablending($dst, false);
	imagesavealpha($dst, true);
	imagecopy($dst, $this->resized, 0, 0, $x, $y, $width, $height);
	imagedestroy($this->resized);
	$this->resized = $dst;

	return $this;
    }

    public function save($filepath = null, $sanitize = true, $quality = 95)
    {
	$quality = is_numeric($quality) ? $quality : 95;
	$sanitize = (bool)$sanitize;
	if (null === $filepath)
	{
	    $filepath = $this->filepath;
	}
	if ($sanitize)
	{
	    $info = pathinfo($filepath);
	    $filename = $info['filename'];
	    $filename = preg_replace(array('/[?:\/*""<>|&]/', '/\s+/'),
				     array('', '_'), $filename);
	    $filepath = empty($info['dirname']) ? '' : $info['dirname'] . DIRECTORY_SEPARATOR;
	    $filepath .= $filename . '.'.$info['extension'];
	}
	switch ($this->ext)
	{
	case 'jpg':
	    imagejpeg($this->resized, $filepath, $quality);
	    break;
	case 'gif':
	    imagegif($this->resized, $filepath);
	    break;
	case 'png':
	    imagepng($this->resized, $filepath, 9);
	    break;
	}
    }

    public function render($quality = 95)
    {
	$quality = is_numeric($quality) ? $quality : 95;
	switch ($this->ext)
	{
	case 'jpg':
	    header('Content-type: image/jpeg');
	    imagejpeg($this->resized, null, $quality);
	    break;
	case 'gif':
	    header('Content-type: image/gif');
	    imagegif($this->resized, null);
	    break;
	case 'png':
	    header('Content-type: image/png');
	    imagepng($this->resized, null, 9);
	    break;
	}
    }
}