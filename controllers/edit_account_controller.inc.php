<?
	require_once(FRAMEWORK_DIR.'/form_handler.inc.php');
	require_once(MODEL_DIR.'/public_user.inc.php');
	require_once(MODEL_DIR.'/user_change_log.inc.php');

	class EditAccountController extends FormHandler {

		var $submit_value = 'Submit and Confirm';

		function EditAccountController($args) {
			foreach($args as $key=>$value) {
				$this->$key = $value;
			}

			parent::FormHandler($args);

			$this->object = new PublicUser($this->conf);
			$this->html_builder = new HTMLBuilder();

			$this->templates['success'] = TEMPLATE_DIR.'/user/edit_account_success.php';
			$this->templates['form'] = TEMPLATE_DIR.'/user/edit_account_form.php';
		}

		function checkErrors(&$request) {
			$errors = parent::checkErrors($request);

			$username = $request->fetch($this->form_method, 'username');

			if(!in_array('username', $this->error_field_list) && strlen($username)) {
				if(strlen($username) > 64) {
					$this->addError('username', 'The email address must be no longer than 64 characters.');
				}
			}
			if(!in_array('username', $this->error_field_list) && strlen($username)) {
				if(strlen($username) < 6) {
					$this->addError('username', 'The email address must be at least 6 characters in length.');
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

				if($request->fetchSession('valvalis_user.id')) {
					$query_args['where'][] = $user->id_column.' != '.$request->fetchSession('valvalis_user.id');
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

			return $this->errors;
		}

		function finish($request) {
			$user = new PublicUser($this->conf);

			$user->db_select(
				array(
					'where' => array(
						'user.id = '.$request->fetchSession('valvalis_user.id')
					)
				)
			);

			$user->fetch();
			$old_user = $user->get();

			$update_fields = array_keys($user->fields);

			$update_fields = array_diff($update_fields, array('password'));

			$update_data = array();

			foreach($update_fields as $field) {
				if($request->fetchRequest($field) != $old_user[$field]) {
					$update_data['user_modified_date'] = 'NOW()';

					$update_data[$field] = $user->db_quote($request->fetchRequest($field));
				}
			}

			$update_data['user_reviewed'] = 1;
			$update_data['modified_date'] = 'NOW()';
			$update_data['user_reviewed_date'] = 'NOW()';



			$user->db_update(
				array(
					'data' => $update_data,
					'where' => array(
						'user.id = '.$request->fetchSession('valvalis_user.id')
					),
					'debug' => false
				)
			);

			// Get the calculated values
			$user->db_select(
				array(
					'fields' => array_keys($update_data),
					'where' => array(
						'user.id = '.$request->fetchSession('valvalis_user.id')
					)
				)
			);

			$user->fetch();
			$new_user = $user->get();

			// Update the log
			$username = $request->fetchSession('valvalis_user.username');
			$user_change_log = new UserChangeLog($this->conf);
			foreach($new_user as $field=>$value) {
				$user_change_log->db_insert(
					array(
						'data' => array(
							'user_id' => $request->fetchSession('valvalis_user.id'),
							'field_name' => $user_change_log->db_quote($field),
							'field_value' => $user_change_log->db_quote($value),
							'date_modified' => 'NOW()',
							'responsible_user' => $user_change_log->db_quote($username),
							'operation' => $user_change_log->db_quote('update')
						)
					)
				);
			}

			$_SESSION['valvalis_user']['username'] = $request->fetchRequest('username');


			// Update the user's session data
			$auth =& new Authentication($this->conf);
			$auth->check_auth($update_data['username'], $update_data['password']);

			$out = $this->showTemplate(
				array(
					'template_file' => $this->templates['success'],
					'replacements' => $replacements
				)

			);

			return $out;
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

	function buildFormFooter($data) {
		$message = '<strong>By clicking &quot;submit&quot; I am acknowledging I
					have carefully reviewed the above information and it
					is accurate to the best of my knowledge.</strong>';
		$form_footer .= $this->buildFormRow($message);

		$submit_button = $this->html_builder->show('submit',
									   array(
										 'name'   =>'submit',
										 'value'  =>'Submit'
										 )
									   );

		$form_footer .= $this->buildFormRow($submit_button);

		return $form_footer;
	}

	}

?>