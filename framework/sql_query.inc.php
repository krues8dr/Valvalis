<?php

	#####
	## SQL Query Class
	##
	## Author: Bill Hunt (bill@krues8dr.com)
	##
	## Purpose: 
	## Builds a query from an array.  Used to 
	## isolate the SQL from the PHP code.
	## 
	## Change Log:
	## 07.27.04 - Created file, imported functions 
	##            from DBInterface class.
	## 11.07.04 - Added singleton functionality. (alpha)
	## 01.08.05 - Completely rewrote class.
	##          - Added query caching.
	##          - Replaced singleton with UniqueRegistry.
	## 01.09.05 - Broke apart query builder into separate 
	##            functions.
	## 01.27.05 - Returned singleton functionality.
	#####

	class SQLQuery {
		
		var $default_join_type = 'LEFT JOIN';
		var $null_value = 'NULL';
		
		function SQLQuery() {
		
			// register_shutdown_function(array(&$this, '_SQLQuery'));
		}
		
		function _SQLQuery() {
			// Do nothing.
		}
		
		function &instance($args, $name = 'SQLQuery') {
			static $instance;
			
			if(!is_object($instance) && class_exists($name)) {
				$instance = new $name($args);
			}
			
			return $instance;
		}
		
		function build_query($args) {
	  		
	  		switch (strtoupper($args['function'])) {

	  			case 'SELECT' : 
					$query = $this->build_select($args);
					break;
					
				case 'INSERT' : 
					$query = $this->build_insert($args);
					break;
					
				case 'UPDATE' : 
					$query = $this->build_update($args);
					break;
					
				case 'DELETE' : 
					$query = $this->build_delete($args);
					break;
				
				default :
					$query = false;
					break;
									
			}
		
			return $query;
		}
		
		function build_select($args = '') {

			// List of included tables
			$foreign_table_list[] = $args['table'];	

			$fields = $this->build_fields($args['table'], $args['fields']);

			if($args['distinct']) {
				$distinct = 'DISTINCT ';	
			}
			
			$query = 'SELECT '.$distinct.$fields.' FROM '.$args['table'];
			

			$where = $this->build_where($args['table'], $args['where']);
			$group = $this->build_group($args['table'], $args['group']);
			$order = $this->build_order($args['table'], $args['order']);
			$limit = $this->build_limit($args['limit']);
			
			$tail .= $where . $group . $order . $limit;
			
			$joins = $this->parse_joins($query . $tail, $args);
	
			$query .= $joins . $tail;

			return $query;
		}
		
		function build_insert($args = '') {
		
			$data = $this->build_data($args['data']);
			
			$query = 'INSERT INTO '.$args['table'].' SET '.$data;
			
			return $query;
				
		}
		
		function build_update($args = '') {
			$data = $this->build_data($args['data']);
			$where = $this->build_where($args['table'], $args['where']);
			$query = 'UPDATE '.$args['table'].' SET '.$data.$where;
			
			return $query;
				
		}
		
		function build_delete($args = '') {
		
			$where = $this->build_where($args['table'], $args['where']);
			
			$query = 'DELETE FROM '.$args['table'].$where;
			
			return $query;
				
		}
		
		function build_fields($table, $fields = '') {
			if($fields) {
				if(is_array($fields)) {  			
					$fields = join(', ', $fields);
				}
			}
			else {
				$fields = '*';
			}  	
			return $fields;
		}
				
		function build_data($data = array()) {
			if(is_array($data)) {
				foreach($data as $field=>$value) {
					if(!strlen($value)) {
						$value = $this->null_value;
					}
					$fields[] = $field.' = '.$value;		
				}
				
				$fields = join(', ', $fields);
			}
			
			return $fields;
		}
				
		function build_where($table, $where = '') {
			if($where) {
			  if(is_array($where)) {
			  	foreach($where as $key=>$value) {
			  		if(!strlen($value)) {
			  			unset($where[$key]);
			  		}
			  	}
			  	$where = array_values($where);
				$where = join(' AND ', $where);
			  }
	
			  $where = ' WHERE ' . $where;
			}
			
			return $where;
		}

		function build_group($table, $group = '') {
			if($group) {
		
			  if(is_array($group)) {
				$group = join(', ', $group);
			  }
	
			  $group = ' GROUP BY ' . $group;
			}
			return $group;
		}
				
		function build_order($table, $order = '') {
			if($order) {
		
			  if(is_array($order)) {
				$order = join(', ', $order);
			  }
	
			  $order = ' ORDER BY ' . $order;
			}
			
			return $order;
		}
		
		function build_limit($limit = '') {
			if(is_array($limit) && count($limit)) {
				if($limit['begin'] && $limit['count']) {
					$limit = $limit['begin'].', '.$limit['count'];
				}
				elseif($limit['count']) {
					$limit = $limit['count'];
				}
				elseif($limit['begin'] && $limit['end']) {
					$limit = $limit['begin'].', '.($limit['begin'] - $limit['end']);
				}
			}
			
			if(strlen($limit)) {
				$limit = ' LIMIT ' . $limit;
			}
			
			return $limit;
		}
		
		function parse_joins($query = '', $args = array()) {
			$relational_tables = $args['relational_tables'];
			$table = $args['table'];
			
			if(strpos($query, '.')) {
				// Check for foreign tables
				preg_match_all('/([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)/', $query, $matches, PREG_PATTERN_ORDER);
		
				// For each field found...
				foreach($matches[1] as $key=>$foreign_table) {
				
					// See if the table is in the foreign_table_list...
					
					if(!is_array($foreign_table_list) || !in_array($foreign_table, $foreign_table_list)) {
						$foreign_table_list[] = $foreign_table;
						
						if(is_array($relational_tables[$foreign_table])) {
							if($relational_tables[$foreign_table]['join_type']) { $join_type = $relational_tables[$foreign_table]['join_type']; }
							else { $join_type = $this->default_join_type; }
							
							$joins .= ' '.$join_type.' '.$foreign_table.' ON ';
							if(array($relational_tables[$foreign_table]['conditions'])) {
								unset($join_conditions);
								foreach($relational_tables[$foreign_table]['conditions'] as $local_key=>$foreign_key) {
									if(strlen($foreign_key)) {
										if(strpos($local_key, '.') === false) {
											$local_key = $table.'.'.$local_key;
										}
										if(strpos($foreign_key, '.') === false) {
											$foreign_key = $foreign_table.'.'.$foreign_key;
										}
	
										$join_conditions[] = $local_key.' = '.$foreign_key;
									}
									else {
										// Handle special conditions.  e.g. "submitted = 1"
										$join_conditions[] = $local_key;
									}
								}
								
								$joins .= join(' AND ', $join_conditions);
							}
						}
					}
				}
			}
			return $joins;
		}
		
		function tableize_fields($table, $fields) {
			if(is_array($fields)) {
				foreach($fields as $key => $field) {
					$fields[$key] = $this->tableize_field($field);
				}
			}
			else {
				$fields = $this->tableize_field($fields);
			}
			
			return $fields;
		}	
		
		function tableize_field($table, $field) {
			if(strpos($query, '.') === false) {
				$field = $table.'.'.$field;
			}
			return $field;
		}
		
	}

?>