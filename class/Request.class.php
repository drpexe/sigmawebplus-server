<?php
	defined('VALID_ENTRY_POINT') or die('');

	abstract class Request
	{
		abstract protected function writeToDatabase();
	}
	
	const REQUEST_SUCCESS          = 1;
	const REQUEST_SUCCESSCACHE     = 2;
	const REQUEST_INVALIDREQUEST   = 3;
	const REQUEST_LOCALERROR       = 4;
	const REQUEST_REMOTEERROR      = 5;
	const REQUEST_WRONGCREDENTIALS = 6;
	const REQUEST_BAN              = 7;
	const REQUEST_ERRORBAN         = 8;
	
	class RequestFactory
	{
		
	}


?>