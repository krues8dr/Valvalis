<?php

require_once(MODEL_DIR.'user.inc.php');
require_once(FRAMEWORK_DIR.'request.inc.php');

class Authentication {

	var $conf;
	var $request;

	function Authentication($conf) {
		$this->conf = $conf;
		
		$this->request = new Request();
	}
	
	function authenticate($username = '', $password = '') {
		$valid = false;
	
		if($this->request->fetchSession('valvalis_user.username') && strlen($this->request->fetchSession('valvalis_user.password'))) {
			$valid_user = $this->check_auth($this->request->fetchSession('valvalis_user.username'), $this->request->fetchSession('valvalis_user.password'));
		}
		elseif($username && strlen($password)) {
			$valid_user = $this->check_auth($username, $password);
		}
		
		if($valid_user) {
			$this->check_last_update($valid_user);
			
			$valid = true;
		}
		else {
			$this->redirect('/user/login.php');
		}
		
		return $valid;
	}
	
	function check_auth($username, $password) {
		if(strlen($username) && strlen($password)) {
			$user = new User($this->conf);
			
			$user_query_args['where'][] = 'username='.$user->db_quote($username);
			$user_query_args['where'][] = 'password='.$user->db_quote($password);
			$user_query_args['where'][] = 'status='.$user->db_quote('active');
			
			$user->db_select($user_query_args);
			
			if($user->fetch()) {
				$valid_user = $user->get();
				$this->start_session($valid_user);
			}	
		}
		
		return $valid_user;
	}
	
	function start_session($user) {
		$_SESSION['valvalis_user'] = $user;
	}
	
	function end_session() {
		unset($_SESSION['valvalis_user']);
	}
	
	function check_last_update($user) {
		$site_option = new DAO(
			array(
				'table' => 'site_option',
				'conf' => $this->conf
			)
		);
		
		$site_option->db_select(
			array(
				'where' => array(
					'name = "user_data_reset_date"'
				)
			)
		);
		$site_option->fetch();
		$user_update_date = $site_option->get('value');
	
		if($user_update_date) {
			if(!$this->request->fetchRequest('verify')) {
				$update_time = strtotime($user_update_date.' 00:00:00');
	
				if($update_time < time()) {

					
					if(!$user['user_reviewed_date'] || $user['user_reviewed_date'] == '0000-00-00 00:00:00') {
						$user_time = '0';
					}
					else {
						$user_time = strtotime($user['user_reviewed_date']);
					}
					
					//print '<!-- '.$user_time.' -->';
					//print '<!-- '.strtotime($user['modified_date']) .'<'.  $update_time.'='.(int) (strtotime($user['user_modified_date']) <  $update_time).'-->';
					
					if($user_time <  $update_time) {
						$this->redirect('/user/edit_account.php?verify=1');
					}
					
				} /* if($update_time > time()) { */
			} /* if(!$this->request->fetchRequest('verify')) */
		} /* if(defined('USER_UPDATE_DATE')) */
	}
	
	function redirect($location = '/') {
		session_write_close();
		header('Location: '.$location);
		exit();
	}
}

?>