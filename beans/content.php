<?php

class content extends BeanBase
{
	var $id;
	var $id_gm;
	var $id_famiglia;
	
	var $id_settore;
	var $id_reparto;
	
	var $nome_it;
	var $descrizione_it;
	var $nome_en;
	var $descrizione_en;
	var $vbn;
	var $C1;
	var $C2;
	var $C3;
	var $C4;
	var $C5;
	var $tipo_colore;
	var $prezzo_0;
	var $prezzo_1;
	var $prezzo_2;
	var $prezzo_3;
	var $prezzo_4;
	var $prezzo_5;
	var $prezzo_6;
	var $prezzo_7;
	var $prezzo_8;
	var $prezzo_9;
	var $prezzo_brico;
	var $gs_barcode;
	var $cod_art_brico;
	var $desc_brico;
	var $cod_iva;
	
	var $qta_min_ord;
	var $qta_scatola;
	
	var $data_inserimento_riga;
	var $data_modifica_riga;
	var $is_active;
	var $is_vbn_updated;
	var $operatore;

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

	function dbFree($db=null, $query = null)
	{
		if (!$this->_is_connection($db) || empty($query))
			return false;

		$result=$db->query($query);
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql

		if($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
			$ret = $row;

		$result->free();
		return $ret;
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

	function dbGetAllCaratteristiche($db=null, $caratteristica)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();
		$query="SELECT DISTINCT(".$caratteristica.") as value FROM ".$this->table_name." WHERE is_active = 1 ORDER BY ".$caratteristica." ASC";
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

	function dbGetAllIdKey($db=null, $key = 'id')
	{
		if (!$this->_is_connection($db))
			return false;
	
		$values=array();
		$query="SELECT * FROM ".$this->table_name." WHERE is_active = 1 AND operatore = 'StreamImportProcedure'";
		$result=$db->query($query);
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);
	
		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql
	
		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
			$values[$row[$key]]=$row;
	
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

	function dbDeleteAll($db=null, $where = null)
	{
		$query = "DELETE FROM ".$this->table_name."".$where;
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

function dbSearchProductInfo($db=null, $search=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();
		
		$query="SELECT
					content.id,
					content.id_famiglia,
					content.id_gm,
					content.nome_it,
					content.vbn,
					content.C1,
					content.C2,
					content.C3,
					content.C4,
					content.C5,
					content.tipo_colore,
					content.prezzo_0,
					content.prezzo_1,
					content.prezzo_2,
					content.prezzo_3,
					content.prezzo_4,
					content.prezzo_5,
					content.prezzo_6,
					content.prezzo_7,
					content.prezzo_8,
					content.prezzo_9,
				
					content.qta_min_ord,
					content.qta_scatola,
				
					content.cod_iva,
					famiglie.famiglia,
					gruppi_merceologici.gruppo
				FROM
					content
				INNER JOIN famiglie ON content.id_famiglia = famiglie.id
				INNER JOIN gruppi_merceologici ON content.id_gm = gruppi_merceologici.id
				
				WHERE content.is_active = 1 ".$search;
		
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
	
	function dbSearch($db=null, $search=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();
		
		$query="SELECT
					content.id,
					content.id_famiglia,
					content.id_gm,
					content.nome_it,
					content.vbn,
					content.C1,
					content.C2,
					content.C3,
					content.C4,
					content.C5,
					content.tipo_colore,
					content.prezzo_0,
					content.prezzo_1,
					content.prezzo_2,
					content.prezzo_3,
					content.prezzo_4,
					content.prezzo_5,
					content.prezzo_6,
					content.prezzo_7,
					content.prezzo_8,
					content.prezzo_9,
				
					content.qta_min_ord,
					content.qta_scatola,
				
					prezzo_brico,
					gs_barcode,
					cod_art_brico,
					desc_brico,
				
					content.cod_iva,
					famiglie.famiglia,
					gruppi_merceologici.gruppo
				FROM
					content
				INNER JOIN famiglie ON content.id_famiglia = famiglie.id
				INNER JOIN gruppi_merceologici ON content.id_gm = gruppi_merceologici.id
				
				INNER JOIN settori_merceologici ON content.id_settore = settori_merceologici.id
				INNER JOIN reparti_merceologici ON content.id_reparto = reparti_merceologici.id
				
				WHERE content.is_active = 1 ".$search;
		
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
	
	function dbSearchDisponibili($db=null, $search=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();
		
		$query="SELECT giacenze.* FROM giacenze INNER JOIN content ON content.id = giacenze.id_content WHERE giacenze.is_active = 1 AND giacenze.disponibilita > 0 ".$search;
		
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
	
	function dbSearchCounted($db=null, $search=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();
		
// 		$query="SELECT
// 					count(content.id) as num
// 				FROM
// 					content
// 				INNER JOIN famiglie ON content.id_famiglia = famiglie.id
// 				INNER JOIN gruppi_merceologici ON content.id_gm = gruppi_merceologici.id
// 				INNER JOIN settori_merceologici ON content.id_settore = settori_merceologici.id
// 				INNER JOIN reparti_merceologici ON content.id_reparto = reparti_merceologici.id
				
// 				WHERE content.is_active = 1 ".$search;
		
		$query="SELECT count(content.id) as num FROM giacenze INNER JOIN content ON content.id = giacenze.id_content WHERE giacenze.is_active = 1 AND giacenze.disponibilita > 0 ".$search;
		
		$result=$db->query($query);
		
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql

		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
			$values=$row;
			
		$result->free();
		return $values;
	}
	
	function dbSearchCountedDisponibili($db=null, $search=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();
		
// 		$query="SELECT
// 					count(giacenze.id) as num
// 				FROM
// 					giacenze
// 				INNER JOIN content ON content.id = giacenze.id_content 
// 				WHERE giacenze.is_active = 1 ".$search;
		
		$query="SELECT count(content.id) as num FROM giacenze INNER JOIN content ON content.id = giacenze.id_content WHERE giacenze.is_active = 1 AND giacenze.disponibilita > 0 ".$search;
		
		$result=$db->query($query);
		
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql

		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
			$values=$row;
			
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

		function getId_gm(){return $this->id_gm;}

	function setId_gm($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->id_gm = (int)$value;
	}

	function getId_famiglia(){return $this->id_famiglia;}

	function setId_famiglia($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->id_famiglia = (int)$value;
	}

	function getId_settore(){return $this->id_settore;}

	function setId_settore($value= null)
	{
		$this->id_settore = (int)$value;
	}

	function getId_reparto(){return $this->id_reparto;}
	
	function setId_reparto($value= null)
	{
		$this->id_reparto = (int)$value;
	}
	
	function getNome_it(){return $this->nome_it;}

	function setNome_it($value= null)
	{
				
								
		$this->nome_it = (string)$value;
	}

		function getDescrizione_it(){return $this->descrizione_it;}

	function setDescrizione_it($value= null)
	{
				
								
		$this->descrizione_it = (string)$value;
	}

		function getNome_en(){return $this->nome_en;}

	function setNome_en($value= null)
	{
				
								
		$this->nome_en = (string)$value;
	}

		function getDescrizione_en(){return $this->descrizione_en;}

	function setDescrizione_en($value= null)
	{
				
								
		$this->descrizione_en = (string)$value;
	}

		function getVbn(){return $this->vbn;}

	function setVbn($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->vbn = (string)$value;
	}

		function getC1(){return $this->C1;}

	function setC1($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->C1 = (string)$value;
	}

		function getC2(){return $this->C2;}

	function setC2($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->C2 = (string)$value;
	}

		function getC3(){return $this->C3;}

	function setC3($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->C3 = (string)$value;
	}

		function getC4(){return $this->C4;}

	function setC4($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->C4 = (string)$value;
	}

	function getC5(){return $this->C5;}

	function setC5($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->C5 = (string)$value;
	}

	
	function getTipo_colore(){return $this->tipo_colore;}

	function setTipo_colore($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->tipo_colore = (string)$value;
	}

		
		function getPrezzo_0(){return $this->prezzo_0;}

	function setPrezzo_0($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->prezzo_0 = (string)$value;
	}

		function getPrezzo_1(){return $this->prezzo_1;}

	function setPrezzo_1($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->prezzo_1 = (string)$value;
	}

		function getPrezzo_2(){return $this->prezzo_2;}

	function setPrezzo_2($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->prezzo_2 = (string)$value;
	}

		function getPrezzo_3(){return $this->prezzo_3;}

	function setPrezzo_3($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->prezzo_3 = (string)$value;
	}

		function getPrezzo_4(){return $this->prezzo_4;}

	function setPrezzo_4($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->prezzo_4 = (string)$value;
	}

		function getPrezzo_5(){return $this->prezzo_5;}

	function setPrezzo_5($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->prezzo_5 = (string)$value;
	}

		function getPrezzo_6(){return $this->prezzo_6;}

	function setPrezzo_6($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->prezzo_6 = (string)$value;
	}

		function getPrezzo_7(){return $this->prezzo_7;}

	function setPrezzo_7($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->prezzo_7 = (string)$value;
	}

		function getPrezzo_8(){return $this->prezzo_8;}

	function setPrezzo_8($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->prezzo_8 = (string)$value;
	}

	function getPrezzo_9(){return $this->prezzo_9;}

	function setPrezzo_9($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->prezzo_9 = (string)$value;
	}

	function getPrezzo_brico(){return $this->prezzo_brico;}

	function setPrezzo_brico($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
								
		$this->prezzo_brico = (string)$value;
	}
	
	function getGs_barcode(){return $this->gs_barcode;}

	function setGs_barcode($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
								
		$this->gs_barcode = (string)$value;
	}

	function getCod_art_brico(){return $this->cod_art_brico;}

	function setCod_art_brico($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
								
		$this->cod_art_brico = (string)$value;
	}

	function getDesc_brico(){return $this->desc_brico;}

	function setDesc_brico($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
								
		$this->desc_brico = (string)$value;
	}

	
	function getCod_iva(){return $this->cod_iva;}

	function setCod_iva($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->cod_iva = (int)$value;
	}
	
	function getQta_min_ord(){return $this->qta_min_ord;}

	function setQta_min_ord($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->qta_min_ord = (int)$value;
	}

	function getQta_scatola(){return $this->qta_scatola;}
	
	function setQta_scatola($value= null)
	{
		if(strlen($value) > 11)
			$value = substr($value, 0, 11);
	
	
		$this->qta_scatola = (int)$value;
	}
	

		function getData_inserimento_riga(){return $this->data_inserimento_riga;}

	function setData_inserimento_riga($value= null)
	{
				
						$exp = explode(" ", $value);
		if(strrpos($exp[0], "-") != 7)
		{
			echo "Errore nel settaggio della properies data_inserimento_riga il valore ".$exp[0]." risulta essere incorretto!";
			exit;
		}
		$expTime = explode(":", $exp[1]);
		if(count($expTime) <= 1)
		{
			echo "Errore nel settaggio della properies data_inserimento_riga il valore ".$exp[1]." risulta essere incorretto!";
			exit;
		}
						$expTime = explode(":", $value);
		if(count($expTime) <= 1 || count($expTime) == 2)
		{
			echo "Errore nel settaggio della properies data_inserimento_riga il valore ".$value." risulta essere incorretto!";
			exit;
		}
				
		$this->data_inserimento_riga = (string)$value;
	}

		function getData_modifica_riga(){return $this->data_modifica_riga;}

	function setData_modifica_riga($value= null)
	{
				
						$exp = explode(" ", $value);
		if(strrpos($exp[0], "-") != 7)
		{
			echo "Errore nel settaggio della properies data_modifica_riga il valore ".$exp[0]." risulta essere incorretto!";
			exit;
		}
		$expTime = explode(":", $exp[1]);
		if(count($expTime) <= 1)
		{
			echo "Errore nel settaggio della properies data_modifica_riga il valore ".$exp[1]." risulta essere incorretto!";
			exit;
		}
						$expTime = explode(":", $value);
		if(count($expTime) <= 1 || count($expTime) == 2)
		{
			echo "Errore nel settaggio della properies data_modifica_riga il valore ".$value." risulta essere incorretto!";
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
	function getIs_vbn_updated(){return $this->is_vbn_updated;}
	
	function setIs_vbn_updated($value= null)
	{
		if(strlen($value) > 11)
			$value = substr($value, 0, 11);
	
	
		$this->is_vbn_updated = (int)$value;
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