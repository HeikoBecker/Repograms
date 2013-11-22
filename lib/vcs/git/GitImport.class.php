<?php
error_reporting(-1);

class gitImport {
	private $RepoObject;

	public function __construct($repo,$user=null,$password=null){
		if ($this->is_valid_url($repo) === false){
			// die("<h1>Possible injection detected</h1>");
			error_log("Injection detected: $repo");
		}	
		$tmp = tempnam(sys_get_temp_dir(),"");
		unlink($tmp);
		mkdir($tmp);
		$tmp;			
		if (!file_exists($tmp)) throw new Exception("Temporary folder could not be created!");
		$command = 	'cd '.$tmp;
		$command.=	' && git clone "'.$repo.'"';
		$command."\n";
		error_log($command);
		shell_exec($command);
		// if (!file_exists($tmp.'/.git')) throw new Exception ('No .git Folder found');
		$command = 'cd '.$tmp."/*/ && git log --numstat --pretty='%x1A},%x1A%H%x1A:{%x1Aauthor%x1A:%x1A%an%x1A,%x1Aauthor_mail%x1A:%x1A%ae%x1A,%x1Adate%x1A:%x1A%at%x1A,%x1Amessage%x1A:%x1A%s%x1A,%x1Achanges%x1A : %x1A'";
		$output = shell_exec($command);
		//echo $output;
		$json = self::unescape($output,chr(26));
		$json = substr($json,3,strlen($json));
		$json = '{'.$json.'"}}';
		
		$json = utf8_encode($json);
		$output_array = json_decode($json,true);
		//	print_r($output_array);
		$this->RepoObject = $output_array;
		self::removeDir($tmp);
	}

	
  
  /* Verify the syntax of the given URL.
  *
  * @access public
  * @param $url The URL to verify.
  * @return boolean
  */
private function is_valid_url($url) {
   if ($this->str_starts_with(strtolower($url), 'http://localhost')) {
     return true;
   }
   return preg_match('/^(https?):\/\/'.                                         // protocol
'(([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+'.         // username
'(:([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+)?'.      // password
'@)?(?#'.                                                  // auth requires @
')((([a-z0-9]\.|[a-z0-9][a-z0-9-]*[a-z0-9]\.)*'.           // domain segments AND
'[a-z][a-z0-9-]*[a-z0-9]'.                                 // top level domain  OR
'|((\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])\.){3}'.
'(\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])'.                 // IP address
')(:\d+)?'.                                                // port
')(((\/+([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)*'. // path
'(\?([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)'.      // query string
'?)?)?'.                                                   // path and query string optional
'(#([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)?'.      // fragment
'$/i', $url);
}


/**
  * String starts with something
  *
  * This function will return true only if input string starts with
  * niddle
  *
  * @param string $string Input string
  * @param string $niddle Needle string
  * @return boolean
  */
function str_starts_with($string, $niddle) {
       return substr($string, 0, strlen($niddle)) == $niddle;
}
	
	public function getRawRepoInfo(){
		if (isset($this->RepoObject) && !is_null($this->RepoObject))
			return $this->RepoObject;
		else
			throw new Exception("Fetching GitObject was not sucessfull");
	}
	
	private function unescape($escapedString, $escapeCharacter) {
        	// Replace bad characters with the corresponding escape sequence for JSON
		$badCharacters = array('/"/','/\//' ,'/\\\/','/\f/','/\n/','/\r/','/\t/'); // Not supported: backspace, utf8-characters
		$correspondingGoodCharacters = array('\"','\/' ,'\\','\f','\n','\r','\t');
	        $intermediate = preg_replace($badCharacters, $correspondingGoodCharacters, $escapedString);
	       	// Replace escapeCharacter with "
	        return preg_replace('/'.$escapeCharacter.'/', '"', $intermediate);
	}
	
	private function removeDir($path){
		$files = array_diff(scandir($path), array('.','..'));
		foreach ($files as $file) {
		 (is_dir("$path/$file")) ? self::removeDir("$path/$file") : unlink("$path/$file");
		}
		return rmdir($path);
	}
}
?>

