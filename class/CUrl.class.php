<?php
	/*	curl.php
		Este código descreve a classe usada para acessar os metodos do cURL
		Essa classe tem suporte nativo a cookies e certificados SSL
	*/ 
	
	defined('VALID_ENTRY_POINT') or die('');
	
	//Limpa o diretorio de cookies
	//Deleta arquivos na pasta cookie que não foram modificados a mais de meia hora
	foreach (glob(SITE_ROOT.'curl/cookies/'."*") as $file) {
		if ($file != SITE_ROOT.'curl/cookies/index.php')
		{
			if (filemtime($file) < time() - 1800) {
				unlink($file);
			}
		}
	}
	
	class CUrl
	{
		protected $object; //Objeto do cURL
		protected $random; //Nome do arquivo de cookies
		
		
		//Constructor
		//Cria o objeto cURL e seta as principais configs
		function cUrl($certificate_name=null, $verify_peer=0, $verify_host=0)
		{
			$this->object = curl_init();
			curl_setopt($this->object, CURLOPT_RETURNTRANSFER, 1); //Forca cURL a retornar a resposta do server (ao inves de dar print)
			curl_setopt($this->object, CURLOPT_HEADER, 0); //Forca cURL a retirar os HEADERS da resposta
			
			//Cria nome do arquivo de cookies (o md5 serve para deixar o nome com um formato padrao)
			$this->random = md5(openssl_random_pseudo_bytes(32, $secure).getenv("REMOTE_ADDR"));
			if (!$secure) { die('Unable to generate secure random bytes'); }
			
			curl_setopt($this->object, CURLOPT_SSL_VERIFYPEER, $verify_peer);
			curl_setopt($this->object, CURLOPT_SSL_VERIFYHOST, $verify_host);
			if (!is_null($certificate_name))
			{
				curl_setopt($this->object, CURLOPT_CAINFO, SITE_ROOT."/curl/".$certificate_name);
			}
		}
		
		//Faz uma request POST e retorna uma string com o resultado
		//$url: string url da pagina
		//$post: array dados do request post
		function requestPost($url, $post)
		{
			//Set request parameters
			curl_setopt($this->object, CURLOPT_URL, $url);
			curl_setopt($this->object, CURLOPT_POST, 1);		
			curl_setopt($this->object, CURLOPT_POSTFIELDS, $post);
			
			//Set cookies
			curl_setopt( $this->object, CURLOPT_COOKIESESSION, true );
			curl_setopt( $this->object, CURLOPT_COOKIEJAR, SITE_ROOT."/curl/cookies/".($this->random));
			curl_setopt( $this->object, CURLOPT_COOKIEFILE, SITE_ROOT."/curl/cookies/".($this->random));
			
			//Request and return
			$result = curl_exec($this->object);
			return utf8_encode($result);
		}
		
		function requestGet($url)
		{
			//Set request parameters
			curl_setopt($this->object, CURLOPT_URL, $url);
			curl_setopt($this->object, CURLOPT_POST, 0);
			curl_setopt($this->object, CURLOPT_POSTFIELDS, "");
		
			//Set cookies
			curl_setopt( $this->object, CURLOPT_COOKIESESSION, true );
			curl_setopt( $this->object, CURLOPT_COOKIEJAR, SITE_ROOT."/curl/cookies/".($this->random));
			curl_setopt( $this->object, CURLOPT_COOKIEFILE, SITE_ROOT."/curl/cookies/".($this->random));
		
			//Request and return
			$result = curl_exec($this->object);
			return utf8_encode($result);
		}
	}
?>