<?php

	#####
	## HTML radio_group Class
	##
	## Author: Bill Hunt (bill@krues8dr.com)
	## 
	## Purpose: Displays an HTML textfield. 
	##
	## Important: 
	## Input type subclass names are in lowercase!
	## This is to make types easily selected.
	##
	## Change Log:
	## 07.03.05 - Created file, copied body from HTML object.
	#####
	
	require_once(HTMLBUILDER_MODULE_DIR.'base_input_type.inc.php');
	
	class radio_group extends BaseInputType {
	
		function radio_group($args = array()) {
			$this->BaseInputType($args);
		}
		
		function show($args) {
			$xhtml = $args['xhtml'];
			unset($args['xhtml']);
		
			$this->args = $args;
			
			$reserved_args = array('values', 'value', 'type', 'id', 'name', 'checked', 'no_label_tags', 'linebreak', 'no_linebreaks');
			$other_args = array_diff(array_keys($args), $reserved_args);
			
			if(is_array($args['values']) && count($args['values'])) {
				foreach($args['values'] as $key => $label) {
					$selected = false;
					
					if(is_array($args['value'])) {
						if(in_array($key, $args['value'])) { $selected = true; }
					}
					else{
						if($args['value'] == $key) { $selected = true; }
					}
					
					$tag .= '<input type="radio" value="'.$key.'"';
					
					if($args['name']) {
						$tag .= ' name="'.$args['name'].'"';
					}
					
					// Show label tags and associate them with options.
					// Defaults to 0, use label tags.
					// Requires an id or a name to continue.
					
					if(!$args['no_label_tags'] && ($args['name'] || $args['id'])) {
						
						unset($label_id);
						if($args['id']) {
							$label_id = $args['id'].'_'.$key;
						}
						elseif($args['name']) {
							$label_id = $args['name'].'_'.$key;
						}
						
					}
					
					if($label_id) {
						$tag .= ' id="'.$label_id.'"';
					}
					

					foreach($other_args as $key) {
						$value = $args[$key];
						
						// escape doublequotes in value.
		
						$value = str_replace('"', '\\"', $value);
						
						$tag .= ' '.$key.'="'.$value.'"';
					}
					
					
					if($selected) { $tag .= ' checked'; }
			
					if($xhtml) {
						$tag .= ' /';
					}
					
					$tag .= '>';

					if($label_id) {
						$tag .= '<label class="radiolabel" for="'.$label_id.'">';
						$tag .= $label;
						$tag .= '</label>';
					}
					else {
						$tag .= $label;
					}
					
					if(!$args['no_label_tags']) {
						$tag = '<nobr>'.$tag.'</nobr>';
					}
					
					$tag .= NL;
					$options[] = $tag;
					unset($tag);
					
				}
				
				if(!isset($args['linebreak'])) {
					if(!$xhtml) {
						$linebreak = '<br>';
					}
					else {
						$linebreak = '<br />';
					}
				}
				else {
					$linebreak = $args['linebreak'];
				}
				
				// Show linebreaks, unless otherwise specified.
				if(!$args['no_linebreaks']) {
					$tag = join($linebreak, $options);
				}
				else {
					$tag = join($options);
				}
				
				
			}


						
			return $tag;		
		}
		
	}
	
?>