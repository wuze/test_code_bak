<?php 

class cURL { 

	var $headers; 
	var $user_agent; 
	var $compression; 
	var $cookie_file; 
	var $proxy; 
	var $arr;

	function cURL($cookies=TRUE,$cookie='cookie.txt',$compression='gzip',$proxy='') { 
		$this->headers[] = 'Connection: Keep-Alive'; 
		$this->headers[] = 'Accept: */*';
		$this->headers[] = 'Accept-Encoding: gzip,deflate'; 
		$this->user_agent = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.95 Safari/537.36";

		$this->cookies=$cookies; 

		if ($this->cookies == TRUE) $this->cookie($cookie); 
	} 


	function cookie($cookie_file) { 
		if (file_exists($cookie_file)) { 
			$this->cookie_file=$cookie_file; 
		} else { 
			fopen($cookie_file,'w') or $this->error('The cookie file could not be opened. Make sure this directory has the correct permissions'); 
			$this->cookie_file=$cookie_file; 
			fclose($this->cookie_file); 
		} 
	} 

	function extractCookies($string) {
		$cookies = array();/*{{{*/
		
		$lines = explode("\n", $string);
	 
		// iterate over lines
		foreach ($lines as $line) {
	 
			// we only care for valid cookie def lines
			if (isset($line[0]) && substr_count($line, "\t") == 6) {
	 
				// get tokens in an array
				$tokens = explode("\t", $line);
	 
				// trim the tokens
				$tokens = array_map('trim', $tokens);
	 
				$cookie = array();
	 
				// Extract the data
				$cookie['domain'] = $tokens[0];
				$cookie['flag'] = $tokens[1];
				$cookie['path'] = $tokens[2];
				$cookie['secure'] = $tokens[3];
	 
				// Convert date to a readable format
				$cookie['expiration'] = "2100-10-10 0:00:00";
	 
				$cookie['name'] = $tokens[5];
				$cookie['value'] = $tokens[6];
	 
				// Record the cookie.
				$cookies = $cookie;
			}
		}

		return $cookies;
	} /*}}}*/

	function GetCookie()
	{
		$ck = $this->extractCookies(file_get_contents($this->cookie_file));
		
		$arr = array();
		if( $ck )
		{
			$arr[$ck['name']] = $ck['value'];
		}
		return  $arr;
	}

	function getDefault($url) {
		$process = curl_init($url); 

		curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent); 
		curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file); 
		curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file); 
		curl_setopt($process, CURLOPT_TIMEOUT, 300); 
		curl_setopt($process, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($process, CURLOPT_FOLLOWLOCATION, 0); 

		$return = curl_exec($process); 
		curl_close($process); 
		return $return; 
	}

	function getGen($url) {
		$process = curl_init($url); 
		curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers); 
		curl_setopt($process, CURLOPT_HEADER, 0); 
		curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent); 
		curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file); 
		curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file); 
		curl_setopt($process, CURLOPT_ENCODING,  $this->compression); 
		curl_setopt($process, CURLOPT_TIMEOUT, 300); 
		curl_setopt($process, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1); 
		$return = curl_exec($process); 
		curl_close($process); 
		return $return; 
	}

	function get($url, $cook="") { 
		$process = curl_init($url); 
		curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent); 
		if ($cook) curl_setopt($process, CURLOPT_COOKIE, $cook); 
		else curl_setopt($process, CULR_COOKIEFILE, $this->cookie_file);

		curl_setopt($process, CURLOPT_TIMEOUT, 300); 
		curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
	   	curl_setopt($process, CURLOPT_FOLLOWLOCATION, 0); 
		$return = curl_exec($process); 
		curl_close($process); 
		return $return; 
	} 


	function post($url, $data, $cook="") { 
		$process = curl_init($url); 

		if($cook) curl_setopt($process, CURLOPT_COOKIE, $cook); 
		else curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file); 

		//curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file); 
		curl_setopt($process, CURLOPT_TIMEOUT, 300); 
		curl_setopt($process, CURLOPT_ENCODING, ""); 
		curl_setopt($process, CURLOPT_POST, 1); 
		curl_setopt($process, CURLOPT_POSTFIELDS, $data); 
		curl_setopt($process, CURLOPT_POSTFIELDS, $data); 
		curl_setopt($process, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($process, CURLOPT_FOLLOWLOCATION, 0); 

		$return = curl_exec($process); 
		//print_r(curl_getinfo($process));
		curl_close($process); 
		return $return; 
	} 

	function error($error) { 
		echo "<center><div style='width:500px;border: 3px solid #FFEEFF; padding: 3px; background-color: #FFDDFF;font-family: verdana; font-size: 10px'><b>cURL Error</b><br>$error</div></center>"; 
		die; } 
} 
?> 
