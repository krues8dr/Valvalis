<?php
/*
	class Request
	
	Created by:
	Bill Hunt (bill@krues8dr.com)
	
	Description: 
	Holds request data; acts as a wrapper for $_GET, $_POST, 
	$_REQUEST, $_SESSION, $_COOKIE and $_FILES
	
	ChangeLog: 
	01.11.06 - bill@krues8dr.com
			 - Created class.
	01.25.06 - bill@krues8dr.com
			 - Added set* methods.
			 - Added session methods.
*/

	class Request {
		var $data;
	
		function Request() {
			$this->data['post'] =& $_POST;
			$this->data['get'] =& $_GET;
			$this->data['request'] =& $_REQUEST;
			$this->data['session'] =& $_SESSION;
			$this->data['cookie'] =& $_COOKIE;
			$this->data['files'] =& $_FILES;
		}
		
		// All fetch* methods call fetch()
		// Note: "fetch" is used here instead of "get" to 
		// avoid confusion with the "GET" request method.
		
		function &fetchPost($var = null) {
			return $this->fetch('post', $var);
		}
		
		function &fetchGet($var = null) {
			return $this->fetch('get', $var);
		}
		
		function &fetchRequest($var = null) {
			return $this->fetch('request', $var);
		}
		
		function &fetchSession($var = null) {
			return $this->fetch('session', $var);
		}
		
		function &fetchCookie($var = null) {
			return $this->fetch('cookie', $var);
		}
		
		function &fetchFiles($var = null) {
			return $this->fetch('files', $var);
		}
		
		function &fetch($method, $var = null) {
			if(strlen($var)) {
				// Hic sunt dracones.
				// Allow the use of dot syntax to access the array.
				// eg. 'elementone.elementtwo.elementthree'
				if(strpos($var, '.') !== false) {
					$parts = split('\.', $var);

					$temp = $this->data[$method];
					
					foreach($parts as $part) {
						$temp = $temp[$part];
					}
					
					$return_val = $temp;
				}
				else {
					$return_val = $this->data[$method][$var];
				}
			}
			else {
				$return_val = $this->data[$method];
			}
			
			return $return_val;
		}
		

		// All set* methods call set()
		// IMPORTANT: setFile() does not exist!
		
		
		/* Usage: (2 options)
		

		1.  setPost(field, value)
		
			$request->setPost('submit', true);
		

		2.  setPost(field_value_hash)
		
			$request->setGet(
				array(
					'function' => 'update',
					'submit'   => true
				)
			);
			
		*/
		
		
		function &setPost($var, $value = null) {
			return $this->set('post', $var, $value);
		}
		
		function &setGet($var, $value = null) {
			return $this->set('get', $var, $value);
		}
		
		function &setRequest($var, $value = null) {
			return $this->set('request', $var, $value);
		}
		
		function &setGetPostRequest($var, $value = null) {
			if($this->set('post', $var, $value) && $this->set('get', $var, $value) && $this->set('request', $var, $value)) {
				$return_value = $value;	
			}
			
			return $return_value;
		}
		
		function &setSession($var, $value = null) {
			return $this->set('session', $var, $value);
		}
		
		function &setCookie($var, $value = null) {
			return $this->set('cookie', $var, $value);
		}
		
		// No setFile().
		
		function &set($method, $var, $value = null) {
			if(is_array($var)) {
				foreach($var as $field=>$hash_value) {
					$this->data[$method][$field] = $hash_value;
				}
				$return_value = true;
			}
			elseif(strlen($var)) {
				$this->data[$method][$var] = $value;
				$return_value = $value;
			}
			return $return_value;
		}		

		// Form utility functions
		
		function isPost() {
			if(count($this->fetchPost()) > 0) {
				return true;
			}
			else {
				return false;
			}
		}
		
		function isSubmitted($method) {
			if(!$method) {
				$method = 'request';
			}
			
			$return_val = false;
			
			if($this->fetch($method, 'submit')) {
				$return_val = true;
			}
			else {
				if($this->fetch($method, 'submit_x')) {
					$return_val = true;
				}
				else {
					if($this->fetch($method, 'submit_y')) {
						$return_val = true;
					}
				}

			}
			
			return $return_val;
			
		}
		
		function hasUploadedFiles() {
			if(count($this->fetchFiles()) > 0) {
				return true;
			}
			else {
				return false;
			}
		}
		
		
		// Session wrapper functions.
		// These don't normally need to be called, but
		// can be called explicitly when necessary.
		// Note: session_register is not present, use
		// setSession() instead.
		
		
		function startSession() {
			return session_start();
		}
		
		function setSessionName($name) {
			return session_name($name);
		}
	
		function getSessionName() {
			return session_name();
		}
		
		function closeSession() {
			return session_write_close();
		}
	
	}


?>