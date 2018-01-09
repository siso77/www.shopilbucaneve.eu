<?php

class content extends BeanBase
{
	var $id;
//	var $bar_code;
	var $name_it;
	var $description_it;
	var $name_en;
	var $description_en;
	var $id_brand;
	var $id_category;
	var $price_it;
	var $price_it_1;
	var $price_it_2;
	var $price_it_3;
	var $price_it_4;
	var $price_discounted_it;
	var $data_inserimento_riga;
	var $data_modifica_riga;
	var $is_active;
	var $operatore;

	var $is_in_ecommerce;
	var $is_in_evidence;
	var $is_invisible;
	var $is_in_offer;
	
	
	function content($conn=null, $id=null)
	{
		parent::BeanBase();
		
		$this->table_name = "content";
		
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

	function dbGetOneByBarCode($db=null, $barCode=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$query="SELECT * FROM magazzino WHERE bar_code='". $barCode ."' AND is_active = 1 ORDER BY id DESC";

		$result=$db->query($query);
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql

		if($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
			$this->fill($row);

		$result->free();

		return $row;
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
	
	function getView()
	{
		return "SELECT 
					content.id, 
					content.id as id_content,
					content.name_it, 
					content.description_it, 
					content.name_en, 
					content.description_en, 
					content.price_it, 
					content.price_discounted_it, 
					content.price_it_1, 
					content.price_it_2, 
					content.price_it_3, 
					content.price_it_4, 
					content.data_inserimento_riga, 
					content.data_modifica_riga, 
					content.operatore,
					category.id as id_category,
					category.name, 
					category.description, 
					category.name_en as category_name_en,
					category.description_en as category_description_en, 
					category.url, 
					category.parent_id,
					magazzino.bar_code,
					magazzino.quantita,
					sizes.size,
					color.color
				FROM
					content
				INNER JOIN category ON content.id_category = category.id
				INNER JOIN magazzino ON content.id = magazzino.id_content
				INNER JOIN color ON magazzino.id_color = color.id
				INNER JOIN sizes ON magazzino.id_size = sizes.id";
	}
	
	
	function dbGetCountContent($db=null, $search=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=null;

		$query = $this->getView().' WHERE '.$this->table_name.'.is_active = 1 AND '.$this->table_name.'.is_invisible = 0 '.$search;
		$result=$db->query($query);

		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql

		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
			$values[] = $row;

		$result->free();
		
		return count($values);
	}

	function dbSearch($db=null, $search=null, $objMagazzino = null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=null;

		$query = $this->getView().' WHERE '.$this->table_name.'.is_active = 1 AND '.$this->table_name.'.is_invisible = 0 AND magazzino.quantita > 0 '.$search;
		$result=$db->query($query);

		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql

		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$row['magazzino'] = null;
			if(!empty($objMagazzino))
			{
				//$where = ' AND magazzino.quantita > 0 AND content.id = '.$row['id'].' ORDER BY magazzino.quantita ASC';
				$where = ' AND magazzino.quantita > 0 AND content.id = '.$row['id'].' ORDER BY magazzino.data_modifica_riga DESC';
				$mag = $objMagazzino->dbSearch($db, $where);
				
				$i = -1;
				$lastBarcode = '';
				$ListAssign = null;

				foreach ($mag as $k => $value)
				{
					if($value['bar_code'] != $lastBarcode && !is_null($value['bar_code']))
					{
						$i++;
						$lastBarcode = $value['bar_code'];
						$ListAssign[$i] = $value;
					}
					else if(!is_null($value['bar_code']))
					{
						$ListAssign[$i]['quantita'] = $ListAssign[$i]['quantita']+$value['quantita'];
					}
				}
				
				$row['magazzino'] = $ListAssign;
				if(!empty($row['magazzino']))
					$products[]= $row;
			}
			if(empty($row['magazzino']) && $row['quantita'] > 0)
				$values[]=$row;
		}
		$result->free();

		if(!empty($values) && !empty($products))
			$values = array_merge($products, $values);
		elseif(!empty($products))
			$values = $products;
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

//	function getBar_code(){return $this->bar_code;}
//
//	function setBar_code($value= null)
//	{
//				if(strlen($value) > 255)
//			$value = substr($value, 0, 255);
//				
//								
//		$this->bar_code = (string)$value;
//	}

	function getName_it(){return $this->name_it;}

	function setName_it($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->name_it = (string)$value;
	}

	function getDescription_it(){return $this->description_it;}

	function setDescription_it($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->description_it = (string)$value;
	}
	
	function getName_en(){return $this->name_en;}

	function setName_en($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->name_en = (string)$value;
	}

	function getDescription_en(){return $this->description_en;}

	function setDescription_en($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->description_en = (string)$value;
	}
	
	function getId_brand(){return $this->id_brand;}

	function setId_brand($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->id_brand = (int)$value;
	}

	function getId_category(){return $this->id_category;}

	function setId_category($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->id_category = (int)$value;
	}

	function getPrice_it(){return $this->price_it;}

	function setPrice_it($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->price_it = (string)$value;
	}

	function getPrice_it_1(){return $this->price_it_1;}

	function setPrice_it_1($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->price_it_1 = (string)$value;
	}
	
	function getPrice_it_2(){return $this->price_it_2;}

	function setPrice_it_2($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->price_it_2 = (string)$value;
	}

	function getPrice_it_3(){return $this->price_it_3;}

	function setPrice_it_3($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->price_it_3 = (string)$value;
	}

	function getPrice_it_4(){return $this->price_it_4;}

	function setPrice_it_4($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->price_it_4 = (string)$value;
	}

	function getPrice_discounted_it(){return $this->price_discounted_it;}

	function setPrice_discounted_it($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
								
		$this->price_discounted_it = (string)$value;
	}
		
	function getData_inserimento_riga(){return $this->data_inserimento_riga;}

	function setData_inserimento_riga($value= null)
	{
		$this->data_inserimento_riga = $value;
	}

	function getData_modifica_riga(){return $this->data_modifica_riga;}

	function setData_modifica_riga($value= null)
	{
		$this->data_modifica_riga = $value;
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

			
	function getIs_in_ecommerce(){return $this->is_in_ecommerce;}

	function setIs_in_ecommerce($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->is_in_ecommerce = (int)$value;
	}

	function getIs_invisible(){return $this->is_invisible;}

	function setIs_invisible($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->is_invisible = (int)$value;
	}
	
	function getIs_in_evidence(){return $this->is_in_evidence;}

	function setIs_in_evidence($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->is_in_evidence = (int)$value;
	}

	function getIs_in_offer(){return $this->is_in_offer;}

	function setIs_in_offer($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->is_in_offer = (int)$value;
	}	
}
?>