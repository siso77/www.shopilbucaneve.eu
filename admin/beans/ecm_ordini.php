<?php

class ecm_ordini extends BeanBase
{
	var $id;
	var $id_user;
	var $importo;
	var $tipo_pagamento;
	var $data_inserimento_riga;
	var $data_modifica_riga;
	var $pagato;
	var $spedito;
	var $fatturato;
	var $note;
	var $traking;
	var $corriere;
	var $is_active;
	var $data_partenza_fornitore_1;
	var $data_partenza_fornitore_2;
	var $operatore;

	function ecm_ordini($conn=null, $id=null)
	{
		parent::BeanBase();
		
		$this->table_name = "ecm_ordini";
		
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

	function dbGetAll($db=null, $order_by = null, $order_type = null, $where = null, $user = null, $userAnag = null, $ecmOrdiniMagazzino = null)
	{
		if (!$this->_is_connection($db))
			return false;

		$params = '';
		if(!empty($order_by))
			$params .= ' ORDER BY '.$order_by.' '.$order_type;
			
		$values=array();
		$query="SELECT * FROM ".$this->table_name." WHERE is_active = 1".$where.$params;
		$result=$db->query($query);
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql

		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if(!empty($user))
			{
				$user->dbGetOne($db, $row['id_user']);
				$userAnag->dbGetOne($db, $user->id_anag);
				$row['user_data'] = $user->vars();
				$row['user_anag_data'] = $userAnag->vars();
			}
			if(!empty($ecmOrdiniMagazzino))
			{
				$row['ecm_ordine_magazzino'] = $ecmOrdiniMagazzino->dbGetAllByIdOrdine($db, $row['id']);
			}
			$values[]=$row;
		}
		
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
		$this->setData_inserimento_riga(date('Y-m-d H:i:s'));
		$this->setData_modifica_riga(date('Y-m-d H:i:s'));
				
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
		$this->setData_modifica_riga(date('Y-m-d H:i:s'));
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
				$this->$func($value[$k]);
		}
	}

	function dbSearch($db=null, $search=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();

		$query = "";
		
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

	function getId_user(){return $this->id_user;}

	function setId_user($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->id_user = (int)$value;
	}

	function getImporto(){return $this->importo;}

	function setImporto($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->importo = (string)$value;
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

	
	function getPagato(){return $this->pagato;}

	function setPagato($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->pagato = (int)$value;
	}

	function getSpedito(){return $this->spedito;}

	function setSpedito($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->spedito = (int)$value;
	}

	function getFatturato(){return $this->fatturato;}

	function setFatturato($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->fatturato = (int)$value;
	}
	
	function getIs_active(){return $this->is_active;}

	function setIs_active($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->is_active = (int)$value;
	}

	function getTipo_pagamento(){return $this->tipo_pagamento;}

	function setTipo_pagamento($value= null)
	{
		$this->tipo_pagamento = $value;
	}
	
	function getOperatore(){return $this->operatore;}

	function setOperatore($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->operatore = (string)$value;
	}

	function getData_partenza_fornitore_1(){return $this->data_partenza_fornitore_1;}
	
	function setData_partenza_fornitore_1($value= null)
	{
		if(strrpos($value, "-") != 7)
		{
			echo "Errore nel settaggio della properies data_inserimento_riga!";
			exit;
		}
			
		$this->data_partenza_fornitore_1 = (string)$value;
	}
	
	function getData_partenza_fornitore_2(){return $this->data_partenza_fornitore_2;}
	
	function setData_partenza_fornitore_2($value= null)
	{
		if(strrpos($value, "-") != 7)
		{
			echo "Errore nel settaggio della properies data_inserimento_riga!";
			exit;
		}
			
		$this->data_partenza_fornitore_2 = (string)$value;
	}
		
	function getNote(){return $this->note;}

	function setNote($value= null)
	{
		$this->note = (string)$value;
	}
	
	function getTraking(){return $this->traking;}

	function setTraking($value= null)
	{
		$this->traking = (string)$value;
	}

	function getCorriere(){return $this->corriere;}

	function setCorriere($value= null)
	{
		$this->corriere = (string)$value;
	}
}
?>