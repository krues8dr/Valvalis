<?php

	#####
	## HTML wysiwyg_textarea Class
	##
	## Author: Bill Hunt (bill@krues8dr.com)
	## 
	## Purpose: Displays an HTML textarea with the SPAW Wysiwyg editor. 
	##
	## Important: 
	## Input type subclass names are in lowercase!
	## This is to make types easily selected.
	##
	## Change Log:
	## 07.03.05 - Created file, copied body from HTML object.
	#####

	require_once(HTMLBUILDER_MODULE_DIR.'textarea.inc.php');
	
	class wysiwyg_textarea extends Textarea {
	
		function wysiwyg_textarea($args = array()) {
			$this->BaseInputType($args);
		}
		
		function show($args) {
			$this->args = $args;
			
			$tag .= parent::show($args);
				
			$tag .= '<script language="javascript" type="text/javascript" src="'.TINYMCE_WEB_DIR.'tiny_mce.js"></script>
<script language="javascript" type="text/javascript">
	// Notice: The simple theme does not use all options some of them are limited to the advanced theme
	tinyMCE.init({
		mode : "exact",
		theme : "advanced",
		plugins : "table,advhr,advimage,advlink,iespell,insertdatetime,preview,zoom,searchreplace,contextmenu",
		theme_advanced_buttons1 : "bold,italic,underline,separator,strikethrough,justifyleft,justifycenter,justifyright,justifyfull,bullist,numlist,undo,redo,link,unlink",
		theme_advanced_buttons2_add_before: "cut,copy,paste,separator,search,replace",
		theme_advanced_buttons3 : "tablecontrols",
		theme_advanced_toolbar_location : "top",
		theme_advanced_path_location : "bottom",
		plugin_insertdate_dateFormat : "%Y-%m-%d",
		plugin_insertdate_timeFormat : "%H:%M:%S",
		elements : "'.$args['name'].'"
	});
</script>';

			return $tag;		
		}
		
		function cleanData($args) {
			$object =& $args['object'];
			$value = $args['value'];
			
			$return_val = $object->db_quote($value);
			
			return $return_val;
		}
		
	}
	
?>