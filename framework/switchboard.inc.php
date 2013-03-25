<?php
/*
	class Switchboard
	
	Created by:
	Bill Hunt (bill@krues8dr.com)
	
	Description: 
	Extends FormHandler to create a switchboard.
	
	ChangeLog: 
	01.10.06 - bill@krues8dr.com
			 - Created object.
*/


require_once(FRAMEWORK_DIR.'form_handler.inc.php');
require_once(FRAMEWORK_DIR.'pager.inc.php');

class Switchboard extends FormHandler {

	var $pager;
	var $per_page_limit;
	var $list_args;
	var $show_single_page_links = false;
	var $sortable_columns = false;
	var $show_insert_link = true;
	var $show_delete_link = true;
	
	var $no_records_message = 'No records found.';
	var $record_not_found_message = 'That record cannot be found.';
	var $insert_link_message = 'Insert Record';
	var $delete_link_message = 'Delete Record';
	var $delete_link_confirm_message = 'Are you sure that you want to delete this record?';
	
	var $use_redirect = true;
	
	var $list_id_column;

	// function Switchboard
	// object constructor.
	function Switchboard($args = array()) {
		if(strlen($args['per_page_limit'])) {
			$this->per_page_limit = $args['per_page_limit'];
		}
		if($args['list_args']) {
			$this->list_args = $args['list_args'];	
		}
		if(isset($args['show_single_page_links'])) {
			$this->show_single_page_links = $args['show_single_page_links'];
		}
		if(isset($args['sortable_columns'])) {
			$this->sortable_columns = $args['sortable_columns'];
		}
		if(isset($args['show_insert_link'])) {
			$this->show_insert_link = $args['show_insert_link'];
		}
		if(isset($args['show_delete_link'])) {
			$this->show_delete_link = $args['show_delete_link'];
		}
	
		parent::FormHandler($args);
	}
	
	// function execute
	// This is the controller for the object.
	function execute(&$request, $default_values = null) {
		if($request->fetchRequest('remove_file')) {
			$this->removeFile($request->fetchRequest('id'), $request->fetchRequest('remove_file'));
		}
		
		if(($request->fetchRequest('insert') || $request->fetchRequest('update')) && !$request->fetchRequest('success')) {
			$this->form_footer = $this->buildSwitchboardFooter($request);
			
			if($request->fetchRequest('update') && $this->object->isValidID($request->fetchRequest('id'))) {
				$count = $this->object->db_select(
					array(
						'where_id' => '='.$request->fetchRequest('id')
					)
				);
				
				if($count > 0) {
					$this->object->fetch();
					$values = $this->object->get();
					
					if(is_array($default_values) && count($default_values)) {
						foreach($default_values as $field=>$value) {
							$values[$field] = $value;
						}
					}
					
					$default_values = $values;
					$return_val = parent::execute($request, $default_values);
				}
				else {
					$return_val = $this->record_not_found_message;
				}
			}
			else {
				$return_val = parent::execute($request, $default_values);
			}
		}
		elseif($request->fetchRequest('delete') && !$request->fetchRequest('success')) {
			$return_value = $this->finish($request);
		}
		else {
			$prompt = $this->getSuccessMessage($request);

			$return_val = $this->showList(&$request, $prompt);
		}
		return $return_val;
	}
	
	function getSuccessMessage($request) {
		if($request->fetchRequest('success')) {
			if($request->fetchRequest('insert')) {
				$prompt = 'Record successfully inserted.';
			}
			elseif($request->fetchRequest('update')) {
				$prompt = 'Record successfully updated.';
			}
			elseif($request->fetchRequest('delete')) {
				$prompt = 'Record successfully deleted.';
			}
		}
		
		return $prompt;
	}
	
	
	// function handlFileUploads
	// Takes uploaded files, sticks them in the db, sets the value in request
	function handleFileUploads(&$request) {
		$files = $request->fetchFiles();
		
		if(is_array($files) && count($files)) {
			foreach($files as $name=>$current_file) {
				if($current_file['tmp_name']) {
					if(in_array($name, array_keys($this->object->fields))) {
						if($request->fetchRequest('id')) {
							$this->object->removePreviousFile($request->fetchRequest('id'), $name);
						}
					}
					$base_filename = $current_file['name'];
					$filename = $this->object->getFilePath($name).$base_filename;
					
					while(file_exists($filename)) {
						$base_filename = generate_random_password().'_'.$current_file['name'];
						$filename = $this->object->getFilePath($name).$base_filename;
					}
					
					write_file($filename, file_get_contents($current_file['tmp_name']));
					unlink($current_file['tmp_name']);
					$request->setGetPostRequest($name, $base_filename);
				}
			}
			
		}
	}
	
	function removeFile($id, $field) {
		$this->object->removePreviousFile($id, $field);
		
		if(is_array($field) && count($field)) {
			foreach($field as $fieldname) {
				$data[$fieldname] = '';
			}
		}
		elseif(strlen($field)) {
			$data[$field] = '""';
		}
		
		if(is_array($data)) {
			$this->object->db_update(
				array(
					'data' => $data,
					'where' => array(
						$this->object->id_column.' = '.$id
					)
				)
			);
		}		
		
	}
	
	
	// function finish
	// performed after a successful update/insert.
	function finish(&$request) {
		if($request->fetchRequest('insert')) {
			$return_val = $this->doInsert($request);
			$method = 'insert';
		}
		elseif($request->fetchRequest('update') && $request->fetchRequest('id')) {
			$return_val = $this->doUpdate($request);
			$method = 'update';
		}
		elseif($request->fetchRequest('delete') && $request->fetchRequest('id')) {
			$return_val = $this->doDelete($request);
			$method = 'delete';
		}
		
		
		if($this->use_redirect) {
		
			$url = $this->form_action.'?'.$method.'=1&success=1';
			if($this->list_link_append) {
				$url .= '&'.$this->list_link_append;
			}
			
			$this->redirect($url);
		}
		else {
			$request->setGetPostRequest('success', 1);
			$prompt = $this->getSuccessMessage($request);

			$return_val = $this->showList(&$request, $prompt);
			return $return_val;
		}
	}
	
	// function showList
	// Template wrapper for getList().
	function showList(&$request, $prompt = null) {
		$list = $this->getList($request);
		$paging_links = $this->showPagingLinks($request);
		$additional_list_buttons = $this->showAdditionalListButtons(&$request, &$this->html_builder);
	
		if(!strlen($list)) {
			$prompt = $this->no_records_message;
		}
		
		if($this->show_insert_link) {
			$url = $_SERVER['PHP_SELF'].'?insert=1';
			if($this->list_link_append) {
				$url .= '&'.$this->list_link_append;
			}

			$menu .= $this->menuItemDecorator(
				$this->html_builder->show('a',
					array(
						'href' => $url,
						'content' => $this->insert_link_message
					)
				)
			);
		}
		
		
		if(strlen($additional_list_buttons)) {
			$menu .= $additional_list_buttons;
		}
		
		$menu = $this->menuDecorator($menu);
		
		if($this->templates['list']) {
			$replacements['list'] = $list;
			$replacements['paging_links'] = $paging_links;
			$replacements['prompt'] = $prompt;

			if(strlen($menu)) {
				$replacements['menu'] = $menu;
			}
			
			$out = $this->showTemplate(
				array(
					'template_file' => $this->templates['list'],
					'replacements' => $replacements
				)
						
			);
		}
		else {
			if(strlen($prompt)) {
				$prompt .= $this->html_builder->show('br');
			}
			$out = $menu.$prompt.$paging_links.$list;
		}
		
		return $out;
	}
	
	function showForm($data, $errors = null, $prompt = null) {
		$prompt = $this->promptDecorator($prompt);
		if($this->form_footer) {
			$args['form_footer'] = $this->form_footer;
		}
		if($this->form_args) {
			$args['form_args'] = $this->form_args;
		}
		$args['data'] = $data;
		
		$form = $this->buildForm($args);
		
		if($data['id'] && $this->show_delete_link) {
			$delete_link = $this->html_builder->show('a', 
				array(
					'href' => $_SERVER['PHP_SELF'].'?id='.$data['id'].'&delete=1',
					'content' => $this->delete_link_message,
					'onclick' => 'return confirm(\''.$this->delete_link_confirm_message.'\');'
				)
			);
		}
		
		if($this->templates['form']) {
			$replacements = array(
				'form' => $form,
				'errors' => $errors,
				'prompt' => $prompt,
				'delete_link' => $delete_link
			);
			
			$out = $this->showTemplate(
				array(
					'template_file' => $this->templates['form'],
					'replacements' => $replacements
				)
						
			);
		}
		else {
			$out = $prompt.$errors.$form.$delete_link;
		}
		
		return $out;
	}
	
	// function doInsert
	// Performed after a successful submit w/insert=true
	function doInsert(&$request) {
		$this->handleFileUploads($request);
	
		$this->object->unsetValue();
		
		foreach($this->object->fields as $field=>$info) {
			$value = $request->fetch($this->form_method, $field);
			$value = $this->html_builder->cleanData($this->object->fields[$field]['input_type'],
				array(
					'object' => &$this->object,
					'value' => $value
				)
			);
			if($this->object->fields[$field]['input_type'] == 'checkbox_group') {
				$checkbox_fields[$field] = $value;
			}
			elseif($this->object->fields[$field]['input_type'] == 'custom') {
				// Do nothing?
			}
			elseif(in_array($this->object->fields[$field]['input_type'], $this->_filefield_types)) {
				if($value) {
					$set_args[$field] = $value;
				}
				else {
					// Do nothing
				}
			}
			else {
				$set_args[$field] = $value;
			}
			unset($value);
		}
		$this->object->set($set_args);
		
		$return_val = $this->object->db_insert();
		
		foreach($checkbox_fields as $field=>$value) {
			if($this->object->hasRelationalObject($this->object->table.'_'.$field.'_index')) {
				$obj = $this->object->table.'_'.$field.'_index';
			}
			elseif($this->object->hasRelationalObject($field.'_'.$this->object->table.'_index')) {
				$obj = $field.'_'.$this->object->table.'_index';
			}
		
			$this->object->callRelationalMethod($obj, 'insertIndexes', 
				array(
					'primary_field' => $this->object->table.'_'.$this->object->id_column,
					'id' => $return_val,
					'index_list' => $value
				)
			);
		}
		
		return $return_val;
	}
	
	// function doUpdate
	// Performed after a successful submit w/update=true
	function doUpdate(&$request) {
		$this->handleFileUploads($request);

		$this->object->unsetValue();
		
		foreach($this->object->fields as $field=>$info) {
			$value = $request->fetch($this->form_method, $field);
			$value = $this->html_builder->cleanData($this->object->fields[$field]['input_type'],
				array(
					'object' => &$this->object,
					'value' => $value
				)
			);
			if($this->object->fields[$field]['input_type'] == 'checkbox_group') {
				$checkbox_fields[$field] = $value;
			}
			elseif($this->object->fields[$field]['input_type'] == 'custom') {
				// Do nothing?
			}
			elseif($this->object->fields[$field]['input_type'] == 'label') {
				// Do nothing?
			}
			elseif(in_array($this->object->fields[$field]['input_type'], $this->_filefield_types)) {
				// There should be quotes wrapping the string, so greater
				// than two characters in lenght, total.
				if(strlen($value) > 2) {  
					$set_args[$field] = $value;
				}
				else {
					// Do nothing
				}
			}
			else {
				$set_args[$field] = $value;
			}
			unset($value);
		}
		$this->object->set($set_args);
		
		$return_val = $this->object->db_update(
			array(
				'where_id' => '= '.$request->fetchRequest('id')
			)
		);
		
		foreach($checkbox_fields as $field=>$value) {
			if($this->object->hasRelationalObject($this->object->table.'_'.$field.'_index')) {
				$obj = $this->object->table.'_'.$field.'_index';
			}
			elseif($this->object->hasRelationalObject($field.'_'.$this->object->table.'_index')) {
				$obj = $field.'_'.$this->object->table.'_index';
			}
		
			$this->object->callRelationalMethod($obj, 'updateIndexes', 
				array(
					'primary_field' => $this->object->table.'_'.$this->object->id_column,
					'id' => $request->fetchRequest('id'),
					'index_list' => $value
				)
			);
		}
		
		return $return_val;
	}
	
	// function doDelete
	// Performed after a successful submit w/delete=true
	function doDelete(&$request) {
		if(is_array($request->fetchRequest('id'))) {
			$id = join(', ', $request->fetchRequest('id'));
		}
		else {
			$id = $request->fetchRequest('id');
		}
		
		foreach($this->object->fields as $field=>$info) {
			$value = $request->fetch($this->form_method, $field);
			$value = $this->html_builder->cleanData($this->object->fields[$field]['input_type'],
				array(
					'object' => &$this->object,
					'value' => $value
				)
			);
			if($this->object->fields[$field]['input_type'] == 'checkbox_group') {
				$checkbox_fields[$field] = $value;
			}
			elseif($this->object->fields[$field]['input_type'] == 'filefield') {
				$this->object->removePreviousFile($id, $field);
			}
		}
		
		$return_val = $this->object->db_delete(
			array(
				'where_id' => 'IN ('.$id.')'
			)
		);
		
		foreach($checkbox_fields as $field=>$value) {
			if($this->object->hasRelationalObject($this->object->table.'_'.$field.'_index')) {
				$obj = $this->object->table.'_'.$field.'_index';
			}
			elseif($this->object->hasRelationalObject($field.'_'.$this->object->table.'_index')) {
				$obj = $field.'_'.$this->object->table.'_index';
			}
		
			$this->object->callRelationalMethod($obj, 'deleteIndexes', 
				array(
					'primary_field' => $this->object->table.'_'.$this->object->id_column,
					'id' => $request->fetchRequest('id'),
					'index_list' => $value
				)
			);
		}
		
		return $return_val;
	}

	// function buildSwitcboardFooter
	// Creates the necessary update/insert logic into the
	// form, as well as the submit button.
	function buildSwitchboardFooter(&$request) {
		if(!$this->static) {
			if($request->fetchRequest('insert')) {
				$fn = 'insert';
				$submit_label = 'Insert';
			}
			elseif($request->fetchRequest('update')) {
				$fn = 'update';
				$submit_label = 'Update';
			}
			
			$form_footer .= $this->html_builder->show('hidden',
				array(
					'name' => $fn,
					'value' => '1'
				)
			);
			if($id = $request->fetchRequest('id')) {
				$form_footer .= $this->html_builder->show('hidden',
					array(
						'name' => 'id',
						'value' => $id
					)
				);
			}
			
			$form_footer .= $this->html_builder->show('submit', 
								   array(
									 'class' => 'submit-button',
									 'name'   =>'submit',
									 'value'  => $submit_label
									 )
			);
		}
		else {
			$form_footer .= $this->html_builder->show('a',
				array(
					'href' => $this->form_action,
					'content' => 'Back to Listing'
				)
			);
			
		}
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
	
	// function getList
	// Retrieves and formats records to show.
	function getList(&$request) {
		$order_dirs = array('asc', 'desc');
		$order_dirs_hash = array_flip($order_dirs);
		
		if($request->fetchRequest('sort_by')) {
			list($sort_field, $sort_order) = split(' ', $request->fetchRequest('sort_by'));
			if(in_array($sort_field, array_keys($this->object->fields))) {
				$this->list_args['order'] = $sort_field;
				
				if(in_array(strtolower($sort_order), $order_dirs)) {
					$this->list_args['order'] .= ' '.$sort_order;
				}
			}
		} 
		
		if(!$this->list_args['order']) {
			$this->list_args['order'] = $this->object->id_column;
		}
	
		$list_args = $this->list_args;
		$list_args['fields'][] = 'count(*) as count';

		$this->object->db_select($list_args);
		$this->object->fetch();
		
		
		$pager_args['item_count'] = $this->object->get('count');
		
		if($request->fetchRequest('page') > 1) {
			$pager_args['page'] = $request->fetchRequest('page');
		}

		
		list($sort_field, $sort_order) = split(' ', $list_args['order']);
		
		$this->pager =& new Pager($pager_args);
		
		if($this->per_page_limit) {
			$this->pager->setItemLimit($this->per_page_limit);
		}
		
		$limit = $this->pager->getLimit();
		
		
		unset($list_args);
		$list_args = $this->list_args;
		
		if(!$list_args['fields']) {
			$fields_to_select[] = $this->object->table.'.'.$this->object->id_column;
			
			foreach($this->object->fields as $field=>$info) {
				if($this->object->fields[$field]['entry_list']) {
					// Order the entry list fields properly
					$entry_list_fields[$this->object->fields[$field]['entry_list']] = $field;
					
					if($this->object->fields[$field]['input_type'] != 'custom') {
						if(strpos($field, '.') === false && strpos($field, '(') === false) {
							$field = $this->object->table.'.'.$field;
						}
						$fields_to_select[] = $field;
					}

				}
			}
			$list_args['fields'] = $fields_to_select;
		}
		
		$list_args['limit'] = $limit['begin'].', '.$limit['count'];
		
		$this->object->db_select($list_args);
		
		// Reorder the entry list fields to match the preferred order
		ksort($entry_list_fields);
		
		foreach($entry_list_fields as $key) {
			
			$label = $this->object->fields[$key]['label'];
			
			if($this->sortable_columns && $this->object->fields[$key]['input_type'] != 'custom') {

				$sort_by = $key;

				if($key == $sort_field) {

					$sort_by .= ' '.$order_dirs[(!$order_dirs_hash[$sort_order])];
					
					if('desc' == strtolower($sort_order)) {
						$label .= $this->html_builder->show('img',
							array(
								'src' => '/images/page/down_arrow.gif',
								'border' => '0'
							)
						);
					}
					elseif('asc' == strtolower($sort_order)) {
						$label .= $this->html_builder->show('img',
							array(
								'src' => '/images/page/up_arrow.gif',
								'border' => '0'
							)
						);
					}
	
				}
				else {
					$sort_by .= ' '.$order_dirs[0];
				}
				
				$label = $this->html_builder->show('a',
					array(
						'href' => $this->form_action . '?sort_by='.$sort_by,
						'content' => $label
					)
				);
			}
			$tds .= $this->html_builder->show('td', 
						array(
							'content' => $label,
							'class' => 'list-item'
						)
					);
		}
		$trs .= $this->html_builder->show('tr',
			array(
				'content' => $tds,
				'class' => 'list-row heading'
			)
		);
		
		while($this->object->fetch()) {
			$row_count++;
			
			unset($cell_count);
			unset($tds);
			unset($first_cell);
			
			foreach($entry_list_fields as $key) {
				$cell_count++;
				if(strpos($key, '.') !== false) {
					list($null, $key_field) = split('\.', $key);
				}
				else {
					$key_field = $key;
				}
				
				if($this->object->fields[$key]['input_type'] != 'custom') {
					$field = $this->object->get($key_field);
				}
				else {
					$field = $this->object->getCustomContent(&$this->html_builder, $key_field);
				}
				
				// Only show the id column if it's been explicitly requested.
				if($key != $this->object->id_column || in_array($this->object->id_column, array_keys($this->object->fields))) {
					if($this->object->fields[$key]['input_type'] == 'select_list') {
						$values = $this->object->getSelectListContent($key_field);
						
						$field = $values[$field];
					}
					elseif($this->object->fields[$key]['input_type'] == 'yesno_field') {
						$noyes = array('' => '', '0' => 'No', '1' => 'Yes');
						$field = $noyes[$field];
					}
					
					if(!$first_cell) {
						if(!$this->list_id_column) {
							$this->list_id_column = $this->object->id_column;
						}
						$url = $this->form_action . '?update=1&id='.$this->object->get($this->list_id_column);
						if($this->list_link_append) {
							$url .= '&'.$this->list_link_append;
						}
						$field = $this->html_builder->show('a', 
							array(
								'href'=> $url,
								'content' => $field
							)
						);
						$first_cell = true;
					}
					$tds .= $this->showListCell($field, 
						array(
							'cell_count' => $cell_count,
							'row_count' => $row_count
						)
					);
				}
			}
			$trs .= $this->showListRow($tds,
				array(
					'row_count' => $row_count
				)
			);
		}
		
		$out .= $this->showListTable($trs,
			array(
				'row_count' => $row_count
			)
		);
		
		
		return $out;
	}
	
	function showListCell($item, $args) {
		$out = $this->html_builder->show('td', 
						array(
							'content' => $item,
							'class' => 'list-item'
						)
					);
		return $out;
	}
	
	function showListRow($items, $args) {
		$out = $this->html_builder->show('tr',
			array(
				'content' => $items,
				'class' => 'list-row'
			)
		);
		return $out;
	}
	
	function showListTable($items, $args) {
		if(strlen($items)) {
			$out .= $this->html_builder->show('table',
				array(
					'content' => $items,
					'class' => 'list-table'
				)
			);
		}
		return $out;
	}
	
	// function showPagingLinks
	// returns out paging links.
	function showPagingLinks($request) {
		if($this->html_builder->xhtml) {
			$joiner = '&amp;';
		}
		else {
			$joiner = '&';
		}
		foreach($request->fetchGet() as $field=>$value) {
			if($field != 'page') {
				$args[] = $field.'='.urlencode($value);	
			}
		}
		
		if(is_array($args) && count($args) > 0) {
			$query_string = $joiner.join($joiner, $args);
		}
	
		$paging_values = $this->pager->getPagingValues();
		for($i = $paging_values['first']; $i <= $paging_values['last']; $i++) {
			if($i == $this->pager->getPage()) {
				$links[] = $this->html_builder->show('li',
					array(
						'class' => 'current_page',
						'content' => $i
					)
				);
			}
			else {
				$link = $this->html_builder->show('a', 
					array(
						'href'=> $this->form_action . '?page='.$i.$query_string,
						'content' => $i
					)
				);
				$links[] = $this->html_builder->show('li',
					array(
						'class' => 'other_page',
						'content' => $link
					)
				);
			}
		}
		
		// Only show the links if there is more than one page or 
		// there's one page and we're supposed to show the link.
		if(count($links) > 1 || ($this->show_single_page_links && count($links) == 1)) {
			$links = join('', $links);
			$out = $this->html_builder->show('ul',
				array(
					'class' => 'paging_list',
					'content' => $links
				)
			);
		}
		
		return $out;
		
	}
	
	function showAdditionalListButtons($request, $html_builder) {
		return $out;
	}

	
}

?>