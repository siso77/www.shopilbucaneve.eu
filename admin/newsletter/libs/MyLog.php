<?php

 /**
  * @package Utils
  * @subpackage MyLog 
  * @author Silvio Sorrentino
  * @version 1.0
  * @access  Public
  * @TODO MyDB classe per la gestione del logging.<BR>
  * I file di log generati verranno creati nelle seguenti directory:<BR>
  * 	- sql_log => PATH_APPLICATION/Logs/sql<BR>
  * 	- error_log => PATH_APPLICATION/Logs/error<BR>	
  * 	- notice_log => PATH_APPLICATION/Logs/notice<BR>
  * 	- action_log => PATH_APPLICATION/Logs/action<BR>
  * 	- login_log => PATH_APPLICATION/Logs/login<BR>
  * 	- logout_log => PATH_APPLICATION/Logs/login<BR>
  * 	- application_log => PATH_APPLICATION/Logs/application<BR><BR>
  * 	- db_log => PATH_APPLICATION/Logs/db<BR><BR>
  * 
  * Essendo estesa la Classe PEAR LOG per la tipologia di log vengono definite le seguenti define:<BR>
  * 'PEAR_LOG_EMERG'  System is unusable <BR>
  * 'PEAR_LOG_ALERT' Immediate action required<BR> 
  * 'PEAR_LOG_SQL' Critical conditions <BR>
  * 'PEAR_LOG_ERR' Error conditions <BR>
  * 'PEAR_LOG_WARNING Warning conditions<BR> 
  * 'PEAR_LOG_NOTICE' Normal but significant<BR> 
  * 'PEAR_LOG_INFO' Informational <BR>
  * 'PEAR_LOG_DEBUG' Debug-level messages<BR><BR> 
  * 
  * 'PEAR_LOG_ALL' All messages <BR>
  * 'PEAR_LOG_NONE' No message <BR><BR>
  * 
   * Log types for PHP's native error_log() function.<BR> 
  * 'PEAR_LOG_TYPE_SYSTEM Use PHP's system logger <BR>
  * 'PEAR_LOG_TYPE_MAIL' Use PHP's mail() function <BR>
  * 'PEAR_LOG_TYPE_DEBUG' Use PHP's debugging connection<BR> 
  * 'PEAR_LOG_TYPE_FILE' Append to a file <BR>
  * 
  * <code>
  * /**
  * *Esempio di utilizzo:
  * {@*}
  * MyLog::sql_log($message);
  * MyLog::action_log($message);
  * MyLog::login_log($message);
  * MyLog::logout_log($message);
  * MyLog::notice_log($message);
  * MyLog::error_log($message);
  * MyLog::db_log($message);
  * </code> 
  */

class MyLog
{
	/**
	 * @name string __log
	 * @access  Private
	 * @static
	 * @todo metodo richiamto in fase di logging che inizializza l'oggetto PEAR LOG<BR>
	 * in base al tipo di log passato come parametro e passando il messaggio di log.<BR>
	 * Inoltre se il file di log supera il limite di circa 10M verra' creato un secondo file di log con data odierna e ora
	 * @param string $message Stringa contenente il messaggio da scrivere nel file di log
	 * @param string $type Stringa contenente il tipo di log da generare
	 * <code>
	 * 	function __log($message, $type="notice")
	 * 	{
	 * 		switch($type)
	 * 		{
	 * 			...
	 * 		}
	 * 		$file = LOG_DIR.$type."/".date("d.m.y").".log";
	 * 		if(MyLog::_overSize($file))
	 * 			MyLog::copy($file);
	 * 
	 * 		$log = &Log::factory('file', $file, $pear_type);
	 * 		MyLog::__setLogFormat($log);
	 * 
	 * 		$log->log($message);
	 * 		$log->close();
	 * 	}
	 * </code>
	 */ 	
	function __log($message, $type="notice")
	{
		switch($type)
		{
			case "sql":
			case "db":
			{
				$pear_type = PEAR_LOG_SQL;
			}
			break;
			case "action":
			{
				$pear_type = PEAR_LOG_NOTICE;
			}
			break;
			case "login":
			{
				$pear_type = PEAR_LOG_INFO;
			}
			break;
			case "RAC":
			case "application":
			{
				$pear_type = PEAR_LOG_DEBUG;
			}
			break;
			case "exception_error":
			{
				$pear_type = PEAR_LOG_ALERT;
			}
			break;
			default:
			{
				$pear_type = PEAR_LOG_NOTICE;
			}
		}

		$file = LOG_DIR.$type."/".date("d.m.y").".log";
		
		if(MyLog::_overSize($file))
			MyLog::copy($file);

		$log = &Log::factory('file', $file, $pear_type);
		MyLog::__setLogFormat($log);
		
		$log->log($message);
		$log->close();
	}

	/**
	 * @name string action_log
	 * @access  Public
	 * @todo Incapsulamento per la logica di generazione del log delle azioni
	 * @param string $message Stringa contenente il messaggio da loggare
	 * <code>
	 * 	function action_log($message)
	 * 	{
	 * 		MyLog::__log($message, "action");
	 * 	}
	 * </code>
	 */ 		
	function action_log($message)
	{
		MyLog::__log($message, "action");
	}

	/**
	 * @name string login_log
	 * @access  Public
	 * @todo Incapsulamento per la logica di generazione del log dei login
	 * @param string $message Stringa contenente il messaggio da loggare
	 * <code>
	 * 	function login_log($message)
	 * 	{
	 * 		MyLog::__log($message, "login");
	 * 	}
	 * </code>
	 */ 		
	function login_log($message)
	{
		$message .= 
			" Utente di dominio: [ ".$_SESSION['USER_NAME']." ] " .
			"REMOTE IP: [ ".$_SERVER['REMOTE_ADDR']." ] " .
			"REMOTE PORT: [ ".$_SERVER['REMOTE_PORT']." ] " .
			"REMOTE MAC Address: [".$_SESSION['Physical_Address']." ] " .
			"HOST NAME: [".$_SESSION['Host_Name']." ]";
					
			MyLog::__log($message, "login");
	}

	/**
	 * @name string application_log
	 * @access  Public
	 * @todo Incapsulamento per la logica di generazione del log dello sniffer
	 * <code>
	 * 	function application_log()
	 * 	{
	 * 		$message = 
	 * 			" Utente di dominio: [ ".$_SESSION['USER_NAME']." ] " .
	 * 			"REMOTE IP: [ ".$_SERVER['REMOTE_ADDR']." ] " .
	 * 			"REMOTE PORT: [ ".$_SERVER['REMOTE_PORT']." ] " .
	 * 			"REMOTE MAC Address: [".$_SESSION['Physical_Address']." ] " .
	 * 			"HOST NAME: [".$_SESSION['Host_Name']." ]";
	 * 		MyLog::__log($message, "application");
	 * 	}
	 * </code>
	 */ 		
	function application_log()
	{
		$message = 
			" Utente di dominio: [ ".$_SESSION['USER_NAME']." ] " .
			"REMOTE IP: [ ".$_SERVER['REMOTE_ADDR']." ] " .
			"REMOTE PORT: [ ".$_SERVER['REMOTE_PORT']." ] " .
			"REMOTE MAC Address: [".$_SESSION['Physical_Address']." ] " .
			"HOST NAME: [".$_SESSION['Host_Name']." ]";
		MyLog::__log($message, "application");
	}

	function RAC_log()
	{
		$message = " Utente di dominio: [ ".$_SESSION['USER_NAME']." ] REMOTE IP: [ ".$_SERVER['REMOTE_ADDR']." ] REMOTE PORT: [ ".$_SERVER['REMOTE_PORT']." ] REMOTE MAC Address: [".$_SESSION['Physical_Address']." ] HOST NAME: [".$_SESSION['Host_Name']." ]";
		MyLog::__log($message, "RAC");
	}
	
	/**
	 * @name string logout_log
	 * @access  Public
	 * @todo Incapsulamento per la logica di generazione del log del logout
	 * @param string $message Stringa contenente il messaggio da loggare
	 * <code>
	 * 	function logout_log($user)
	 * 	{
	 * 		$message =  " Data: [".date("m.d.y")."] " .
	 * 					"Ora: [".date("H:i:s")."] " .
	 * 					"Utente ".APPLICATION.": [".$user."] " .
	 * 					"Azione: [Logout]";
	 * 		MyLog::__log($message, "login");
	 * 	}
	 * </code>
	 */ 		
	function logout_log($user)
	{
		$message =  " Data: [".date("m.d.y")."] " .
					"Ora: [".date("H:i:s")."] " .
					"Utente ".APPLICATION.": [".$user."] " .
					"Azione: [Logout]";
		MyLog::__log($message, "login");
	}

	/**
	 * @name string sql_log
	 * @access  Public
	 * @todo Incapsulamento per la logica di generazione del log dell'sql
	 * @param string $message Stringa contenente il messaggio da loggare
	 * <code>
	 * 	function sql_log($message)
	 * 	{
	 * 		MyLog::__log($message, "sql");
	 * 	}
	 * </code>
	 */ 		
	function sql_log($message)
	{
		MyLog::__log($message, "sql");
	}

	/**
	 * @name string db_log
	 * @access  Public
	 * @todo Incapsulamento per la logica di generazione del log del DB
	 * @param string $message Stringa contenente il messaggio da loggare
	 * <code>
	 * 	function db_log($message)
	 * 	{
	 * 		MyLog::__log($message, "db");
	 * 	}
	 * </code>
	 */ 		
	function db_log($message)
	{
		MyLog::__log($message, "db");
	}

	/**
	 * @name string notice_log
	 * @access  Public
	 * @todo Incapsulamento per la logica di generazione del log dei notice
	 * @param string $message Stringa contenente il messaggio da loggare
	 * <code>
	 * 	function notice_log($message)
	 * 	{
	 * 		MyLog::__log($message, "notice");
	 * 	}
	 * </code>
	 */ 		
	function notice_log($message)
	{
		MyLog::__log($message, "notice");
	}

	/**
	 * @name string error_log
	 * @access  Public
	 * @todo Incapsulamento per la logica di generazione del log degli errori
	 * @param string $message Stringa contenente il messaggio da loggare
	 * <code>
	 * 	function error_log($message)
	 * 	{
	 * 		MyLog::__log($message, "exception_error");
	 * 	}
	 * </code>
	 */ 		
	function error_log($message)
	{
		MyLog::__log($message, "exception_error");
	}

	/**
	 * @name string _overSize
	 * @access  Private
	 * @return int Dimenzione del file di log
	 * @todo Metodo di Utility per il controllo della dimenzione del file di log
	 * @param file $file File di log del quale ricavare la dimenzione
	 * <code>
	 * 	function _overSize(&$file)
	 * 	{
	 * 		return (@filesize($file) > 1048576);
	 * 	}
	 * </code>
	 */ 		
	function _overSize(&$file)
	{
		return (@filesize($file) > 1048576);
	}

	/**
	 * @name string copy
	 * @access  Private
	 * @todo Metodo di Utility per la copia del file di dimensione superiore al limite impostato
	 * @param file $old_file File di log di dimensione superiore al limite impostato
	 * <code>
	 * 	function copy(&$old_file)
	 * 	{
	 * 		$tokens = explode(".", $old_file);
	 * 		$old_file_name = $tokens[0];
	 * 		$file_name = $old_file_name."_".date("Y_m_d__H_i").".log";
	 * 		
	 * 		$log_txt = file_get_contents($old_file);
	 * 	
	 * 		if (!file_exists($file_name)) 
	 * 		{
	 * 			$fp = fopen($file_name, "w");
	 * 			fwrite($fp, $log_txt);
	 * 			fclose($fp);
	 * 	
	 * 			$fp_old = fopen($old_file, "w");
	 * 			fclose($fp_old);
	 * 		}
	 * 	}
	 * </code>
	 */ 		
	function copy(&$old_file)
	{
		$tokens = explode(".", $old_file);
		$old_file_name = $tokens[0];
		$file_name = $old_file_name."_".date("Y_m_d__H_i").".log";
		
		$log_txt = file_get_contents($old_file);
		
		if (!file_exists($file_name)) 
		{
			$fp = fopen($file_name, "w");
			fwrite($fp, $log_txt);
			fclose($fp);

			$fp_old = fopen($old_file, "w");
			fclose($fp_old);
		}
	}
	
	/**
	 * @name string __setLogFormat
	 * @access  Private
	 * @todo Metodo per settare il formato del log generato
	 * @param object $log oggetto PEAR LOG
	 * <code>
	 * 	function __setLogFormat(&$log)
	 * 	{
	 * 		$log->_lineFormat = '%1$s%2$s[%3$s]%4$s';
	 * 		$log->_timeFormat = '%b %d %H:%M:%S|';
	 * 		$log->_formatMap = array('%{timestamp}'  => '%1$s',
	 * 	                        '%{ident}'      => '%2$s',
	 * 	                        '%{priority}'   => '%3$s',
	 * 	                        '%{message}'    => '%4$s',
	 * 	                        '%\{'=> '%%{');	
	 * 	}
	 * </code>
	 */ 		
	function __setLogFormat(&$log)
	{
		$log->_lineFormat = '%1$s%2$s[%3$s]%4$s';
		$log->_timeFormat = '%b %d %H:%M:%S|';
		$log->_formatMap = array('%{timestamp}'  => '%1$s',
                            '%{ident}'      => '%2$s',
                            '%{priority}'   => '%3$s',
                            '%{message}'    => '%4$s',
                            '%\{'=> '%%{');	
	}
}

/*
class toBB_Time
{
	var $_start;

	function toBB_Time()
	{
		$this->_start = $this->_getmicrotime();
	}
	
	function stop()
	{		
		return round(($this->_getmicrotime() - $this->_start), 4);
	}

	function _getmicrotime()
	{ 
		list($usec, $sec) = explode(" ",microtime()); 
		return ((float)$usec + (float)$sec); 
    }
}
*/
?>