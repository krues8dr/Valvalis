<?php

require_once(FRAMEWORK_DIR.'switchboard.inc.php');
require_once(MODEL_DIR.'/user.inc.php');
require_once(MODEL_DIR.'/user_change_log.inc.php');

class UserSwitchboard extends Switchboard {

	var $per_page_limit = 50;

	function UserSwitchboard($args) {
		parent::Switchboard($args);
	}

	function buildFormRow($label, $row = null) {
		if(strlen($row)) {
			$out = $this->html_builder->show('tr',
							array(
								'id' => 'row_'.preg_replace('/([^a-zA-Z0-9])/', '', strip_tags($label)),
								'content' => $this->html_builder->show('td',
									  array(
										'content' =>   $label,
										'class'   => 'label_row',
										'valign'  => 'top'
										)
									  ) .
								$this->html_builder->show('td',
									  array(
										'class' => 'input_cell',
										'content' => $row
										)
									  )
							)
						  )."\n";

		}
		else {
			$out = $this->html_builder->show('tr',
							array(
								'content' => $this->html_builder->show('td',
									  array(
										'colspan' => 2,
										'content' =>   $label,
										'class'   => 'header_label_row',
										'valign'  => 'top'
										)
									  )
							)
						  );
		}
		return $out;
	}

	function checkErrors(&$request) {
		/* Check for basic errors */
		parent::checkErrors($request);

		$username = $request->fetch($this->form_method, 'username');

		if(!in_array('username', $this->error_field_list) && strlen($username)) {
			if(strlen($username) > 64) {
				$this->addError('username', 'The email address must be no longer than 64 characters.');
			}
		}

		if(!in_array('username', $this->error_field_list) && strlen($username)) {
			if(!preg_match('/^([a-zA-Z0-9\.\-@]+)$/', $username)) {
				$this->addError('username', 'The email address can only contain the following characters: A-Z, 0-9, @ (at), . (period), and - (hyphen)');
			}
		}

		if(!in_array('username', $this->error_field_list) && strlen($username)) {
			// Check to see if username is taken
			$user = new User($this->object->conf);

			$query_args['fields'] = array('username');
			$query_args['where'][]  = 'username = '.$user->db_quote($username);

			if($request->fetchRequest('id')) {
				$query_args['where'][] = $user->id_column.' != '.$request->fetchRequest('id');
			}

			if($user->db_select($query_args)) {
				$this->addError('username', 'That email address is already in use.  Please choose another.');
			}
		}

		if(!in_array('username', $this->error_field_list) && strlen($username)) {
			if(!valid_email($username)) {
				$this->addError('username', 'That email address does not appear to be valid.');
			}
		}

		$password = $request->fetch($this->form_method, 'password');

		if(!in_array('password', $this->error_field_list) && strlen($password)) {
			if(strlen($password) > 64) {
				$this->addError('password', 'The password must be no longer than 64 characters.');
			}
		}

		return $this->errors;
	}

	function getList(&$request) {
		foreach(range('a', 'z') as $cur_letter) {
			$temp_where = $where;
			$temp_where[] = 'left(last_name, 1) = '.$this->object->db_quote($cur_letter);
			$count = $this->object->db_select(
				array(
					'fields' => array('count(*) as count'),
					'where' => $temp_where
				)
			);
			$this->object->fetch();
			if($this->object->get('count') > 0) {
				$letters[] = $cur_letter;
			}
		}

		if($request->fetchRequest('letter')) {
			$this->current_letter = $request->fetchRequest('letter');
		}
		else {
			$this->current_letter = $letters[0];
		}

		if(!$is_search) {
			$where[] = 'left(last_name, 1) IN('.$this->object->db_quote($this->current_letter).')';
		}

		$list_args = $this->list_args;


		if($list_args['where']) {
			$list_args['where'] = array_merge($list_args['where'], $where);
		}
		else {
			$list_args['where'] = $where;
		}
		$list_args['order'] = 'last_name, first_name';

		if(!$list_args['fields']) {
			$fields_to_select[] = $this->object->table.'.'.$this->object->id_column;

			foreach($this->object->fields as $field=>$info) {
				if($this->object->fields[$field]['entry_list']) {
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

		$this->object->db_select($list_args);

		ksort($entry_list_fields);

		foreach($entry_list_fields as $key) {

			$label = $this->html_builder->show('strong', $this->object->fields[$key]['label']);

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
				'class' => 'list-row'
			)
		);

		while($this->object->fetch()) {
			unset($tds);
			unset($first_cell);

			foreach($entry_list_fields as $key) {
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
						$url = $this->form_action . '?update=1&id='.$this->object->get($this->object->id_column);
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
					$tds .= $this->html_builder->show('td',
						array(
							'content' => $field,
							'class' => 'list-item'
						)
					);
				}
			}
			$trs .= $this->html_builder->show('tr',
				array(
					'content' => $tds,
					'class' => 'list-row'
				)
			);
		}

		if(strlen($trs)) {
			$out .= $this->html_builder->show('table',
				array(
					'content' => $trs,
					'class' => 'list-table'
				)
			);
		}


		$this->letters = $letters;

		return $out;
	}

	function showPagingLinks($request) {

		$letters = $this->letters;
		$current_letter = $this->current_letter;


		$html_builder =& $this->html_builder;

		$letter_list = range('a', 'z');
		foreach($letter_list as $letter) {

			if($letter == $current_letter) {
				$out .= $this->decorateCurrentLetter($letter);
			}
			elseif(in_array($letter, $letters)) {
				$url = $this->buildUrlFromRequest($request);
				if(strlen($url)) {
					$url .= '&';
				}
				else {
					$url .= '?';
				}
				$url .= 'letter='.$letter;

				$out .= $this->decorateActiveLetter($url, $letter);
			}
			else {
				$out .= $this->decorateInactiveLetter($letter);
			}
		}

		if(strlen($out)) {
			$out = $this->decorateLetterList($out);
		}

		return $out;
	}

	function decorateActiveLetter($url, $letter) {
		$html_builder =& $this->html_builder;

		$link = $html_builder->show('a',
			array(
				'href' => $url,
				'content' => $letter
			)
		);

		$out = $html_builder->show('li',
			array(
				'class' => 'active_letter',
				'content' => $link
			)
		);

		return $out;
	}

	function decorateInactiveLetter($letter) {
		$html_builder =& $this->html_builder;

		$out = $html_builder->show('li',
			array(
				'class' => 'other_page',
				'content' => $letter
			)
		);

		return $out;
	}

	function decorateCurrentLetter($letter) {
		$html_builder =& $this->html_builder;

		$out = $html_builder->show('li',
			array(
				'class' => 'current_page',
				'content' => $letter
			)
		);

		return $out;
	}

	function decorateLetterList($out) {
		$html_builder =& $this->html_builder;

		$out = $html_builder->show('ul',
			array(
				'class' => 'paging_list',
				'content' => $out
			)
		);

		return $out;
	}

	function buildUrlFromRequest($request) {
		if(count($request->fetchGet())) {
			foreach($request->fetchGet() as $key=>$value) {
				if($key != 'letter') {
					if(is_array($value)) {
						foreach($value as $current_value) {
							$atoms[] = $key.'[]='.$current_value;
						}
					}
					else {
						$atoms[] = $key.'='.$value;
					}
				}
			}
		}
		elseif(count($request->fetchPost())) {
			foreach($request->fetchPost() as $key=>$value) {
				if($key != 'letter') {
					if(is_array($value)) {
						foreach($value as $current_value) {
							$atoms[] = $key.'[]='.$current_value;
						}
					}
					else {
						$atoms[] = $key.'='.$value;
					}
				}
			}
		}

		if(count($atoms)) {
			$url = '?'.join('&', $atoms);
		}

		return $url;
	}

	function getAltLetters() {
		$equiv_hash = array(
			'a' => array('Ë', 'ç', 'å', 'Ì', '€', '', '®', 'ˆ', '‡', '‰', '‹', 'Š', 'Œ', '¾'),
			'c' => array('‚', ''),
			'e' => array('é', 'ƒ', 'æ', 'è', '', 'Ž', '', '‘'),
			'i' => array('í', 'ê', 'ë', 'ì', '“', '’', '”', '•'),
			'n' => array('„', '–'),
			'o' => array('ñ', 'î', 'ï', 'Í', '…', '¯', '˜', '—', '™', '›', 'š', '¿'),
			'u' => array('ô', 'ò', 'ó', '†', '', 'œ', 'ž', 'Ÿ'),
			's' => array('§'),
			'z' => array('§')
		);
		return $equiv_hash;
	}

	function getEquivalentLetters($letter, $quote_callback, $joiner) {

		$letter_list = array();

		$equiv_hash = $this->getAltLetters();

		if(count($equiv_hash[$letter])) {
			$letter_list = $equiv_hash[$letter];
		}

		array_unshift($letter_list, $letter);

		foreach($letter_list as $current_letter) {
			$quoted_list[] = call_user_func($quote_callback, $current_letter);
		}

		$return_value = join($joiner, $quoted_list);

		return $return_value;
	}

	// Override default methods

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

		$user_change_log = new UserChangeLog($this->object->conf);
		foreach($set_args as $field=>$value) {
			$user_change_log->db_insert(
				array(
					'data' => array(
						'user_id' => $request->fetchRequest('id'),
						'field_name' => $user_change_log->db_quote($field),
						'field_value' => $user_change_log->db_quote($value),
						'date_modified' => 'NOW()',
						'responsible_user' => $user_change_log->db_quote('admin'),
						'operation' => $user_change_log->db_quote('insert')
					)
				)
			);
		}

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
		$user = new User($this->object->conf);

		$user->db_select(
			array(
				'where' => array(
					'user.id = '.$request->fetchRequest('id')
				)
			)
		);

		$user->fetch();
		$old_user = $user->get();

		$this->handleFileUploads($request);

		$this->object->unsetValue();

		foreach($this->object->fields as $field=>$info) {
			$value = $request->fetch($this->form_method, $field);
			$new_user[$field] = $value;

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

		foreach($new_user as $field=>$value) {
			if($new_user[$field] != $old_user[$field]) {
				$changed_fields[$field] = $value;
			}
		}

		$user_change_log = new UserChangeLog($this->object->conf);
		foreach($changed_fields as $field=>$value) {
			$user_change_log->db_insert(
				array(
					'data' => array(
						'user_id' => $request->fetchRequest('id'),
						'field_name' => $user_change_log->db_quote($field),
						'field_value' => $user_change_log->db_quote($value),
						'date_modified' => 'NOW()',
						'responsible_user' => $user_change_log->db_quote('admin'),
						'operation' => $user_change_log->db_quote('update')
					)
				)
			);
		}

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

		$user_change_log = new UserChangeLog($this->object->conf);
		if(is_array($request->fetchRequest('id'))) {
			foreach($request->fetchRequest('id') as $delete_id) {
				$user_change_log->db_insert(
					array(
						'data' => array(
							'user_id' => $delete_id,
							'field_name' => '',
							'field_value' => '',
							'date_modified' => 'NOW()',
							'responsible_user' => $user_change_log->db_quote('admin'),
							'operation' => $user_change_log->db_quote('delete')
						)
					)
				);
			}
		}
		else {
			$delete_id = $request->fetchRequest('id');
			$user_change_log->db_insert(
				array(
					'data' => array(
						'user_id' => $delete_id,
						'field_name' => '',
						'field_value' => '',
						'date_modified' => 'NOW()',
						'responsible_user' => $user_change_log->db_quote('admin'),
						'operation' => $user_change_log->db_quote('delete')
					)
				)
			);
		}



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
					'id' => $id,
					'index_list' => $value
				)
			);
		}

		return $return_val;
	}

}

?>