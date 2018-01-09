<?php

class giacenze extends BeanBase
{
	var $id;
	var $bar_code;
	var$descrizione;
	var $C1;
	var $C2;
	var $C3;
	var $C4;
	var $C5;
	var $diametro_vaso;
	var $altezza_pianta;
	var $prezzo_sc;
	var $prezzo_pi;
	var $volume_singolo;
	var $volume_sc;
	var $prezzo_0;
	var $prezzo_1;
	var $prezzo_2;
	var $prezzo_3;
	var $prezzo_4;
	var $prezzo_5;
	var $prezzo_6;
	var $prezzo_7;
	var $prezzo_8;
	var $prezzo_9;
	var $id_gm;
	var $id_famiglia;
	var $quantita;
	
	var $quantita_1;
	var $quantita_2;
	var $quantita_3;
	var $quantita_4;
	var $quantita_5;
	var $quantita_6;
	var $quantita_7;
	var $quantita_8;
	var $quantita_9;
	
	var $disponibilita;
	var $unita_misura;
	var $stato;
	var $note;
	var $cod_iva;
	var $have_image;
	var $carrello;
	var $colori;
	var $data_inserimento_riga;
	var $data_modifica_riga;
	var $is_active;
	var $operatore;

	function giacenze($conn=null, $id=null)
	{
		parent::BeanBase();

		$this->table_name = "giacenze";

		if(isset($id))
		{
			if(is_array($id))
				$this->fill($id);
			elseif(is_numeric($id) && $id>0)
				$this->dbGetOne($conn, $id);
		}
	}

	function dbGetOne($db=null, $id=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$query="SELECT * FROM ".$this->table_name." WHERE id=". $id . " AND is_active = 1";
		$result=$db->query($query);
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql

		if($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
			$this->fill($row);

		$result->free();
	}

	function dbGetAll($db=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();
		$query="SELECT * FROM ".$this->table_name." WHERE is_active = 1";
		$result=$db->query($query);
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql

		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
				$values[]=$row;

		$result->free();
		return $values;
	}

	function dbGetAllIdKey($db=null, $key = 'id')
	{
		if (!$this->_is_connection($db))
			return false;
	
		$values=array();
		$query="SELECT * FROM ".$this->table_name." WHERE is_active = 1";
		$result=$db->query($query);
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);
	
		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql
	
		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
			$values[$row[$key]]=$row;
	
		$result->free();
		return $values;
	}
	
	function _dbAdd($db=null)
	{
		if (!$this->_is_connection($db))
			return false;

						$id = $db->nextId($this->table_name);
$this->setID($id);
																																																																																				$this->setIs_active(1);
																		
		$values=$this->vars();

		$table_fields=array_keys($values);
		$table_values=array_values($values);

		$sth = $db->autoPrepare($this->table_name, $table_fields, DB_AUTOQUERY_INSERT);
		$db->execute($sth, $table_values);

		//		Loggo la query sql
		$this->BeanLog("PEAR_DB", $db);
		//		Loggo la query sql

		return $id;
	}

	function _dbUpdate($db=null)
	{
		if(!$this->_is_connection($db))
			return false;

				$id = $this->id;
																																																																																				$this->setIs_active(1);
																		
		$values=$this->vars();

		unset($values['id']);

		$table_fields = array_keys($values);
		$table_values = array_values($values);

		$sth = $db->autoPrepare($this->table_name, $table_fields, DB_AUTOQUERY_UPDATE, "id = ".$id);
		$db->execute($sth, $table_values);
		//		Loggo la query sql
		$this->BeanLog("PEAR_DB", $db);
		//		Loggo la query sql

		return $this->vars();
	}

	function dbStore($db=null)
	{
		if(!$this->_is_connection($db))
			return false;

		if(isset($this->id) && is_numeric($this->id) && $this->id>0)
			return $this->_dbUpdate($db);
		else
			return $this->_dbAdd($db);
	}

	function fast_edit($db, $id=null, $key="", $value="")
	{
		if(!$this->_is_connection($db))
			return false;

		$query="UPDATE ".$this->table_name." SET ".$key."='".$value."' WHERE id =".$id."";

		$db->query($query);

		//		Loggo la query sql
		$this->BeanLog("PEAR_DB", $db);
		//		Loggo la query sql
	}

	function _is_connection($db)
	{
		$ret = false;
		if(is_object($db) && is_subclass_of($db, 'db_common') && method_exists($db, 'simpleQuery') )
			$ret = true;
		return $ret;
	}

	function dbDelete($db=null, $IDS=null, $is_logical = true)
	{
																																														
		if(is_array($IDS) && count($IDS) > 1)
		{
			if($is_logical)
				$query = "UPDATE ".$this->table_name." SET is_active = 0 WHERE id IN (".implode(", ", $IDS).")";
			else
				$query = "DELETE FROM ".$this->table_name." WHERE id IN (".implode(", ", $IDS).")";
		}
		else
		{
			if($is_logical)
				$query = "UPDATE ".$this->table_name." SET is_active = 0 WHERE id = ".$IDS[0];
			else
				$query = "DELETE FROM ".$this->table_name." WHERE id = ".$IDS[0];
		}

		$db->query($query);
		//		Loggo la query sql
		$this->BeanLog("PEAR_DB", $db);
		//		Loggo la query sql		
	}

	function fill($value=null) 
	{ 
		if(!is_array($value)) 
			$value=array(); 	
		
		$props = $this->vars(); 
		foreach($props as $k=>$v) 
		{ 
			$func = "set".ucfirst($k); 
			if(isset($value[$k]))
			{
				$value[$k] = str_replace('"', "''", stripslashes($value[$k]));
				$this->$func($value[$k]);
			}
		}
	}

	function dbSearch($db=null, $search=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();

		$query="SELECT * FROM ".$this->table_name." WHERE is_active = 1 ".$search;
		$result=$db->query($query);

		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql

		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
			$values[]=$row;

		$result->free();
		return $values;
	}
	
	function vars() 
	{  
		$vars = get_object_vars($this);
		unset($vars['table_name']);
		return $vars;  
	}
	
	/*			GET e SET		*/	
		function getId(){return $this->id;}

	function setId($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->id = (int)$value;
	}

		function getBar_code(){return $this->bar_code;}

	function setBar_code($value= null)
	{
		$this->bar_code = (string)$value;
	}
	
	function getDescrizione(){return $this->descrizione;}
	
	function setDescrizione($value= null)
	{
		$this->descrizione = (string)$value;
	}
	
		function getC1(){return $this->C1;}

	function setC1($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->C1 = (string)$value;
	}

		function getC2(){return $this->C2;}

	function setC2($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->C2 = (string)$value;
	}

		function getC3(){return $this->C3;}

	function setC3($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->C3 = (string)$value;
	}

		function getC4(){return $this->C4;}

	function setC4($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->C4 = (string)$value;
	}

		function getC5(){return $this->C5;}

	function setC5($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->C5 = (string)$value;
	}

	function getDiametro_vaso(){return $this->diametro_vaso;}
	
	function setDiametro_vaso($value= null)
	{
		$this->diametro_vaso = (string)$value;
	}
	
	function getAltezza_pianta(){return $this->altezza_pianta;}
	
	function setAltezza_pianta($value= null)
	{
		$this->altezza_pianta = (string)$value;
	}
	
	function getVolume_singolo(){return $this->volume_singolo;}
	
	function setVolume_singolo($value= null)
	{
		$this->volume_singolo = (string)$value;
	}
	
	function getVolume_sc(){return $this->volume_sc;}
	
	function setVolume_sc($value= null)
	{
		$this->volume_sc = (string)$value;
	}
	
	function getPrezzo_0(){return $this->prezzo_0;}

	function setPrezzo_0($value= null)
	{
				
								
		$this->prezzo_0 = (string)$value;
	}

		function getPrezzo_1(){return $this->prezzo_1;}

	function setPrezzo_1($value= null)
	{
				
								
		$this->prezzo_1 = (string)$value;
	}

		function getPrezzo_2(){return $this->prezzo_2;}

	function setPrezzo_2($value= null)
	{
				
								
		$this->prezzo_2 = (string)$value;
	}

		function getPrezzo_3(){return $this->prezzo_3;}

	function setPrezzo_3($value= null)
	{
				
								
		$this->prezzo_3 = (string)$value;
	}

		function getPrezzo_4(){return $this->prezzo_4;}

	function setPrezzo_4($value= null)
	{
				
								
		$this->prezzo_4 = (string)$value;
	}

		function getPrezzo_5(){return $this->prezzo_5;}

	function setPrezzo_5($value= null)
	{
				
								
		$this->prezzo_5 = (string)$value;
	}

		function getPrezzo_6(){return $this->prezzo_6;}

	function setPrezzo_6($value= null)
	{
				
								
		$this->prezzo_6 = (string)$value;
	}

		function getPrezzo_7(){return $this->prezzo_7;}

	function setPrezzo_7($value= null)
	{
				
								
		$this->prezzo_7 = (string)$value;
	}

		function getPrezzo_8(){return $this->prezzo_8;}

	function setPrezzo_8($value= null)
	{
				
								
		$this->prezzo_8 = (string)$value;
	}

		function getPrezzo_9(){return $this->prezzo_9;}

	function setPrezzo_9($value= null)
	{
				
								
		$this->prezzo_9 = (string)$value;
	}

		function getId_gm(){return $this->id_gm;}

	function setId_gm($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->id_gm = (int)$value;
	}

		function getId_famiglia(){return $this->id_famiglia;}

	function setId_famiglia($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->id_famiglia = (int)$value;
	}
	
	function getQuantita(){return $this->quantita;}

	function setQuantita($value= null)
	{
		$this->quantita = (int)$value;
	}

	function getQuantita_1(){return $this->quantita_1;}

	function setQuantita_1($value= null)
	{
		$this->quantita_1 = (int)$value;
	}

	function getQuantita_2(){return $this->quantita_2;}

	function setQuantita_2($value= null)
	{
		$this->quantita_2 = (int)$value;
	}
	
	function getQuantita_3(){return $this->quantita_3;}

	function setQuantita_3($value= null)
	{
		$this->quantita_3 = (int)$value;
	}
	
	function getQuantita_4(){return $this->quantita_4;}

	function setQuantita_4($value= null)
	{
		$this->quantita_4 = (int)$value;
	}
	
	function getQuantita_5(){return $this->quantita_5;}

	function setQuantita_5($value= null)
	{
		$this->quantita_5 = (int)$value;
	}
	
	function getQuantita_6(){return $this->quantita_6;}

	function setQuantita_6($value= null)
	{
		$this->quantita_6 = (int)$value;
	}
	
	function getQuantita_7(){return $this->quantita_7;}

	function setQuantita_7($value= null)
	{
		$this->quantita_7 = (int)$value;
	}
	
	function getQuantita_8(){return $this->quantita_8;}

	function setQuantita_8($value= null)
	{
		$this->quantita_8 = (int)$value;
	}
	
	function getQuantita_9(){return $this->quantita_9;}

	function setQuantita_9($value= null)
	{
		$this->quantita_9 = (int)$value;
	}
	
	function getDisponibilita(){return $this->disponibilita;}

	function setDisponibilita($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->disponibilita = (int)$value;
	}

		function getUnita_misura(){return $this->unita_misura;}

	function setUnita_misura($value= null)
	{
				if(strlen($value) > 10)
			$value = substr($value, 0, 10);
				
								
		$this->unita_misura = (string)$value;
	}

		function getStato(){return $this->stato;}

	function setStato($value= null)
	{
				if(strlen($value) > 2)
			$value = substr($value, 0, 2);
				
								
		$this->stato = (string)$value;
	}

		function getNote(){return $this->note;}

	function setNote($value= null)
	{
				
								
		$this->note = (string)$value;
	}

	function getCod_iva(){return $this->cod_iva;}

	function setCod_iva($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->cod_iva = (int)$value;
	}

		function getHave_image(){return $this->have_image;}

	function setHave_image($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->have_image = (int)$value;
	}

	function getCarrello(){return $this->carrello;}
	
	function setCarrello($value= null)
	{
		if(strlen($value) > 11)
			$value = substr($value, 0, 11);
	
	
		$this->carrello = (int)$value;
	}
	
	function getColori(){return $this->colori;}
	
	function setColori($value= null)
	{
		$this->colori = (int)$value;
	}
			
	function getData_inserimento_riga(){return $this->data_inserimento_riga;}

	function setData_inserimento_riga($value= null)
	{
				
						$exp = explode(" ", $value);
		if(strrpos($exp[0], "-") != 7)
		{
			echo "Errore nel settaggio della properies data_inserimento_riga il valore ".$exp[0]." risulta essere incorretto!";
			exit;
		}
		$expTime = explode(":", $exp[1]);
		if(count($expTime) <= 1)
		{
			echo "Errore nel settaggio della properies data_inserimento_riga il valore ".$exp[1]." risulta essere incorretto!";
			exit;
		}
						$expTime = explode(":", $value);
		if(count($expTime) <= 1 || count($expTime) == 2)
		{
			echo "Errore nel settaggio della properies data_inserimento_riga il valore ".$value." risulta essere incorretto!";
			exit;
		}
				
		$this->data_inserimento_riga = (string)$value;
	}

		function getData_modifica_riga(){return $this->data_modifica_riga;}

	function setData_modifica_riga($value= null)
	{
				
						$exp = explode(" ", $value);
		if(strrpos($exp[0], "-") != 7)
		{
			echo "Errore nel settaggio della properies data_modifica_riga il valore ".$exp[0]." risulta essere incorretto!";
			exit;
		}
		$expTime = explode(":", $exp[1]);
		if(count($expTime) <= 1)
		{
			echo "Errore nel settaggio della properies data_modifica_riga il valore ".$exp[1]." risulta essere incorretto!";
			exit;
		}
						$expTime = explode(":", $value);
		if(count($expTime) <= 1 || count($expTime) == 2)
		{
			echo "Errore nel settaggio della properies data_modifica_riga il valore ".$value." risulta essere incorretto!";
			exit;
		}
				
		$this->data_modifica_riga = (string)$value;
	}

		function getIs_active(){return $this->is_active;}

	function setIs_active($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->is_active = (int)$value;
	}

		function getOperatore(){return $this->operatore;}

	function setOperatore($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->operatore = (string)$value;
	}	
}
?>