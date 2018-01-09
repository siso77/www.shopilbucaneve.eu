<?php


/**
* Classe che si occupa di recuperare il tempo di esecuzione di una o più istruzioni.
* 
*
* <code>
* $time = new Time();
*   
* for($i=0; $i<10000;$i++){}
*
* $time = $time->stop();
*
* print($time);
* </code>
*
* @package SisoLibs
* @version 1.0
* @author Silvio Sorrentino
*/
class Time
{
	/**
	*
	* @access private
	* @var float
	*
	*/
	var $_start;

	/**
	*
	* Prende il tempo di partenza
	*
	* @return Time
	*
	*/
	function Time()
	{
		$this->_start = $this->_getmicrotime();
	}
	
	/**
	*
	* Ritorna il tempo trascorso dall'istanza in millesimi di secondo
	*
	* @access public
	* @return float
	*
	*/
	function stop()
	{		
		return round(($this->_getmicrotime() - $this->_start), 4);
	}

	/**
	*
	* Ritorna l'ora UNIX in microsecondi
	*
	* @access private
	* @return float
	*
	*/
	function _getmicrotime()
	{ 
		list($usec, $sec) = explode(" ",microtime()); 
		return ((float)$usec + (float)$sec); 
    }
}

?>