<?php

class utenti extends BeanBase
{
	var $id;
	var $username;
	var $password;
	var $privilegi;
	var $timestamp;
	var $mail;
	var $nome;
	var $cognome;
	var $telefono;
	var $categoria;
	var $category;
	var $indirizzo;
	var $cellulare;

	function utenti($conn=null, $id=null)
	{
		parent::BeanBase();
		
		$this->table_name = "utenti";
		
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

	function login($db=null, $user, $pwd)
	{
		if (!$this->_is_connection($db))
			return false;

		$query="SELECT * FROM ".$this->table_name." WHERE username='". $user."' AND password = '".$pwd."'" ;

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
	
	function _getQueryAllUser()
	{
		return "SELECT 
						utenti.id as id, 
						utenti.username as username, 
						utenti.password as password, 
						utenti.privilegi as privilegi, 
						utenti.timestamp as timestamp, 
						utenti.mail as mail, 
						utenti.nome as nome, 
						utenti.cognome as cognome, 
						utenti.telefono as telefono, 
						utenti.indirizzo as indirizzo, 
						utenti.cellulare as cellulare, 
						categorie.id as category_id, 
						categorie.nome as categoria
		FROM (utenti INNER JOIN utenti_categoria ON utenti.id = utenti_categoria.id_utente) INNER JOIN categorie ON utenti_categoria.id_categoria = categorie.id";
	}
	
	function getQueryAllUser()
	{
		return "SELECT 
						utenti.id as id, 
						utenti.username as username, 
						utenti.password as password, 
						utenti.privilegi as privilegi, 
						utenti.timestamp as timestamp, 
						utenti.mail as mail, 
						utenti.nome as nome, 
						utenti.cognome as cognome, 
						utenti.telefono as telefono, 
						utenti.indirizzo as indirizzo, 
						utenti.cellulare as cellulare
				FROM utenti ";
	}
	
	function dbGetAll($db=null, $order_by = null, $order_type = null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();
		if(!empty($order_by))
			$params = ' ORDER BY '. $order_by;
		if(!empty($order_type) && !empty($order_by))
			$params = ' ORDER BY '. $order_by.' '.$order_type;
		$query=$this->getQueryAllUser().$params;
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

	function dbGetAllByIdCat($db=null, $id_cat)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();

		$query=$this->getQueryAllUser().' WHERE categorie.id = '.$id_cat;
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
						
		$values=$this->vars();
		$id = $values['id'];
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
				$this->$func($value[$k]);
		}
	}

	function dbSearch($db=null, $search=null, $order_by = null, $order_type = null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();
		foreach ($search as $key => $value)
		{
			if(!empty($value))
				$where .= $this->table_name.'.'.$key . " LIKE '%" . $value . "%' AND ";
		}
		$where = substr($where, 0, -4);
		
		if(!empty($order_by))
			$params = ' ORDER BY '. $order_by;
		if(!empty($order_type) && !empty($order_by))
			$params = ' ORDER BY '. $order_by.' '.$order_type;

		if(empty($where))
			$query = $this->getQueryAllUser().$params;//"SELECT * FROM ".$this->table_name;
		else 
			$query = $this->getQueryAllUser()." WHERE ".$where.$params;
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
	
	function _dbSearch($db=null, $search=null, $order_by = null, $order_type = null, $BeanUserCategory)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();
		foreach ($search as $key => $value)
		{
			if(!empty($value))
			{
				if($key == 'category')
				{
					$key = 'utenti_categoria.id_categoria';
					$where .= $key . " LIKE '%" . $value . "%' AND ";
				}
				else
					$where .= $this->table_name.'.'.$key . " LIKE '%" . $value . "%' AND ";
			}
		}
		$where = substr($where, 0, -4);
		
		if(!empty($where))
			$where = ' WHERE '.$where;
			
		if(!empty($order_by))
			$params = ' ORDER BY '. $order_by;
		if(!empty($order_type) && !empty($order_by))
			$params = ' ORDER BY '. $order_by.' '.$order_type;

		$result=$db->query($this->getQueryAllUser().$where.$params);
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql

		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if(empty($row['cognome']) || $row['cognome'] == ' ')
			{
				$tmp[$row['id']] = $row;
				$tmp[$row['id']]['category'] = $BeanUserCategory->dbGetByIdUser($db, $row['id']);
			}
			else
			{
				$values[$row['id']] = $row;
				$values[$row['id']]['category'] = $BeanUserCategory->dbGetByIdUser($db, $row['id']);
			}
		}
		if(!empty($tmp) && !empty($values))
			$values = array_merge($values, $tmp);

		$result->free();
		return $values;		
	}
	function dbSearchAccount($db=null, $search=null, $order_by = null, $order_type = null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();
		foreach ($search as $key => $value)
		{
			if(!empty($value))
				$where .= $this->table_name.'.'.$key . " LIKE '%" . $value . "%' AND ";
		}
		$where = substr($where, 0, -4);
		
		if(!empty($order_by))
			$params = ' ORDER BY '. $order_by;
		if(!empty($order_type) && !empty($order_by))
			$params = ' ORDER BY '. $order_by.' '.$order_type;

		if(empty($where))
			$query = $this->getQueryAllUser().' WHERE username IS NOT NULL '.$params;//"SELECT * FROM ".$this->table_name;
		else 
			$query = $this->getQueryAllUser()." WHERE ".$where.' AND username IS NOT NULL '.$params;
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

	function getUsername(){return $this->username;}

	function setUsername($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->username = (string)$value;
	}

	function getPassword(){return $this->password;}

	function setPassword($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->password = (string)$value;
	}

	function getPrivilegi(){return $this->privilegi;}

	function setPrivilegi($value = 1 )
	{
		$this->privilegi = $value;
	}

	function getTimestamp(){return $this->timestamp;}

	function setTimestamp($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->timestamp = (int)$value;
	}

	function getMail(){return $this->mail;}

	function setMail($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->mail = (string)$value;
	}

	function getNome(){return $this->nome;}

	function setNome($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->nome = (string)$value;
	}

	function getCognome(){return $this->cognome;}

	function setCognome($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->cognome = (string)$value;
	}

	function getTelefono(){return $this->telefono;}

	function setTelefono($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->telefono = (string)$value;
	}

	function getCategoria(){return $this->categoria;}

	function setCategoria($value= null)
	{
		$this->categoria = $value;
	}

	function getCategory(){return $this->category;}

	function setCategory($value= null)
	{
		$this->category = $value;
	}
	
	function getIndirizzo(){return $this->indirizzo;}

	function setIndirizzo($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->indirizzo = (string)$value;
	}

	function getCellulare(){return $this->cellulare;}

	function setCellulare($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->cellulare = (string)$value;
	}

			
}
?>