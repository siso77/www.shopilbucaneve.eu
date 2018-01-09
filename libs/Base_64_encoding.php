<?php
/*
 * Classe per il Criptaggio di stringhe (Anche plugin Smarty)
 * 
 * $string Stringa da criptare
 * $key    Chiave utilizzata per il criptaggio e decriptaggio
 * 
 * Esempio di utilizzo
 * Base_64_encoding::encrypt($string, $key);
 * Base_64_encoding::decrypt($string, $key);
 * 
 * */
class Base_64_encoding
{
	function encrypt($string, $key) 
	{
	   $result = '';
	   for($i=0; $i<strlen($string); $i++) 
	   {
	     $char = substr($string, $i, 1);
	     $keychar = substr($key, ($i % strlen($key))-1, 1);
	     $char = chr(ord($char)+ord($keychar));
	     $result.=$char;
	   }
	
	   return base64_encode($result);
	}
	
	function decrypt($string, $key) 
	{
	   $result = '';
	   $string = base64_decode($string);
	
	   for($i=0; $i<strlen($string); $i++) 
	   {
	     $char = substr($string, $i, 1);
	     $keychar = substr($key, ($i % strlen($key))-1, 1);
	     $char = chr(ord($char)-ord($keychar));
	     $result.=$char;
	   }
	
	   return $result;
	}
}
?>