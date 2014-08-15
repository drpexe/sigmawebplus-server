<?php 
	defined('VALID_ENTRY_POINT') or die('');
	
	require_once SITE_ROOT.'class/ServerCredentials.class.php';
	require_once SITE_ROOT.'class/Server.class.php';

	class RequestHandler
	{
		
		protected $serverType;
		protected $serverCredentials;
		protected $clientVersion;
		
		public function start()
		{
			if ($_SERVER['REQUEST_METHOD'] == 'POST')
			{
				$username = $_POST['u'];
				$password = $_POST['p'];
				$version  = $_POST['v'];
				$server   = $_POST['s'];
				$hash     = $_POST['h'];
				
				/*
				 * Validate data sent by client
				 */
				$validRequest = true;
				$validRequest = $validRequest && (!empty($username));
				$validRequest = $validRequest && (!empty($password));
				$validRequest = $validRequest && (!empty($version));
				$validRequest = $validRequest && (!empty($server));
				
				$validRequest = $validRequest && (preg_match("/^[a-zA-Z0-9]+$/", $username));
				$validRequest = $validRequest && (preg_match("/^[a-zA-Z0-9]+$/", $server));
				
				if ($validRequest)
				{ 
					try
					{
						/*
						 * Fetch data from server
						*/
						$serverFactory = new ServerFactory();
						$server = $serverFactory->build($server);
						$aluno = $server->fetchFromServer(new ServerCredentials($username, $password));
					
					
						/*
						 * Check output and return server response
						 */
						$response = $aluno->exportToXML();
						$response_hash = md5($response);
						
						if ($response_hash != $hash)
						{
							echo '<response hash="'.$response_hash.'">'.$response.'</response>';
						}
						else
						{
							echo '<response hash="'.$response_hash.'" />';
						}
					}
					catch (Exception $e)
					{
						echo '<response error="Error fetching the results" />';
					}
					
				}
				else 
				{
					//throw new RequestHandlerException("Request is not valid");
					echo '<response error="Request is not valid" />';
				}
			}
			else
			{
				//throw new RequestHandlerException("RequestHandler only accepts POST method");
				echo '<response error="RequestHandler only accepts POST method" />';
			}
		}
	}
	
	class RequestHandlerException extends Exception {}
?>