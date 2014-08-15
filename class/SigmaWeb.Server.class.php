<?php
	defined('VALID_ENTRY_POINT') or die('');
	
	ini_set('default_charset','UTF-8');

	require_once SITE_ROOT.'class/Student.class.php';
	require_once SITE_ROOT.'class/Server.class.php';
	require_once SITE_ROOT.'class/CUrl.class.php';
	require_once SITE_ROOT.'class/DOM.func.php';

	class SigmaWeb extends Server
	{
		protected $name;
		protected $courses;
		
		protected function beforeFetchFromServer($serverCredentials)
		{
			/*
			 * Estima um limite de até 2 minutos para o script pegar os resultados do server
			 * Esse limite é alto pois as vezes o server pode está sobrecarregado (ex. final de semestre)
			 */
			set_time_limit(60*2);
			
			// Todo 
		}
		
		protected function onFetchFromServer($serverCredentials)
		{
			/*
			 * Cria uma nova instancia do CUrl e carrega o certificado SSL da UDESC
			 * A verificacão da validade do certificado foi desabilitada pois o certificado da UDESC está vencido desde 2006
			 */
			$request = new CUrl("UDESCCertificateAuthority.crt", 0, 2);
			
			/*
			 * Esta array é utilizada para guardar os resultados temporariamente
			 * Depois de obter todos os resultados, um objeto Student será criado e os dados alocados
			 */
			$aluno = array();
			
			/*
			 * Realiza o login no sistema do sigmaweb.cav.udesc.br
			 */
			$response = $request->requestPost("https://sigmaweb.cav.udesc.br/sw/sigmaweba.php", array(LSIST => "SigmaWeb",LUNID => "UDESC",lusid => $serverCredentials->getUsername(),luspa => $serverCredentials->getPassword(),opta => "Login"));
			if ($response == "") { throw new ServerException('Unable to fetch data from sigmaweb.cav.udesc.br'); }
			elseif ($response != '<html><head><META HTTP-EQUIV="Refresh" CONTENT="0;URL=sigmaweb0.php"></head></html>')
			{
				/* Auth failed, check the error */
				$response = str_replace("<head>", '<head><meta http-equiv="content-type" content="text/html; charset=utf-8">', $response);
				$HTML = new DOMDocument; @$HTML->loadHTML($response); @$XPATH = new DOMXPath($HTML);
				$ErrorMsg = $XPATH->query('*/td',$XPATH->query('*/table')->item(1))->item(1)->nodeValue;
				if ($ErrorMsg == "Matrícula e/ou senha inválida")
				{
					throw new ServerException('Authentication error');
				}
				else
				{
					throw new ServerException('Error message from sigmaweb.cav.udesc.br: '.$ErrorMsg);
				}
				unset($HTML); unset($XPATH);
			}
			
			/**
			 * Pega os dados basicos do aluno
			 * Nome, Centro, Matricula, TipoAluno, Semestre e Status do Sistema
			 */
			$response = $request->requestGet("https://sigmaweb.cav.udesc.br/sw/sigmaweb0.php");
			if ($response == "") { throw new ServerException('Unable to fetch data from sigmaweb.cav.udesc.br'); }
			else
			{
				$response = str_replace("<head>", '<head><meta http-equiv="content-type" content="text/html; charset=utf-8">', $response);
				$HTML = new DOMDocument; @$HTML->loadHTML($response); @$XPATH = new DOMXPath($HTML);
				$aluno['Centro'] = $XPATH->query('*/td',$XPATH->query('*/table')->item(0))->item(1)->nodeValue;
					
				$aluno['Nome'] = explode("<br>",DOMinnerHTML($XPATH->query('*/td',$XPATH->query('*/table')->item(0))->item(2))); $aluno['Nome'] = $aluno['Nome'][0];
				$aluno['Matricula'] = explode("<br>",DOMinnerHTML($XPATH->query('*/td',$XPATH->query('*/table')->item(0))->item(2))); $aluno['Matricula'] = explode(" - ", $aluno['Matricula'][1]); $aluno['Matricula'] = $aluno['Matricula'][0];
				$aluno['TipoAluno'] = explode("<br>",DOMinnerHTML($XPATH->query('*/td',$XPATH->query('*/table')->item(0))->item(2))); $aluno['TipoAluno'] = explode(" - ", $aluno['TipoAluno'][1]); $aluno['TipoAluno'] = $aluno['TipoAluno'][1];
			
					
				$aluno['Semestre'] = DOMinnerHTML($XPATH->query('td',$XPATH->query('*/tr',$XPATH->query('*/td',$XPATH->query('*/table')->item(2))->item(2))->item(4))->item(0));
				$aluno['Status'] = DOMinnerHTML($XPATH->query('td',$XPATH->query('*/tr',$XPATH->query('*/td',$XPATH->query('*/table')->item(2))->item(2))->item(5))->item(0));
				$aluno['StatusNum'] = intval(substr($aluno['Status'],8,1));
				unset($HTML); unset($XPATH);
			}
			
			/*
			 * Pega a lista de materias do aluno
			 */
			if ($request->requestGet("https://sigmaweb.cav.udesc.br/sw/sigmaweb0.php") == '') { throw new ServerException('Unable to fetch data from sigmaweb.cav.udesc.br'); }
			if ($request->requestGet("https://sigmaweb.cav.udesc.br/sw/sigmaweb1.php?var=R6645") == '') { throw new ServerException('Unable to fetch data from sigmaweb.cav.udesc.br'); }
			if ($request->requestGet("https://sigmaweb.cav.udesc.br/sw/sigmaweb4.php") == '') { throw new ServerException('Unable to fetch data from sigmaweb.cav.udesc.br'); }
			if ($request->requestGet("https://sigmaweb.cav.udesc.br/sw/sigmaweb6.php") == '') { throw new ServerException('Unable to fetch data from sigmaweb.cav.udesc.br'); }
			if ($request->requestPost("https://sigmaweb.cav.udesc.br/sw/sigmaweb7.php", array(nseme => substr($aluno['Semestre'],-6), opta => 'Avancar')) == '') { throw new ServerException('Unable to fetch data from sigmaweb.cav.udesc.br'); }
			
			$response = $request->requestGet("https://sigmaweb.cav.udesc.br/sw/sigmaweb7.php");
			if ($response == "") { throw new ServerException('Unable to fetch data from sigmaweb.cav.udesc.br'); }
			elseif(strstr($response, '>ERRO:</td>')) /* Error fetching data */
			{
				/*
				 * Isso pode ser causado por um erro no servidor, ou o aluno nao esta matriculado
				 * Basicamente, colocamos a variavel materias como NULL, de forma que o resto do programa sabe que nao ha nenhuma materia
				 */ 
				$aluno['Materias'] = null;
			}
			else
			{
				/*
				 * Faz um parse da lista de materias
				 */
				$response = str_replace("<head>", '<head><meta http-equiv="content-type" content="text/html; charset=utf-8">', $response);
				$HTML = new DOMDocument; @$HTML->loadHTML($response); @$XPATH = new DOMXPath($HTML);
				$CountTurmas = substr_count($response, "<tr><td class=btdse");
				
				$aluno['Materias'] = array();
				
				for ($TurmaID=0; $TurmaID<$CountTurmas; $TurmaID++)
				{
					$materia = array();
					$totalColspan = 0;
					for ($Coluna=0; $Coluna<=11; $Coluna++)
					{
						switch ($Coluna)
						{
							case 1: //Codigo
								$materia['Codigo'] = DOMinnerHTML($XPATH->query("/html/body/table[2]/tr[".(3+$TurmaID)."]/td")->item($Coluna - $totalColspan));
								break;
							case 2: //Turma
								$materia['Turma'] = DOMinnerHTML($XPATH->query("/html/body/table[2]/tr[".(3+$TurmaID)."]/td")->item($Coluna - $totalColspan));
								break;
							case 3: //Descricao
								$materia['Nome'] = substr(DOMinnerHTML($XPATH->query("/html/body/table[2]/tr[".(3+$TurmaID)."]/td")->item($Coluna - $totalColspan)),2);
								break;
							case 5: //Regist
								$materia['Aulas'] = DOMinnerHTML($XPATH->query("/html/body/table[2]/tr[".(3+$TurmaID)."]/td")->item($Coluna - $totalColspan));
								
								if ($materia['Aulas'] == "Sem Registro")
								{
									$materia['Aulas'] = null;
									$materia['Presencas'] = null;
								}
								break;
							case 6: //Presencas
								$materia['Presencas'] = DOMinnerHTML($XPATH->query("/html/body/table[2]/tr[".(3+$TurmaID)."]/td")->item($Coluna - $totalColspan));
								break;
							case 9: //Exame
								$materia['Exame'] = DOMinnerHTML($XPATH->query("/html/body/table[2]/tr[".(3+$TurmaID)."]/td")->item($Coluna - $totalColspan));
								if (strlen($materia['Exame']) > 4) { $materia['Exame'] = null;}
								break;
							case 10: //Media Fin
								$materia['MediaFinal'] = DOMinnerHTML($XPATH->query("/html/body/table[2]/tr[".(3+$TurmaID)."]/td")->item($Coluna - $totalColspan));
								if (strlen($materia['MediaFinal']) > 4) { $materia['MediaFinal'] = null; }
								break;
						}
						
						$colspan = $XPATH->query("/html/body/table[2]/tr[".(3+$TurmaID)."]/td")->item($Coluna - $totalColspan)->getAttribute('colspan');
						if ($colspan=="") { $colspan = 1; }
									
							
						$totalColspan += ($colspan - 1);
						$Coluna += ($colspan - 1);
					}
					$materia['Notas'] = null;
					array_push($aluno['Materias'], $materia);
				}
				unset($HTML); unset($XPATH);
			}
			
			/*
			 * Obtem os resultados parciais de cada materia
			 */
			if (($aluno['StatusNum'] >= 4) && (!is_null($aluno['Materias']))) //Verifica se o sistema nao esta em periodo de matricula e se o aluno esta matriculado
			{
				foreach ($aluno['Materias'] as &$Materia)
				{
					if ($request->requestGet("https://sigmaweb.cav.udesc.br/sw/sigmaweb1.php?var=R6655") == '') { throw new ServerException('Unable to fetch data from sigmaweb.cav.udesc.br'); }
					if ($request->requestGet("https://sigmaweb.cav.udesc.br/sw/sigmaweb4.php") == '') { throw new ServerException('Unable to fetch data from sigmaweb.cav.udesc.br'); }
					if ($request->requestGet("https://sigmaweb.cav.udesc.br/sw/sigmaweb5.php") == '') { throw new ServerException('Unable to fetch data from sigmaweb.cav.udesc.br'); }
					if ($request->requestPost("https://sigmaweb.cav.udesc.br/sw/sigmaweb7.php", array(nagru => $Materia['Codigo']."/".$Materia['Turma']."/".$aluno['Centro'], opta => 'Enter')) == '') { throw new ServerException('Unable to fetch data from sigmaweb.cav.udesc.br'); }
					
					$response = $request->requestGet("https://sigmaweb.cav.udesc.br/sw/sigmaweb7.php");
					if ($response == "") { throw new ServerException('Unable to fetch data from sigmaweb.cav.udesc.br'); }
					elseif(strstr($response, '>ERRO:</td>')) /* Error fetching data */
					{
						//Erro, parte para a proxima!
					}
					else
					{
						$NotasAluno = array(); //Variavel temporaria para guardar notas
						
						/* Carrega XML parser */
						$response = str_replace("<head>", '<head><meta http-equiv="content-type" content="text/html; charset=utf-8">', $response);
						$HTML = new DOMDocument; @$HTML->loadHTML($response); @$XPATH = new DOMXPath($HTML);
						
						/* Conta o numero de notas e calcula se elas estao dispostas em 1 ou mais linhas */
						$NumNotas = $XPATH->query("/html/body/table[2]/tr[@bgcolor='#D6D6FF']/th")->length;
						$NumLinhas = ceil(($NumNotas-5)/5);
						
						/* Carrega o Nome e Peso de cada avaliacao do cabecalho */
						for ($a=3;$a<=($NumNotas-2);$a++)
						{
							$Nota = explode("<br>",DOMinnerHTML($XPATH->query("/html/body/table[2]/tr[@bgcolor='#D6D6FF']/th")->item($a)));
							array_push($NotasAluno, array(Nome=>substr($Nota[0],1,-1), Peso=>$Nota[1]));
						}
						
						/* Procura o aluno na lista de resultados e carrega todas as suas notas*/
						$NotasAluno_Table = $XPATH->query("/html/body/table[2]/tr/td[contains(., '".utf8_encode(strtoupper($this->dadosAluno['Nome']))."')]/..")->item(0);
						for ($a=0; $a<=$NumLinhas-1;$a++)
						{
							/* Pega todos os resultados em uma mesma linha */
							for ($b=($a*5); ($b<=($a*5)+4) && ($b<=$NumNotas-4-$NumLinhas); $b++)
							{
								$NumColuna = ($b+(3*($a==0))-(5*$a));
								$NotasAluno[$b]['Nota'] = DOMinnerHTML($XPATH->query("td", $NotasAluno_Table)->item($NumColuna));
								if ($NotasAluno[$b]['Nota'] = "") { $NotasAluno[$b]['Nota'] = null; }
							}
							
							/* Pula para a proxima linha */
							if ($b<=$NumNotas-4-$NumLinhas)
							{
								$NotasAluno_Table = $XPATH->query("following-sibling::*[1]", $NotasAluno_Table)->item(0);
							}
						}
						
						//Salva dados temporarios na variavel permanente
						$Materia['Notas'] = $NotasAluno;
					}
				}
				unset($Materia);
			}
			
			return $aluno;
		}
		
		protected function afterFetchFromServer($fetchResult)
		{
			/*
			 * Coloca todos os resultados dentro de um objeto da classe Student
			 */
			
			$courses = array();
			foreach ($fetchResult['Materias'] as $Materia)
			{
				
				$frequencia = new CourseAttendance(
						intval($Materia['Aulas']), 
						intval($Materia['Presencas']), 
						(intval($Materia['Aulas'])-intval($Materia['Presencas']))
				);
				
				//Todo notas
				if (sizeof($Materia['Notas']) > 0)
				{
					$notas = array();
					foreach ($Materia['Notas'] as $nota)
					{
						//Todo notas
						array_push($notas, null);
					}
				}
				else
				{
					$notas = null;
				}
				
				
				array_push($courses, new StudentCourse($Materia['Nome'], $Materia['Codigo'], $notas, $frequencia));
			}
			
			$dados = array(
				'name'    => $fetchResult['Nome'],
				'courses' => $courses
			);
			
			return new Student($dados);
		}
	}
?>