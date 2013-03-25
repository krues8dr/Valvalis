<?php

class UserHomeController {

	function UserHomeController($args = array()) {
		foreach($args as $key=>$value) {
			$this->$key = $value;
		}
	}
	
	function execute($request) {
		$user = new User($this->conf);
		
		$user->db_select(
			array(
				'where' => array(
					'user.id = '.$_SESSION['valvalis_user']['id'],
					'status = "active"'
				)
			)
		);
		$user->fetch();
		
		$replacements['user'] = $user->get();
		
		$template = new Template();
		
		$out = $template->show(
			array(
				'template_file' => TEMPLATE_DIR.'/user/user_home.php',
				'data' => $replacements
			)
					
		);
		
		return $out;
	
	}

}


?>