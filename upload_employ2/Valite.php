<?php

define('WORD_WIDTH',6);
define('WORD_HIGHT',10);
define('OFFSET_X',2);
define('OFFSET_Y',0);
define('WORD_SPACING',4);

class valite
{
	public function setImage($Image)
	{
		$this->ImagePath = $Image;
	}
	
	public function getData()
	{
		return $data;
	}
	
	public function getResult()
	{
		return $DataArray;
	}
	
	public function getHec()
	{
		$res = imagecreatefromjpeg($this->ImagePath);
		$size = getimagesize($this->ImagePath);
		$data = array();
		for($i = 0; $i < $size[1]; ++$i)
		{
			for($j = 0; $j < $size[0]; ++$j)
			{
				$rgb = imagecolorat($res,$j,$i);
				$rgbarray = imagecolorsforindex($res, $rgb);
				if($rgbarray['red'] < 160 || $rgbarray['green'] < 160 || $rgbarray['blue'] < 160)
				{
					$data[$i][$j]=1;
					//echo "0";
				}else{
					$data[$i][$j]=0;
					//echo "-";
				}
			}
			//echo "<br>";
		}
		$this->DataArray = $data;
		$this->ImageSize = $size;
	}
	
	public function run()
	{
		$result = "";
		$data = array("","","","");
		
		for($i = 0; $i < 4; ++$i)
		{
			$x = ($i * (WORD_WIDTH + WORD_SPACING)) + OFFSET_X;
			$y = OFFSET_Y;
			for($h = $y; $h < (OFFSET_Y + WORD_HIGHT); ++$h)
			{
				for($w = $x; $w < ($x + WORD_WIDTH); ++$w)
				{
					$data[$i] .= $this->DataArray[$h][$w];
				}
			}
		}


		foreach($data as $numKey => $numString)
		{
			$max = 0.0;
			$num = 0;
			foreach($this->Keys as $key => $value)
			{
				$percent = 0.0;
				similar_text($value, $numString, $percent);
				if(intval($percent) > $max)
				{
					$max = $percent;
					$num = $key;
					if(intval($percent) > 95)
						break;
				}
			}
			$result .= $num;
		}
		$this->data = $result;

		return $result;
	}

	public function Draw()
	{
		for($i = 0; $i < $this->ImageSize[1]; ++$i)
		{
	        for($j = 0; $j < $this->ImageSize[0]; ++$j)
		    {
			    echo $this->DataArray[$i][$j];
	        }
		    echo "\n";
		}
	}
	
	public function __construct()
	{
		$this->Keys = array(
			'0'=>'011110100001100001100001100001100001100001100001100001011110',
			'1'=>'001000111000001000001000001000001000001000001000001000111110',
			'2'=>'011110100001100001000001000010000100001000010000100001111111',
			'3'=>'011110100001100001000010001100000010000001100001100001011110',
			'4'=>'000100000100001100010100100100100100111111000100000100001111',
			'5'=>'111111100000100000101110110001000001000001100001100001011110',
			'6'=>'001110010001100000100000101110110001100001100001100001011110',
			'7'=>'111111100010100010000100000100001000001000001000001000001000',
			'8'=>'011110100001100001100001011110010010100001100001100001011110',
			'9'=>'011100100010100001100001100011011101000001000001100010011100',
		);
	}
	
	protected $ImagePath;
	protected $DataArray;
	protected $ImageSize;
	protected $data;
	protected $Keys;
	protected $NumStringArray;
}


/************以下为bmp转jpg的函数**************/
function bmp2gd($src, $dest = false)  
{  /*{{{*/
    /*** try to open the file for reading ***/  
    if(!($src_f = fopen($src, "rb")))  
    {  
        return false;  
    }  
  
	/*** try to open the destination file for writing ***/  
	if(!($dest_f = fopen($dest, "wb")))  
    {  
        return false;  
    }  
	  
	/*** grab the header ***/  
	$header = unpack("vtype/Vsize/v2reserved/Voffset", fread( $src_f, 14));  
	  
	/*** grab the rest of the image ***/  
	$info = unpack("Vsize/Vwidth/Vheight/vplanes/vbits/Vcompression/Vimagesize/Vxres/Vyres/Vncolor/Vimportant",  
	fread($src_f, 40));  
	  
	/*** extract the header and info into varibles ***/  
	extract($info);  
	extract($header);  
	  
	/*** check for BMP signature ***/  
	if($type != 0x4D42)  
	{  
	    return false;  
	}  
	  
	/*** set the pallete ***/  
	$palette_size = $offset - 54;  
	$ncolor = $palette_size / 4;  
	$gd_header = "";  
	  
	/*** true-color vs. palette ***/  
	$gd_header .= ($palette_size == 0) ? "\xFF\xFE" : "\xFF\xFF";  
	$gd_header .= pack("n2", $width, $height);  
	$gd_header .= ($palette_size == 0) ? "\x01" : "\x00"; 
	 
	if($palette_size) {  
		$gd_header .= pack("n", $ncolor);  
	}  
	/*** we do not allow transparency ***/  
	$gd_header .= "\xFF\xFF\xFF\xFF";  
	  
	/*** write the destination headers ***/  
	fwrite($dest_f, $gd_header);  
	  
	/*** if we have a valid palette ***/  
	if($palette_size)  
	{  
	    /*** read the palette ***/  
	    $palette = fread($src_f, $palette_size);  
	    /*** begin the gd palette ***/  
	    $gd_palette = "";  
	    $j = 0;  
	    /*** loop of the palette ***/  
	    while($j < $palette_size)  
	    {  
	        $b = $palette{$j++};  
	        $g = $palette{$j++};  
	        $r = $palette{$j++};  
	        $a = $palette{$j++};  
	        /*** assemble the gd palette ***/  
	        $gd_palette .= "$r$g$b$a";  
	    }  
	    /*** finish the palette ***/  
	    $gd_palette .= str_repeat("\x00\x00\x00\x00", 256 - $ncolor);  
	    /*** write the gd palette ***/  
	    fwrite($dest_f, $gd_palette);  
	}  
	
	/*** scan line size and alignment ***/  
	$scan_line_size = (($bits * $width) + 7) >> 3;  
	$scan_line_align = ($scan_line_size & 0x03) ? 4 - ($scan_line_size & 0x03) : 0;  
	  
	/*** this is where the work is done ***/  
	for($i = 0, $l = $height - 1; $i < $height; $i++, $l--)  
	{  
	    /*** create scan lines starting from bottom ***/  
	    fseek($src_f, $offset + (($scan_line_size + $scan_line_align) * $l));  
	    $scan_line = fread($src_f, $scan_line_size);  
	    if($bits == 24)  
	    {  
	        $gd_scan_line = "";  
	        $j = 0;  
	        while($j < $scan_line_size)  
	        {  
	            $b = $scan_line{$j++};  
	            $g = $scan_line{$j++};  
	            $r = $scan_line{$j++};  
	            $gd_scan_line .= "\x00$r$g$b";  
	        }  
	    }  
	    elseif($bits == 8)  
	    {  
	        $gd_scan_line = $scan_line;  
	    }  
	    elseif($bits == 4)  
	    {  
	        $gd_scan_line = "";  
	        $j = 0;  
	        while($j < $scan_line_size)  
	        {  
	            $byte = ord($scan_line{$j++});  
	            $p1 = chr($byte >> 4);  
	            $p2 = chr($byte & 0x0F);  
	            $gd_scan_line .= "$p1$p2";  
	        }  
	        $gd_scan_line = substr($gd_scan_line, 0, $width);  
	    }  
	    elseif($bits == 1)  
	    {  
	        $gd_scan_line = "";  
	        $j = 0;  
	        while($j < $scan_line_size)  
	        {  
	            $byte = ord($scan_line{$j++});  
	            $p1 = chr((int) (($byte & 0x80) != 0));  
	            $p2 = chr((int) (($byte & 0x40) != 0));  
	            $p3 = chr((int) (($byte & 0x20) != 0));  
	            $p4 = chr((int) (($byte & 0x10) != 0));  
	            $p5 = chr((int) (($byte & 0x08) != 0));  
	            $p6 = chr((int) (($byte & 0x04) != 0));  
	            $p7 = chr((int) (($byte & 0x02) != 0));  
	            $p8 = chr((int) (($byte & 0x01) != 0));  
	            $gd_scan_line .= "$p1$p2$p3$p4$p5$p6$p7$p8";  
	        }  
	    /*** put the gd scan lines together ***/  
	    $gd_scan_line = substr($gd_scan_line, 0, $width);  
	    }  
	    /*** write the gd scan lines ***/  
	    fwrite($dest_f, $gd_scan_line);  
	}  
	/*** close the source file ***/  
	fclose($src_f);  
	/*** close the destination file ***/  
	fclose($dest_f);  
	  
	return true;  
}  
  /*}}}*/

/** 
 * 
 * @ceate a BMP image 
 * 
 * @param string $filename 
 * 
 * @return bin string on success 
 * 
 * @return bool false on failure 
 * 
 */  
function ImageCreateFromBmp($filename)  
{  /*{{{*/
    /*** create a temp file ***/  
    $tmp_name = tempnam("/tmp", "GD");  
    /*** convert to gd ***/  
    if(bmp2gd($filename, $tmp_name))  
    {
        /*** create new image ***/  
        $img = imagecreatefromgd($tmp_name);  
        /*** remove temp file ***/  
        unlink($tmp_name);  
        /*** return the image ***/  
        return $img;  
    }
    return false;  
}/*}}}*/




?>
