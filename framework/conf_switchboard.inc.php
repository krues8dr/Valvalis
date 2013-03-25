<?php
/*
	class ConfSwitchboard
	
	Created by:
	Bill Hunt (bill@krues8dr.com)
	
	Description: 
	Extends FormHandler to create a switchboard.
	This switchboard type is used for a single-record
	configuration table.
	
	ChangeLog: 
	01.16.06 - bill@krues8dr.com
			 - Created object from Switchboard.
*/


require_once(FRAMEWORK_DIR.'form_handler.inc.php');
require_once(FRAMEWORK_DIR.'pager.inc.php');

class ConfSwitchboard extends FormHandler {

	var $pager;

	// function Switchboard
	// object constructor.
	function Switchboard($args) {
		parent::FormHandler($args);
	}
	
	// function execute
	// This is the controller for the object.
	function execute(&$request, $default_values = null) {
			$this->form_footer = $this->buildSwitchboardFooter($request);
			
			if(!$request->isSubmitted($this->form_method)) {
				$count = $this->object->db_select(
					array(
						'limit' => '1'
					)
				);
				
				if($count) {
					$this->object->fetch();
					$values = $this->object->get();
				}
				
				if(is_array($default_values) && count($default_values)) {
					foreach($default_values as $field=>$value) {
						$values[$field] = $value;	
					}
				}
				
				$default_values = $values;
				
				if($request->fetchRequest('success')) {
					$prompt = 'Record successfully updated.';
				}
				$return_val = $this->showForm($default_values, null, $prompt);
			}
			else {
				$return_val = parent::execute($request, $default_values);
			}
			

		return $return_val;
	}
	
	
	// function handlFileUploads
	// Overrides FormHandler handleFileUploads fn.
	// Takes uploaded files, sticks them in the db, sets the value in request
	function handleFileUploads(&$request) {
		
	}

	
	// function finish
	// performed after a successful update/insert.
	function finish(&$request) {
		$count = $this->object->db_select(
			array(
				'limit' => '1'
			)
		);
		if($count > 0) {
			$this->object->fetch();
			$request->data['request']['id'] = $this->object->get($this->object->id_column);
			$return_val = $this->doUpdate($request);
			// The return value here is the number of affected rows.
			// Thus, it may return a false negative.
		}
		else {
			$return_val = $this->doInsert($request);
		}
		$url = $this->form_action.'?success=1';
		
		$this->redirect($url);
	}
	
	
	// function doInsert
	// Performed after a successful submit w/insert=true
	function doInsert(&$request) {
		$this->object->unsetValue();
		
		foreach($this->object->fields as $field=>$info) {
			$value = $request->fetch($this->form_method, $field);
			$value = $this->html_builder->cleanData($this->object->fields[$field]['input_type'],
				array(
					'object' => &$this->object,
					'value' => $value
				)
			);
		
			$set_args[$field] = $value;
			unset($value);
		}
		$this->object->set($set_args);
		
		$return_val = $this->object->db_insert();
		
		return $return_val;
	}
	
	// function doUpdate
	// Performed after a successful submit w/update=true
	function doUpdate(&$request) {
		$this->object->unsetValue();
		
		foreach($this->object->fields as $field=>$info) {
			$value = $request->fetch($this->form_method, $field);
			$value = $this->html_builder->cleanData($this->object->fields[$field]['input_type'],
				array(
					'object' => &$this->object,
					'value' => $value
				)
			);
		
			$set_args[$field] = $value;
			unset($value);
		}
		$this->object->set($set_args);
		
		$return_val = $this->object->db_update(
			array(
				'where_id' => '= '.$request->fetchRequest('id')
			)
		);
		
		return $return_val;
	}

	// function buildSwitcboardFooter
	// Creates the necessary update/insert logic into the
	// form, as well as the submit button.
	function buildSwitchboardFooter(&$request) {
		
		$form_footer .= $this->html_builder->show('submit', 
							   array(
							   	 'class' => 'submit-button',
								 'name'   =>'submit',
								 'value'  => 'Update'
								 )
		);
	
		$form_footer = $this->html_builder->show('tr', 
			$this->html_builder->show('td', 
				  array(
					'content' => $form_footer,
					'class' => 'submit_cell',
					'colspan' => '2'
					)
			  )
		);
		
		return $form_footer;
	}


	
}

?>