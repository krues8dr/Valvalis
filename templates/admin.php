<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<title>Valvalis Management<?php print ($this->title ? ' - '.$this->title : ''); ?></title>
	<link rel="stylesheet" type="text/css" href="/css/admin.css" />
	<script language="Javascript" src="/js/IEmarginFix.js" type="text/javascript"></script>
	<script language="Javascript" src="/js/prototype1_6_0_1/prototype.js" type="text/javascript"></script>
	<script language="Javascript" src="/js/scriptaculous1_8_1/scriptaculous.js" type="text/javascript"></script>

</head>
<body>
<div id="main">
	<div id="content_block">
		<div class="content_main">
			<h1 class="title">Valvalis Management</h1>
			<div class="body_text">
				<div id="nav">
					<ul>
						<li><a href="/admin/">Main</a></li>
						<li><a href="/admin/user.php">Users</a></li>
					</ul>
				</div>
				<div id="admin_content">
					<h2><?php print $this->title; ?></h2>
					<?php print $this->content; ?>

				</div>
			</div>
		</div>
	</div>
</div>
</body>
</html>
