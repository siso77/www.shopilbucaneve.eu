<?php
class wora_users extends BeanBase
{
	var $id;
	var $id_type;
	var $id_anag;
	var $username;
	var $password;
	var $last_access;
	var $operatore;
	var $data_inserimento_riga;
	var $data_modifica_riga;
	var $is_active;
	
	function wora_users($conn=null, $id=null)
	{
		parent::BeanBase();
		
		$this->table_name = "wora_users";
		
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
		$query="SELECT * FROM ".$this->table_name." WHERE username = '". $username . "' AND password = '". $password ."'";
		$result=$db->query($query);

		if($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$ret = $row;
			$this->fill($row);
		}
		$result->free();
		return $ret;
	}
	
	function dbGetOne($db=null, $id=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$query="SELECT * FROM ".$this->table_name." WHERE id=". $id . "";
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
		//$query="SELECT * FROM ".$this->table_name." WHERE is_active = 1";
		$query="SELECT * FROM ".$this->table_name." WHERE id_owner = 0";
		$result=$db->query($query);
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql
		$i = 0;
		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$BeanWoraUsersAnag = new wora_users_anag();
			$BeanWoraUsersType = new wora_users_type();
			
			$WoraUsersAnag = $BeanWoraUsersAnag->dbGetOne($db, $row['id_anag']);
			$WoraUsersType = $BeanWoraUsersType->dbGetOne($db, $row['id_type']);

			$values[$i]=$row;
			$values[$i]['anag'] = $WoraUsersAnag;
			$values[$i]['type'] = $WoraUsersType;
			$i++;
		}
		$result->free();
		
		return $values;
	}
	
	function dbGetAllByIdOwner($db=null, $id_owner)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();
		//$query="SELECT * FROM ".$this->table_name." WHERE is_active = 1";
		$query="SELECT * FROM ".$this->table_name." WHERE id_owner = ".$id_owner;
		$result=$db->query($query);
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql
		$i = 0;
		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$BeanWoraUsersAnag = new wora_users_anag();
			$BeanWoraUsersType = new wora_users_type();
			
			$WoraUsersAnag = $BeanWoraUsersAnag->dbGetOne($db, $row['id_anag']);
			$WoraUsersType = $BeanWoraUsersType->dbGetOne($db, $row['id_type']);

			$values[$i]=$row;
			$values[$i]['anag'] = $WoraUsersAnag;
			$values[$i]['type'] = $WoraUsersType;
			$i++;
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
		
		$this->setData_inserimento_riga(date('Y-m-d'));
		$this->setData_modifica_riga(date('Y-m-d'));
		$this->setIs_active(1);
		
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
		$this->setData_modifica_riga(date('Y-m-d'));					
		$this->setIs_active(1);
		
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

	function dbSearch($db=null, $where=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();
//		$query = "SELECT * FROM ".$this->table_name." WHERE `".$this->table_name."`.is_active = 1 ".$where;
		$query = 'SELECT 
			wora_users.id as id, 
			wora_users.username, 
			wora_users.last_access, 
			wora_users_anag.name, 
			wora_users_anag.surname, 
			wora_users_anag.email, 
			wora_users_type.name as type,
			wora_users.data_inserimento_riga, 
			wora_users.data_modifica_riga
		FROM 
			(
				wora_users INNER JOIN wora_users_type ON wora_users.id_type = wora_users_type.id
			) 
			INNER JOIN wora_users_anag ON wora_users.id_anag = wora_users_anag.id';
		
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

	function getId_anag(){return $this->id_anag;}

	function setId_anag($value= null)
	{
				if(strlen($value) > 10)
			$value = substr($value, 0, 10);
				
								
		$this->id_anag = (int)$value;
	}

	function getId_owner(){return $this->id_owner;}

	function setId_owner($value= null)
	{
				if(strlen($value) > 10)
			$value = substr($value, 0, 10);
				
								
		$this->id_owner = (int)$value;
	}
	
	function getUsername(){return $this->username;}

	function setUsername($value= null)
	{
		$value = str_replace('\\', '', str_replace("'", "`", $value));
							
		$this->username = (string)$value;
	}

	function getPassword(){return $this->password;}

	function setPassword($value= null)
	{
		$this->password = str_replace('\\', '', str_replace("'", "`", $value));
	}			

	function getLast_access(){return $this->last_access;}

	function setLast_access($value= null)
	{
		$this->last_access = date('Y-m-d H:i:s');
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