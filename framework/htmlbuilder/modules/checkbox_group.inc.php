<?php

	#####
	## HTML checkbox_group Class
	##
	## Author: Bill Hunt (bill@krues8dr.com)
	## 
	## Purpose: Displays an HTML checkbox group. 
	##
	## Important: 
	##   Input type subclass names are in lowercase!
	##   This is to make types easily selected.
	##
	##   This is a compound type.
	##
	## Change Log:
	## 08.31.05 - Created file.
	#####

	require_once(HTMLBUILDER_MODULE_DIR.'base_input_type.inc.php');
	require_once(HTMLBUILDER_MODULE_DIR.'checkbox.inc.php');
	
	class checkbox_group extends BaseInputType {
	
		function checkbox_group($args = array()) {
			$this->BaseInputType($args);
		}
		
		function show($args) {
			$this->args = $args;
			
			if(!$args['cols']) {
				$args['cols'] = 1;
			}
			$counter = 1;
			
			unset($tds);
			unset($trs);
			if(is_array($args['values'])) {
				foreach($args['values'] as $name=>$label) {
					if(strlen($name)) {
						unset($checked);
						if(is_array($args['value'])) {
							$checked = in_array($name, $args['value']);
						}
	
						$tds .= '<td>' . checkbox::show(
							array(
								'name' => $args['name'].'[]',
								'value' => $name,
								'checked' => $checked,
								'label' => $label
							)
						) . '</td>';
						if($counter % $args['cols'] == 0) {
							$trs .= '<tr>'.$tds.'</tr>'.NL;
							unset($tds);
						}
						
						$counter++;
					}
				}
				
				$remainder = $counter % $args['cols'];
				if($remainder != 0) {
					for($i = 1; $i <= $remainder; $i++) {
						$tds .= '<td>&nbsp;</td>';
					}
				
					$trs .= '<tr>'.$tds.'</tr>'.NL;
					unset($tds);
				}

			}
			
			if(strlen($trs)) {
				$tag = '<table>'.$trs.'</table>';
			}
			
			return $tag;		
		}
		
	}
	
?>