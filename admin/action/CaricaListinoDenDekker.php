<?php 
class CaricaListinoDenDekker extends DBSmartyAction
{
	function __construct()
	{
		parent::DBSmartyAction();
		
		if($_SERVER['REQUEST_METHOD'] == 'POST' || !empty($_REQUEST['import_from_mail']))
		{
			if(!empty($_FILES['listino']['name']))
			{
				$ext = substr($_FILES['listino']['name'], -3);
				$filename = substr($_FILES['listino']['name'], 0, -4);
				$destination = APP_ROOT."/upload_fornitori/dendekker/".$filename."_".date('Ymd_His').".".$ext;
				move_uploaded_file($_FILES['listino']['tmp_name'], $destination);
			}
			else
			{
				$directory = APP_ROOT."/upload_fornitori/dendekker/";
				$d = dir($directory);
				while (false !== ($entry = $d->read())) 
				{
					if($entry != '.' && $entry != '..' && $entry != '.DS_Store' && $entry != '.svn')
						$destination = APP_ROOT."/upload_fornitori/dendekker/".$entry;
				}
				$d->close();
			}
			chmod($destination, 0777);

			include_once(APP_ROOT."/libs/ext/Excel/reader.php");
			$data = new Spreadsheet_Excel_Reader();
			$data->setOutputEncoding('CP1251'); // Set output Encoding.
			$data->read($destination);
			
// 			$query_trucate = "TRUNCATE TABLE giacenze_fornitori WHERE operatore = 'ImportFornitoriDenDekker'";
			$query_trucate = "DELETE FROM giacenze_fornitori WHERE operatore = 'ImportFornitori1'";
			mysql_query($query_trucate, $this->conn->connection);
			
			$query_trucate = "ALTER TABLE giacenze_fornitori AUTO_INCREMENT = 1";
			mysql_query($query_trucate, $this->conn->connection);
				
			$j = 0;
			for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++)
			{
				$values = $data->sheets[0]['cells'][$i];

				if(!empty($values[1]))
				{
					$qty_scatola = $values[7];
					$qty_scatole = $values[6];

					$raggio = str_replace(',', '.', $values[3]) / 2;
					$altezza = str_replace(',', '.', $values[4]);
					$area_base = 3.14 * $raggio * $raggio;

					$volume_singolo = $area_base * $altezza;
					$volume_sc = $volume_singolo * $qty_scatola;

					$query = "INSERT INTO `giacenze_fornitori` (
							`codice`, 
							`bar_code`, 
							`descrizione`, 
							`qta_scatola`, 
							`qta_pianale`, 
							`diametro_vaso`, 
							`altezza_pianta`,
							
							`volume_singolo`,
							`volume_sc`,
							
							`prezzo_sc`, 
							`prezzo_pi`, 
							`prezzo_acquisto`, 
							`carrello`, 
							`stato`, 

							`image`, 
							`referenza`,
							`categoria`,  
							`fornitore`,  
							`data_inserimento_riga`, 
							`data_modifica_riga`, 
							`is_active`, 
							`operatore`) VALUES (
							'', 
							'', 
							'".mysql_real_escape_string($values[1])."', 
							'".$qty_scatola."',
							'".$qty_scatole."', 
							'".str_replace(',', '.', $values[3])."', 
							'".str_replace(',', '.', $values[4])."', 
									
							'".$volume_singolo."',
							'".$volume_sc."',
							
							'".$values[12]."', 
							'',
							'".$values[11]."', 
							'".$values[5]."', 
							'".$values[8]."', 

							'".$values[2]."', 
							'".$values[10]."',
							'".$values[13]."',  
							'".$values[14]."',  
							'".date('Y-m-d')."', 
							'".date('Y-m-d')."', 
							'1', 
							'ImportFornitori1');";

					mysql_query($query, $this->conn->connection);
					$j++;
				}
			}
			unlink($destination);
			$this->tEngine->assign('msg', "Importazione contenuti avventuta con successo, sono stati importati ".$j." prodotti.");
			Base_CacheCore::getInstance()->clean();
		}
		$this->tEngine->assign('action_class_name', get_class($this));
		$this->tEngine->assign('tpl_action', get_class($this));
		$this->tEngine->display('Index');
	}
}
?>