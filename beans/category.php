<?php

class category extends BeanBase
{
	var $id;
	var $name;
	var $description;
	var $name_en;
	var $description_en;
	var $url;
	var $parent_id;

	function category($conn=null, $id=null)
	{
		parent::BeanBase();
		
		$this->table_name = "category";
		
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

	function dbGetAll($db=null, $order_by, $order_type)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();
		$query="SELECT * FROM ".$this->table_name.' ORDER BY '.$order_by.' '.$order_type;
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

	function dbGetAllByName($db=null, $name)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=false;
		$query="SELECT * FROM ".$this->table_name." WHERE name = '".$name."'";
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
	
	function _dbAdd($db=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$id = $db->nextId($this->table_name);
		$this->setID($id);

		if(is_null($this->parent_id))
			$this->setParent_id($id);
		if(empty($this->url))
			$this->setUrl('parentId='.$id);
			
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
				$query = "UPDATE ".$this->table_name." SET  = 0 WHERE id = ".$IDS;
			else
				$query = "DELETE FROM ".$this->table_name." WHERE id = ".$IDS;
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


	/*		Custom Function		*/
	function dbGetCategoryTree($db=null, $order_by = null, $order_type = null, $idCategoryToFilter = null)
	{
		if (!$this->_is_connection($db))
			return false;

		$params = '';
		if(!empty($idCategoryToFilter))
			$params .= ' AND id='.$idCategoryToFilter;
		//if(!empty($order_by))
			//$params .= ' ORDER BY '.$order_by.' '.$order_type;
		$values=array();
		$query="SELECT * FROM ".$this->table_name." WHERE parent_id = 0".$params;
		$result=$db->query($query);
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql
		
		include_once(APP_ROOT."/beans/content.php");
		$BeanContent = new content();
		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if(!empty($order_by))
				$params = ' ORDER BY '.$order_by.' '.$order_type;

			$numContent = $BeanContent->dbGetCountContent($db, ' AND category.id = '.$row['id']);
			$row['num_contents'] = $numContent;

			$sub_query="SELECT * FROM ".$this->table_name." WHERE parent_id = ".$row['id'].$params;
			$res=$db->query($sub_query);
			
			while($r=$res->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$nContent = $BeanContent->dbGetCountContent($db, ' AND category.id = '.$r['id']);
				$r['num_contents'] = $nContent;
				if($r['num_contents'] > 0)
					$sub_cat[] = $r;
			}
			$values[$row['id']]=$row;
			$values[$row['id']]['sub_category']=$sub_cat;
			$sub_cat = array();
		}

		$result->free();
		return $values;
	}	

	function dbGetCategoryByParentId($db=null, $parentId)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();
		$query="SELECT * FROM ".$this->table_name." WHERE parent_id = ".$parentId;
		$result=$db->query($query);
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql

		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$values[] = $row['id'];
		}

		$result->free();
		return $values;
	}	

	function dbGetCategoryByName($db=null, $name)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();
		$query="SELECT * FROM ".$this->table_name." WHERE name = '".$name."'";

		$result=$db->query($query);
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql

		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$values[] = $row;
		}

		$result->free();
		return $values;
	}	
	
	function dbGetRootCategory($db=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();
		$query="SELECT * FROM ".$this->table_name." WHERE parent_id = 0";
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
	
	/*		Custom Function		*/
	
	/*			GET e SET		*/	
	function getId(){return $this->id;}

	function setId($value= null)
	{
				if(strlen($value) > 10)
			$value = substr($value, 0, 10);
				
								
		$this->id = (int)$value;
	}

	function getName(){return $this->name;}

	function setName($value= null)
	{
				if(strlen($value) > 50)
			$value = substr($value, 0, 50);
				
								
		$this->name = (string)$value;
	}

	function getDescription(){return $this->description;}

	function setDescription($value= null)
	{
				if(strlen($value) > 50)
			$value = substr($value, 0, 50);
				
								
		$this->description = (string)$value;
	}

	function getName_en(){return $this->name_en;}

	function setName_en($value= null)
	{
				if(strlen($value) > 50)
			$value = substr($value, 0, 50);
				
								
		$this->name_en = (string)$value;
	}

	function getDescription_en(){return $this->description_en;}

	function setDescription_en($value= null)
	{
				if(strlen($value) > 50)
			$value = substr($value, 0, 50);
				
								
		$this->description_en = (string)$value;
	}
	
	function getUrl(){return $this->url;}

	function setUrl($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->url = (string)$value;
	}

	function getParent_id(){return $this->parent_id;}

	function setParent_id($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->parent_id = (int)$value;
	}		
}
?>