<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN">
<html>
<head>
	<title>pTransl alpha</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="/includes/style.css" media="all" />
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.min.js"></script>
	<script type="text/javascript" src="/includes/main.js"></script>
</head>
<body>

<div class='header'>
	<div class='left'></div>
	<div class='right'>
		<?if (!$user):?>
			Please login for access to this system.
		<?else:?>
			Welcome, <b><?=$user[username]?></b>!
		<?endif;?>
		<br/>
		<br/>
		<a href='/p/resource'/>Resources</a>
		<?if ($userId == 1):?>
		 | <a href='/p/import'/>Import</a>
		<?endif;?>
		<?if ($user):?>
		 | <a href='/p/account'/>Account</a>
		 | <a href='/p/logout'/>Logout</a>
		<?endif;?>
		
	</div>
</div>
<div class='main'>
