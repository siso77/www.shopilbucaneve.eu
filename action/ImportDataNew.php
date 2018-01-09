<?php
define( 'APP_ROOT', str_replace('/action', '', str_replace('\\', '/', getcwd()) ));
define( 'APPLICATION_CONFIG_FILENAME', 'config.xml' );

if(!isset($_SERVER['APPLICATION_ENV']))
	$_SERVER['APPLICATION_ENV'] = 'pro';

error_reporting(E_ERROR);
ini_set('display_errors', false);
ini_set("max_execution_time", "360000");

include_once(APP_ROOT.'/libs/Dump.php');
include_once(APP_ROOT.'/libs/INI.php');
include_once(APP_ROOT.'/libs/configureSystem.php');
include_once(APP_ROOT.'/libs/BeanBase.php');
include_once(APP_ROOT.'/libs/xml_parser.php');
include_once(APP_ROOT.'/libs/debugTime.php');
/**
 * Inclusione dello ZendCache
 */
define( 'CACHE_CONFIG_INI_PATH', APP_ROOT.'/ZendCache/' );
ini_set('include_path', APP_ROOT.'/ZendCache/');
require_once(APP_ROOT.'/ZendCache/Zend/Cache.php');
require_once(APP_ROOT.'/ZendCache/Cache.php');
/**
 * Inclusione dello ZendCache
*/
new configureSystem();

/***
 * Inizio Logica di Caricamento
 */
	$conn;
	$operator;
	$email_customer_name;	
	$separator = ';';
	$num_customers_inserted;
	$num_products_inserted;
	$num_family_inserted;
	$num_content_inserted;
	$fileCustomer;
	$fileContent;
	$fileArticle;
	$fileFamily;
	$customer_name;
	$email_customer_logo;

	$flagFile;
	
	$conn = MyDB::connect();
	$operator = 'StreamImportProcedure';
	
	$flagFile = APP_ROOT."/FlorSysIntegration/In/finito.txt";
	$flagInizioFile = APP_ROOT."/FlorSysIntegration/In/inizio.txt";
	
	if(is_file(APP_ROOT."/FlorSysIntegration/In/CLIENTI.CSV"))
		$fileCustomer = APP_ROOT."/FlorSysIntegration/In/CLIENTI.CSV";
	if(is_file(APP_ROOT."/FlorSysIntegration/In/GIACENZA.CSV"))
		$fileContent = APP_ROOT."/FlorSysIntegration/In/GIACENZA.CSV";
	if(is_file(APP_ROOT."/FlorSysIntegration/In/FAMIGLIE.CSV"))
		$fileFamily = APP_ROOT."/FlorSysIntegration/In/FAMIGLIE.CSV";
	if(is_file(APP_ROOT."/FlorSysIntegration/In/ARTICOLI.CSV"))
		$fileArticle = APP_ROOT."/FlorSysIntegration/In/ARTICOLI.CSV";
	if(is_file(APP_ROOT."/FlorSysIntegration/In/AGENTI.CSV"))
		$fileAgents = APP_ROOT."/FlorSysIntegration/In/AGENTI.CSV";
	if(is_file(APP_ROOT."/FlorSysIntegration/In/BARCODES.CSV"))
		$fileBarcodes = APP_ROOT."/FlorSysIntegration/In/BARCODES.CSV";
	if(is_file(APP_ROOT."/FlorSysIntegration/In/CATEGORIE.CSV"))
		$fileCustomerCategory = APP_ROOT."/FlorSysIntegration/In/CATEGORIE.CSV";
	
	
	$email_customer_name = PREFIX_META_TITLE;
	$email_customer_logo = WWW_ROOT.'themes/uploads/2013/03/logo1.png';
	
	$debugTime = new debugTime();
	Start($flagFile, $fileCustomer, $fileContent, $fileFamily, $fileArticle, $fileAgents, $fileBarcodes, $flagInizioFile, $fileCustomerCategory);
	$debugTime->OutPutDebugTime('Esecuzione avvenuta in sec: ');
	
	
	function Start($flagFile = null, $fileCustomer = null, $fileContent = null, $fileFamily = null, $fileArticle = null, $fileAgents = null, $fileBarcodes = null, $flagInizioFile = null, $fileCustomerCategory = NULL)
	{
		if(true)
// 		if($_REQUEST['user'] == 'admin' && $_REQUEST['pwd'] == 'f7b44cfafd5c52223d5498196c8a2e7b' && $_SERVER['HTTP_STREAM_DEMO_INTEGRATION'] == 'f7b44cfafd5c52223d5498196c8a2e7b') //pwd = md5('stream')
		{
			if(is_file($flagInizioFile))
			{
				date_default_timezone_set('Europe/Dublin');
				$start_time = date('Y-m-d H:i:s', filemtime($flagInizioFile)); // fill this in with actual time in this format
				$end_time = date('Y-m-d H:i:s'); // fill this in with actual time in this format
			
				$start = new DateTime($start_time);
				$interval = $start->diff(new DateTime($end_time));
				if($interval->h > 0 || $interval->d > 0)
				{
					unlink($flagInizioFile);
					$flagInizioFile = null;
				}
			}			
			if(is_file($flagFile) && !is_file($flagInizioFile))
			{
				$handle = fopen(APP_ROOT."/FlorSysIntegration/In/inizio.txt", "w");
				fclose($handle);
				
				storeMonitor();

// 				if(date('H') > 3)				
// 					$fileArticle = null;

				$isImport = false;
				if(!empty($fileCustomer))
				{
					chmod($fileCustomer, 0777);
					importCustomer($fileCustomer);
					unlink($fileCustomer);
					$isImport = true;
				}

				if(!empty($fileContent))
				{
					chmod($fileContent, 0777);
					importContent($fileContent);
					unlink($fileContent);
// 					copy($fileContent,  APP_ROOT."/FlorSysIntegration/In/bck/GIACENZA_".date('d_m_Y__H_i_s').".CSV");
					$isImport = true;
				}
				
//				if(!empty($fileAgents))
//				{
//					chmod($fileCustomer, 0777);
//					importAgents($fileAgents);
//					unlink($fileAgents);
//					$isImport = true;
//				}
				
				if(!empty($fileArticle))
				{
					chmod($fileArticle, 0777);
					importArticoli($fileArticle);
					//importContent($fileArticle);
					//copy($fileArticle,  APP_ROOT."/FlorSysIntegration/In/bck/ARTICOLI_".date('d_m_Y__H_i_s').".CSV");
					unlink($fileArticle);
					$isImport = true;
				}
				
				if(!empty($fileFamily))
				{
					chmod($fileFamily, 0777);
					importFamily($fileFamily);
					unlink($fileFamily);
					$isImport = true;
				}
				
				if(!empty($fileBarcodes))
				{
					chmod($fileBarcodes, 0777);
					importBarcodes($fileBarcodes);
					unlink($fileBarcodes);
					$isImport = true;
				}
				
				if(!empty($fileCustomerCategory))
				{
					chmod($fileBarcodes, 0777);
					importCustomerCategory($fileCustomerCategory);
					unlink($fileCustomerCategory);
					$isImport = true;
				}
				
				checkEternalIds();
				Base_CacheCore::getInstance()->clean();

				// CANCELLO I FILE DI SEMAFORO
				unlink($flagFile);
				unlink($flagInizioFile);
			}
		}
	}

	function sendEmailConfirmation()
	{
		global $email_customer_name;

// 		$hdrs = array("From" 	=> EMAIL_ADMIN_FROM,
// 					  "To" 			=> "siso77@gmail.com",
// 					  "Cc" 			=> "", 
// 					  "Bcc" 		=> "", 
// 					  "Subject" 	=> "Importazione Contenuti da FlorSystem per ".$this->email_customer_name,
// 					  "Date"		=> date("r")
// 					  );
// 		$this->setHeaders($hdrs);

		$headers .= 'From: Stream <".EMAIL_ADMIN_FROM.">' . "\r\n";
		$to = "siso77@gmail.com";
		mail($to, "Importazione Contenuti da FlorSystem per ".$email_customer_name,"IMPORTAZIONE AVVENUTA CON SUCCESSO", $headers);
	}
	
	function storeMonitor()
	{
		global $conn;
		$query = "UPDATE  monitor SET last_execute = '".date('Y-m-d H:i:s')."'";
		mysql_query($query, $conn->connection);
	}
	
	function destroyData($fileCustomer = null, $fileContent = null, $fileFamily = null, $fileArticle = null)
	{
		global $conn;
		global $operator;

		if(!empty($fileFamily))
			mysql_query("TRUNCATE TABLE famiglie", $conn->connection);
	}

	function checkEternalIds()
	{
		global $conn;
		
		$res = mysql_query("SELECT * FROM gruppi_merceologici", $conn->connection);
		while($row=mysql_fetch_assoc($res))
			$ids_gm[$row['id']] = $row['id'];

		$res = mysql_query("SELECT * FROM settori_merceologici", $conn->connection);
		while($row=mysql_fetch_assoc($res))
			$ids_sett[$row['id']] = $row['id'];
		
		$res = mysql_query("SELECT * FROM reparti_merceologici", $conn->connection);
		while($row=mysql_fetch_assoc($res))
			$ids_rep[$row['id']] = $row['id'];
		

		
		$result = mysql_query("SELECT * FROM giacenze GROUP BY id_gm", $conn->connection);
		while($row=mysql_fetch_assoc($result))
		{
			if(array_key_exists($row['id_gm'], $ids_gm))
				unset($ids_gm[$row['id_gm']]);
		}
		
		$result = mysql_query("SELECT * FROM giacenze GROUP BY id_settore", $conn->connection);
		while($row=mysql_fetch_assoc($result))
		{
			if(array_key_exists($row['id_settore'], $ids_sett))
				unset($ids_sett[$row['id_settore']]);
		}
		
		$result = mysql_query("SELECT * FROM giacenze GROUP BY id_reparto", $conn->connection);
		while($row=mysql_fetch_assoc($result))
		{
			if(array_key_exists($row['id_reparto'], $ids_rep))
				unset($ids_rep[$row['id_reparto']]);
		}

		foreach ($ids_gm as $id)
			mysql_query("DELETE FROM `gruppi_merceologici` WHERE id = ".$id."", $conn->connection);
		
		foreach ($ids_sett as $id)
			mysql_query("DELETE FROM `settori_merceologici` WHERE id = ".$id."", $conn->connection);
		
		foreach ($ids_rep as $id)
			mysql_query("DELETE FROM `reparti_merceologici` WHERE id = ".$id."", $conn->connection);
	}
	
	function importAgents($File)
	{
		global $conn;
		global $operator;
		global $separator;

		$fh = fopen($File, 'rb'); 
		$key = 0;
		while ( ($data = fgetcsv($fh, 1000, $separator)) !== false)
		{
			$result = mysql_query("SELECT * FROM `agenti` WHERE codice_agente = '".$data[0]."'", $conn->connection);
			if($row=mysql_fetch_assoc($result))
			{
				$query = "UPDATE `agenti`
						SET 
						codice_agente = '".mysql_real_escape_string($data[0])."',
						nominativo = '".mysql_real_escape_string($data[1])."'
					WHERE id = '".$row['id']."'";
				$result = mysql_query($query, $conn->connection);
				$query = null;
			}
			else
			{
				$query = "INSERT INTO agenti (
					codice_agente ,
					nominativo 
					) VALUES (
					'".mysql_real_escape_string($data[0])."',
					'".mysql_real_escape_string($data[1])."')";
				mysql_query($query, $conn->connection);
				$query = null;
			}
			$key++;
		}
	}
	
	function importCustomerCategory($File)
	{
		global $conn;
		global $operator;
		global $separator;
	
		$fh = fopen($File, 'rb');
		$key = 0;
		while ( ($data = fgetcsv($fh, 1000, $separator)) !== false)
		{
			$result = mysql_query("SELECT * FROM `customer_category` WHERE id_category = '".$data[0]."'", $conn->connection);
			if($row=mysql_fetch_assoc($result))
			{
				$customer_exists = $row['id'];

				$result = mysql_query("UPDATE `customer_category`
						SET
						id_category = '".mysql_real_escape_string($data[0])."',
						descrizione = '".mysql_real_escape_string($data[1])."'
					WHERE id = '".$row['id']."'", $conn->connection);
			}
			else
			{
				$query = "INSERT INTO customer_category (
					id_category,
					descrizione
					) VALUES (
					'".$data[0]."',
					'".mysql_real_escape_string($data[1])."')";
				mysql_query($query, $conn->connection);
				$query = null;
			}
			$key++;
		}
	}
	
	function importCustomer($File)
	{
		global $conn;
		global $operator;
		global $separator;

		$fh = fopen($File, 'rb'); 
		$key = 0;
		while ( ($data = fgetcsv($fh, 1000, $separator)) !== false)
		{
			$result = mysql_query("SELECT * FROM `customer` WHERE customer_code = '".$data[0]."'", $conn->connection);
			if($row=mysql_fetch_assoc($result))
			{
				$customer_exists = $row['id'];
				$result = mysql_query("UPDATE `customer`
						SET 
						ragione_sociale = '".mysql_real_escape_string($data[1])."',
						p_iva = '".mysql_real_escape_string($data[11])."',
						indirizzo = '".mysql_real_escape_string($data[2])."',
						provincia = '".mysql_real_escape_string($data[5])."',
						stato = '".mysql_real_escape_string($data[6])."',
						citta = '".mysql_real_escape_string($data[4])."',
						cap = '".mysql_real_escape_string($data[3])."',
						cellulare = '".mysql_real_escape_string($data[8])."',
						fisso = '".mysql_real_escape_string($data[7])."',
						fax = '".mysql_real_escape_string($data[9])."',
						email = '".mysql_real_escape_string($data[10])."',
						listino = '".$data[12]."',
						scorporo_iva = '".$data[13]."',
						
						data_modifica_riga = '".date('Y-m-d')."',
						operatore = '".$operator."'
					WHERE id = '".$row['id']."'", $conn->connection);
			}
			else
			{
				$query = "INSERT INTO customer (
					customer_code ,
					nome ,
					cognome ,
					codice_fiscale ,
					ragione_sociale ,
					p_iva ,
					indirizzo ,
					provincia ,
					stato ,
					citta ,
					cap ,
					cellulare ,
					fisso ,
					fax ,
					email ,
					text_spedizione ,
					indirizzo_spedizione ,
					cap_spedizione ,
					citta_spedizione ,
					provincia_spedizione ,
					stato_spedizione ,
					percentuale_sconto ,
					listino ,
					registred_from ,
					scorporo_iva,
					
					data_inserimento_riga ,
					data_modifica_riga ,
					is_active,
					operatore
					
					) VALUES (
					'".mysql_real_escape_string($data[0])."',
					'',
					'',
					'',
					'".mysql_real_escape_string($data[1])."',
					'".mysql_real_escape_string($data[11])."',
					'".mysql_real_escape_string($data[2])."',
					'".mysql_real_escape_string($data[5])."',
					'".mysql_real_escape_string($data[6])."',
					'".mysql_real_escape_string($data[4])."',
					'".mysql_real_escape_string($data[3])."',
					'".mysql_real_escape_string($data[8])."',
					'".mysql_real_escape_string($data[7])."',
					'".mysql_real_escape_string($data[9])."',
					'".mysql_real_escape_string($data[10])."',
					'',
					'',
					'',
					'',
					'',
					'',
					'',
					'".$data[12]."',
					'',
					'".$data[13]."',
					
					'".date('Y-m-d')."',
					'".date('Y-m-d')."',
					1,
					'".$operator."'
					)";
				mysql_query($query, $conn->connection);

				$query = null;
			}

			$key++;
		}
	}

	function importBarcodes($File)
	{
		global $conn;
		global $operator;
		global $separator;
		
		$fh = fopen($File, 'rb');
		$key = 0;
		while ( ($data = fgetcsv($fh, 1000, $separator)) !== false)
		{
			$result = mysql_query("SELECT * FROM barcodes WHERE codice_articolo = '".$data[0]."'", $conn->connection);

			if($row=mysql_fetch_assoc($result))
			{
				$query = "UPDATE barcodes SET codice_articolo = '".$data[0]."', bar_code = '".mysql_real_escape_string($data[1])."' WHERE id = ".$row['id'];
				mysql_query($query, $conn->connection);
			}
			else
			{
				$query = "INSERT INTO barcodes (codice_articolo,bar_code) VALUES ('".$data[0]."','".$data[1]."')";
				mysql_query($query, $conn->connection);				
			}
		}
	}
	
	function importContent($File)
	{
		global $conn;
		global $operator;
		global $separator;
		
		
		if(date('H') == '7' && (date('i') > '00' && date('i') < '20'))
		{
			mysql_query('TRUNCATE TABLE content', $conn->connection);
			mysql_query('TRUNCATE TABLE giacenze', $conn->connection);
		}		
					
		$fh = fopen($File, 'rb');
		$key = 0;
		while ( ($data = fgetcsv($fh, 1000, $separator)) !== false)
		{
//			$have_image = false;
//			if(have_image($data[1], $data[0]))
//				$have_image = true;
				
			$result = mysql_query("SELECT ecm_basket.id as id_basket, ecm_basket.key_session, ecm_basket_magazzino.id as id_ecm_magazzino, giacenze.bar_code, giacenze.quantita, ecm_basket_magazzino.quantita, ecm_basket.is_active
									FROM ecm_basket INNER JOIN ecm_basket_magazzino ON ecm_basket.id = ecm_basket_magazzino.id_basket
										 INNER JOIN giacenze ON ecm_basket_magazzino.id_magazzino = giacenze.id
									WHERE ecm_basket_magazzino.is_active = 1", $conn->connection);
			while($row=mysql_fetch_assoc($result))
			{
				if($row['bar_code'] == $data[0])
				{
					$data[6] = $data[6] - $row['quantita'];
					if($data[6] <= 0)
					{
						$dispo = $row['quantita'] + $data[6];
						if($dispo <= 0)
						{
							$query = "DELETE FROM ecm_basket_magazzino WHERE id = ".$row['id_ecm_magazzino'];
							mysql_query($query, $conn->connection);
							if(!empty($row['key_session']))
							{
								session_id($row['key_session']);
								session_start();
								$filename = APP_ROOT."/tmp/sess_".$row['key_session'];
								$handle = fopen($filename, "r");
								$contents = fread($handle, filesize($filename));
								fclose($handle);
								$session = unserialize_php($contents);
								
								unset($session[$row['key_session']]['basket']['n_carrelli']);
								unset($session[$row['key_session']]['basket']['perc_occupazione']);
			
								foreach ($session[$row['key_session']]['basket'] as $k=>$val)
								{
									if($row['bar_code'] == $val['giacenza']['bar_code'])
										unset($session[$row['key_session']]['basket'][$k]);
								}
								$sess_serialized = session_serialize($session);
									
								$fp = fopen($filename, 'w');
								fwrite($fp, $sess_serialized);
								fclose($fp);
							}
						}
						else
						{
							$query = "UPDATE ecm_basket_magazzino SET quantita = ".$dispo." WHERE id = ".$row['id_ecm_magazzino'];
							mysql_query($query, $conn->connection);
						}
					}
			
				}
			}
			
			if($data[6] < 0)
				$data[6] = 0;			

			$diametro_vaso = null;
			$altezza_pianta = null;
			$volume_singolo = null;
			$volume_sc = null;
				
			$result = mysql_query("SELECT * FROM gruppi_merceologici WHERE gruppo = '".$data[8]."'", $conn->connection);
			if(!$row=mysql_fetch_assoc($result))
				mysql_query("INSERT INTO gruppi_merceologici (gruppo) VALUES ('" . $data[8]. "')", $conn->connection);
			
			$result = mysql_query("SELECT * FROM gruppi_merceologici WHERE gruppo = '".$data[8]."'", $conn->connection);
			if($row=mysql_fetch_assoc($result))
				$id_gm = $row['id'];
				
			$result = mysql_query("SELECT * FROM famiglie WHERE famiglia = '".$data[9]."'", $conn->connection);
			if(!$row=mysql_fetch_assoc($result))
				mysql_query("INSERT INTO famiglie (codice_famiglia, famiglia) VALUES ('".substr($data[9], 0, 3)."', '".$data[9]."')", $conn->connection);
			$result = mysql_query("SELECT * FROM famiglie WHERE famiglia = '".$data[9]."'", $conn->connection);
			if($row=mysql_fetch_assoc($result))
				$id_famiglia = $row['id'];

			$result = mysql_query("SELECT * FROM settori_merceologici WHERE settore = '".$data[4]."'", $conn->connection);
			if(!$row=mysql_fetch_assoc($result))
				mysql_query("INSERT INTO settori_merceologici (settore) VALUES ('".$data[4]."')", $conn->connection);
			$result = mysql_query("SELECT * FROM settori_merceologici WHERE settore = '".$data[4]."'", $conn->connection);
			if($row=mysql_fetch_assoc($result))
				$id_settore = $row['id'];

			$result = mysql_query("SELECT * FROM reparti_merceologici WHERE reparto = '".$data[5]."'", $conn->connection);
			if(!$row=mysql_fetch_assoc($result))
				mysql_query("INSERT INTO reparti_merceologici (reparto) VALUES ('".$data[5]."')", $conn->connection);
			$result = mysql_query("SELECT * FROM reparti_merceologici WHERE reparto = '".$data[5]."'", $conn->connection);
			if($row=mysql_fetch_assoc($result))
				$id_reparto = $row['id'];

			$query_upd_content = null;
			$res = mysql_query("SELECT * FROM content WHERE vbn = '".$data[1]."'", $conn->connection);
			$exists_content = null;
			if($r=mysql_fetch_assoc($res))
			{
				$query_upd_content = "UPDATE content SET
						nome_it = '".mysql_real_escape_string(str_replace(',', '.', $data[2]))."',
						descrizione_it = '".mysql_real_escape_string(str_replace(',', '.', $data[2]))."',
						nome_en = '".mysql_real_escape_string(str_replace(',', '.', $data[2]))."',
						descrizione_en = '".mysql_real_escape_string(str_replace(',', '.', $data[2]))."',								
						id_gm = '".$id_gm."',
						id_famiglia = '".$id_famiglia."',
						id_settore = '".$id_settore."',
						id_reparto = '".$id_reparto."',
						prezzo_0 = '".str_replace(',', '.', $data[12])."',
						prezzo_1 = '".str_replace(',', '.', $data[14])."',
						prezzo_2 = '".str_replace(',', '.', $data[15])."',
						prezzo_3 = '".str_replace(',', '.', $data[16])."',
						prezzo_4 = '".str_replace(',', '.', $data[17])."',
						prezzo_5 = '".str_replace(',', '.', $data[18])."',
						prezzo_6 = '".str_replace(',', '.', $data[19])."',
						prezzo_7 = '".str_replace(',', '.', $data[20])."',
						prezzo_8 = '".str_replace(',', '.', $data[21])."',
						prezzo_9 = '".str_replace(',', '.', $data[22])."',
						qta_min_ord =  '".str_replace(',', '.', $data[11])."',
						qta_scatola =  '".str_replace(',', '.', $data[11])."',
						cod_iva = '".$data[13]."',
						have_image = ".((int)$have_image)."
				WHERE id = ".$r['id'];
				mysql_query($query_upd_content, $conn->connection);

				$exists_content = $r;
				$query_upd_content = null;
			}
			if(empty($exists_content))
			{
				$query = "INSERT INTO content (
					vbn,
					nome_it,
					descrizione_it,
					nome_en,
					descrizione_en,
					id_gm,
					id_famiglia,
					id_settore,
					id_reparto,
					C1,
					C2,
					C3,
					C4,
					C5,
					tipo_colore,
					prezzo_0,
					prezzo_1,
					prezzo_2,
					prezzo_3,
					prezzo_4,
					prezzo_5,
					prezzo_6,
					prezzo_7,
					prezzo_8,
					prezzo_9,
					cod_iva,
					have_image,
					is_active,
					data_inserimento_riga,
					data_modifica_riga,
					qta_min_ord,
					qta_scatola,
						
					operatore) VALUES
					('".$data[1]."',
					'".mysql_real_escape_string($data[2])."',
					'".mysql_real_escape_string($data[2])."',
					'".mysql_real_escape_string($data[2])."',
					'".mysql_real_escape_string($data[2])."',
					".$id_gm.",
					".$id_famiglia.",
					'".$id_settore."',
					'".$id_reparto."',
					'".str_replace(',', '.', $data[3])."',
					'".str_replace(',', '.', $data[4])."',
					'".str_replace(',', '.', $data[5])."',
					'".str_replace(',', '.', $data[7])."',
					'',
					'',
					'".str_replace(',', '.', $data[12])."',
					'".str_replace(',', '.', $data[14])."',
					'".str_replace(',', '.', $data[15])."',
					'".str_replace(',', '.', $data[16])."',
					'".str_replace(',', '.', $data[17])."',
					'".str_replace(',', '.', $data[18])."',
					'".str_replace(',', '.', $data[19])."',
					'".str_replace(',', '.', $data[20])."',
					'".str_replace(',', '.', $data[21])."',
					'".str_replace(',', '.', $data[22])."',
					'".$data[13]."',
					".((int)$have_image).",
					1,
					'".date('Y-m-d H:i:s')."',
					'".date('Y-m-d H:i:s')."',
					'".str_replace(',', '.', $data[11])."',
					'".str_replace(',', '.', $data[11])."',
					'".$operator."_articoli')";
				mysql_query($query, $conn->connection);

				$rres = mysql_query("SELECT MAX(id) as last_id FROM content", $conn->connection);
				if($rrow=mysql_fetch_assoc($rres))
					$exists_content['id'] = $rrow['last_id'];
			}

			$idContent = $exists_content['id'];
			$nota = null;
			$colori=null;
			$exp = null;
			$exp_colore = null;
			if(!empty($data[5]))
			{
				$exp = explode('|', $data[5]);
				$exp_colore = explode('@', $exp[1]);
				foreach ($exp_colore as $val)
					$colori .= $val.'|';
				$nota = $exp[0];
			}
			
			
			$result = mysql_query("SELECT * FROM giacenze WHERE bar_code = '".$data[0]."' ORDER BY bar_code DESC", $conn->connection);
			if($row=mysql_fetch_assoc($result))
			{
				$query = "UPDATE giacenze SET 
						quantita = ".$data[11].", 
						quantita_mazzo = ".$data[11].",
		
						descrizione = '".mysql_real_escape_string(str_replace(',', '.', $data[2]))."',
						disponibilita = '".$data[10]."', 
						stato = '".$data[9]."', 
						note = '".mysql_real_escape_string($nota)."', 
						diametro_vaso = '".$diametro_vaso."',
						altezza_pianta = '".$altezza_pianta."',
						volume_singolo = '".$volume_singolo."',
						volume_sc = '".$volume_sc."',
								
						carrello = '".$data[30]."',
						id_gm = '".$id_gm."',
						id_famiglia = '".$id_famiglia."',
						id_settore = '".$id_settore."',
						id_reparto = '".$id_reparto."',
						id_content = '".$idContent."',		
						prezzo_0 = '".$data[12]."',
						prezzo_1 = '".$data[14]."' ,
						prezzo_2 = '".$data[15]."' ,
						prezzo_3 = '".$data[16]."' ,
						prezzo_4 = '".$data[17]."' ,
						prezzo_5 = '".$data[18]."' ,
						prezzo_6 = '".$data[19]."' ,
						prezzo_7 = '".$data[20]."' ,
						prezzo_8 = '".$data[21]."' ,
						prezzo_9 = '".$data[22]."',
						cod_iva = '".$data[13]."', 
						colori = '".mysql_real_escape_string($colori)."'
						WHERE id = ".$row['id'];
				mysql_query($query, $conn->connection);

				$query = null;				
			}
			else
			{
				$query = "INSERT INTO  giacenze (
							bar_code ,
							descrizione,
							C1 ,
							C2 ,
							C3 ,
							C4 ,
							C5 ,
							diametro_vaso,
							altezza_pianta,
							volume_singolo,
							volume_sc,
							prezzo_0 ,
							prezzo_1 ,
							prezzo_2 ,
							prezzo_3 ,
							prezzo_4 ,
							prezzo_5 ,
							prezzo_6 ,
							prezzo_7 ,
							prezzo_8 ,
							prezzo_9 ,
							id_gm ,
							id_famiglia ,
							id_settore ,
							id_reparto ,
							quantita ,
							quantita_mazzo ,
							disponibilita ,
							unita_misura ,
							stato ,
							note ,
							cod_iva ,
							have_image ,
							carrello ,
							colori , 
							data_inserimento_riga ,
							data_modifica_riga ,
							is_active ,
							operatore,
							id_content
						)
					VALUES (";
					$query .= "'".$data[0]."',"; // BAR CODE
					$query .= "'".mysql_real_escape_string($data[2])."',"; // DESC ART
					$query .= "'".$data[3]."',"; // C1
					$query .= "'".$data[4]."' ,"; // C2
					$query .= "'".$data[5]."' ,"; // C3
					$query .= "'".$data[7]."' ,"; // C4
					$query .= "'".$data[300]."' ,"; // C5
					$query .= "'".$diametro_vaso."' ,"; // DIAMETRO VASO
					$query .= "'".$altezza_pianta."' ,"; // ALTEZZA PIANTA
					$query .= "'".$volume_singolo."' ,"; // VOLUME SINGOLO
					$query .= "'".$volume_sc."' ,"; // VOLUME SCATOLA
					$query .= "'".$data[12]."',"; // PREZZO 0
					$query .= "'".$data[14]."' ,"; // PREZZO 1
					$query .= "'".$data[15]."' ,"; // PREZZO 2
					$query .= "'".$data[16]."' ,"; // PREZZO 3
					$query .= "'".$data[17]."' ,"; // PREZZO 4
					$query .= "'".$data[18]."' ,"; // PREZZO 5
					$query .= "'".$data[19]."' ,"; // PREZZO 6
					$query .= "'".$data[20]."' ,"; // PREZZO 7
					$query .= "'".$data[21]."' ,"; // PREZZO 8
					$query .= "'".$data[22]."' ,"; // PREZZO 9
					$query .= "'".$id_gm."' ,"; // GRUPPO
					$query .= "'".$id_famiglia."' ,"; // FAMIGLIA
					$query .= "'".$id_settore."' ,"; // SETTORE
					$query .= "'".$id_reparto."' ,"; // REPARTO

					$query .= "'".$data[11]."' ,"; // QUANTITA MAZZO
					$query .= "'".$data[11]."',"; // QUANTITA CONF
					
					$query .= "'".$data[10]."',"; // DISPONIBILITA
					$query .= "'".$data[23]."',"; // UNITA MISURA
					$query .= "'".$data[28]."',"; // STATO
					$query .= "'".mysql_real_escape_string($nota)."',"; // NOTE
					$query .= "'".$data[13]."',"; // COD IVA
					$query .= "'".(int)$have_image."',";
					
					$query .= "'".$data[30]."', ";// CARRELLO
					$query .= "'".mysql_real_escape_string($colori)."',";// COLORI
					
					$query .= "'".date('Y-m-d H:i:s')."',";  
					$query .= "'".date('Y-m-d H:i:s')."', ";
					$query .= "'1',"; // IS ACTIVE
					$query .= "'".$operator."',";
					$query .= $idContent;
				$query .= ")";
		
				mysql_query($query, $conn->connection);
				$query = null;
			}
				
			$nota = null;
			$id_gm = null;
			$id_content = null;
			$id_famiglia = null;
			$id_reparto = null;
			$id_settore = null;
			$key++;
		}
		fclose($fh);
		
// 		_dump('Sono stati importati '.$key.' contenuti!');
		return true;

	}
		
	function importArticoli($File)
	{
		global $conn;
		global $operator;
		global $separator;

		if(date('H') != '00')
			return false;

		$fh = fopen($File, 'rb');
		$key = 0;
		while ( ($data = fgetcsv($fh, 1000, $separator)) !== false)
		{
			$have_image = false;
			if(have_image($data[0], $data[0]))
				$have_image = true;

			$result = mysql_query("SELECT * FROM gruppi_merceologici WHERE gruppo = '".$data[4]."'", $conn->connection);
			if(!$row=mysql_fetch_assoc($result))
				mysql_query("INSERT INTO gruppi_merceologici (gruppo) VALUES ('" . $data[4]. "')", $conn->connection);
			$result = mysql_query("SELECT * FROM gruppi_merceologici WHERE gruppo = '".$data[4]."'", $conn->connection);
			if($row=mysql_fetch_assoc($result))
				$id_gm = $row['id'];
				
			$result = mysql_query("SELECT * FROM famiglie WHERE famiglia = '".$data[5]."'", $conn->connection);
			if(!$row=mysql_fetch_assoc($result))
				mysql_query("INSERT INTO famiglie (codice_famiglia, famiglia) VALUES ('".substr($data[5], 0, 3)."', '".$data[5]."')", $conn->connection);
			$result = mysql_query("SELECT * FROM famiglie WHERE famiglia = '".$data[5]."'", $conn->connection);
			if($row=mysql_fetch_assoc($result))
				$id_famiglia = $row['id'];
			
// 			$result = mysql_query("SELECT * FROM settori_merceologici WHERE settore = '".$data[4]."'", $conn->connection);
// 			if(!$row=mysql_fetch_assoc($result))
// 				mysql_query("INSERT INTO settori_merceologici (settore) VALUES ('".$data[4]."')", $conn->connection);
// 			$result = mysql_query("SELECT * FROM settori_merceologici WHERE settore = '".$data[4]."'", $conn->connection);
// 			if($row=mysql_fetch_assoc($result))
// 				$id_settore = $row['id'];
			
// 			$result = mysql_query("SELECT * FROM reparti_merceologici WHERE reparto = '".$data[5]."'", $conn->connection);
// 			if(!$row=mysql_fetch_assoc($result))
// 				mysql_query("INSERT INTO reparti_merceologici (reparto) VALUES ('".$data[5]."')", $conn->connection);
// 			$result = mysql_query("SELECT * FROM reparti_merceologici WHERE reparto = '".$data[5]."'", $conn->connection);
// 			if($row=mysql_fetch_assoc($result))
// 				$id_reparto = $row['id'];

			
			$result = mysql_query("SELECT * FROM content WHERE vbn = '".$data[0]."'", $conn->connection);
			if($row=mysql_fetch_assoc($result))
			{
				$query = "UPDATE  content SET  
						id_gm =  '".$id_gm."',
						id_famiglia =  '".$id_famiglia."',
						id_settore =  '".$id_settore."',
						id_reparto =  '".$id_reparto."',
						nome_it =  '".$data[1]."',
						descrizione_it =  '".$data[1]."',
						nome_en =  '".$data[1]."',
						descrizione_en =  '".$data[1]."',
						vbn =  '".$data[0]."',
						prezzo_0 =  '".str_replace(',', '.', $data[12])."',
						prezzo_1 =  '".str_replace(',', '.', $data[14])."',
						prezzo_2 =  '".str_replace(',', '.', $data[15])."',
						prezzo_3 =  '".str_replace(',', '.', $data[16])."',
						prezzo_4 =  '".str_replace(',', '.', $data[17])."',
						prezzo_5 =  '".str_replace(',', '.', $data[18])."',
						prezzo_6 =  '".str_replace(',', '.', $data[19])."',
						prezzo_7 =  '".str_replace(',', '.', $data[20])."',
						prezzo_8 =  '".str_replace(',', '.', $data[21])."',
						prezzo_9 =  '".str_replace(',', '.', $data[22])."',
						qta_min_ord =  '".str_replace(',', '.', $data[7])."',
						qta_scatola =  '".str_replace(',', '.', $data[8])."',
								
						operatore =  '".$operator."_articoli' 
						WHERE  content.id =".$row['id'].";";
				mysql_query($query, $conn->connection);
			}
			else
			{
				$query = "INSERT INTO content (
					vbn,
					nome_it,
					descrizione_it,
					nome_en,
					descrizione_en,
					id_gm,
					id_famiglia,
					id_settore,
					id_reparto,
					C1,
					C2,
					C3,
					C4,
					C5,
					tipo_colore,
					prezzo_0,
					prezzo_1,
					prezzo_2,
					prezzo_3,
					prezzo_4,
					prezzo_5,
					prezzo_6,
					prezzo_7,
					prezzo_8,
					prezzo_9,
					cod_iva,
						
					qta_min_ord,
					qta_scatola,
						
					have_image,
					is_active,
					data_inserimento_riga,
					data_modifica_riga,
					operatore) VALUES
					('".$data[0]."',
					'".mysql_real_escape_string($data[1])."',
					'".mysql_real_escape_string($data[1])."',
					'".mysql_real_escape_string($data[1])."',
					'".mysql_real_escape_string($data[1])."',
					".$id_gm.",
					".$id_famiglia.",
					'".$id_settore."',
					'".$id_reparto."',
					'',
					'',
					'',
					'',
					'',
					'',
					'".str_replace(',', '.', $data[12])."',
					'".str_replace(',', '.', $data[14])."',
					'".str_replace(',', '.', $data[15])."',
					'".str_replace(',', '.', $data[16])."',
					'".str_replace(',', '.', $data[17])."',
					'".str_replace(',', '.', $data[18])."',
					'".str_replace(',', '.', $data[19])."',
					'".str_replace(',', '.', $data[20])."',
					'".str_replace(',', '.', $data[21])."',
					'".str_replace(',', '.', $data[22])."',
					'',
							
					'".str_replace(',', '.', $data[7])."',
					'".str_replace(',', '.', $data[8])."',
							
					".((int)$have_image).",
					1,
					'".date('Y-m-d H:i:s')."',
					'".date('Y-m-d H:i:s')."',
					'".$operator."_articoli')";
				mysql_query($query, $conn->connection);				
			}
			$id_gm = null;
			$id_famiglia = null;
				
			$key++;
		}
		return true;
	}

	/***
	 * Funzioni per la ricerca delle immagini sui prodotti
	 */
	function have_image($vbn, $bar_code)
	{
		/***
		 * Logica per il settaggio del parametro dell'immagine
		*/
 		$imageVbn = getImageFromVbn($vbn);
		$imageBarCode = dbGetImageFromBarCode($bar_code);
		$imageCustom = dbGetImageProductFromBarCode($bar_code);
		/***
		 * Logica per il settaggio del parametro dell'immagine
		*/
		if(!empty($imageVbn))
			return true;
		if(!empty($imageBarCode))
			return true;
		if(!empty($imageCustom))
			return true;
		
		return false;
	}	

	function getImageFromVbn($vbn)
	{
		$file = WWW_VBN_IMAGE_PAHT.'/vbn_images/'.$vbn.'.jpg';
		$file_headers = @get_headers($file);
	
		if($file_headers[0] == 'HTTP/1.1 404 Not Found')
			return false;
		else
			return WWW_VBN_IMAGE_PAHT.'/vbn_images/'.$vbn.'.jpg';
	}
	
	function dbGetImageFromBarCode($bar_code, $dimension = 'Small_')
	{
		include_once(APP_ROOT.'/beans/images_giacenze.php');
	
		$BeanImages = new images_giacenze();
		$images = $BeanImages->dbGetAllByBarCode(MyDB::connect(), $bar_code);
	
		if(!empty($images[0]['name']))
			return $images;
		else
			return false;
	}
	
	function dbGetImageProductFromBarCode($bar_code)
	{
		if(is_file(APP_ROOT.'/FlorSysIntegration/img/'.$bar_code.'.jpg'))
			return WWW_ROOT.'FlorSysIntegration/img/'.$bar_code.'.jpg';
		else
			return false;
	}
?>