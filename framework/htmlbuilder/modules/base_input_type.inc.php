<?php

	#####
	## HTML Base Input Type Class
	##
	## Author: Bill Hunt (bill@krues8dr.com)
	##
	## Purpose: prototype class for input types.
	##
	## Important:
	## Input type subclass names are in lowercase!
	##
	## Change Log:
	## 07.03.05 - Created file, copied body from HTML object.
	#####

	class BaseInputType {

		var $args = array();

		function BaseInputType($args = array()) {
			$this->xhtml = $args['xhtml'];
		}

		function show($args) {
			$xhtml = $args['xhtml'];
			unset($args['xhtml']);

			$this->args = $args;

			if($args['object']) {
				unset($args['object']);
			}

			$tag = '<input';

			foreach($args as $key=>$value) {
				// escape doublequotes in value.

				$value = str_replace('"', '\\"', $value);

				$tag .= ' '.$key.'="'.$value.'"';
			}

			if($xhtml) {
				$tag .= ' /';
			}

			$tag .= '>'.NL;

			return $tag;
		}

		function showStatic($args) {
			return $args['value'];
		}

		function getExtraContent($args) {
			return false;
		}

		function preformatData($args) {
			return $args['data'];
		}

		function cleanData($args) {
			return $args['value'];
		}

	}

?>