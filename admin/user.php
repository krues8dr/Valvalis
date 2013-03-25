<?php

{

require_once($_SERVER['DOCUMENT_ROOT'].'/valvalis/config/conf.php');
require_once(CONF_DIR.'configuration.inc.php');

require_once(HTMLBUILDER_DIR.'html_builder.inc.php');
require_once(FRAMEWORK_DIR.'request.inc.php');
require_once(FRAMEWORK_DIR.'template.inc.php');

//require_once(FRAMEWORK_DIR.'switchboard.inc.php');
require_once(CONTROLLER_DIR.'user_switchboard.inc.php');

require_once(MODEL_DIR.'user.inc.php');

$config =& new Configuration();

$html_builder =& new HTMLBuilder(
	array(
		'conf' => &$config,
		'xhtml' => true
	)
);

$request =& new Request();


$user =& new User($config);


$handler =& new UserSwitchboard(
	array(
		'object' => &$user,
		'html_builder' => &$html_builder,
		'list_args' => array(
			'order' => array('last_name', 'first_name')
		)
	)
);

$out = $handler->execute($request);

$data['content'] = $out;
$data['title'] = 'Manage Users';

$template = new Template();

print $template->show(
	array(
		'template_file' => TEMPLATE_DIR.'admin.php',
		'data' => $data
	)
);

}
?>