<?php

class users_anag extends BeanBase
{
	var $id;
	var $name;
	var $surname;
	var $email;
	var $address;
	var $address_secondary;
	var $city;
	var $province;
	var $nation;
	var $cap;
	var $phone;
	var $mobile;
	var $sex;
	var $p_iva;
	var $codice_fiscale;
	var $name_spedizione;
	var $surname_spedizione;
	var $address_spedizione;
	var $address_secondary_spedizione;
	var $city_spedizione;
	var $province_spedizione;
	var $nation_spedizione;
	var $cap_spedizione;
	var $test;
	var $settore_merceologico;

	function users_anag($conn=null, $id=null)
	{
		parent::BeanBase();
		
		$this->table_name = "users_anag";
		
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

		$query="SELECT * FROM ".$this->table_name." WHERE id=". $id ;
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
		$query="SELECT * FROM ".$this->table_name;
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
		//if(!isset($id) || !is_numeric($id) || $id<1)
			//$id=0;
		
						
						
						
						
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
				$query = "UPDATE ".$this->table_name." SET  = 0 WHERE id IN (".implode(", ", $IDS).")";
			else
				$query = "DELETE FROM ".$this->table_name." WHERE id IN (".implode(", ", $IDS).")";
		}
		else
		{
			if($is_logical)
				$query = "UPDATE ".$this->table_name." SET  = 0 WHERE id = ".$IDS[0];
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

	function getName(){return $this->name;}

	function setName($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
		$this->name = (string)$value;
	}

	function getSurname(){return $this->surname;}

	function setSurname($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
		$this->surname = (string)$value;
	}

	function getEmail(){return $this->email;}

	function setEmail($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
		$this->email = (string)$value;
	}

	function getAddress(){return $this->address;}

	function setAddress($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
		$this->address = (string)$value;
	}
	
	function getAddress_secondary(){return $this->address_secondary;}

	function setAddress_secondary($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
		$this->address_secondary = (string)$value;
	}
	

	function getCity(){return $this->city;}

	function setCity($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
		$this->city = (string)$value;
	}

	function getProvince(){return $this->province;}

	function setProvince($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
		$this->province = (string)$value;
	}

	function getNation(){return $this->nation;}

	function setNation($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
		$this->nation = (string)$value;
	}

	function getCap(){return $this->cap;}

	function setCap($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
		$this->cap = (string)$value;
	}

	function getPhone(){return $this->phone;}

	function setPhone($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
		$this->phone = (string)$value;
	}

	function getMobile(){return $this->mobile;}

	function setMobile($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
		$this->mobile = (string)$value;
	}

	function getSex(){return $this->sex;}

	function setSex($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
		$this->sex = (string)$value;
	}

	function getP_iva(){return $this->p_iva;}
	
	function setP_iva($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
			
		$this->p_iva = (string)$value;
	}
	
	function getCodice_fiscale(){return $this->codice_fiscale;}
	
	function setCodice_fiscale($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
			
		$this->codice_fiscale = (string)$value;
	}
	
	
	function getName_spedizione(){return $this->name_spedizione;}

	function setName_spedizione($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
			
		$this->name_spedizione = (string)$value;
	}
	
	function getSurname_spedizione(){return $this->surname_spedizione;}

	function setSurname_spedizione($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
		
		$this->surname_spedizione = (string)$value;
	}
	
	function getAddress_spedizione(){return $this->address_spedizione;}

	function setAddress_spedizione($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
			
		$this->address_spedizione = (string)$value;
	}
	
	function getAddress_secondary_spedizione(){return $this->address_secondary_spedizione;}

	function setAddress_secondary_spedizione($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);

		$this->address_secondary_spedizione = (string)$value;
	}
	
	function getCity_spedizione(){return $this->city_spedizione;}

	function setCity_spedizione($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
								
		$this->city_spedizione = (string)$value;
	}
	
	function getProvince_spedizione(){return $this->province_spedizione;}

	function setProvince_spedizione($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
								
		$this->province_spedizione = (string)$value;
	}
	
	function getNation_spedizione(){return $this->nation_spedizione;}

	function setNation_spedizione($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
								
		$this->nation_spedizione = (string)$value;
	}
	
	function getCap_spedizione(){return $this->cap_spedizione;}

	function setCap_spedizione($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
			
		$this->cap_spedizione = (string)$value;
	}
	
	function getTest(){return $this->test;}
	
	function setTest($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
			
		$this->test = (string)$value;
	}
	
	function getSettore_merceologico(){return $this->settore_merceologico;}
	
	function setSettore_merceologico($value= null)
	{			
		$this->settore_merceologico = (string)$value;
	}
}
?>