<?php

class newsletters extends BeanBase
{
	var $id;
	var $object;
	var $date_last_modify;

	function newsletters($conn=null, $id=null)
	{
		parent::BeanBase();
		
		$this->table_name = "newsletters";
		
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
		$this->setDate_last_modify(date('Y-m-d H:i:s'));

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

						//$id = $this->id;
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
				$this->$func($value[$k]);
		}
	}

	function dbSearch($db=null, $search=null, $order_by = null, $order_type = null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();

		if(!empty($order_by))
			$params = ' ORDER BY '. $order_by;
		if(!empty($order_type) && !empty($order_by))
			$params = ' ORDER BY '. $order_by.' '.$order_type;
			
		$where = "";
		foreach($search as $key => $val)
			$where .= $key." LIKE '%".$val."%' AND ";
		$where = substr($where, 0, -4);
		
		$query = 'SELECT distinct(newsletters.id), newsletters.object, newsletters.date_last_modify 
					FROM newsletters INNER JOIN (newsletter_data INNER JOIN newsletters_to_newsletter_data ON newsletter_data.id = newsletters_to_newsletter_data.id_newsletter_data) ON newsletters.id = newsletters_to_newsletter_data.id_newsletter';
		if(!is_null($search))
			$query .= ' WHERE '.$where;

		$result=$db->query($query.$params);

		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql

		$i = 0;
		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if(empty($row['object']))
				$row['object'] = '(Nessun Oggetto)';

			$query = 'SELECT newsletter_data.id, newsletter_data.titolo, newsletter_data.news  
						FROM newsletters INNER JOIN (newsletter_data INNER JOIN newsletters_to_newsletter_data ON newsletter_data.id = newsletters_to_newsletter_data.id_newsletter_data) ON newsletters.id = newsletters_to_newsletter_data.id_newsletter';
			$query .= ' WHERE newsletters.id = '.$row['id'];
				
			$res=$db->query($query);
			$newsData = null;
			while($r=$res->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$newsData[] = $r;
			}
			$values[$i]=$row;
			$values[$i]['news_data'] = $newsData;
		
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

	function getObject(){return $this->object;}

	function setObject($value= null)
	{
		$value = str_replace('\\', '', $value);
		$value = htmlspecialchars($value, ENT_QUOTES);
		$value = htmlentities($value, ENT_QUOTES | ENT_IGNORE, "UTF-8");
		
		$this->object = $value;
	}
	
	function getDate_last_modify(){return $this->date_last_modify;}

	function setDate_last_modify($value= null)
	{
		$exp = explode(" ", $value);
		if(strrpos($exp[0], "-") != 7)
		{
			echo "Errore nel settaggio della properies date_last_modify il valore ".$exp[0]." risulta essere incorretto!";
			exit;
		}
		$expTime = explode(":", $exp[1]);
		if(count($expTime) <= 1)
		{
			echo "Errore nel settaggio della properies date_last_modify il valore ".$exp[1]." risulta essere incorretto!";
			exit;
		}
						$expTime = explode(":", $value);
		if(count($expTime) <= 1 || count($expTime) == 2)
		{
			echo "Errore nel settaggio della properies date_last_modify il valore ".$value." risulta essere incorretto!";
			exit;
		}
				
		$this->date_last_modify = (string)$value;
	}
}
?>