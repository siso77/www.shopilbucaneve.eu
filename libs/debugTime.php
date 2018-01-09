<?php
/*
 * Created on Dec 14, 2005
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 * 
 * 
 * Esempio di utilizzo:
 * 
 * 		$debugTime = new debugTime();
 * 		$debugTime->DebugStart();
 * 
 * 		Codice del quale si vuole testare il tempo di esecuzione
 * 
 * 		$debugTime->DebugEnd();
 * 
 * 		$debugTime->OutPutDebugTime($msg, $new_line [optional]);
 * 
 * 			Parametri:
 * 				$msg = "Eventuale messaggio per la visualizzazione";
 * 				**		Flag per printare andando a capo di default è true	**
 * 				$new_line = true/false
 *
 */
 class debugTime
 {
 	var $start = null;
 	var $end = null;
 	var $Sstart = null;
 	var $Send = null;
 	
 	function debugTime()
 	{
 		$this->DebugStart();
 	}
 	
 	function DebugStart()
 	{
 		$this->start = $this->__getMicroTime();
 		$this->Sstart = $this->__getSecondTime();
 	}
 	
 	function DebugEnd()
 	{
 		$this->end = $this->__getMicroTime(); 
 		$this->Send = $this->__getSecondTime();
 	}
 	
 	function __getMicroTime()
 	{
 		list($usec, $sec) = explode(" ",microtime());
 		return ((float)$usec + (float)$sec);
 		//return microtime();
 	}

 	function __getSecondTime()
 	{
 		return date("s");
 	}
 	
 	function OutPutDebugTime($message, $new_line = true)
 	{
 		if(DEBUG_TIME)
 		{
			$this->DebugEnd();
	 		$diff = round(($this->end - $this->start), 2); 
	 		$Sdiff = round(($this->Send - $this->Sstart), 2);
	 		if($new_line)
	 			echo '<div style="background-color:#ffffff">', $message," Seconds ", $Sdiff, " MicroSec ", $diff,"\n", '</div>';
	 		else
	 			echo '<div style="background-color:#ffffff">', $message," Seconds ", $Sdiff, " MicroSec ", $diff, '</div>';
 		}
 	}
 }
?>
