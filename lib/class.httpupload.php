<?

/**
 * class.httpupload.php
 * a smart class help you in checking and saving uploads
 * Visit http://en.vietapi.com/wiki/index.php/PHP:_HttpUpload for more information
 * @package Misc
 * @author Nguyen Quoc Bao <quocbao.coder@gmail.com>
 * @version 1.0
 **/
 
define('HTTPUPLOAD_ERROR_OK' , 1);
define('HTTPUPLOAD_ERROR_NO_FILE' , -1);
define('HTTPUPLOAD_ERROR_INI_SIZE' , -2); //php size limit
define('HTTPUPLOAD_ERROR_FORM_SIZE' , -3); //form size limit
define('HTTPUPLOAD_ERROR_SIZE' , -4); //class size limit
define('HTTPUPLOAD_ERROR_IMG' , -5); //image size limit
define('HTTPUPLOAD_ERROR_EXT' , -6); //extension is not allowed
define('HTTPUPLOAD_ERROR_MIME' , -7); //mime is not allowed
define('HTTPUPLOAD_ERROR_WRITE' , -8); //there was a problem during processing file
define('HTTPUPLOAD_ERROR_PARTIAL' , -9); //The uploaded file was only partially uploaded
 
class httpupload {

	var $uploadDir = '';
	var $uploadName = '';
	var $uploadIndex = '';
	var $maxSize = 0;
	var $seperator = "/";
	var $handler = '';
	var $handlerType = ''; //move , copy , data
	var $targetName = '';
	var $savedName = '';
	var $maxWidth = '';
	var $maxHeight = '';
	var $allowExt = array();
	var $allowMime = array();
	var $fileCHMOD = 0777;
	var $prefix = false;
	var $extension = false;
	var $error_code = 0;
	var $error_lang = array(
			HTTPUPLOAD_ERROR_NO_FILE => "No file was submited.",
			HTTPUPLOAD_ERROR_INI_SIZE => "The uploaded file exceeds the upload_max_filesize directive in php.ini.",
			HTTPUPLOAD_ERROR_FORM_SIZE => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.",
			HTTPUPLOAD_ERROR_SIZE => "The uploaded file exceeds the max file size directive that was specified in class setting.",
			HTTPUPLOAD_ERROR_IMG => "The uploaded file exceeds the max image width or max image height directive that was specified in class setting.",
			HTTPUPLOAD_ERROR_EXT => "File extension is not allowed.",
			HTTPUPLOAD_ERROR_MIME => "File mime is not allowed.",
			HTTPUPLOAD_ERROR_WRITE => "There was an error during writing file . Maybe target file is existed or PHP cannot write the target file.",
			HTTPUPLOAD_ERROR_PARTIAL => "The uploaded file was only partially uploaded.",
	);

	/**
	 * Set upload directory
	 * @param string $dir upload directory
	 **/
	function setuploaddir($dir) {
		$this->uploadDir = $dir;
	}

	/**
	 * Set upload name
	 * @param string $name $HTTP_POST_VARS[$name]
	 **/
	function setuploadname($name) {
		$this->uploadName = $name;
	}
	
	/**
	 * Set upload name index
	 * @param string $name $HTTP_POST_VARS[$name][$index]
	 **/
	function setuploadindex($index) {
		$this->uploadIndex = $index;
	}

	/**
	 * Set upload filename
	 * @param string $name File name
	 **/
	function settargetfile($name) {
		$this->targetName = $name;
	}
	
	/**
	 * Set upload image size
	 * @param string $w Max image width
	 * @param string $h Max image height
	 **/
	function setimagemaxsize($w = null , $h = null) {
		$this->maxWidth = $w;
		$this->maxHeight = $h;
	}

	/**
	 * Set upload mime filter
	 * @param mixed $a Filter data
	 * @param string $s Text seperator (for string filter data)
	 **/
	function setallowmime($a , $s = '|') {
		if (!is_array($a)) {
			if (strpos($a , $s) === false) {
				$a = array($a);
			} else {
				$a = explode($s , $a);
			}
		}
		$this->allowMime = array();
		foreach ($a as $val) {
			$this->allowMime[] = strtolower(trim($val));
		}
	}
	
	/**
	 * Set upload extension filter
	 * @param mixed $a Filter data
	 * @param string $s Text seperator (for string filter data)
	 **/
	function setallowext($a , $s = '|') {
		if (!is_array($a)) {
			if (strpos($a , $s) === false) {
				$a = array($a);
			} else {
				$a = explode($s , $a);
			}
		}
		$this->allowExt = array();
		foreach ($a as $val) {
			$val = trim($val);
			if (substr($val , 0 , 1) != ".") $val = ".$val";
			$this->allowExt[] = strtolower($val);
		}
	}
	
	
	/**
	 * Set max file size
	 * @param int $size Max file size
	 **/
	function setmaxsize($size) {
		$this->maxSize = intval($size);
	}
	
	function httpupload($dir = '' , $name = '' , $index = '') {
		$this->uploadDir = $dir;
		$this->uploadName = $name;
		$this->uploadIndex = $index;
	}
	
	function getuploadname() {
		$FILE = $this->getuploadinfo($this->uploadName , $this->uploadIndex);
		if ($FILE === false) return false;
		return @$FILE['name'];
	}

	function getuploadsize() {
		$FILE = $this->getuploadinfo($this->uploadName , $this->uploadIndex);
		if ($FILE === false) return false;
		return @$FILE['size'];
	}
	
	function getuploadtype() {
		$FILE = $this->getuploadinfo($this->uploadName , $this->uploadIndex);
		if ($FILE === false) return false;
		return @$FILE['type'];
	}
	
	function getuploadtmp() {
		$FILE = $this->getuploadinfo($this->uploadName , $this->uploadIndex);
		if ($FILE === false) return false;
		return @$FILE['tmp_name'];
	}
	
	function getsavedname($fullpath=true) {
		$FILE = $this->getuploadinfo($this->uploadName , $this->uploadIndex);
		if ($FILE === false || $this->savedName == '') return false;
		return ($fullpath ? $this->uploadDir . "/" : "") . $this->savedName;
	}
	
	function isempty() {
		$FILE = $this->getuploadinfo($this->uploadName , $this->uploadIndex);
		if ($FILE === false) return true;
		return ($FILE['size'] == 0);
	}
	
	function hasupload() {
		$FILE = $this->getuploadinfo($this->uploadName , $this->uploadIndex);
		if ($FILE === false) return false;
		return (isset($FILE['name']));
	}
	
	/**
	 * Default file upload handler
	 * @access private
	 **/
	function processfile($b , $t , $mod , $overWrite = false) {
		$FILE = $this->getuploadinfo($this->uploadName , $this->uploadIndex);
		if ($FILE === false) return false;
		$p = '';
		$p2 = $FILE['tmp_name'];
		if (trim($b) == '') {
			$p = $t;
		} else {
			if (substr($b , strlen($b) - 1 , 1) != $this->seperator) $p = $b . $this->seperator;
			else $p = $b;
			$p .= $t;
		}
		if (file_exists($p)) {
			//exist file , have to check
			if (is_dir($p)) return false;
			if (!$overWrite) return false;
		}
		if  (!@copy($p2 , $p)) return false;
		@chmod($p , $mod);
		$this->savedName = $t;
		return true;
	}
	
	/**
	 * Check and Save upload file
	 * @param bool $overWrite Overwrite existed file
	 * @return bool
	 **/
	function upload($overWrite = false) {
		$this->savedName = '';
		$this->set_error(0);
		if (!$this->hasUpload()) return $this->set_error(HTTPUPLOAD_ERROR_NO_FILE);
		$FILE = $this->getuploadinfo($this->uploadName , $this->uploadIndex);
		switch ($FILE['error']) {
			case 1:
				return $this->set_error(HTTPUPLOAD_ERROR_INI_SIZE);
			break;
			case 2:
				return $this->set_error(HTTPUPLOAD_ERROR_FORM_SIZE);
			break;
			case 3:
				return $this->set_error(HTTPUPLOAD_ERROR_PARTIAL);
			break;
			case 4:
				return $this->set_error(HTTPUPLOAD_ERROR_NO_FILE);
			break;
		}
		$ext = ".";
		if (!(strpos($FILE['name'] , ".")) === false) {
			$ext = explode("." , $FILE['name']);
			$ext = "." . $ext[count($ext) - 1];
		}
		$ext = strtolower($ext);
		//check max file size
		if (intval($this->maxSize) > 0 && $FILE['size'] > $this->maxSize) return $this->set_error(HTTPUPLOAD_ERROR_SIZE);
		//check extension
		if (is_array($this->allowExt) && count($this->allowExt) > 0 && !in_array($ext , $this->allowExt)) return $this->set_error(HTTPUPLOAD_ERROR_EXT);
		//check mime
		if (is_array($this->allowMime) && count($this->allowMime) > 0 && !in_array($FILE['type'] , $this->allowMime)) return $this->set_error(HTTPUPLOAD_ERROR_MIME);
		//check image size
		if (intval($this->maxWidth) > 0 || intval($this->maxHeight) > 0) {
			$imageSize = @getimagesize($FILE['tmp_name']);
			if ($imageSize === false) return false;
			if (intval($this->maxWidth) > 0 && $imageSize[0] > intval($this->maxWidth)) return $this->set_error(HTTPUPLOAD_ERROR_IMG);
			if (intval($this->maxHeight) > 0 && $imageSize[1] > intval($this->maxHeight)) return $this->set_error(HTTPUPLOAD_ERROR_IMG);
		}
		//process file
		if (trim($this->targetName) == '') {
			//Self Generator
			if ($this->prefix === false) {
				$f = $FILE['name'];
				if (!(strpos($f , ".") === false)) {
					$f = explode("." , $f);
					unset($f[count($f) -1]);
					$f = implode("." , $f);
				}
			} else {
				$f = uniqid(trim($this->prefix));
			}
			if ($this->extension === false) {
				if ($ext != '.') $f .= $ext;
			} else {
				if (substr($this->extension , 0 , 1) != ".") $this->extension = "." . $this->extension;
				if (trim($this->extension) != '.') $f .= $this->extension;
			}
		} else {
			//User name
			$f = trim($this->targetName);
		}
		$this->savedName = $f;
		//ok , now process copy
		if ($this->handlerType == '' || $this->handler == '') {
			//process default handler
			if ($this->processfile($this->uploadDir , $f , $this->fileCHMOD , $overWrite)) return $this->set_error(HTTPUPLOAD_ERROR_OK);
			else return $this->set_error(HTTPUPLOAD_ERROR_WRITE);
		} else {
			//process user handler
			$b = $this->uploadDir;
			if (trim($b) == '') {
				$p = $f;
			} else {
				if (substr($b , strlen($b) - 1 , 1) != $this->seperator) $p = $b . $this->seperator;
				else $p = $b;
				$p .= $f;
			}
			$f = $this->handler;
			switch (trim(strtolower($this->handlerType))) {
				case 'copy':
				case 'move':
					// function (src , tgt , CHMOD)
					/*
					if (is_array($f)) {
						return $f[0]->$f[1]($p , $FILE['tmp_name'] , $this->fileCHMOD);
					}
					else return $f($p , $FILE['tmp_name'] , $this->fileCHMOD);
					*/
					if (@call_user_func($f , $p , $FILE['tmp_name'] , $this->fileCHMOD)) return $this->set_error(HTTPUPLOAD_ERROR_OK);
					else return $this->set_error(HTTPUPLOAD_ERROR_WRITE);
				break;
				case 'data':
					// function (targetFile , data , CHMOD)
					/*
					if (is_readable($FILE['tmp_name'])) $data = implode("" , file($FILE['tmp_name']));
					else return false;
					if (is_array($f)) {
						return $f[0]->$f[1]($p , $data , $this->fileCHMOD);
					}
					else return $f($p , $data , $this->fileCHMOD);
					*/
					if (@call_user_func($f , $p , $data , $this->fileCHMOD)) return $this->set_error(HTTPUPLOAD_ERROR_OK);
					else return $this->set_error(HTTPUPLOAD_ERROR_WRITE);
				break;
				default:
					if ($this->processfile($this->uploadDir , $f , $this->fileCHMOD , $overWrite)) return $this->set_error(HTTPUPLOAD_ERROR_OK);
					else return $this->set_error(HTTPUPLOAD_ERROR_WRITE);
			}
		}
	}
	
	//multi-file upload
	function upload_ex($name,$overwrite=false) {
		$FILES = $this->getuploadinfo($name,null);
		if (isset($FILES['name']) && is_array($FILES['name'])) {
			$results = array();
			$old_index = $this->uploadIndex;
			$old_name = $this->uploadName;
			
			$this->uploadName = $name;
			
			foreach ($FILES['name'] as $index => $dummy) {
				//handle each file
				$this->uploadIndex = $index;
				$this->upload($overwrite);
				
				$results[] = array(
					'error_code' => $this->error_code ,
					'name' => $this->getuploadname() ,
					'size' => $this->getuploadsize() ,
					'type' => $this->getuploadtype() , 
					'tmp_name' => $this->getuploadtmp() ,
					'file' => $this->getsavedname(false) ,
					'fullpath' => $this->getsavedname(true) ,
					'index' => $index ,
				);
			}
			$this->uploadIndex = $old_index;
			$this->uploadName = $old_name;
			return $results;
		} else return false;
	}
	
	function &getuploadinfo($name , $index = '') {
		global $HTTP_POST_FILES;
		if ($index == '' && !($index === 0)) {
			if (isset($HTTP_POST_FILES[$name])) {
				return $HTTP_POST_FILES[$name];
			}
		} else {
			if (isset($HTTP_POST_FILES[$name]['name'][$index])) {
				return array(
					'name' => $HTTP_POST_FILES[$name]['name'][$index],
					'tmp_name' => $HTTP_POST_FILES[$name]['tmp_name'][$index],
					'size' => $HTTP_POST_FILES[$name]['size'][$index],
					'type' => $HTTP_POST_FILES[$name]['type'][$index],
					'error' => $HTTP_POST_FILES[$name]['error'][$index],
				);
			}
		}
		return false;
	}
	
	function set_error($code) {
		$this->error_code = $code;
		if ($code != HTTPUPLOAD_ERROR_OK) return false;
		return true;
	}
	
	function get_error($code = null) {
		if ($code === null) $code = $this->error_code;
		return @$this->error_lang[$code];
	}
	
}

?>