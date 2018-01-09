<?php
// ini_set('error_reporting', E_ALL);
// error_reporting(E_ALL);
// ini_set('display_errors', 'On');

class PaySella extends DBSmartyAction
{
	function PaySella()
	{
		parent::DBSmartyAction();
		
		switch ($_REQUEST['st'])
		{
			case '1':
				
				//$shopLogin = 'GESPAY59371';
				$shopLogin = '9090221';
				$currency = '242'; //payment currency  242 -> Euro
				$amount = str_replace(',', '.', $this->tEngine->getFormatPrice($_REQUEST['amount'])); //payment amount
				$shopTransactionID = $_REQUEST['transactionId']; //your payment order identifier
				//Custom fileds
				//$customInfo = 'BV_CODICECLIENTE=12*P1*BV_SESSIONID=398';
				
				$param['shopLogin'] = $shopLogin;
				$param['uicCode'] = $currency;
				$param['amount'] = $amount;
				$param['shopTransactionId'] = $shopTransactionID;
				//$param['buyerName'] = $_SESSION['LoggedUser']['customer_data']['ragione_sociale'];
				//if(!empty($_SESSION['LoggedUser']['customer_data']['email']))
				//	$param['buyerEmail'] = $_SESSION['LoggedUser']['customer_data']['email'];
								
				require_once(APP_ROOT."/libs/ext/nusoap_sella/SellaEncryptDecrypt.php");				
				$soapSella = new SellaEncryptDecrypt();
				$soapSella->doRequest('Encrypt', $param);
				
				if($err = $soapSella->getError())
					exit($err);
				else
				{
					header('Location: https://ecomm.sella.it/pagam/pagam.aspx?a='.$shopLogin.'&b='.$soapSella->getEncString());
					exit();
				}
			break;
		}
		if(!empty($_REQUEST['serv_to_serv']))
		{
			require_once(APP_ROOT."/libs/ext/nusoap_sella/SellaEncryptDecrypt.php");
			$shopLogin = $_GET["a"];
			$CryptedString = $_GET["b"];
			$params = array('shopLogin' => $shopLogin, 'CryptedString' => $CryptedString);
			$soapSella = new SellaEncryptDecrypt();
			$soapSella->doRequest('Decrypt', $param);
			foreach ($soapSella->objectresult as $elem)
			{
				if(is_array($elem))
				{
					foreach ($elem as $el)
						$str .= $el." - ";
				}
				else
					$str .= $elem." - ";
			}
			mail('siso77@gmail.com','Sella S2S', $str);
			exit();
		}
		if(!empty($_REQUEST['get_response']))
		{
			echo '<html>';
			require_once($_SERVER["DOCUMENT_ROOT"]."/libs/ext/nusoap_sella/nusoap.php");
			//$wsdl="https://ecomms2s.sella.it/gestpay/gestpayws/WSCryptDecrypt.asmx?WSDL";
			//$wsdl = "https://testecomm.sella.it/gestpay/gestpayws/WSCryptDecrypt.asmx?WSDL";
			$client = new nusoap_client($wsdl,true);
			$shopLogin = $_GET["a"];
			$CryptedString = $_GET["b"];
			echo $shopLogin . '<br/>';
			echo $CryptedString . '<br/>';
			$params = array('shopLogin' => $shopLogin, 'CryptedString' => $CryptedString);
			
			$objectresult = $client->call('Decrypt',$params);
			foreach ($objectresult as $elem)
				echo $elem." - ";
			
			$err = $client->getError();
			if ($err) {
				// Display the error
				echo '<h2>Error</h2><pre>' . $err . '</pre>';
			} else {
				// Display the result
				echo '<h2></h2>';
				echo '<h2>Result</h2>';
				echo '<pre>';
				print_r ($objectresult);
				 
				foreach ($objectresult as $elem)
					echo $elem." - ";
				echo '</pre>';
			}
			echo '</html>';
			exit();
		}

		$this->tEngine->assign('tpl_action', 'PaySella');
		$this->tEngine->display('Index');
	}
}