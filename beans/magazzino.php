<?php

class magazzino extends BeanBase
{
	var $id;
	var $bar_code;
	var $id_content;
	var $id_fornitore;
	var $id_color;
	var $id_color_2;
	var $id_color_3;
	var $id_size;
	var $quantita;
	var $quantita_caricata;
	var $percentuale_sconto;
	var $ddt;
	var $prezzo_acquisto;
	var $fattura_carico;
	var $iva;
	var $data_inserimento_riga;
	var $data_modifica_riga;
	var $is_active;
	var $operatore;

	function magazzino($conn=null, $id=null)
	{
		parent::BeanBase();
		
		$this->table_name = "magazzino";
		
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
		$this->setData_inserimento_riga(date('Y-m-d'));
		$this->setData_modifica_riga(date('Y-m-d'));
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
		$this->setData_modifica_riga(date('Y-m-d'));
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
	
	function getView()
	{
		return "SELECT 
						fornitore.nome as fornitore,
						category.name, 
						magazzino.bar_code, 
						content.name_it, 
						content.description_it, 
						content.price_it, 
						content.price_it_1, 
						content.price_it_2, 
						content.price_it_3, 
						content.price_it_4, 
						
						content.price_discounted_it, 
						
						content.id as id_content,
						
						content.name_en, 
						content.description_en, 
						
						brands.id as id_brand,  
						brands.name name_brand, 
						category.id as id_category,
						
						category.name_en as category_name_en,
						fornitore.id as id_fornitore,  
						
						sizes.id as id_size,  
						sizes.size, 
						color.id as id_color,
						color.color,
						magazzino.id as id_magazzino, 
						magazzino.id_color_2,
						magazzino.id_color_3,
						content.is_in_ecommerce, 
						content.is_invisible,
						content.is_in_evidence, 
						content.is_in_offer, 
						magazzino.quantita, 
						magazzino.quantita_caricata,  
						magazzino.percentuale_sconto,
						magazzino.ddt, 
						magazzino.fattura_carico,
						magazzino.data_inserimento_riga,  
						magazzino.data_modifica_riga,  
						magazzino.prezzo_acquisto,
						magazzino.operatore
				FROM 
				(
					(
						(
							(
								(magazzino JOIN content ON magazzino.id_content = content.id) 
								LEFT JOIN category ON content.id_category = category.id
							) 
							LEFT JOIN brands ON content.id_brand = brands.id
						) 
						LEFT JOIN fornitore ON magazzino.id_fornitore = fornitore.id
					) 
					LEFT JOIN color ON magazzino.id_color = color.id
				) 
				LEFT JOIN sizes ON magazzino.id_size = sizes.id
				"; 
					//INNER JOIN (images_color INNER JOIN color ON images_color.id_color = color.id) ON magazzino.id_color = color.id";
	}

	function dbSearch($db=null, $search=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=null;

		$query = $this->getView()." WHERE magazzino.is_active = 1 ".$search;
		$result=$db->query($query);
		
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql

		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$values[]=$row;
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
		$this->id = $value;
	}

	function getBar_code(){return $this->bar_code;}

	function setBar_code($value= null)
	{
		$this->bar_code = $value;
	}
	
	function getId_content(){return $this->id_content;}

	function setId_content($value= null)
	{
		$this->id_content = $value;
	}

	function getId_fornitore(){return $this->id_fornitore;}

	function setId_fornitore($value= null)
	{
		$this->id_fornitore = $value;
	}

	function getId_color(){return $this->id_color;}

	function setId_color($value= null)
	{
		$this->id_color = $value;
	}
	
	function getId_color_2(){return $this->id_color_2;}

	function setId_color_2($value= null)
	{
		$this->id_color_2 = $value;
	}

	function getId_color_3(){return $this->id_color_3;}

	function setId_color_3($value= null)
	{
		$this->id_color_3 = $value;
	}
	
	function getId_size(){return $this->id_size;}

	function setId_size($value= null)
	{
		$this->id_size = $value;
	}
	
	function getIs_in_ecommerce(){return $this->is_in_ecommerce;}

	function setIs_in_ecommerce($value= null)
	{
		$this->is_in_ecommerce = $value;
	}

	function getIs_in_evidence(){return $this->is_in_evidence;}

	function setIs_in_evidence($value= null)
	{
		$this->is_in_evidence = $value;
	}

	function getIs_in_offer(){return $this->is_in_offer;}

	function setIs_in_offer($value= null)
	{
		$this->is_in_offer = $value;
	}

	function getQuantita(){return $this->quantita;}

	function setQuantita($value= null)
	{
		$this->quantita = $value;
	}

	function getQuantita_caricata(){return $this->quantita_caricata;}

	function setQuantita_caricata($value= null)
	{
		$this->quantita_caricata = $value;
	}

	function getPercentuale_sconto(){return $this->percentuale_sconto;}

	function setPercentuale_sconto($value= null)
	{
		$this->percentuale_sconto = $value;
	}

	function getDdt(){return $this->ddt;}

	function setDdt($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->ddt = (string)$value;
	}
	
	function getPrezzo_acquisto(){return $this->prezzo_acquisto;}

	function setPrezzo_acquisto($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->prezzo_acquisto = (string)$value;
	}
	
	function getFattura_carico(){return $this->fattura_carico;}

	function setFattura_carico($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
								
		$this->fattura_carico = (string)$value;
	}	
	
	function getIva(){return $this->iva;}

	function setIva($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
								
		$this->iva = (string)$value;
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