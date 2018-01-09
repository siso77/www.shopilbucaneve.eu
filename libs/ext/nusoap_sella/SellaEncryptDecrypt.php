<?php
//require_once($_SERVER["DOCUMENT_ROOT"]."/libs/ext/nusoap_sella/nusoap.php");
class SellaEncryptDecrypt
{
	var $wsdl;
	var $objectresult;
	var $client;
	var $error;
	
	function __construct()
	{
		$this->wsdl = "https://ecomms2s.sella.it/gestpay/gestpayws/WSCryptDecrypt.asmx?WSDL";//Production
		//$this->wsdl = "https://testecomm.sella.it/gestpay/gestpayws/WSCryptDecrypt.asmx?WSDL";//Test
	}
	
	function doRequest($type, $param)
	{
		$this->client = new nusoap_client($this->wsdl,true);
		$this->objectresult = $this->client->call($type, $param);
	}
	
	function getError()
	{
		$err = $this->client->getError();
		if ($err)
			return '<h2>Error</h2><pre>' . $err . '</pre>';
		else 
		{
			$errCode = $this->objectresult['EncryptResult']['GestPayCryptDecrypt']['ErrorCode'];
			if ($errCode != '0') 
			{
				$str.= '<div class="error">Error:';
				$str.= $errCode;
				$str.= '<br>ErrorDesc:';
				$str.= $this->objectresult['EncryptResult']['GestPayCryptDecrypt']['ErrorDescription'] ;
				$str.= '</div>';
				return $str;
			}
		}
		return false;
	}
	
	function getEncString()
	{
		return $this->objectresult['EncryptResult']['GestPayCryptDecrypt']['CryptDecryptString'];		
	}
}