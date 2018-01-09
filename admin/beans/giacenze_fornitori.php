<?php

class giacenze_fornitori extends BeanBase
{
	var $id;
	var $codice;
	var $bar_code;
	var $descrizione;
	var $qta_scatola;
	var $qta_pianale;
	var $diametro_vaso;
	var $altezza_pianta;
	var $prezzo_sc;
	var $prezzo_pi;
	var $volume_singolo;
	var $volume_sc;
	var $carrello;
	var $stato;
	var $fornitore;
	var $data_inserimento_riga;
	var $data_modifica_riga;
	var $is_active;
	var $operatore;

	function giacenze_fornitori($conn=null, $id=null)
	{
		parent::BeanBase();

		$this->table_name = "giacenze_fornitori";

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

	function dbGetGm($db=null, $search=null)
	{
		if (!$this->_is_connection($db))
			return false;
	
		$values=array();
	
		$query="SELECT stato FROM ".$this->table_name." WHERE is_active = 1 ".$search." GROUP BY stato ";
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
	
	function dbSearchCounted($db=null, $search=null)
	{
		if (!$this->_is_connection($db))
			return false;
	
		$values=array();
	
		$query="SELECT count(id) as num FROM ".$this->table_name." WHERE is_active = 1 ".$search;
	
		$result=$db->query($query);
	
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);
	
		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql
	
		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
			$values=$row;
			
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

		function getCodice(){return $this->codice;}

	function setCodice($value= null)
	{
				if(strlen($value) > 14)
			$value = substr($value, 0, 14);
				
								
		$this->codice = (string)$value;
	}

		function getBar_code(){return $this->bar_code;}

	function setBar_code($value= null)
	{
				if(strlen($value) > 14)
			$value = substr($value, 0, 14);
				
								
		$this->bar_code = (string)$value;
	}

		function getDescrizione(){return $this->descrizione;}

	function setDescrizione($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->descrizione = (string)$value;
	}

		function getQta_scatola(){return $this->qta_scatola;}

	function setQta_scatola($value= null)
	{
				if(strlen($value) > 20)
			$value = substr($value, 0, 20);
				
								
		$this->qta_scatola = (string)$value;
	}

		function getQta_pianale(){return $this->qta_pianale;}

	function setQta_pianale($value= null)
	{
				if(strlen($value) > 20)
			$value = substr($value, 0, 20);
				
								
		$this->qta_pianale = (string)$value;
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

		function getPrezzo_sc(){return $this->prezzo_sc;}

	function setPrezzo_sc($value= null)
	{
				if(strlen($value) > 14)
			$value = substr($value, 0, 14);
				
								
		$this->prezzo_sc = (string)$value;
	}

	function getPrezzo_pi(){return $this->prezzo_pi;}

	function setPrezzo_pi($value= null)
	{
				if(strlen($value) > 14)
			$value = substr($value, 0, 14);
				
								
		$this->prezzo_pi = (string)$value;
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
	
	function getCarrello(){return $this->carrello;}

	function setCarrello($value= null)
	{
				if(strlen($value) > 10)
			$value = substr($value, 0, 10);
				
								
		$this->carrello = (string)$value;
	}

		function getStato(){return $this->stato;}

	function setStato($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->stato = (string)$value;
	}

		function getFornitore(){return $this->fornitore;}

	function setFornitore($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->fornitore = (string)$value;
	}

		function getData_inserimento_riga(){return $this->data_inserimento_riga;}

	function setData_inserimento_riga($value= null)
	{
				
				if(strrpos($value, "-") != 7)
		{
			echo "Errore nel settaggio della properies data_inserimento_riga!";
			exit;
		}
								
		$this->data_inserimento_riga = (string)$value;
	}

		function getData_modifica_riga(){return $this->data_modifica_riga;}

	function setData_modifica_riga($value= null)
	{
				
				if(strrpos($value, "-") != 7)
		{
			echo "Errore nel settaggio della properies data_modifica_riga!";
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