<?php
 /**
  * @author Silvio Sorrentino
  * @version 1.0
  * 
  * @access  Public
  * 
  * BeanBase � la base dei bean per le azioni
  * Le sue funzioni sono:
  *
  * - la gestione automatica dei file di log per (Login, query SQL generiche, query SQL per il menu o/e query di inserimento/modifica)
  *			il log viene eseguito o non in base alla direttiva IF_LOG (true/false) settata nel file di configurazione (application.ini/application.xml)
  *			inoltre il log viene suddiviso su tre tipologie settate tramite la direttiva LOG_LEVEL:
  *					- LOG_LEVEL=all (tutte le query sql)
  *					- LOG_LEVEL=store (solo le query di inserimento/modifica)
  *					- LOG_LEVEL=link (solo le query per il recupero del menu)
  *
  *			Il log dei login � sempre generato (per direttiva IF_LOG=true)
  * 		Il log dei login � strutturato secondo il seguente esempio:
  *					-  Utente di dominio: [ ] REMOTE IP: [ ] REMOTE PORT: [ ] REMOTE MAC Address: [ ] HOST NAME: [ ] FULL DATE [ ]
  *
  *			Il log SQL � strutturato secondo il seguente esemmpio
  *					- E' stata eseguita la query [ ] dall'utente di dominio [ ]
  *				
  */

class BeanBase
{
	/**
	 * Properties
	 * @name string tablename
	 * @access  Private
	 * 
	 * Properties il nome della tabella che estender� BeanBase
	 *  
	 */
	var $table_name;
	
	/**
	 * Costruttore
	 * @name string BeanBase 
	 */
	function BeanBase()
	{
		if(IF_LOG)
		{
			$_SESSION['SYSTEM_LOG']['USER'] = $_SESSION['LoggedUser']['username'];
			$_SESSION['SYSTEM_LOG']['REMOTE_PORT'] = $_SERVER['REMOTE_PORT'];
			$_SESSION['SYSTEM_LOG']['REMOTE_IP'] = $_SERVER['REMOTE_ADDR'];
			$_SESSION['SYSTEM_LOG']['REMOTE_MAC_ADDRESS'] = "";
			$_SESSION['SYSTEM_LOG']['HOST_NAME'] = "";
			$_SESSION['SYSTEM_LOG']['FULL_DATE'] = date("d-m-Y");
	
			
			$_SESSION['SYSTEM_LOG']['MESSAGE_LOG'] = " Utente di dominio: [ ".$_SESSION['SYSTEM_LOG']['USER']." ] " .
					"REMOTE PORT: [ ".$_SESSION['SYSTEM_LOG']['REMOTE_PORT']." ] " .
					"REMOTE IP: [ ".$_SESSION['SYSTEM_LOG']['REMOTE_IP']." ] " .
//					"HOST NAME: [ ".$_SESSION['SYSTEM_LOG']['HOST_NAME']." ] " .
					"FULL DATE [ ".$_SESSION['SYSTEM_LOG']['FULL_DATE']." ]";
		}
	}
	
	
	/**
	 * @access Protected
	 * @name string fill
	 * @param mixed
	 */
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
	
	/**
	 * @access Protected
	 * @name string vars
	 */
	function vars() {  return get_object_vars($this);  }
	
	/**
	 * @access Protected
	 * @name string _is_connection
	 */
	function _is_connection($db)
	{
		$ret = false;
		if(is_object($db) && is_subclass_of($db, 'db_common') && method_exists($db, 'simpleQuery') )
			$ret = true;
		return $ret;
	}	
	
	/**
	 * @access Protected
	 * @name string _showErrorNoQuery Output in caso di errore in esecuzione delle query SQL
	 * @param string $message Stringa opzionale per l'aggiunta di eventuali messaggi
	 */

	function _showErrorNoQuery($message = null)
	{
		if($_SESSION['UserData']['username'] && DISPLAY_ERROR_QUERY)
		{
			$err = array(
							"DB_ERROR" => true, 
							"ERROR_MESSAGE" => "Errore nell'esecuzione della query ( o DataBase vioto ) ".$message
						);
			$html = "<html>" .
					"<body>" .
					"<font color=red size=4>Errore nell'esecuzione della query ( o DataBase vioto ) <BR>Parameters:<BR> " .
					$message."</font>" .
					"</body>" .
					"</html>";
			
			$fp = fopen (APP_ROOT."/style/template/sql_error.html", "w");
			fwrite($fp, $html);
			fclose($fp);
			$file_error = WWW_ROOT."/".APPLICATION."/style/template/sql_error.html";
			
			echo "<script>" .
					"var w = '620';" .
					"var h = '490';" .
					"var top = (screen.availHeight/2)-(h/2);" .
					"var left = (screen.availWidth/2)-( w/2);" .
					"window.open('".$file_error."', '', 'width='+w+', height='+h+', left=0, top=0, menubar=no, status=no, location=no, toolbar=no, scrollbars=yes, resizable=yes,top='+top+',left='+left);" .
					"</script>";
		}
		return $err;
	}

	/**
	 * @access Public
	 * @name string BeanLog 
	 * @param mixed $type optional tipo di log da passare alla chiamata (PEAR_DB, query, query_link, login)
	 * @param mixed $param no optional parametro da passare alla chiamata (query SQL / oggetto DB di PEAR)
	 */

	function BeanLog($type = null, $param = null)
	{
		
			/**
			 * @name boolean IF_LOG
			 * 
			 * Define settata nel file di configurazione di tipo booleano
			 * 
			 */

		if(IF_LOG)
		{
			$type = strtolower($type);
			switch($type)
			{
				case "pear_db":
					if(LOG_LEVEL == "all" || LOG_LEVEL == "store")
						$this->BeanPearDbLog($param);
				break;
				case "query":
					if(LOG_LEVEL == "all")
						$this->BeanQueryLog($param);
				break;
				case "query_link":
					if(LOG_LEVEL == "link")
						$this->BeanQueryLog($param);
				break;
				case "login":
					if(LOG_LEVEL == "all")
						$this->BeanLoginLog($param);
				break;
			}
			unset($_SESSION['SYSTEM_LOG']);
		}
	}
	
	/**
	 * @access Private
	 * @name string BeanLoginLog 
	 * @param string $user dati relativi all'utente che ha effettuato il login
	 * <code>
	 * 	function BeanLoginLog($user)
	 *	{
	 *		$this->setUSER($user);
	 *		MyLog::login_log($this->getMESSAGE_LOG());
	 *	}
	 * </code>	 
	 */

	function BeanLoginLog($user)
	{
		$this->setUSER($user);
		MyLog::login_log($this->getMESSAGE_LOG());
	}

	/**
	 * @access Private
	 * @name string BeanPearDbLog 
	 * @param object $db oggetto PEAR
	 * <code>
	 * 	function BeanPearDbLog($db)
	 *	{
	 *		$msg = " E' stata eseguita la query: [ ".$db->last_query." ] dall'utente di dominio [ ".$_SESSION['USER_NAME']." ".$_SESSION['USER_FULL_NAME']." ]";
	 *		MyLog::sql_log($msg);
	 *	}
	 * </code>
	 */

	function BeanPearDbLog($db)
	{
		$msg = " E' stata eseguita la query: [ ".$db->last_query." ] dall'utente di dominio [ ".$_SESSION['LoggedUser']['username']." ]".$_SESSION['SYSTEM_LOG']['MESSAGE_LOG'];
		MyLog::sql_log($msg);
	} 

	/**
	 * @access private
	 * @name string BeanQueryLog 
	 * @param string $query stringa contenente l'ultima query eseguita al DB
	 * <code>
	 * 	function BeanQueryLog($query)
	 *	{
	 *		$msg = " E' stata eseguita la query: [ ".$query." ] dall'utente di dominio [ ".$_SESSION['USER_NAME']." ".$_SESSION['USER_FULL_NAME']." ]";
	 *		MyLog::sql_log($msg);
	 *	}
	 * </code>
	 */

	function BeanQueryLog($query)
	{
		$msg = " E' stata eseguita la query: [ ".$query." ] dall'utente di dominio [ ".$_SESSION['LoggedUser']['username']." ]".$_SESSION['SYSTEM_LOG']['MESSAGE_LOG'];
		MyLog::sql_log($msg);
	} 
	
	/*		TEMP SESSION MANIPULATION FUNCTION		*/

	/**
	 * @access private
	 * @name string setREMOTE_PORT 
	 * @param string $value stringa contenente la remote port dell'utente che sta effettuando il login
	 * <code>
	 * 	function setREMOTE_PORT($value)
	 *	{
	 *		$_SESSION['SYSTEM_LOG']['REMOTE_PORT'] = $value;
	 *	}
	 * </code>
	 */
	
	function setREMOTE_PORT($value)
	{
		$_SESSION['SYSTEM_LOG']['REMOTE_PORT'] = $value;
	}

	/**
	 * @access private
	 * @name string setREMOTE_MAC_ADDRESS 
	 * @param string $value stringa contenente il MAC ADDRESS dell'utente che sta effettuando il login
	 * <code>
	 * 	function setREMOTE_MAC_ADDRESS($value)
	 *	{
	 *		$_SESSION['SYSTEM_LOG']['REMOTE_MAC_ADDRESS'] = $value;
	 *	}
	 * </code>
	 */

	function setREMOTE_MAC_ADDRESS($value)
	{
		$_SESSION['SYSTEM_LOG']['REMOTE_MAC_ADDRESS'] = $value;
	}
	
	/**
	 * @access private
	 * @name string setHOST_NAME 
	 * @param string $value stringa contenente l' HOST NAME dell'utente che sta effettuando il login
	 * <code>
	 * 	function setHOST_NAME($value)
	 *	{
	 *		$_SESSION['SYSTEM_LOG']['HOST_NAME'] = $value;
	 *	}
	 * </code>
	 */
	 	
	function setHOST_NAME($value)
	{
		$_SESSION['SYSTEM_LOG']['HOST_NAME'] = $value;
	}
	
	/**
	 * @access private
	 * @name string setFULL_DATE 
	 * @param string $value stringa contenente la data di login
	 * <code>
	 * 	function setFULL_DATE($value)
	 *	{
	 *		$_SESSION['SYSTEM_LOG']['FULL_DATE'] = $value;
	 *	}
	 * </code>
	 */
	
	function setFULL_DATE($value)
	{
		$_SESSION['SYSTEM_LOG']['FULL_DATE'] = $value;
	}
	
	/**
	 * @access private
	 * @name string setREMOTE_IP 
	 * @param string $value stringa contenente il REMOTE IP dell'utente che sta effettuando il login
	 * <code>
	 * 	function setREMOTE_IP($value)
	 *	{
	 *		$_SESSION['SYSTEM_LOG']['REMOTE_IP'] = $value;
	 *	}
	 * </code>
	 */
	
	function setREMOTE_IP($value)
	{
		$_SESSION['SYSTEM_LOG']['REMOTE_IP'] = $value;
	}
	
	/**
	 * @access private
	 * @name string setUSER 
	 * @param string $value stringa contenente lo USER NAME dell'utente che sta effettuando il login
	 * <code>
	 * 	function setUSER($value)
	 *	{
	 *		$_SESSION['SYSTEM_LOG']['USER'] = $value;
	 *	}
	 * </code>
	 */
	
	function setUSER($value)
	{
		$_SESSION['SYSTEM_LOG']['USER'] = $value;
	}
	
	/**
	 * @access private
	 * @name string getMESSAGE_LOG 
	 * @return string ritorna una stringa contenente il messaggio completo da loggare
	 * <code>
	 * 	function getMESSAGE_LOG()
	 *	{
	 *		$_SESSION['SYSTEM_LOG']['MESSAGE_LOG'] = " Utente di dominio: [ ".$_SESSION['SYSTEM_LOG']['USER']." ] REMOTE IP: [ ".$_SESSION['SYSTEM_LOG']['REMOTE_PORT']." ] REMOTE PORT: [ ".$_SESSION['SYSTEM_LOG']['REMOTE_IP']." ] REMOTE MAC Address: [ ".$_SESSION['SYSTEM_LOG']['REMOTE_MAC_ADDRESS']." ] HOST NAME: [ ".$_SESSION['SYSTEM_LOG']['HOST_NAME']." ] FULL DATE [ ".$_SESSION['SYSTEM_LOG']['FULL_DATE']." ]";		
	 *		return $_SESSION['SYSTEM_LOG']['MESSAGE_LOG'];
	 *	}
	 * </code>
	 */

	function getMESSAGE_LOG()
	{
		$_SESSION['SYSTEM_LOG']['MESSAGE_LOG'] = " Utente di dominio: [ ".$_SESSION['SYSTEM_LOG']['USER']." ] REMOTE IP: [ ".$_SESSION['SYSTEM_LOG']['REMOTE_PORT']." ] REMOTE PORT: [ ".$_SESSION['SYSTEM_LOG']['REMOTE_IP']." ] REMOTE MAC Address: [ ".$_SESSION['SYSTEM_LOG']['REMOTE_MAC_ADDRESS']." ] HOST NAME: [ ".$_SESSION['SYSTEM_LOG']['HOST_NAME']." ] FULL DATE [ ".$_SESSION['SYSTEM_LOG']['FULL_DATE']." ]";		
		return $_SESSION['SYSTEM_LOG']['MESSAGE_LOG'];
	}	
	/*		TEMP SESSION MANIPULATION FUNCTION		*/
}
?>
