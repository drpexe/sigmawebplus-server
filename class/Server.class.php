<?php 
	defined('VALID_ENTRY_POINT') or die('');

	require_once SITE_ROOT.'class/ServerCredentials.class.php';
	require_once SITE_ROOT.'class/Student.class.php';

	/*
	 * Include all classes that inherits Server 
	 */
	require_once SITE_ROOT.'class/SigmaWeb.Server.class.php';
	
	abstract class Server
	{
		abstract protected function beforeFetchFromServer($serverCredentials);
		abstract protected function onFetchFromServer($serverCredentials);
		abstract protected function afterFetchFromServer($fetchResult);
		
		/**
		 * Fetch data from server
		 */
		final public function fetchFromServer($serverCredentials)
		{
			if ($serverCredentials instanceof ServerCredentials)
			{
				$this->beforeFetchFromServer($serverCredentials);
				$fetchResult = $this->onFetchFromServer($serverCredentials);
				$student = $this->afterFetchFromServer($fetchResult);
				if (($student instanceof Student) || (is_null($student)))
				{
					return $student;
				}
				else
				{
					throw new ServerException('afterFetchFromServer must return an instance of Student or null');
				}
			}
			else
			{
				throw new ServerException('$serverCredentials is not instance of ServerCredentials');
			}
		}
	}
	
	class ServerFactory
	{
		public function build($serverType)
		{
			switch ($serverType)
			{
				case 'udescsigma':
					return new SigmaWeb();
					break;
				default:
					throw new ServerException('"'.$serverType.'" does not match any Server classes');
			}
		}
	}
	
	class ServerException extends Exception {}
?>