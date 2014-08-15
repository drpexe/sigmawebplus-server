<?php
	defined('VALID_ENTRY_POINT') or die('');

	class ServerCredentials
	{
		protected $username;
		protected $password;
	
		function __construct($username, $password)
		{
			if (!is_string($username)) { throw new Exception('$username must be a String'); }
			if (!is_string($password)) { throw new Exception('$password must be a String'); }
			
			$this->username = $username;
			$this->password = $password;
		}
	
		public function getUsername()
		{
			return $this->username;
		}
	
		public function getPassword()
		{
			return $this->password;
		}
	}
?>