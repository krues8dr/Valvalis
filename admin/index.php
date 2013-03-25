<?php

{

require_once($_SERVER['DOCUMENT_ROOT'].'/valvalis/config/conf.php');
require_once(CONF_DIR.'configuration.inc.php');

require_once(HTMLBUILDER_DIR.'html_builder.inc.php');
require_once(FRAMEWORK_DIR.'request.inc.php');
require_once(FRAMEWORK_DIR.'template.inc.php');

$data['content'] = '';
$data['title'] = 'Site Administration';

$template = new Template();

print $template->show(
	array(
		'template_file' => TEMPLATE_DIR.'admin.php',
		'data' => $data
	)
);

}

?>