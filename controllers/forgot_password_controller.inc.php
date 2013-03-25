<?
	require_once(FRAMEWORK_DIR.'/form_handler.inc.php');
	require_once(MODEL_DIR.'/forgot_password.inc.php');

	class ForgotPasswordController extends FormHandler {
		
		function ForgotPasswordController($args) {
			foreach($args as $key=>$value) {
				$this->$key = $value;
			}
		
			parent::FormHandler($args);
			
			$this->object = new ForgotPassword($this->conf);
			$this->html_builder = new HTMLBuilder();
			
			$this->templates['success'] = TEMPLATE_DIR.'/user/forgot_password_success.php';
			$this->templates['form'] = TEMPLATE_DIR.'/user/forgot_password_form.php';
		}
		
		function checkErrors(&$request) {
			$errors = parent::checkErrors($request);
			
			$username = $request->fetch($this->form_method, 'username');
			
			if(!in_array('username', $this->error_field_list)) {
				// Check to see if username is taken
				$user = new User($this->object->conf);
				
				$query_args['fields'] = array('username');
				$query_args['where'][]  = 'username = '.$user->db_quote($username);
				
				$user->db_select($query_args);
				if(!$user->fetch()) {
					$this->addError('username', 'That email address is not in our system.');
				}

			}
				
			return $this->errors;
		}
		
		function finish($request) {
			$user = new User($this->conf);
			
			$user->db_select(
				array(
					'where' => array(
						'username = '.$user->db_quote($request->fetchRequest('username'))
					)
				)
			);
			
			$user->fetch();
			
			$mailbody = 'You have requested your password.
			
Password: '.$user->get('password').'

You may login here:
'.PUBLIC_URL.'/user/login.php
';

$headers = 'From: User <test@example.com>';

		
			mail($user->get('username'), 'Password Request', $mailbody, $headers);
		
			$out = $this->showTemplate(
				array(
					'template_file' => $this->templates['success'],
					'replacements' => $replacements
				)
						
			);
			
			return $out;
		}
		
	}
	
?>