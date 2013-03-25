<?php

/*
	DAO (Data Access Object)
	
	Author: Bill Hunt (bill@krues8dr.com)
	
	Purpose: 
	Prototype interface for a database table.  
	
	Change Log:
	07.27.04 - Created file, imported functions 
	           from old DBInterface class.
	01.08.05 - Completely rewrote class to use querybuilder.
	         - Added query caching.
	01.10.06 - Modified for use with ADODB.
			 _ Changed return results in DB query functions
			   to match expected.
	01.30.06 - Modified to allow 'data' argument in db_update 
			   and db_insert functions.
	
*/
	
	class DAO {

		var $table;
		var $id_column = 'id';
		
		/* Fields hash.  Example:
		var $fields = array(
			'id' => array(
				'label' => 'ID',
				'input_type' => 'textfield',
				'required' => true,
				'entry_list' => true
			),
			'mls_acct' => array(
				'label' => 'MLS #',
				'input_type' => 'textfield'
			)
		);
		*/
		var $fields = array();
		
		var $values = array();
		var $result_object;
		
		// A hash of relational tables and keys.
		// An array is is used as multiple keys might be necessary.
		// Ex: 
		// var $foreign_keys = array(
		//	'foreign_table' => array(
		//		 'keys' => array('local_key' => 'foreign_key'),
		//		 'join_type' => 'join'
		//	 )
		// );
		
		var $foreign_keys = array();
		var $default_join_type = 'LEFT JOIN';
		
		var $relational_tables = array();
		var $relational_objects = array();
		
		var $relational_object_hash = array();
		
		var $db_interface;
		var $conf;
		var $upload_dir;
		var $web_upload_dir;
		
		var $is_initialized = false;
		
		function DAO ($args) {
			register_shutdown_function(array(&$this, '_DAO'));
			
			if($args['table']) {
				$this->table = $args['table'];
			}
			
			if(!$this->table) {
				trigger_error(get_class($this).' (DAO) initialized with no table.', E_USER_ERROR);
			}
			
			if($args['id_column']) { $this->id_column = $args['id_column']; }

			if($args['conf']) {
				$this->conf =& $args['conf'];
			}

			require_once(FRAMEWORK_DIR.'dbinterface.inc.php');
			$this->db_interface = &DBInterface::instance($this->conf);
			
			$this->upload_dir = UPLOAD_DIR . strtolower(get_class($this)).'/';
			$this->web_upload_dir = WEB_UPLOAD_URL . strtolower(get_class($this)).'/';
		}
		
		function _DAO() {
			// Do nothing.
		}
		
		function addRelationalObject($internal_name, $name, $init_args = array(), $path = null) {
			$this->relational_object_hash[$internal_name]['name'] = $name;
			if(!strlen($path)) {
				$path = MODEL_DIR.$internal_name.'.inc.php';
			}
			$this->relational_object_hash[$internal_name]['path'] = $path;
			$this->relational_object_hash[$internal_name]['init_args'] = $init_args;
		}
		
		function hasRelationalObject($object) {
			if($this->relational_object_hash[$object]) {
				$return_val = true;
			}
			else {
				$return_val = false;
			}
			
			return $return_val;
		}
		
		function initializeRelationalObject($internal_name, $args = array()) {
			$name = $this->relational_object_hash[$internal_name]['name'];
			$path = $this->relational_object_hash[$internal_name]['path'];
		
			if(strlen($name) && strlen($path) && file_exists($path)) {
				// If there are no args passed for the object, 
				// check to see if others were set previously.
				if(!count($args) && count($this->relational_object_hash[$internal_name]['init_args'])) {
					$args = $this->relational_object_hash[$internal_name]['init_args'];
				}
				
				require_once($path);
				$this->relational_objects[$internal_name] =& new $name($this->conf);
				return true;
			}
		}
		
		function callRelationalMethod($internal_name, $method) {
			$num_base_args = 2; // (two base args, $internal_name and $method)
			
			if(!is_object($this->relational_objects[$internal_name]) && is_array($this->relational_object_hash[$internal_name])) {
				$this->initializeRelationalObject($internal_name);
			}
						
			if(method_exists($this->relational_objects[$internal_name], $method)) {
				// Get all of the passed args.
				$args = func_get_args();
				
				// Pop off the base args
				for($i = 0; $i < $num_base_args; $i++) {
					array_shift($args);
				}
				
				// Do some magic to call the method.
				$return_val =& call_user_func_array(array(&$this->relational_objects[$internal_name], $method), $args);
				
				return $return_val;
			}
			
		}
		
		function getRelationalValue($internal_name, $value) {
			if(!is_object($this->relational_objects[$internal_name]) && is_array($this->relational_object_hash[$internal_name])) {
				$this->initializeRelationalObject($internal_name);
			}
			
			return $this->relational_objects[$internal_name]->$value;
		}
		
		function initialize($args = array()) {
			$this->is_initialized = true;
			// Set the initial value of the object
			$this->result_object = &$this->db_select($args);
			return $this->numRows();
		}
		
		function numRows() {
			if($this->result_object) {
				return $this->result_object->db_numRows();
			}
		}
		
		function fetch() {
			if(is_object($this->result_object)) {
				if($result = $this->result_object->db_fetchRow()) {
					$this->unsetValue();
					$this->set($result);
					$return_val = true;
				}
				else {
					$return_val = false;
				}
			}
			else {
				trigger_error('DAO->fetch() needs a result object.', E_USER_ERROR);
			}
			
			return $return_val;
		}
		
		function get($fields = false) {
			if(is_array($fields) && count($fields)) {
				foreach($fields as $field) {
					$return_val[$field] = $this->_getField($field);	
				}
			}
			elseif(!is_array($fields) && strlen($fields)) {
				$return_val = $this->_getField($fields);	
			}
			else {
				if(is_array($this->values)) {
					foreach($this->values as $field=>$value) {
						$return_val[$field] = $this->_getField($field);	
					}
				}
				else {
					foreach($this->fields as $field) {
						$return_val[$field] = $this->_getField($field);	
					}
				}
			}
			return $return_val;
		}
		
		// This returns the actual db-retrieved value,
		// not a formatted version as get() does.
		function getRaw($fields = array()) {
			if(is_array($fields) && count($fields)) {
				foreach($fields as $field) {
					$return_val[$field] = $this->values[$field];	
				}
			}
			elseif(!empty($fields)) {
				$return_val = $this->values[$fields];
			}
			else {
				/*
				foreach($this->fields as $field) {
					$return_val[$field] = $this->values[$field];	
				}
				*/
				return $this->values;
			}
			
			return $return_val;
		}
		
		function _getField($field) {
			// If there's a decorator for this field, use it.
			$function_name = 'get'.str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
			if(method_exists($this, $function_name)) {
				$return_val = $this->$function_name($this->values[$field]);
			}
			else {
				$return_val = $this->values[$field];
			}	
			
			return $return_val;
		}

	
		function set($fields) {
			if(is_array($fields)) {
				foreach($fields as $field=>$value) {
					$this->_setField($field, $value);
				}
			}
			else {
				trigger_error('DAO->set() expects an array.', E_USER_ERROR);
			}
		}
		
		function unsetValue($fields = null) {
			if(is_array($fields) && count($fields)) {
				foreach($fields as $field) {
					unset($this->values[$field]);
				}
			}
			else {
				unset($this->values);
			}
		}
		
		function _setField($field, $value) {
			// If there's a decorator for this field, use it.
			$function_name = 'set'.ucwords(str_replace('_', '', $field));
			if(method_exists($this, $function_name)) {
				$this->$function_name($value);
			}
			else {
				$this->values[$field] = $value;
			}	
		}
		
		// This returns the actual db-retrieved value,
		// not a formatted version as get() does.
		function setRaw($field, $value) {
			$this->values[$field] = $value;
		}
		
		
		
		// Function db_query
		// Called by the four db_* methods.
		
		function &db_query($args) {
			if($this->_set_names && $this->_set_names != $this->db_interface->_set_names) {
				$this->db_interface->_set_names = $this->_set_names;
			}
		
			// Note that the where_id argument does not have a built-in 
			// comparison operator.
			if($args['where_id']) {
				if(!is_array($args['where']) && strlen($args['where'])) {
					$args['where'] = array($args['where']);
				}
				$args['where'][] = $this->table.'.'.$this->id_column.' '.trim($args['where_id']);
			}

			$this->result_object = &$this->db_interface->db_query($args);
			
			return $this->result_object;
		}
		
		// Function db_select
		// Returns the number of selected rows.
		// Wrapper for db_query.
		function db_select ($args) {

			// SELECT queries can be cached.
			static $cache = array();
		
			$args['table'] = $this->table;
			$args['function'] = 'SELECT';
	
			if(!$args['fields'] && $this->fields) {
				$relational_types = array('checkbox_group');
				$ignore_types = array('custom');
				
				$ignore_types = array_merge($ignore_types, $relational_types);
				foreach($this->fields as $field=>$data) {
					if(!in_array($this->fields[$field]['input_type'], $ignore_types)) {
						$args['fields'][] = $field;
					}
				}
				
				// Select the table id column by default.
				// Make this the first selected column, so it 
				// will be overridden by any other id columns 
				// requested.
				if($this->id_column) {
					$id_column = $this->id_column;
					if(strpos($id_column, '.') === false) {
						$id_column = $this->table.'.'.$id_column;
					}
					
					array_unshift($args['fields'], $id_column);
				}
			}
			else {
				// Replace instances of "id" with the true id column.
				if(in_array('id', $args['fields']) && $this->id_column != 'id') {
					$key = array_search('id', $args['fields']);
					$args['fields'][$key] = $this->id_column;
				}
			}
	
			if(!$args['relational_tables']) {
				$args['relational_tables'] = $this->relational_tables;
			}
	
			if($args['cache']) {
				unset($args['cache']);
				$cache_name = mhash(MHASH_MD5, serialize($args));
			}
			if(!$cache[$cache_name]) {
				$result_object = &$this->db_query($args);
				if($cache_name) {
					$cache[$cache_name] = &$result_object;
				}
			}
			else {
				$result_object = &$cache[$cache_name];
			}
			return $result_object->db_numRows();
		}
		
		// Function db_update
		// Returns the number of affected rows
		// Wrapper for db_query.
		function db_update($args = array()) {
			$args['table'] = $this->table;
			$args['function'] = 'update';
			if(!$args['data']) {
				$args['data'] = $this->values;
			}
			
			$this->db_query($args);
			
			return $this->db_interface->db_affectedRows();
		}

		// Function db_insert
		// Returns the id of the inserted row
		// Wrapper for db_query.
		function db_insert($args = array()) {
			$args['table'] = $this->table;
			$args['function'] = 'insert';
			if(!$args['data']) {
				$args['data'] = $this->values;
			}
			
			$this->db_query($args);
			
			return $this->db_interface->db_insertId();
		}
		
		// Function db_delete
		// Returns the number of affected rows
		// Wrapper for db_query.
		function db_delete($args) {
			$args['table'] = $this->table;
			$args['function'] = 'delete';
			$this->db_query($args);
			
			return $this->db_interface->db_affectedRows();
		}
		
		// debugQuery is maintained as a separate function,
		// so that it can be overriden, if needed.  
	
		function &debug_query($args) {
		
				// Check for "array( &$this, 'function' )" syntax.
				if ( is_array($args['debug']) && is_object($args['debug'][0]) && method_exists($args['debug'][0], $args['debug'][1]) ) {
					$return_value = &$args['debug'][0]->$args['debug'][1]($args);
				}
				
				// Check for a matching function, like "print" or "die".
				elseif( is_string($args['debug']) && function_exists($args['debug']) ) {
					$return_value = &$args['debug']($args);
				}
				
				// Otherwise, just do something simple.
				else {
					if(is_array($args)) {
						var_dump($args);
					}
					else {
						print $args; 
					}
					exit;
				}
				
				return $return_value;
		}
		
		function db_quote($data) {
			return $this->db_interface->db_quote($data);
		}
		
		function getSelectListContent($field) {
			return array();
		}

		function getRadioGroupContent($field) {
			return array();
		}

		function getCustomContent($html_builder, $key, &$request) {
			return null;
		}
		
		function getExtraArgs(&$html_builder, $key, $value = null) {
			$args = array();
			
			return $args;
		}
		
		function getCheckboxGroupContent($field, $args = array(), $label_field = null) {
			$return_values = array();

			if($this->hasRelationalObject($field)) {			
				if(!strlen($label_field)) {
					$fields = array_keys($this->getRelationalValue($field, 'fields'));
					$label_field = $fields[0];
				}
				else {
					$args['fields'][] = $label_field;
					$args['fields'][] = $this->getRelationalValue($field, 'id_column');
				}

				$this->callRelationalMethod($field, 'db_select', $args);
				
				while($this->callRelationalMethod($field, 'fetch')) {
					$key = $this->callRelationalMethod($field, 'get', $this->getRelationalValue($field, 'id_column'));
					
					$val = $this->callRelationalMethod($field, 'get');					
					$value = $val[$label_field];
					$return_values[$key] = $value;
				}
			}
			
			return $return_values;
		}
		
		function getCheckboxGroupDefault($field) {
			$return_values = array();
			
			if($this->hasRelationalObject($this->table.'_'.$field.'_index')) {
				$obj = $this->table.'_'.$field.'_index';
			}
			elseif($this->hasRelationalObject($field.'_'.$this->table.'_index')) {
				$obj = $field.'_'.$this->table.'_index';
			}

			if($this->hasRelationalObject($obj) && $this->hasRelationalObject($field) && $this->get($this->id_column)) {
				
				$this->callRelationalMethod($obj, 'db_select',
					array(
						'where' => array(
							$this->table.'_'.$this->id_column.' = '.$this->get($this->id_column)
						)
					)
				);
				
				while($this->callRelationalMethod($obj, 'fetch')) {
					$return_values[] = $this->callRelationalMethod($obj, 'get', $field.'_'.$this->getRelationalValue($field, 'id_column'));
				}
			}
			return $return_values;
		}
		
		// Index Object functions
		// The index object's primary and secondary (index) fields 
		// must be the first two in the fields list for this to work.
		// Example:
		// 
		// $fields = array(
		// 	'secondary_id',
		//  'primary_id,
		//  'other field'
		// );
		
		function insertIndexes($args) {
			$primary_field = $args['primary_field'];
			$id = $args['id'];
			$index_list = $args['index_list'];
			if(strlen($args['secondary_field'])) {
				$secondary_field = $args['secondary_field'];
			}
			else {
				$temp_index = (!array_search($primary_field, array_keys($this->fields)));
				$temp_fields = array_keys($this->fields);
				$secondary_field = $temp_fields[$temp_index];
			}
			
			if(is_array($index_list) && count($index_list)) {
			  foreach($index_list as $current_index) {
			  	if(strlen($current_index)) {
					$this->db_insert(
							 array(
								   'data'  => array(
										$primary_field => $id,
										$secondary_field => $current_index
									)
							   )
						 );
				}
			  }
			}
		}
		
		function updateIndexes($args) {
			$primary_field = $args['primary_field'];
			$id = $args['id'];
			$index_list = $args['index_list'];
			if(strlen($args['secondary_field'])) {
				$secondary_field = $args['secondary_field'];
			}
			else {
				$temp_index = (!array_search($primary_field, array_keys($this->fields)));
				$temp_fields = array_keys($this->fields);
				$secondary_field = $temp_fields[$temp_index];
			}
			
			$result_count = $this->db_select(
				array(
					'where' => array( $primary_field.'='.$id )
				)
			);
			
			if($result_count > 0) {
				while($this->fetch()) {
					$previous_indexes[] = $this->get($secondary_field);
				}
			}

			if(is_array($index_list) && count($index_list)) {
			  foreach($index_list as $current_index) {
				if(!is_array($previous_indexes) || !in_array($current_index, $previous_indexes)) {
					if(strlen($current_index)) {
					  $this->db_insert(
					   array(
						 'data'  => array(
								  $primary_field => $id,
								  $secondary_field => $current_index
								  )
						 )
					   );
					}
				}
			  }
			}
			
			if(is_array($previous_indexes) && count($previous_indexes)) {
			  foreach($previous_indexes as $current_index) {
				if(!is_array($index_list) || !in_array($current_index, $index_list)) {
				  $this->db_delete(
						   array(
							 'where' => $primary_field.' = '.$id.' AND '.$secondary_field.' = '.$current_index.''
							 )
						   );
				}	
			  }
			}

		}
		
		function deleteIndexes($args) {

			$primary_field = $args['primary_field'];
			$id = $args['id'];
			
			if(strlen($args['secondary_field'])) {
				$secondary_field = $args['secondary_field'];
			}
			else {
				$secondary_field = $this->fields[(!array_search($primary_field, array_keys($this->fields)))];
			}
			
			$this->db_delete(
					 array(
					   'where' => $primary_field.' = '.$id.''
					   )
					 );
		}
		
		function isValidID($id, $where = array()) {
			$return_value = false;
			if(strlen($id) && is_numeric($id) && preg_match('/^([0-9])+$/', $id)) {
				$where[] = $this->id_column .' = '.$id;
				$return_value = $this->db_select(
					array(
						'fields' => array($this->id_column),
						'where' => $where
					)
				);
				
			}
			
			return $return_value;
		}
		
		function getFilePath($field) {
			$return_value = UPLOAD_DIR.$this->table.'/'.$field.'/';
			return $return_value;	
		}
		
		function getWebUploadPath($field) {
			$return_value = WEB_UPLOAD_URL.$this->table.'/'.$field.'/';
			return $return_value;	
		}
	
		function removePreviousFile($id, $fields) {
			if(is_array($fields)) {
				$args['fields'] = $fields;
			}
			elseif(strlen($fields)) {
				$args['fields'] = array($fields);
			}
			
			if(is_array($id) && count($id)) {
				$args['where'] = $this->id_column .' IN('.join(',', $id).')';
			}
			elseif(strlen($id)) {
				$args['where_id'] = '='.$id;
			}
			
			
			
			$this->db_select($args);
			
			while($this->fetch()) {
				foreach($this->get() as $name=>$value) {
					if(strlen($value)) {
						$file_path = $this->getFilePath($name).$value;
	
						if(file_exists($file_path)) {
							if(unlink($file_path)) {
								$return_value = true;
							}
							else {
								trigger_error('Cannot remove file: '.$file_path, E_USER_WARNING);
								$return_value = false;
							}
						}
						else {
							trigger_error('File does not exist to remove: '.$file_path, E_USER_NOTICE);
							$return_value = true;
						}
					}
				}
			}
			
			return $return_value;
		}
	}
	
?>