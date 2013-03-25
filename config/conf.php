<?php

define('VALVALIS_INIT', true);

/**** Do Initial Setup ****/

//Prevent Magic Quotes from affecting scripts, regardless of server settings

//Make sure when reading file data,
//PHP doesn't "magically" mangle backslashes!
set_magic_quotes_runtime(FALSE);

if (get_magic_quotes_gpc()) {
   /*
   All these global variables are slash-encoded by default,
   because    magic_quotes_gpc is set by default!
   (And magic_quotes_gpc affects more than just $_GET, $_POST, and $_COOKIE)
   */
   $_SERVER = stripslashes_array($_SERVER);
   $_GET = stripslashes_array($_GET);
   $_POST = stripslashes_array($_POST);
   $_COOKIE = stripslashes_array($_COOKIE);
   $_FILES = stripslashes_array($_FILES);
   $_ENV = stripslashes_array($_ENV);
   $_REQUEST = stripslashes_array($_REQUEST);
   $HTTP_SERVER_VARS = stripslashes_array($HTTP_SERVER_VARS);
   $HTTP_GET_VARS = stripslashes_array($HTTP_GET_VARS);
   $HTTP_POST_VARS = stripslashes_array($HTTP_POST_VARS);
   $HTTP_COOKIE_VARS = stripslashes_array($HTTP_COOKIE_VARS);
   $HTTP_POST_FILES = stripslashes_array($HTTP_POST_FILES);
   $HTTP_ENV_VARS = stripslashes_array($HTTP_ENV_VARS);
   if (isset($_SESSION)) {    #These are unconfirmed (?)
       $_SESSION = stripslashes_array($_SESSION, '');
       $HTTP_SESSION_VARS = stripslashes_array($HTTP_SESSION_VARS, '');
   }
   /*
   The $GLOBALS array is also slash-encoded, but when all the above are
   changed, $GLOBALS is updated to reflect those changes.  (Therefore
   $GLOBALS should never be modified directly).  $GLOBALS also contains
   infinite recursion, so it's dangerous...
   */
}

function stripslashes_array($data) {
   if (is_array($data)){
       foreach ($data as $key => $value){
           $data[$key] = stripslashes_array($value);
       }
       return $data;
   }else{
       return stripslashes($data);
   }
}

session_start();

error_reporting(E_ERROR | E_PARSE | E_USER_ERROR);

//ini_set("display_errors","1");
//error_reporting(E_ALL);


/**** Set Path Constants ****/

// get base dir
$base_dir = $_SERVER['DOCUMENT_ROOT'].'/';


// General Directory Constants:
$initial_constants = get_defined_constants();


// Set file baselines
define('DEFAULT_FILE_PERMISSION', 0666);

define('DEFAULT_DIR_PERMISSION', 0777);


// Set base dir
define('BASE_DIR', $base_dir);

// Set web root
define('WEB_ROOT', BASE_DIR);

// Set the base url
define('PUBLIC_URL', 'http://example.com');


// Specific Directory Constants:

// Set template dir
define('TEMPLATE_DIR', WEB_ROOT.'templates/');

// Set the upload dir
define('UPLOAD_DIR', BASE_DIR.'uploads/');

// Set the public upload dir
define('WEB_UPLOAD_URL', '/uploads/');

// Set the library directory
define('LIB_DIR', WEB_ROOT.'valvalis/');

// Set the configuration directory
define('CONF_DIR', LIB_DIR.'config/');

// Set the framework directory
define('FRAMEWORK_DIR', LIB_DIR.'framework/');

// Set the model object directory
define('MODEL_DIR', LIB_DIR.'models/');

// Set the controller object directory
define('CONTROLLER_DIR', LIB_DIR.'controllers/');

// Set the function library directory
define('FUNCTIONLIB_DIR', LIB_DIR.'function/');

// Set the include dir
define('INCLUDE_DIR',  LIB_DIR.'include/');


// Proprietary Library Directories:

define('HTMLBUILDER_DIR', FRAMEWORK_DIR.'htmlbuilder/');

define('HTMLBUILDER_MODULE_DIR', HTMLBUILDER_DIR.'modules/');

define('THIRD_PARTY_DIR', LIB_DIR.'third_party/');

define('TINYMCE_DIR', THIRD_PARTY_DIR.'tiny_mce/');

define('TINYMCE_WEB_DIR', '/lib/third_party/tiny_mce/');

define('BIN_DIR', THIRD_PARTY_DIR.'bin/');


// Directories that can be created by the system.
$create_dirs = array(
	'UPLOAD_DIR'
);



// Check that directories exist
$user_defined_constants = array_diff(get_defined_constants(), $initial_constants);
$suffix = '_DIR';
foreach ($user_defined_constants as $key=>$value) {
	if (substr($key,(0 - strlen($suffix)),strlen($suffix)) == $suffix) {
		if(!is_dir(constant($key))) {
			trigger_error('Directory does not exist: '.constant($key).' ('.$key.')', E_USER_WARNING);
			if(in_array($key, $create_dirs)) {
				if(mkdir(constant($key), 0700)) {
					trigger_error('Created directory '.constant($key).' ('.$key.')', E_USER_WARNING);
				}
				else {
					trigger_error('Tried to create directory '.constant($key).' ('.$key.'), but failed.', E_USER_WARNING);
				}
			}
		}
		elseif(!is_readable(constant($key))) {
			trigger_error('Directory is not readable: '.constant($key).' ('.$key.')', E_USER_WARNING);
		}
	}
}
unset($user_defined_constants);
unset($suffix);
unset($initial_constants);


/**** Set Up Function Library ****/

// Require all of the functions in the function lib that aren't already defined.
$h = opendir(FUNCTIONLIB_DIR);
while (false !== ($entry = readdir($h))) {
	$entry_file = FUNCTIONLIB_DIR.$entry;
	if(is_file($entry_file) && !is_dir($entry_file) && is_readable($entry_file)) {
		$filename = explode('.', $entry);
		if(strlen($filename[0]) && !function_exists($filename[0])) {
			require_once($entry_file);
		}
	}
}
closedir($h);
unset($h);


?>