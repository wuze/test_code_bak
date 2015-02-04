<?php
include_once(dirname(__FILE__)."/curl.php");
include_once(dirname(__FILE__)."/Valite.php");

class Common
{
	var $curl;
	function __construct()
	{
		$this->curl= new cUrl("TRUE","cookie.txt", "gzip","IE");
	}

	function GetCook()
	{
		return $this->curl->GetCookie();
	}

	function getDefault($url)
	{/*{{{*/
		$ret = $this->curl->getDefault($url);
		return $ret;
	}/*}}}*/

	// post
	function getYYDefault($url, $data)
	{/*{{{*/
		$ret  = $this->curl->post($url, $data);
		return $ret;
	}/*}}}*/

	function GenCode($url, $cook)
	{/*{{{*/
		$data = $this->curl->get($url,$cook);
		$fp = fopen("valid.bmp","wb");
		fwrite($fp, $data);
		fclose($fp);

		//转换图片格式
		$img = ImageCreateFromBmp("valid.bmp");  
		imagejpeg($img, "valid.jpg");  

		//识别验证码图片
		$valid = new Valite();
		$valid->setImage("valid.jpg");
		$valid->getHec();
		$valideCode = $valid->run();
		return $valideCode;
	}/*}}}*/

	function Login($url, $act, $pwd, $code,$cookie="")
	{/*{{{*/
		$param = ($act.'|-|'.$pwd.'|-|'.$code.'|-|');
		$ret   = $this->curl->post($url, array("Impress_form"=>$param),$cookie);

		return $ret;
	}/*}}}*/


	function doPost($url, $data, $cookie)
	{/*{{{*/
		$ret  = $this->curl->post($url, $data, $cookie);
		return $ret;
	}/*}}}*/

}

