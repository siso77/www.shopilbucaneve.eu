<?php

class vendite extends BeanBase
{
	var $id;
	var $id_cliente;
//	var $id_magazino;
	var $fattura;
	var $tipo_pagamento;
	var $quantita;
	var $totale;
	var $data_vendita;
	var $ddt;
	var $free_text;
	var $percentuale_sconto;
	var $is_iva;
	var $data_inserimento_riga;
	var $data_modifica_riga;
	var $data_fatturazione;
	var $channel;
	var $is_invoiced;
	var $is_active;
	var $operatore;

	function vendite($conn=null, $id=null)
	{
		parent::BeanBase();
		
		$this->table_name = "vendite";
		
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

	function dbGetAllByCustomFields($db=null, $fields = '*', $where = '')
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();
		$query="SELECT ".$fields." FROM ".$this->table_name."".$where;
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
		$this->setIs_active(1);
		$this->setData_modifica_riga(date('Y-m-d H:i:s'));

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

	function getView()
	{
		return 'SELECT 
					vendite.id,
					content.bar_code, 
					content.name_it, 
					content.description_it, 
					content.name_en, 
					content.description_en, 
					vendite_magazzino.personal_price, 
					vendite_magazzino.quantita, 
					vendite_magazzino.total, 
					vendite.fattura, 
					vendite.tipo_pagamento, 
					vendite_magazzino.quantita, 
					vendite.totale, 
					vendite.data_vendita,
					vendite.operatore,
					vendite.channel,
					customer.id as id_customer,  
					customer.nome, 
					customer.cognome, 
					customer.codice_fiscale, 
					customer.indirizzo, 
					customer.provincia, 
					customer.stato, 
					customer.citta, 
					customer.cap, 
					customer.cellulare, 
					customer.fisso, 
					customer.email, 
					customer.indirizzo_spedizione, 
					customer.cap_spedizione, 
					customer.citta_spedizione, 
					customer.provincia_spedizione, 
					customer.stato_spedizione, 
					customer.percentuale_sconto, 
					customer.data_inserimento_riga
					FROM 
					(
						(
							vendite_magazzino INNER JOIN 
							(magazzino INNER JOIN content ON magazzino.id_content = content.id) ON vendite_magazzino.id_magazzino = magazzino.id
						) 
						INNER JOIN vendite ON vendite_magazzino.id_vendita = vendite.id
					) 
					INNER JOIN customer ON vendite.id_cliente = customer.id';
	}
	
	function dbSearch($db=null, $search=null, $order_by_for_invoices = null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();

		$query = $this->getView().$search;

		$result=$db->query($query);

		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql
		$i = 0;
		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if(!empty($order_by_for_invoices))
				$values[$row[$order_by_for_invoices].$i]=$row;
			else
				$values[]=$row;
			$i++;
		}
		$result->free();
		return $values;
	}
	
	function getViewListVendite()
	{
		return 'SELECT 
					vendite.id,
					vendite.fattura, 
					vendite.tipo_pagamento, 
					vendite.totale, 
					vendite.data_vendita,
					vendite.operatore,
					vendite.channel,
					customer.id as id_customer,  
					customer.nome, 
					customer.cognome, 
					customer.codice_fiscale, 
					customer.indirizzo, 
					customer.provincia, 
					customer.stato, 
					customer.citta, 
					customer.cap, 
					customer.cellulare, 
					customer.fisso, 
					customer.email, 
					customer.indirizzo_spedizione, 
					customer.cap_spedizione, 
					customer.citta_spedizione, 
					customer.provincia_spedizione, 
					customer.stato_spedizione, 
					customer.percentuale_sconto, 
					customer.data_inserimento_riga
					FROM 
						vendite, customer
					WHERE 
						vendite.id_cliente = customer.id';		
	}	
	
	function dbSearchListVendite($db=null, $search=null, $order_by_for_invoices = null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();

		$query = $this->getViewListVendite().$search;
		$result=$db->query($query);

		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql
		$i = 0;
		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if(!empty($order_by_for_invoices))
				$values[$row[$order_by_for_invoices].$i]=$row;
			else
				$values[]=$row;
			$i++;
		}
		$result->free();
		return $values;
	}
	
	function dbSearchStatsVendite($db=null, $search=null, $order_by_for_invoices = null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();

		$query = 'SELECT
					content.bar_code,
					content.name_it as content_name,
					content.description_it,
					content.name_en,
					content.description_en,
					vendite_magazzino.personal_price,
					vendite_magazzino.quantita,
					vendite_magazzino.total,
					vendite.id,
					vendite.fattura,
					vendite.tipo_pagamento,
					vendite.totale,
					vendite.data_vendita,
					vendite.operatore,
					customer.id,
					customer.nome,
					customer.cognome,
					customer.codice_fiscale,
					customer.indirizzo,
					customer.provincia,
					customer.stato,
					customer.citta,
					customer.cap,
					customer.cellulare,
					customer.fisso,
					customer.email,
					customer.indirizzo_spedizione,
					customer.cap_spedizione,
					customer.citta_spedizione,
					customer.provincia_spedizione,
					customer.stato_spedizione,
					customer.percentuale_sconto,
					brands.`name` as name_brand,
					category.`name` as category_name,
					category.description as category_description,
					category.name_en as category_name_en
					FROM
						content
					INNER JOIN magazzino ON magazzino.id_content = content.id
					INNER JOIN vendite_magazzino ON vendite_magazzino.id_magazzino = magazzino.id
					INNER JOIN vendite ON vendite_magazzino.id_vendita = vendite.id
					INNER JOIN customer ON vendite.id_cliente = customer.id
					INNER JOIN brands ON content.id_brand = brands.id
					INNER JOIN category ON content.id_category = category.id'.$search;

		$result=$db->query($query);
//echo $query;
//exit();
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql
		$i = 0;
		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if(!empty($order_by_for_invoices))
				$values[$row[$order_by_for_invoices].$i]=$row;
			else
				$values[]=$row;
			$i++;
		}
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

	function getId_cliente(){return $this->id_cliente;}

	function setId_cliente($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->id_cliente = (int)$value;
	}

//	function getId_magazino(){return $this->id_magazino;}
//
//	function setId_magazino($value= null)
//	{
//				if(strlen($value) > 11)
//			$value = substr($value, 0, 11);
//				
//								
//		$this->id_magazino = (int)$value;
//	}

	function getFattura(){return $this->fattura;}

	function setFattura($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->fattura = (string)$value;
	}

	function getTipo_pagamento(){return $this->tipo_pagamento;}

	function setTipo_pagamento($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->tipo_pagamento = (string)$value;
	}

	
	function getTotale(){return $this->totale;}

	function setTotale($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->totale = (string)$value;
	}
	
	function getQuantita(){return $this->quantita;}

	function setQuantita($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->quantita = (string)$value;
	}

	function getData_vendita(){return $this->data_vendita;}

	function setData_vendita($value= null)
	{
				
				if(strrpos($value, "-") != 7)
		{
			echo "Errore nel settaggio della properies data_vendita!";
			exit;
		}
								
		$this->data_vendita = (string)$value;
	}

	function getDdt(){return $this->ddt;}

	function setDdt($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->ddt = (string)$value;
	}

	function getFree_text(){return $this->free_text;}

	function setFree_text($value= null)
	{
				
								
		$this->free_text = (string)$value;
	}

	function getPercentuale_sconto(){return $this->percentuale_sconto;}

	function setPercentuale_sconto($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->percentuale_sconto = (string)$value;
	}

	function getIs_iva(){return $this->is_iva;}

	function setIs_iva($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->is_iva = (int)$value;
	}

	function getChannel(){return $this->channel;}

	function setChannel($value= null)
	{
		$this->channel = (string)$value;
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

	function getData_fatturazione(){return $this->data_fatturazione;}

	function setData_fatturazione($value= null)
	{
				
				if(strrpos($value, "-") != 7)
		{
			echo "Errore nel settaggio della properies data_fatturazione!";
			exit;
		}
								
		$this->data_fatturazione = (string)$value;
	}

	function getIs_invoiced(){return $this->is_invoiced;}

	function setIs_invoiced($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->is_invoiced = (int)$value;
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