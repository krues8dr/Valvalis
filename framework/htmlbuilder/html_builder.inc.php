<?php

	#####
	## HTMLBuilder Wrapper Class
	##
	## Author: Bill Hunt (bill@krues8dr.com)
	##
	## Purpose: Provides a generic wrapper for HTML elements.
	##
	## Change Log:
	## 07.26.04 - Created file, wrote initial form functions.
	## 10.06.04 - Added filefield function.
	## 01.23.05 - Modified name/id argument creation.
	## 07.03.05 - Created show and loadModule functions for importing
	##            modular types.  Modified arguments & constructor.
	## 07.10.05 - Put all input types into separate objects.
	##
	## Important:
	##   At this time,
	##   the pattern type is still the singleton for object fields;
	##   this should be switched to an object-aggregation schema like
	##   PEAR's HTML QuickForm at some point.
	#####

	if (!defined('NL')) {
		define('NL', "\n", true);
	}

	class HTMLBuilder {

		var $xhtml;
		var $conf;
		var $module_args = array();
		var $modules = array();

		function HTMLBuilder($args = array()) {

			// If using XHTML tags, show the trailing slash
			// on unpaired tags.
			if($args['xhtml']) { $this->xhtml = $args['xhtml']; }
			if($args['conf']) {
				$this->conf =& $args['conf'];
			}
			if($args['module_args']) {
				$this->module_args = $args['module_args'];
			}

		}

		// These methods all just call _moduleMethod,
		// which calls the selected module's method.

		function showStatic($module, $args = array()) {
			return $this->_moduleMethod($module, 'showStatic', $args);
		}

		function show($module, $args = null) {
			return $this->_moduleMethod($module, 'show', $args);
		}

		function getExtraContent($module, $args = array()) {
			return $this->_moduleMethod($module, 'getExtraContent', $args);
		}

		function cleanData($module, $args = array()) {
			return $this->_moduleMethod($module, 'cleanData', $args);
		}

		function preformatData($module, $args = array()) {
			$data = $this->_moduleMethod($module, 'preformatData', $args);

			return $data;
		}

		function _moduleMethod($module, $method, $args = null) {
			if(!is_array($args) && strlen($args)) {
				$content = $args;
				$args = array();
				$args['content'] = $content;
			}
			$args['xhtml'] = $this->xhtml;

			if(!is_object($this->modules[$module])) {
				$this->loadModule($module);
			}

			if(method_exists($this->modules[$module], $method)) {
				$return_val = $this->modules[$module]->$method($args);
			}
			elseif(method_exists($this->modules['default'], $method)) {
				$args['tag'] = $module;
				$return_val = $this->modules['default']->$method($args);
			}

			return $return_val;
		}

		// For right now, this just instantiates an input object for
		// each input /type/.  At some point, it would be nice to have
		// an input object for each input /field/.

		function loadModule($module) {
				if(!is_object($this->modules[$module])) {
					$module_file = HTMLBUILDER_MODULE_DIR.$module.'.inc.php';
					if(file_exists($module_file)) {
						require_once($module_file);
						$this->modules[$module] =& new $module($this->module_args);
					}
					else {
						trigger_error('Module "'.$module.'" does not exist!', E_USER_WARNING);
						$module = 'default';

						$module_file = HTMLBUILDER_MODULE_DIR.'default.inc.php';
						if(file_exists($module_file)) {
							require_once($module_file);
							$this->modules['default'] =& new HTMLDefault($this->module_args);
						}

					}
				}
		}


	}

?>