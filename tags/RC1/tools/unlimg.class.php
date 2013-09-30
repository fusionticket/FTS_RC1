<?php

class unlimg
{
	var $l = 0;
	var $im = false;
	var $str = false;
		
	function __construct($l=500,$wd='')
	{
		$this->wd = sha1(strtolower($wd));
		
		/*
		if (file_exists('cache/'.$this->wd.'.png'))
		{
			header('Content-Type: image/png');
			readfile('cache/'.$this->wd.'.png');
			exit;
		}
		*/
		
		$this->im = imagecreatetruecolor($l,$l);
		$this->l = $l;
		
		imagesavealpha($this->im,true);
		imagefill($this->im,0,0,-6);

		$this->str = str_split($this->wd,2);
		
		foreach ($this->str as &$val)
			$val = hexdec($val);
		
		$sp = ($this->str[19] ^ $this->str[1] ^ $this->str[14]) % 10 + 3;
		$d = 1400;

		$sh1dx = $this->str[0] % 4 + 1;
		$sh1dy = $this->str[5] % 4 + 1;

		$sh2dx = $this->str[9] % 8 + 1;
		$sh2dy = $this->str[2] % 8 + 1;

		$sh1imx = ($this->str[4] ^ $this->str[17])/255;
		$sh1imy = ($this->str[16] ^ $this->str[5])/255;

		$sh2imx = ($this->str[13] ^ $this->str[12])/255;
		$sh2imy = ($this->str[17] ^ $this->str[0])/255;
		
		$shclr = $this->str[2] ^ $this->str[8];
		$shclg = $this->str[7] ^ $this->str[18];
		$shclb = $this->str[3] ^ $this->str[7];
		
		$gr = (2*pi())/$sp;
		
		for ($i=0;$i<=$sp;$i++)
		{

			$a[] = $this->l/2+$d*sin(($i+$sh1imx)*$gr)/$sh1dx;
			$a[] = $this->l/2+$d*cos(($i+$sh1imy)*$gr)/$sh1dy;

			$a[] = $this->l/2-$d*sin(($i+$sh2imx)*$gr)/$sh2dx;
			$a[] = $this->l/2-$d*cos(($i+$sh2imy)*$gr)/$sh2dy;
		}
		
		imagefilledpolygon($this->im,$a,$sp*2,imagecolorallocate($this->im,$shclr,$shclg,$shclb));
	}
	
	function iout($l=100)
	{
		$im = imagecreatetruecolor($l,$l);
		imagesavealpha($im,true);
		imagefill($im,0,0,-6);
		imagecopyresampled($im,$this->im,0,0,0,0,$l,$l,$this->l,$this->l);
		
		header('Content-Type: image/png');
		imagepng($im);
		
		/*
		imagepng($im,'cache/'.$this->wd.'.png',9,PNG_ALL_FILTERS);
		readfile('cache/'.$this->wd.'.png');
		*/
	}
}