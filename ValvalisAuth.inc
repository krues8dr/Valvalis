<?php

/*  Authentication.inc

    
    
    Created: 11.09.06

    Purpose:

    Important:

    Change Log:

*/   
session_start();
class ValvalisAuth {  
	
	var $account_object;
	var $query_builder;
	
	var $username_field = 'email';
	var $password_field = 'password';
	
	var $password_quoting = true;
	var $username_quoting = true;
	
	var $additional_user_where = array(); // for instance, 'status = 1';
	
	var $data_store;
	var $data_store_location = 'account';
	
	// The first field in this list must be a non-null field, like the id.
	var $data_store_fields = array(
		'id',
		'email',
		'password',
		'first_name',
		'middle_name',
		'last_name',
		'agency_id',
		'state_dot',
		'address1',
		'address2',
		'city',
		'state_id',
		'zip',
		'title',
		'division',
		'phone1',
		'phone2',
		'fax',
		'website',
		'wg',
		'other_interests',
		'group_id',
		'region_id'
	);

	// List of object variables which need to be referenced (=&), not set (=).
	var $_ref_args = array(
		'data_store',
		'account_object'
	);
	
	function Authentication($args = array(), $ref_args = array()) {
		if(!is_array($ref_args)) {
			$ref_args = $this->_ref_args;
		}
		
		foreach($args as $arg=>$value) {
			if(!in_array($arg, $ref_args)) {
				$this->$arg = $value;
			}
			else {
				$this->$arg = &$value;
			}
		}
		
		if(empty($this->data_store)) {
			$this->data_store =& $_SESSION;
		}
	}
	
	function clearAuth() {
		unset($this->data_store[$this->data_store_location]);
	}
	
	function checkAuth($username, $password, $login = false, $where = array()) {
		foreach($this->additional_user_where as $condition) {
			$where[] = $condition;
		}

		$password = $this->cryptPassword($password);
		
		if($this->password_quoting) {
			$password = $this->account_object->db->qstr($password);
		}
		if($this->username_quoting) {
			$username = $this->account_object->db->qstr($username);
		}
		
		$where[] = $this->password_field . '=' . $password;
		$where[] = $this->username_field.  '=' . $username;

		$query_args = array(
			'table' => $this->account_object->table,
			'fields'=> $this->data_store_fields,
			'where' => $where
		);
			
		$query = $this->query_builder->build_select($query_args);
		
		$account_row = $this->account_object->getRow($query);
		
		if($account_row['group_id']){
			//get group name
			$where = array('id=' . $account_row['group_id']);
			$query_args = array(
				'table' => 'user_group',
				'fields'=> array('name'),
				'where' => $where
			);
			
			$query = $this->query_builder->build_select($query_args);
			$group_row = $this->account_object->getRow($query);
			
			$account_row['group_name'] = $group_row['name'];
		}
		if($account_row['agency_id']){
			//get agency name
			$where = array('id=' . $account_row['agency_id']);
			$query_args = array(
				'table' => 'agency',
				'fields'=> array('agency'),
				'where' => $where
			);
			
			$query = $this->query_builder->build_select($query_args);
			$agency_row = $this->account_object->getRow($query);
			
			$account_row['agency_name'] = $agency_row['agency'];
		}
		if($account_row['state_id']){
			//get state name
			$where = array('id=' . $account_row['state_id']);
			$query_args = array(
				'table' => 'state',
				'fields'=> array('name'),
				'where' => $where
			);
			
			$query = $this->query_builder->build_select($query_args);
			$state_row = $this->account_object->getRow($query);
			
			$account_row['state_name'] = $state_row['name'];
		}			
		
		if(strlen($account_row[$this->data_store_fields[0]])) {
			$return_value = true;
			
			if($login) {
				$this->data_store[$this->data_store_location] = $account_row;
			}

		}
		else {
			$return_value = false;
		}
		
		return $return_value;
	}
	
	function cryptPassword($password) {
		// MySQL crypt.
		// $password = 'PASSWORD('.$this->account_object->db_quote($password).')';	
		// $this->password_quoting = false;
		
		// PHP crypt.
		// $password = mhash($password);
		// $this->password_quoting = true;

		return sha1(PASSWORD_SALT . $password);
	}
	
	function getAuthData() {
		return $this->data_store[$this->data_store_location];
	}
	
}

?>