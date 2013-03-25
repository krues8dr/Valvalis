<?php

	#####
	## HTML Default Class
	##
	## Author: Bill Hunt (bill@krues8dr.com)
	## 
	## Purpose: HTML Builder default class.  If the requested
	## module cannot be found, it tries this one.
	##
	## Important: 
	## Input type subclass names are in lowercase!
	##
	## Change Log:
	## 01.11.06 - Created file, copied body from Base Input Type object.
	#####

	class HTMLDefault {
	
		var $xhtml = false;
		var $args = array();
		
		// Luckily, there are a limited number of empty tags in (X)HTML.
		var $html_empty_tags = array(
			'area',
			'base',
			'basefont',
			'br',
			'col',
			'frame',
			'hr',
			'img',
			'input',
			'isindex',
			'link',
			'meta',
			'param'
		);
		
		var $xhtml_empty_tags = array(
			'area',
			'base',
			'br',
			'col',
			'hr',
			'img',
			'input',
			'link',
			'meta',
			'param'
		);
		
		
	
		function HTMLDefault($args = array()) {

		}
	
		function show($args) {
			$xhtml = $args['xhtml'];
			unset($args['xhtml']);
			
			$this->args = $args;
			
			if($args['tag']) {
				$tag_name = $args['tag'];
				if($xhtml) {
					$tag_name = strtolower($tag_name);
				}
				unset($args['tag']);
				
			}
			
			if($args['object']) { 
				unset($args['object']);
			}
			if($args['content']) {
				$content = $args['content'];
				unset($args['content']);
			}
			
			
			$tag = '<'.$tag_name;
			
			foreach($args as $key=>$value) {
				// escape doublequotes in value.

				$value = str_replace('"', '\\"', $value);
				
				$tag .= ' '.$key.'="'.$value.'"';
			}
			
			if($xhtml && in_array($tag_name, $this->xhtml_empty_tags)) {
				$tag .= ' /';
			}
			
			$tag .= '>';
			
			if(
				($xhtml && !in_array($tag_name, $this->xhtml_empty_tags)) ||
				(!$xhtml && !in_array(strtolower($tag_name), $this->html_empty_tags))
			) {
				$tag .= $content;
				$tag .= '</'.$tag_name.'>';
			}
			
			return $tag;
		}
	
	}
	
?>