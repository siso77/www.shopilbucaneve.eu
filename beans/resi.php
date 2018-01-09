<?php

class resi extends BeanBase
{
	var $id;
	var $id_contenuto;
	var $id_distributore;
	var $id_cliente;
	var $id_rappresentante;
	var $id_rif_new_age;
	var $id_magazino;
	var $quantita;
	var $data_reso;
	var $data_inserimento_riga;
	var $data_modifica_riga;
	var $is_active;
	var $operatore;

	function resi($conn=null, $id=null)
	{
		parent::BeanBase();
		
		$this->table_name = "resi";
		
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
	
	function dbGetAllByIdCliente($db=null, $id, $BeanMagazzino, $BeanContenuti)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();
		$query="SELECT * FROM ".$this->table_name." WHERE is_active = 1 AND id_cliente = ".$id." ORDER BY id DESC";

		$result=$db->query($query);
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql

		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$BeanMagazzino->dbGetOne($db, $row['id_magazino']);
			$row['magazzino'] = $BeanMagazzino->vars();
			
			$BeanContenuti->dbGetOne($db, $row['magazzino']['id_contenuto']);
			$row['contenuto'] = $BeanContenuti->vars();
			
			$values[]=$row;
		}
		$result->free();
		return $values;
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
			{
				$value[$k] = str_replace('"', "''", stripslashes($value[$k]));
								$this->$func($value[$k]);
			}
		}
	}

	function getView()
	{
		
		return '
				SELECT 
					contenuti.id as id_contenuto, 
					contenuti.isbn, 
					contenuti.titolo, 
					contenuti.descrizione, 
					contenuti_tipo.id as id_contenuto_tipo,
					contenuti_tipo.tipo as contenuto_tipo, 
					contenuti.prezzo, 
					resi.quantita, 
					distributore.id as id_distributore, 
					distributore.nome as distributore, 
					autori.nome as autore, 
					casa_editrice.nome as casa_editrice, 
					clienti.nome, 
					clienti.cognome, 
					rappresentante.nome as rappresentante_nome, 
					rappresentante.cognome as rappresentante_cognome, 
					resi.id,
					resi.id as id_reso,  
					resi.data_reso, 
					resi.data_inserimento_riga,
					resi.data_modifica_riga  
					FROM 
						(
							(
								(
									(
										(								
											(contenuti INNER JOIN resi ON resi.id_contenuto = contenuti.id) 
											INNER JOIN contenuti_tipo ON contenuti.id_contenuto_tipo = contenuti_tipo.id
										) 
										INNER JOIN distributore ON resi.id_distributore = distributore.id
									) 
									INNER JOIN casa_editrice ON contenuti.id_casa_editrice = casa_editrice.id
								) 
								INNER JOIN autori ON contenuti.id_autore = autori.id
							)
							INNER JOIN clienti ON clienti.id = resi.id_cliente
						)
						INNER JOIN rappresentante ON rappresentante.id = resi.id_rappresentante
						';
	}
		
	function dbSearch($db=null, $search=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();

		$query = $this->getView()." AND ".$this->table_name.".is_active = 1 ".$search;


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

	function getId_contenuto(){return $this->id_contenuto;}

	function setId_contenuto($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->id_contenuto = (int)$value;
	}
	
	function getId_distributore(){return $this->id_distributore;}

	function setId_distributore($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->id_distributore = (int)$value;
	}

	function getId_cliente(){return $this->id_cliente;}

	function setId_cliente($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->id_cliente = (int)$value;
	}

	function getId_rappresentante(){return $this->id_rappresentante;}

	function setId_rappresentante($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->id_rappresentante = (int)$value;
	}

	function getId_rif_new_age(){return $this->id_rif_new_age;}

	function setId_rif_new_age($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->id_rif_new_age = (int)$value;
	}

	function getId_magazino(){return $this->id_magazino;}

	function setId_magazino($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->id_magazino = (int)$value;
	}

	function getQuantita(){return $this->quantita;}

	function setQuantita($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->quantita = (string)$value;
	}

	function getData_reso(){return $this->data_reso;}

	function setData_reso($value= null)
	{
				
				if(strrpos($value, "-") != 7)
		{
			echo "Errore nel settaggio della properies data_visione!";
			exit;
		}
								
		$this->data_reso = (string)$value;
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