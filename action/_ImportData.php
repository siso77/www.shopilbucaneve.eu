<?php
error_reporting(E_ERROR);
ini_set('display_errors', 'On');
ini_set("max_execution_time", "360000");

include_once(APP_ROOT."/libs/ext/Excel/reader.php");
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/gruppi_merceologici.php");
include_once(APP_ROOT."/beans/content_extra.php");
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT."/beans/customer.php");
include_once(APP_ROOT."/beans/famiglie.php");
include_once(APP_ROOT."/beans/giacenze.php");

class ImportData extends DBSmartyMailAction
{
	private $separator = ';';
	private $operator;
	private $num_customers_inserted;
	private $num_products_inserted;
	private $num_family_inserted;
	private $num_content_inserted;
	private $fileCustomer;
	private $fileContent;
	private $fileArticle;
	private $fileFamily;
	private $customer_name;
	private $email_customer_logo;
	private $flagFile;
	
	function __construct()
	{
		$this->flagFile = APP_ROOT."/FlorSysIntegration/In/finito.txt";
		
		if(is_file(APP_ROOT."/FlorSysIntegration/In/CLIENTI.CSV"))
			$this->fileCustomer = APP_ROOT."/FlorSysIntegration/In/CLIENTI.CSV";
		if(is_file(APP_ROOT."/FlorSysIntegration/In/GIACENZA.CSV"))
			$this->fileContent = APP_ROOT."/FlorSysIntegration/In/GIACENZA.CSV";
		if(is_file(APP_ROOT."/FlorSysIntegration/In/FAMIGLIE.CSV"))
			$this->fileFamily = APP_ROOT."/FlorSysIntegration/In/FAMIGLIE.CSV";
		if(is_file(APP_ROOT."/FlorSysIntegration/In/ARTICOLI.CSV"))
			$this->fileArticle = APP_ROOT."/FlorSysIntegration/In/ARTICOLI.CSV";

		$this->email_customer_name = PREFIX_META_TITLE;
		$this->email_customer_logo = WWW_ROOT.'themes/uploads/2013/03/logo1.png';
		$this->Start();
	}
	
	function Start()
	{
		parent::DBSmartyMailAction();
		
// 		if(true)
		if($_REQUEST['user'] == 'admin' && $_REQUEST['pwd'] == 'f7b44cfafd5c52223d5498196c8a2e7b' && $_SERVER['HTTP_STREAM_DEMO_INTEGRATION'] == 'f7b44cfafd5c52223d5498196c8a2e7b')
		{
			if(is_file($this->flagFile))
			{
				//pwd = md5('stream')
				$this->operator = 'StreamImportProcedure';
	
				if(date('H') > 2)
					$this->fileArticle = null;
	
				$this->destroyData();
				$isImport = false;
	
				if(!empty($this->fileArticle))
				{
					chmod($this->fileArticle, 0777);
					$this->importArticoli($this->fileArticle);
					copy($this->fileArticle, APP_ROOT."/FlorSysIntegration/In/bck/ARTICOLI_".date('d_m_Y__H_i_s').".CSV");
					unlink($this->fileArticle);
					$isImport = true;
				}
				
				if(!empty($this->fileFamily))
				{
					chmod($this->fileFamily, 0777);
					$this->importFamily($this->fileFamily);
					copy($this->fileFamily, APP_ROOT."/FlorSysIntegration/In/bck/FAMIGLIE_".date('d_m_Y__H_i_s').".CSV");
					unlink($this->fileFamily);
					$isImport = true;
				}
				
				if(!empty($this->fileCustomer))
				{
					chmod($this->fileCustomer, 0777);
					$this->importCustomer($this->fileCustomer);
					copy($this->fileCustomer, APP_ROOT."/FlorSysIntegration/In/bck/CLIENTI_".date('d_m_Y__H_i_s').".CSV");
					unlink($this->fileCustomer);
					$isImport = true;
				}
				if(!empty($this->fileContent))
				{
					chmod($this->fileContent, 0777);
					$this->importContent($this->fileContent);
					copy($this->fileContent,  APP_ROOT."/FlorSysIntegration/In/bck/GIACENZA_".date('d_m_Y__H_i_s').".CSV");
					unlink($this->fileContent);
					$isImport = true;
				}
	
				if($isImport)
				{
					$this->sendEmailConfirmation();
					Base_CacheCore::getInstance()->clean();
					
					$configCacheKey = 'giacenze';
					if (!$giacenze = Base_CacheCore::getInstance()->load($configCacheKey))
					{
						$BeanGiacenze = new giacenze();
						$giacenze = $BeanGiacenze->dbGetAllIdKey(MyDB::connect());
					
						if(!empty($giacenze) && CACHE_PRODUCTS)
							Base_CacheCore::getInstance()->save($giacenze, $configCacheKey);
					}
	
					$configCacheKey = 'content';
					if (!$contenuti = Base_CacheCore::getInstance()->load($configCacheKey))
					{
						$BeanContent = new content();
						$contenuti = $BeanContent->dbGetAllIdKey(MyDB::connect());
					
						if(!empty($contenuti) && CACHE_PRODUCTS)
							Base_CacheCore::getInstance()->save($contenuti, $configCacheKey);
					}
					
					$configCacheKey = 'ecm_content_search_disp'.md5('LIMIT 0,6');
					if (!$content = Base_CacheCore::getInstance()->load($configCacheKey))
					{
						$content = $BeanContent->dbSearchDisponibili($this->conn, $where.$order.$this->limit);
						if(!empty($content) && CACHE_PRODUCTS)
							Base_CacheCore::getInstance()->save($content, $configCacheKey);
					}				
				}
				// CANCELLO IL FILE SEMAFORO
				unlink($this->flagFile);
			}
		}
	}

	function sendEmailConfirmation()
	{
		$hdrs = array("From" 	=> EMAIL_ADMIN_FROM,
					  "To" 			=> "siso77@gmail.com",
					  "Cc" 			=> "", 
					  "Bcc" 		=> "", 
					  "Subject" 	=> "Importazione Contenuti da FlorSystem per ".$this->email_customer_name,
					  "Date"		=> date("r")
					  );
		$this->setHeaders($hdrs);

		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<html>
				<HEAD>
					<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
				    <title>Import Content</title>
				</HEAD>
				<body style="background-color:#fff;">
				<table width="100%" height="100%" border="0" cellspacing="10" style="">
				<tr>
					<td width="50" style="color:#000;font-size:22px;"><img src="'.$this->email_customer_logo.'"></td>
					<td align="left" style="color:#fff;font-size:22px;color: #999;font-weight: bold;"></td>
				</tr>';
		$html .= '<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;">
						Riepilogo importazione<br>
						Clienti : '.$this->num_customers_inserted.'<br>
						Contenuti: '.$this->num_products_inserted.'<br>
						Articoli: '.$this->num_content_inserted.'<br>
						Famiglie: '.$this->num_family_inserted.'<br>
						Ora: '.date('H').'
					</td>
				</tr>
			</table>
			</body>
			</html>';

		$this->setHtmlText($html);
		$this->mail_factory();
		$to = "siso77@gmail.com";
		$is_send = $this->sendMail($to);
	}
	
	function sendEmailError()
	{
		$hdrs = array("From" 	=> EMAIL_ADMIN_FROM,
					  "To" 			=> "siso77@gmail.com",
					  "Cc" 			=> "", 
					  "Bcc" 		=> "", 
					  "Subject" 	=> "[ERROR] Importazione Contenuti da FlorSystem per ".$this->email_customer_name,
					  "Date"		=> date("r")
					  );
		$this->setHeaders($hdrs);

		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<html>
				<HEAD>
					<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
				    <title>Import Content</title>
				</HEAD>
				<body style="background-color:#fff;">
				<table width="100%" height="100%" border="0" cellspacing="10" style="">
				<tr>
					<td width="50" style="color:#000;font-size:22px;"><img src="'.$this->email_customer_logo.'"></td>
					<td align="left" style="color:#fff;font-size:22px;color: #999;font-weight: bold;"></td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;">
						ATTENZIONE NON SONO STATI TROVATI I FILE CSV DA FLOR SISTEMI
					</td>
				</tr>
			</table>
			</body>
			</html>';

		$this->setHtmlText($html);
		$this->mail_factory();
		$to = "siso77@gmail.com";
		$is_send = $this->sendMail($to);
	}

	function sendEmailStart()
	{
		$hdrs = array("From" 	=> EMAIL_ADMIN_FROM,
				"To" 			=> "siso77@gmail.com",
				"Cc" 			=> "",
				"Bcc" 		=> "",
				"Subject" 	=> "[START] Importazione Contenuti da FlorSystem per ".$this->email_customer_name,
				"Date"		=> date("r")
		);
		$this->setHeaders($hdrs);
	
		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<html>
				<HEAD>
					<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
				    <title>Import Content</title>
				</HEAD>
				<body style="background-color:#fff;">
				<table width="100%" height="100%" border="0" cellspacing="10" style="">
				<tr>
					<td width="50" style="color:#000;font-size:22px;"><img src="'.$this->email_customer_logo.'"></td>
					<td align="left" style="color:#fff;font-size:22px;color: #999;font-weight: bold;"></td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;">
						PROCEDURA PARTITA CORRETTAMENTE
					</td>
				</tr>
			</table>
			</body>
			</html>';
	
		$this->setHtmlText($html);
		$this->mail_factory();
	
	
		$to = "siso77@gmail.com";
		$is_send = $this->sendMail($to);
	}

	function sendEmailEnd()
	{
		$hdrs = array("From" 	=> EMAIL_ADMIN_FROM,
				"To" 			=> "siso77@gmail.com",
				"Cc" 			=> "",
				"Bcc" 		=> "",
				"Subject" 	=> "[END] Importazione Contenuti da FlorSystem per ".$this->email_customer_name,
				"Date"		=> date("r")
		);
		$this->setHeaders($hdrs);
	
		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<html>
				<HEAD>
					<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
				    <title>Import Content</title>
				</HEAD>
				<body style="background-color:#fff;">
				<table width="100%" height="100%" border="0" cellspacing="10" style="">
				<tr>
					<td width="50" style="color:#000;font-size:22px;"><img src="'.$this->email_customer_logo.'"></td>
					<td align="left" style="color:#fff;font-size:22px;color: #999;font-weight: bold;"></td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;">
						PROCEDURA TERMINATA CORRETTAMENTE
					</td>
				</tr>
			</table>
			</body>
			</html>';
	
		$this->setHtmlText($html);
		$this->mail_factory();
	
	
		$to = "siso77@gmail.com";
		$is_send = $this->sendMail($to);
	}	

	function __destruct()
	{
// 		$this->sendEmailEnd();
	}
	
	function importCustomer($File)
	{
		$data = file($File);

		foreach ($data as $key => $value)
		{
			$exp = explode($this->separator, $value);

			$BeanCustomer = new customer();
			$BeanCustomer->setCustomer_code($exp[0]);
			$BeanCustomer->setRagione_sociale($exp[1]);
			$BeanCustomer->setIndirizzo($exp[2]);
			$BeanCustomer->setIndirizzo_spedizione($exp[2]);
			$BeanCustomer->setCap($exp[3]);
			$BeanCustomer->setCap_spedizione($exp[3]);
			$BeanCustomer->setCitta($exp[4]);
			$BeanCustomer->setCitta_spedizione($exp[4]);
			$BeanCustomer->setProvincia($exp[5]);
			$BeanCustomer->setProvincia_spedizione($exp[5]);
			$BeanCustomer->setStato($exp[6]);
			$BeanCustomer->setStato_spedizione($exp[6]);
			$BeanCustomer->setFisso($exp[7]);
			$BeanCustomer->setCellulare($exp[8]);
			$BeanCustomer->setFax($exp[9]);
			$BeanCustomer->setEmail($exp[10]);
			$BeanCustomer->setP_iva($exp[11]);
			$BeanCustomer->setListino($exp[12]);
			$BeanCustomer->setOperatore($this->operator);
			$BeanCustomer->dbStore($this->conn);
		}

		$this->num_customers_inserted = $key;
		
//		_dump('Sono stati importati '.$this->num_customers_inserted.' clienti!');
	}
	
	function destroyData()
	{
		if(!empty($this->fileCustomer))
		{
			$BeanCustomer = new customer();
			$BeanCustomer->dbDeleteAll($this->conn);
			mysql_query('DROP TABLE customer_seq', $this->conn->connection);
		}
		
		if(!empty($this->fileFamily))
		{
			$BeanFamiglie = new famiglie();
			$BeanFamiglie->dbDeleteAll($this->conn);
			mysql_query('DROP TABLE famiglie_seq', $this->conn->connection);
		}

		if(!empty($this->fileArticle))
		{
			$BeanContent = new content();
			$BeanContent->dbDeleteAll($this->conn, " WHERE operatore = '".$this->operator.'_articoli'."'");
			mysql_query('DROP TABLE content_seq', $this->conn->connection);
		}
		
		if(!empty($this->fileContent))
		{
			$BeanContent = new content();
			$BeanContent->dbDeleteAll($this->conn, " WHERE operatore = '".$this->operator."'");
// 			mysql_query('DROP TABLE content_seq', $this->conn->connection);
			
			$BeanGrippiMerceologici = new gruppi_merceologici();
			$BeanGrippiMerceologici->dbDeleteAll($this->conn);
			mysql_query('DROP TABLE gruppi_merceologici_seq', $this->conn->connection);
			
			$BeanContentExtra = new content_extra();
			$BeanContentExtra->dbDeleteAll($this->conn);
			mysql_query('DROP TABLE content_extra_seq', $this->conn->connection);
			
			$BeanGiacenze = new giacenze();
			$BeanGiacenze->dbDeleteAll($this->conn);
			mysql_query('DROP TABLE giacenze_seq', $this->conn->connection);
			
			$BeanFamiglie = new famiglie();
			$BeanFamiglie->dbDeleteAll($this->conn);
			mysql_query('DROP TABLE famiglie_seq', $this->conn->connection);
		}
	}
	
	function importFamily($File)
	{
		$key = 0;
		$fh = fopen($File, 'rb');
		while ( ($data = fgetcsv($fh, 1000, $this->separator)) !== false)
		{
			$BeanFamiglie = new famiglie();
			$BeanFamiglie->setFamiglia($data[1]);
			$BeanFamiglie->setCodice_famiglia($data[0]);

			$existFamily = $BeanFamiglie->dbGetByFamiglia($this->conn, $data[1]);
			if(empty($existFamily))
			{
				$idFamiglia = $BeanFamiglie->dbStore($this->conn);
			}
			else
				$idFamiglia = $existFamily[0]['id'];
			$key++;
		}
		$this->num_family_inserted = $key;
		return true;
	}
	
	function importContent($File)
	{
		$fh = fopen($File, 'rb'); 
		$key = 0;
		while ( ($data = fgetcsv($fh, 1000, $this->separator)) !== false)
		{
			$BeanGruppiMerceologici = new gruppi_merceologici($this->conn, array('gruppo'=>$data[8]));		
			$existGruppo = $BeanGruppiMerceologici->dbGetByGruppo($this->conn, $data[8]);
			if(empty($existGruppo))
			{
				$idGm = $BeanGruppiMerceologici->dbStore($this->conn);
			}
			else
				$idGm = $existGruppo[0]['id'];
				
			$BeanFamiglie = new famiglie($this->conn, array('famiglia'=>$data[8], 'codice_famiglia'=>substr($data[8], 0, 3)));
			$existFamiglia = $BeanFamiglie->dbGetByCodFamiglia($this->conn, substr($data[8], 0, 3));
			if(empty($existFamiglia))
			{
				$idFamiglia = $BeanFamiglie->dbStore($this->conn);
			}
			else
				$idFamiglia = $existFamiglia[0]['id'];
							
			$BeanContent = new content();
			$BeanContent->setVbn($data[1]);
			$BeanContent->setNome_it($data[2]);
			$BeanContent->setDescrizione_it($data[2]);
			$BeanContent->setNome_en($data[2]);
			$BeanContent->setDescrizione_en($data[2]);
			$BeanContent->setId_gm($idGm);
			$BeanContent->setId_famiglia($idFamiglia);

			$BeanContent->setC1($data[3]);
			$BeanContent->setC2($data[4]); // SOTTO CARATTERISTICA DELLA C1 (VARIETA')
			
			$exp = explode(' ', $data[5]);
			$tipoColore = $exp[1];
				
			$BeanContent->setC3($exp[0]); // COLORE
			
			$BeanContent->setC4($data[6]); // QUALITÀ/ALTEZZA/VASO/GRAMMATURA
			$exp = explode(' ', $data[7]);
			$BeanContent->setC5($exp[0]); // PAESE PROVENIENZA
			
			$BeanContent->setTipo_colore($tipoColore); // PAESE PROVENIENZA

			$BeanContent->setPrezzo_0(str_replace(',', '.', $data[12]));
			$BeanContent->setPrezzo_1(str_replace(',', '.', $data[14]));
			$BeanContent->setPrezzo_2(str_replace(',', '.', $data[15]));
			$BeanContent->setPrezzo_3(str_replace(',', '.', $data[16]));
			$BeanContent->setPrezzo_4(str_replace(',', '.', $data[17]));
			$BeanContent->setPrezzo_5(str_replace(',', '.', $data[18]));
			$BeanContent->setPrezzo_6(str_replace(',', '.', $data[19]));
			$BeanContent->setPrezzo_7(str_replace(',', '.', $data[20]));
			$BeanContent->setPrezzo_8(str_replace(',', '.', $data[21]));
			$BeanContent->setPrezzo_9(str_replace(',', '.', $data[22]));

			$BeanContent->setCod_iva($data[13]);
			$BeanContent->setIs_active(1);
			$BeanContent->setData_inserimento_riga(date('Y-m-d H:i:s'));
			$BeanContent->setData_modifica_riga(date('Y-m-d H:i:s'));
			$BeanContent->setOperatore($this->operator);
			$idContent = $BeanContent->dbStore($this->conn);
			if(!empty($data[11]))
			{
				$BeanGiacenze = new giacenze();
				$BeanGiacenze->setBar_code($data[0]);
				$BeanGiacenze->setId_content($idContent);
				$BeanGiacenze->setId_fornitore(0);
				$BeanGiacenze->setQuantita($data[11]);
				$BeanGiacenze->setDisponibilita($data[10]);
				$BeanGiacenze->setData_inserimento_riga(date('Y-m-d H:i:s'));
				$BeanGiacenze->setData_modifica_riga(date('Y-m-d H:i:s'));
				$BeanGiacenze->setIs_active(1);
				$BeanGiacenze->setOperatore($this->operator);
				$idGiacenza = $BeanGiacenze->dbStore($this->conn);	
			}	
			$key++;
		}
		fclose($fh);
		$this->num_products_inserted = $key;
		//		_dump('Sono stati importati '.$this->num_products_inserted.' contenuti!');
		return true;
	}
	
	function importArticoli($File)
	{
		$arrayData = file($File);
		foreach($arrayData as $value)
		{
			$data = explode(';', $value);
			array_unshift($data, '.'.rand(1000000, 10000000));

			$BeanGruppiMerceologici = new gruppi_merceologici($this->conn, array('gruppo'=>$data[5]));
			$existGruppo = $BeanGruppiMerceologici->dbGetByGruppo($this->conn, $data[5]);
			if(empty($existGruppo))
			{
				$idGm = $BeanGruppiMerceologici->dbStore($this->conn);
			}
			else
				$idGm = $existGruppo[0]['id'];
			
			$BeanFamiglie = new famiglie($this->conn, array('famiglia'=>$data[5], 'codice_famiglia'=>substr($data[5], 0, 3)));
			$existFamiglia = $BeanFamiglie->dbGetByCodFamiglia($this->conn, substr($data[5], 0, 3));
			if(empty($existFamiglia))
			{
				$idFamiglia = $BeanFamiglie->dbStore($this->conn);
			}
			else
				$idFamiglia = $existFamiglia[0]['id'];
			
			$BeanContent = new content();
			$BeanContent->setVbn($data[1]);
			$BeanContent->setNome_it($data[2]);
			$BeanContent->setDescrizione_it($data[2]);
			$BeanContent->setNome_en($data[2]);
			$BeanContent->setDescrizione_en($data[2]);
			$BeanContent->setId_gm($idGm);
			$BeanContent->setId_famiglia($idFamiglia);
			
			$BeanContent->setC1($data[3]);
			$BeanContent->setC2($data[4]); // SOTTO CARATTERISTICA DELLA C1 (VARIETA')
			
			$exp = explode(' ', $data[4]);
			$tipoColore = $exp[1];
			$BeanContent->setC3($exp[0]); // COLORE
			
			$BeanContent->setC4(null); // QUALITA/ALTEZZA/VASO/GRAMMATURA
			$BeanContent->setC5(null); // PAESE PROVENIENZA
			
			$BeanContent->setTipo_colore($tipoColore); // PAESE PROVENIENZA
			
			$BeanContent->setPrezzo_0(null);
			$BeanContent->setPrezzo_1(null);
			$BeanContent->setPrezzo_2(null);
			$BeanContent->setPrezzo_3(null);
			$BeanContent->setPrezzo_4(null);
			$BeanContent->setPrezzo_5(null);
			$BeanContent->setPrezzo_6(null);
			$BeanContent->setPrezzo_7(null);
			$BeanContent->setPrezzo_8(null);
			$BeanContent->setPrezzo_9(null);
			$BeanContent->setCod_iva(null);
			$BeanContent->setIs_active(1);
			$BeanContent->setData_inserimento_riga(date('Y-m-d H:i:s'));
			$BeanContent->setData_modifica_riga(date('Y-m-d H:i:s'));
			$BeanContent->setOperatore($this->operator.'_articoli');
			$idContent = $BeanContent->dbStore($this->conn);

			$data = null;
			$BeanGruppiMerceologici = null;
			$existGruppo = null;
			$idGm = null;
			$BeanFamiglie = null;
			$existFamiglia = null;
			$idFamiglia = null;
			$BeanContent = null;
			$idContent = null;

			$key++;			
		}
		$this->num_content_inserted = $key;
		
		return true;

// 		$fh = fopen($File, 'rb');
// 		$key = 0;
// 		while ( ($data = fgetcsv($fh, 1000, $this->separator)) !== false)
// 		{
// 			$data[0] = '.'.rand(1000000, 10000000);

// 			$BeanGruppiMerceologici = new gruppi_merceologici($this->conn, array('gruppo'=>$data[5]));
// 			$existGruppo = $BeanGruppiMerceologici->dbGetByGruppo($this->conn, $data[5]);
// 			if(empty($existGruppo))
// 			{
// 				$idGm = $BeanGruppiMerceologici->dbStore($this->conn);
// 			}
// 			else
// 				$idGm = $existGruppo[0]['id'];
	
// 			$BeanFamiglie = new famiglie($this->conn, array('famiglia'=>$data[5], 'codice_famiglia'=>substr($data[5], 0, 3)));
// 			$existFamiglia = $BeanFamiglie->dbGetByCodFamiglia($this->conn, substr($data[5], 0, 3));
// 			if(empty($existFamiglia))
// 			{
// 				$idFamiglia = $BeanFamiglie->dbStore($this->conn);
// 			}
// 			else
// 				$idFamiglia = $existFamiglia[0]['id'];
				
// 			$BeanContent = new content();
// 			$BeanContent->setVbn($data[1]);
// 			$BeanContent->setNome_it($data[2]);
// 			$BeanContent->setDescrizione_it($data[2]);
// 			$BeanContent->setNome_en($data[2]);
// 			$BeanContent->setDescrizione_en($data[2]);
// 			$BeanContent->setId_gm($idGm);
// 			$BeanContent->setId_famiglia($idFamiglia);
	
// 			$BeanContent->setC1($data[3]);
// 			$BeanContent->setC2($data[4]); // SOTTO CARATTERISTICA DELLA C1 (VARIETA')
				
// 			$exp = explode(' ', $data[4]);
// 			$tipoColore = $exp[1];
// 			$BeanContent->setC3($exp[0]); // COLORE

// 			$BeanContent->setC4(null); // QUALITÀ/ALTEZZA/VASO/GRAMMATURA
// 			$BeanContent->setC5(null); // PAESE PROVENIENZA

// 			$BeanContent->setTipo_colore($tipoColore); // PAESE PROVENIENZA
	
// 			$BeanContent->setPrezzo_0(null);
// 			$BeanContent->setPrezzo_1(null);
// 			$BeanContent->setPrezzo_2(null);
// 			$BeanContent->setPrezzo_3(null);
// 			$BeanContent->setPrezzo_4(null);
// 			$BeanContent->setPrezzo_5(null);
// 			$BeanContent->setPrezzo_6(null);
// 			$BeanContent->setPrezzo_7(null);
// 			$BeanContent->setPrezzo_8(null);
// 			$BeanContent->setPrezzo_9(null);
	
// 			$BeanContent->setCod_iva(null);
// 			$BeanContent->setIs_active(1);
// 			$BeanContent->setData_inserimento_riga(date('Y-m-d H:i:s'));
// 			$BeanContent->setData_modifica_riga(date('Y-m-d H:i:s'));
// 			$BeanContent->setOperatore($this->operator);
// 			$idContent = $BeanContent->dbStore($this->conn);

	// 			if(!empty($data[11]))
	// 			{
	// 				$BeanGiacenze = new giacenze();
	// 				$BeanGiacenze->setBar_code($data[0]);
	// 				$BeanGiacenze->setId_content($idContent);
	// 				$BeanGiacenze->setId_fornitore(0);
	// 				$BeanGiacenze->setQuantita($data[11]);
	// 				$BeanGiacenze->setDisponibilita($data[10]);
	// 				$BeanGiacenze->setData_inserimento_riga(date('Y-m-d H:i:s'));
	// 				$BeanGiacenze->setData_modifica_riga(date('Y-m-d H:i:s'));
	// 				$BeanGiacenze->setIs_active(1);
	// 				$BeanGiacenze->setOperatore($this->operator);
	// 				$idGiacenza = $BeanGiacenze->dbStore($this->conn);
	// 			}
// 			$key++;
// 		}
// 		fclose($fh);
// 		$this->num_products_inserted = $key;
		//		_dump('Sono stati importati '.$this->num_products_inserted.' contenuti!');
	}
}
?>