<?php

class wora_users_anag extends BeanBase
{
	var $id;
	var $name;
	var $surname;
	var $email;

	function wora_users_anag($conn=null, $id=null)
	{
		parent::BeanBase();
		
		$this->table_name = "wora_users_anag";
		
		if(isset($id))
		{
			if(is_array($id))
				$this->fill($id);
			elseif(is_numeric($id) && $id>0)
				$this->dbGetOne($conn, $id);
		}
	}

	function login($db=null, $username, $password)
	{
		$ret = false;
		$query="SELECT * FROM ".$this->table_name." WHERE username = '". $username . "' AND password = '". $password ."' AND is_active = 1";
		$result=$db->query($query);
		
		if($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
			$ret = $row;

		$result->free();
		
		return $ret;
	}
	
	function dbGetOne($db=null, $id=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$query="SELECT * FROM ".$this->table_name." WHERE ID=". $id . "";
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
		//$this->setDate_registred(date("Y-m-d"));
		//$this->setIs_active(1);
		$values=$this->vars();
		
		
		$table_fields=array_keys($values);
		$table_values=array_values($values);

		$sth = $db->autoPrepare($this->table_name, $table_fields, DB_AUTOQUERY_INSERT);		
		$PearRet = $db->execute($sth, $table_values);

		if(get_class($PearRet) == "DB_Error")
			return false;

		//		Loggo la query sql
		$this->BeanLog("PEAR_DB", $db);
		//		Loggo la query sql

		return $id;
	}

	function _dbUpdate($db=null)
	{
		if(!$this->_is_connection($db))
			return false;
					
		//$this->setIs_active(1);
								
		$values=$this->vars();
		$id = $values['id'];
		unset($values['id']);

		$table_fields = array_keys($values);
		$table_values = array_values($values);

		$sth = $db->autoPrepare($this->table_name, $table_fields, DB_AUTOQUERY_UPDATE, "id = ".$id);
		$PearRet = $db->execute($sth, $table_values);
		//		Loggo la query sql
		$this->BeanLog("PEAR_DB", $db);
		//		Loggo la query sql
		
		if(get_class($PearRet) == "DB_Error")
			return false;
		
		return $this->vars();
	}

	function dbStore($db=null)
	{
		if(!$this->_is_connection($db))
			return false;

		if(isset($this->id) && is_numeric($this->id) && $this->id > 0)
			return $this->_dbUpdate($db);
		else
			return $this->_dbAdd($db);
	}

	function fast_edit($db, $ID=null, $key="", $value="")
	{
		if(!$this->_is_connection($db))
			return false;
		
		$query="UPDATE ".$this->table_name." SET ".$key."='".$value."' WHERE id =".$ID."";

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

	function dbDelete($db=null, $id=null)
	{
		$query = "DELETE FROM ".$this->table_name." WHERE id = ".$id;
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
		if(strlen($value) > 10)
			$value = substr($value, 0, 10);
								
		$this->id = (int)$value;
	}

	function getId_type(){return $this->id_type;}

	function setId_type($value= null)
	{
				if(strlen($value) > 10)
			$value = substr($value, 0, 10);
				
								
		$this->id_type = (int)$value;
	}

	function getName(){return $this->name;}

	function setName($value= null)
	{
		$this->name = str_replace('\\', '', str_replace("'", "`", $value));
	}

	function getSurname(){return $this->surname;}

	function setSurname($value= null)
	{		
		$this->surname = str_replace('\\', '', str_replace("'", "`", $value));
	}

	function getEmail(){return $this->email;}

	function setEmail($value= null)
	{
		$this->email = $value;
	}			
}
?>