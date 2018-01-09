<?php

class customer extends BeanBase
{
	var $id;
	var $customer_code;
	var $nome;
	var $cognome;
	var $codice_fiscale;
	var $ragione_sociale;
	var $p_iva;
	var $indirizzo;
	var $provincia;
	var $stato;
	var $citta;
	var $cap;
	var $cellulare;
	var $fisso;
	var $fax;
	var $email;
	var $text_spedizione;
	var $indirizzo_spedizione;
	var $cap_spedizione;
	var $citta_spedizione;
	var $provincia_spedizione;
	var $stato_spedizione;
	var $percentuale_sconto;
	var $listino;
	var $registred_from;
	var $scorporo_iva;
	var $codice_agente;
	var $data_inserimento_riga;
	var $data_modifica_riga;
	var $is_active;
	var $operatore;

	function customer($conn=null, $id=null)
	{
		parent::BeanBase();
		
		$this->table_name = "customer";
		
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
		$this->setData_inserimento_riga(date('Y-m-d'));
		$this->setData_modifica_riga(date('Y-m-d'));
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
		$this->setData_modifica_riga(date('Y-m-d'));						
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

	function dbDeleteAll($db=null)
	{
		$query = "DELETE FROM ".$this->table_name."";
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

		$values=null;

		$query = "SELECT * FROM ".$this->table_name.' WHERE is_active = 1'.$search;

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

	function getCustomer_code(){return $this->customer_code;}

	function setCustomer_code($value= null)
	{
		if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->customer_code = (int)$value;
	}
	
	function getNome(){return $this->nome;}

	function setNome($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->nome = (string)str_replace('\\\'','&acute;',$value);
	}

	function getCognome(){return $this->cognome;}

	function setCognome($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->cognome = (string)str_replace('\\\'','&acute;',$value);
	}

	function getCodice_fiscale(){return $this->codice_fiscale;}

	function setCodice_fiscale($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->codice_fiscale = (string)$value;
	}

	function getRagione_sociale(){return $this->ragione_sociale;}

	function setRagione_sociale($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->ragione_sociale = (string)str_replace('\\\'','&acute;',$value);
	}
	
	function getP_iva(){return $this->p_iva;}

	function setP_iva($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->p_iva = (string)$value;
	}
	
	function getIndirizzo(){return $this->indirizzo;}

	function setIndirizzo($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->indirizzo = (string)str_replace('\\\'','&acute;',$value);
	}

	function getProvincia(){return $this->provincia;}

	function setProvincia($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->provincia = (string)$value;
	}

	function getStato(){return $this->stato;}

	function setStato($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->stato = (string)str_replace('\\\'','&acute;',$value);
	}

	function getCitta(){return $this->citta;}

	function setCitta($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->citta = (string)str_replace('\\\'','&acute;',$value);
	}

	function getCap(){return $this->cap;}

	function setCap($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->cap = (string)$value;
	}

	function getCellulare(){return $this->cellulare;}

	function setCellulare($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->cellulare = (string)$value;
	}

	function getFisso(){return $this->fisso;}

	function setFisso($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->fisso = (string)$value;
	}

	function getFax(){return $this->fax;}

	function setFax($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
								
		$this->fax = (string)$value;
	}
	
	function getEmail(){return $this->email;}

	function setEmail($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->email = (string)$value;
	}
	
	function getText_spedizione(){return $this->text_spedizione;}

	function setText_spedizione($value= null)
	{
		$this->text_spedizione = (string)str_replace('\\\'','&acute;',$value);
	}
	

	function getIndirizzo_spedizione(){return $this->indirizzo_spedizione;}

	function setIndirizzo_spedizione($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->indirizzo_spedizione = (string)str_replace('\\\'','&acute;',$value);
	}

	function getCap_spedizione(){return $this->cap_spedizione;}

	function setCap_spedizione($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->cap_spedizione = (string)$value;
	}

	function getCitta_spedizione(){return $this->citta_spedizione;}

	function setCitta_spedizione($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->citta_spedizione = (string)str_replace('\\\'','&acute;',$value);
	}

	function getProvincia_spedizione(){return $this->provincia_spedizione;}

	function setProvincia_spedizione($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->provincia_spedizione = (string)$value;
	}

	function getStato_spedizione(){return $this->stato_spedizione;}

	function setStato_spedizione($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->stato_spedizione = (string)str_replace('\\\'','&acute;',$value);
	}

	function getPercentuale_sconto(){return $this->percentuale_sconto;}

	function setPercentuale_sconto($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->percentuale_sconto = (string)$value;
	}

	function getListino(){return $this->listino;}

	function setListino($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
								
		$this->listino = (string)$value;
	}
	
	function getRegistred_from(){return $this->registred_from;}

	function setRegistred_from($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->registred_from = (string)$value;
	}
	
	function getScorporo_iva(){return $this->scorporo_iva;}
	
	function setScorporo_iva($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
	
	
		$this->scorporo_iva = (string)$value;
	}
	
	function getCodice_agente(){return $this->codice_agente;}
	
	function setCodice_agente($value= null)
	{
		$this->codice_agente = $value;
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