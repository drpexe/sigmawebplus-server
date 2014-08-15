<?php
	define('VALID_ENTRY_POINT', 1);
	define('SITE_ROOT', '');
	
	require_once SITE_ROOT.'class/RequestHandler.class.php';
	
	function bootstrap()
	{
		$reqHandler = new RequestHandler();
		$reqHandler->start();
	}
	
	bootstrap();
?>