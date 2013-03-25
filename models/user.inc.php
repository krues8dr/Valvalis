<?php

/* 

CREATE TABLE user (
`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
`username` varchar(255),
`password` varchar(255),
`first_name` varchar(255),
`last_name` varchar(255),
`status` varchar(255)
);

ALTER TABLE user ADD department VARCHAR(255);
ALTER TABLE user ADD office_address_1 VARCHAR(255);
ALTER TABLE user ADD office_city VARCHAR(255);
ALTER TABLE user ADD office_state VARCHAR(255);
ALTER TABLE user ADD office_zip VARCHAR(255);
ALTER TABLE user ADD office_phone VARCHAR(255);
ALTER TABLE user ADD fax_phone VARCHAR(255);
ALTER TABLE user ADD pager_phone VARCHAR(255);

ALTER TABLE user ADD home_address_1 VARCHAR(255);
ALTER TABLE user ADD home_city VARCHAR(255);
ALTER TABLE user ADD home_state VARCHAR(255);
ALTER TABLE user ADD home_zip VARCHAR(255);
ALTER TABLE user ADD home_phone VARCHAR(255);

*/

require_once(FRAMEWORK_DIR.'dao.inc.php');
require_once(MODEL_DIR.'specialty.inc.php');

class User extends DAO {

	var $table = 'user';

	var $fields = array(
		'clinician_id' => array(
			'label' => 'Clinician ID',
			'input_type' => 'textfield',
			'required' => false
		),
		'username' => array(
			'label' => 'Email Address',
			'input_type' => 'textfield',
			'entry_list' => 3,
			'required' => true
		),
		'password' => array(
			'label' => 'Password',
			'input_type' => 'textfield',
			'entry_list' => 0,
			'required' => true
		),
		'title' => array(
			'label' => 'Title',
			'input_type' => 'select_list',
			'required' => true
		),
		'first_name' => array(
			'label' => 'First Name',
			'input_type' => 'textfield',
			'entry_list' => 1,
			'required' => true
		),
		'last_name' => array(
			'label' => 'Last Name',
			'input_type' => 'textfield',
			'entry_list' => 2,
			'required' => true
		),
		'department' => array(
			'label' => 'Department',
			'input_type' => 'textfield',
			'entry_list' => 0
		),
		'office_address_1' => array(
			'label' => 'Address',
			'input_type' => 'textfield',
			'entry_list' => 0
		),
		'office_city' => array(
			'label' => 'City',
			'input_type' => 'textfield',
			'entry_list' => 0
		),
		'office_state' => array(
			'label' => 'State',
			'input_type' => 'textfield',
			'entry_list' => 0
		),
		'office_zip' => array(
			'label' => 'Zip',
			'input_type' => 'textfield',
			'entry_list' => 0
		),
		'office_phone' => array(
			'label' => 'Phone',
			'input_type' => 'textfield',
			'entry_list' => 0
		),
		'fax_phone' => array(
			'label' => 'Fax',
			'input_type' => 'textfield',
			'entry_list' => 0
		),
		'pager_phone' => array(
			'label' => 'Pager',
			'input_type' => 'textfield',
			'entry_list' => 0
		),
		'home_address_1' => array(
			'label' => 'Address',
			'input_type' => 'textfield',
			'entry_list' => 0
		),
		'home_city' => array(
			'label' => 'City',
			'input_type' => 'textfield',
			'entry_list' => 0
		),
		'home_state' => array(
			'label' => 'State',
			'input_type' => 'textfield',
			'entry_list' => 0
		),
		'home_zip' => array(
			'label' => 'Zip',
			'input_type' => 'textfield',
			'entry_list' => 0
		),
		'home_phone' => array(
			'label' => 'Phone',
			'input_type' => 'textfield',
			'entry_list' => 0
		)
	);
	
	var $form_dividers = array(
		'office_address_1' => 'Office Information',
		'home_address_1' => 'Home Information',
	);
	
	function User($conf) {
		parent::DAO(
			array(
				'conf' => &$conf
			)
		);
	}
	
	function getSelectListContent($name) {
		switch ($name) {
			case 'status' :
				$values = array(
					'pending' => 'Pending',
					'active' => 'Active'
				);
				break;
			
			case 'title' : 
				$values = array(
					'Dr.' => 'Dr.',
					'Mr.' => 'Mr.',
					'Mrs.' => 'Mrs.',
					'Ms.' => 'Ms.'
				);
				break;
		}
		
		return $values;
	}

}

?>