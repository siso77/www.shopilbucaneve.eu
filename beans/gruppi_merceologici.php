<?php

class gruppi_merceologici extends BeanBase
{
	var $id;
	var $gruppo;

	function gruppi_merceologici($conn=null, $id=null)
	{
		parent::BeanBase();

		$this->table_name = "gruppi_merceologici";

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

	function dbGetForCombo($db=null, $order = '')
	{
		if (!$this->_is_connection($db))
			return false;

		$values=null;
// 		$query="SELECT gruppi_merceologici.* FROM gruppi_merceologici INNER JOIN `content` ON content.id_gm = gruppi_merceologici.id GROUP BY gruppi_merceologici.id".$order;
// 		$query="SELECT gruppi_merceologici.* FROM gruppi_merceologici INNER JOIN `content` ON content.id_gm = gruppi_merceologici.id WHERE  content.prezzo_0 > 0 GROUP BY gruppi_merceologici.id".$order;
		$query="SELECT gruppi_merceologici.* FROM `gruppi_merceologici` INNER JOIN content ON content.id_gm = gruppi_merceologici.id INNER JOIN giacenze ON content.id = giacenze.id_content WHERE giacenze.disponibilita >= 0 GROUP BY gruppi_merceologici.id ".$order;				
		$result=$db->query($query);
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql
		
		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$q="SELECT famiglie.famiglia, famiglie.id
				FROM giacenze
					 INNER JOIN content ON content.id = giacenze.id_content
					 INNER JOIN famiglie ON giacenze.id_famiglia = famiglie.id
					 INNER JOIN gruppi_merceologici ON giacenze.id_gm = gruppi_merceologici.id
				WHERE gruppi_merceologici.gruppo = '".$row['gruppo']."' AND giacenze.disponibilita >= 0
				GROUP BY famiglie.famiglia
				ORDER BY famiglie.famiglia";
			$res=$db->query($q);
			while($r=$res->fetchRow(DB_FETCHMODE_ASSOC))
				$row['famiglie'][] = $r;

			$values[]=$row;
		}

		$result->free();
		return $values;
	}
	
	function dbGetAll($db=null, $order = '')
	{
		if (!$this->_is_connection($db))
			return false;

		$values=null;
		$query="SELECT * FROM ".$this->table_name.$order;
		$query="SELECT gruppi_merceologici.* FROM `gruppi_merceologici` INNER JOIN content ON content.id_gm = gruppi_merceologici.id INNER JOIN giacenze ON content.vbn = giacenze.bar_code WHERE giacenze.disponibilita > 0 GROUP BY gruppi_merceologici.id ".$order;

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

	function dbGetByGruppo($db=null, $gruppo = null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=false;
		$query="SELECT * FROM ".$this->table_name." WHERE gruppo = '".$gruppo."'";
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

		function getGruppo(){return $this->gruppo;}

	function setGruppo($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->gruppo = (string)$value;
	}

			
}
?>