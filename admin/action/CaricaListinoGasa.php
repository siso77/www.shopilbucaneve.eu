<?php 
class CaricaListinoGasa extends DBSmartyAction
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
				$destination = APP_ROOT."/upload_fornitori/gasa/".$filename."_".date('Ymd_His').".".$ext;
				move_uploaded_file($_FILES['listino']['tmp_name'], $destination);
			}
			else
			{
				$directory = APP_ROOT."/upload_fornitori/gasa/";
				$d = dir($directory);
				while (false !== ($entry = $d->read())) 
				{
					if($entry != '.' && $entry != '..' && $entry != '.DS_Store')
						$destination = APP_ROOT."/upload_fornitori/gasa/".$entry;
				}
				$d->close();
			}
				
			include_once(APP_ROOT."/libs/ext/Excel/reader.php");
			$data = new Spreadsheet_Excel_Reader();
			$data->setOutputEncoding('CP1251'); // Set output Encoding.
			$data->read($destination);
				
			$query_trucate = "DELETE FROM giacenze_forn_gasa WHERE operatore = 'ImportFornitoriGasa'";
			mysql_query($query_trucate, $this->conn->connection);
				
			$query_trucate = "ALTER TABLE giacenze_forn_gasa AUTO_INCREMENT = 1";
			mysql_query($query_trucate, $this->conn->connection);
				
			$j = 0;
			for ($i = 10; $i <= $data->sheets[0]['numRows']; $i++)
			{
				$values = $data->sheets[0]['cells'][$i];
				
				if(count($values) == 2)
					$stato = mysql_real_escape_string($values[2]);
				else 
				{
					if(!empty($values[2]) && !empty($values[13]))
					{
						$qty_scatola = $values[7];
						$qty_pianale = $values[6];

						$raggio = str_replace(',', '.', $values[4]) / 2;
// 						$raggio = $raggio + 1.5;
						//$raggio = str_replace(',', '.', $values[5]);
						
						$expAl = explode('-', $values[5]);
						$altezza = str_replace(',', '.', $expAl[0]);
						$area_base = 3.14 * $raggio * $raggio;

						$volume_singolo = $area_base * $altezza;
						$volume_sc = $volume_singolo * $qty_scatola;

						$bar_code = !empty($values[11]) ? $values[11] : '-';
						$query = "INSERT INTO `giacenze_forn_gasa` (
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
								`carrello`, 
								`stato`, 
								`fornitore`, 
								`data_inserimento_riga`, 
								`data_modifica_riga`, 
								`is_active`, 
								`operatore`) VALUES (
								'".$values[13]."', 
								'".$bar_code."', 
								'".mysql_real_escape_string($values[2]).' - '.mysql_real_escape_string($values[3])."', 
								'".$qty_scatola."', 
								'".$qty_pianale."', 
								'".str_replace(',', '.', $values[4])."', 
								'".$altezza."', 
										
								'".$volume_singolo."',
								'".$volume_sc."',
								
								'".$values[9]."', 
								'".$values[10]."', 
								'".$carrello."', 
								'".$stato."', 
								'gasa', 
								'".date('Y-m-d')."', 
								'".date('Y-m-d')."', 
								'1', 
								'ImportFornitoriGasa');";

						mysql_query($query, $this->conn->connection);
						
						$j++;
					}
				}
			}

			unlink($destination);
			$this->tEngine->assign('msg', "Importazione contenuti avventuta con successo, sono stati importati ".$j." prodotti.");
		}
		$this->tEngine->assign('action_class_name', get_class($this));
		$this->tEngine->assign('tpl_action', get_class($this));
		$this->tEngine->display('Index');
	}
}
?>