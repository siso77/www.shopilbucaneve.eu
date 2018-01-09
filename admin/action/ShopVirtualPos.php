<?php
include_once(APP_ROOT.'/beans/vendite.php');
include_once(APP_ROOT.'/beans/magazzino.php');
include_once(APP_ROOT.'/beans/vendite_magazzino.php');

class ShopVirtualPos extends DBSmartyAction
{
	var $className;
	var $importo_spese;
	var $id_negozio;
	
	function ShopVirtualPos()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);
		
		if(!empty($_REQUEST['params']))
		{
			$params = base64_decode($_REQUEST['params']);
			$exp = explode('&', $params);
			foreach ($exp as $val)
			{
				$ex = explode('=', $val);
				if($ex[0] == 'back')
					$back = 1;
				if($ex[0] == 'confirm')
					$confirm = 1;
				if($ex[0] == 'id_ordine')
					$idOrdine = $ex[1];
				if($ex[0] == 'remote_address')
					$remote_address = $ex[1];
			}

			if(!empty($confirm))
			{
				$BeanVendite = new vendite($this->conn, $_SESSION[$this->className]['REQUEST_DATA']);
				$BeanVendite->setId_cliente(1);
				$BeanVendite->setChannel('NEGOZIO');
				$BeanVendite->setTipo_pagamento($_SESSION[$this->className]['REQUEST_DATA']['tipo_pagamento']);
				$BeanVendite->setData_vendita(date('Y-m-d H:i:s'));
				$BeanVendite->setIs_invoiced(0);
				$BeanVendite->setOperatore($_SESSION['LoggedUser']['username']);
				$idVendita = $BeanVendite->dbStore($this->conn);
				unset($_SESSION[$this->className]['REQUEST_DATA']);

				$TotalPurchase = 0;
				foreach ($_SESSION[$this->className] as $value)
				{
					$BeanMagazzino = new magazzino($this->conn, $value['id_magazzino']);
					$BeanVenditeMagazzino = new vendite_magazzino($this->conn,$value);
					$BeanVenditeMagazzino->setId_vendita($idVendita);
					$idVenditeMagazzino = $BeanVenditeMagazzino->dbStore($this->conn);
					if($BeanMagazzino->getQuantita() > 0)					
						$BeanMagazzino->setQuantita($BeanMagazzino->getQuantita()-$BeanVenditeMagazzino->getQuantita());
					$BeanMagazzino->dbStore($this->conn);
					$TotalPurchase += $value['total'];
				}
				
				$BeanVendite->setTotale($this->FormatEuro($TotalPurchase));
				$idVendita = $BeanVendite->dbStore($this->conn);
				unset($_SESSION[$this->className]);
				$this->_redirect('?act=Shop&confirm_insert=1');
			}
		}

//		$this->importo_spese = $speseSpedizione[0]['name'];
		$this->id_negozio = "129280605500277";
		
		$BeanVendite = new vendite();
		$max = $BeanVendite->dbGetAllByCustomFields($this->conn, ' MAX(id) as max ');
//		$_SESSION[session_id()]['mag_id_ordine']
		
		$IMPORTO = 0;
		foreach ($_SESSION[$this->className] as $value)
		{
			$IMPORTO += str_replace(',', '.', $value['total']);
		}
		$_SESSION[session_id()]['mag_id_ordine'] = $max[0]['max']+1;
		$_SESSION[session_id()]['remote_address'] = $_SERVER['REMOTE_ADDR'];
		$IDNEGOZIO = $this->id_negozio;
		$NUMORD = 'PV_'.$_SESSION[session_id()]['mag_id_ordine'];
		$IMPORTO = str_replace(',', '.', Currency::FormatEuro($IMPORTO));
		$IMPORTO = str_replace('.', '', $IMPORTO);
		$VALUTA="978";
		$TCONTAB="I";
		$TAUTOR="I";
		$URLMS = WWW_ROOT."?act=Ms";
		$URLDONE = WWW_ROOT."?act=".$this->className.'&params='.base64_encode('confirm=1&id_ordine='.$_SESSION[session_id()]['mag_id_ordine'].'&remote_address='.$_SERVER['REMOTE_ADDR']).'&krupy='.session_id();
		$URLBACK = WWW_ROOT.'?act=Shop&params='.base64_encode('back=1&id_ordine='.$_SESSION[session_id()]['mag_id_ordine']).'&krupy='.session_id();
		$KEY = "FjRqt6nyGU-ULeHpRJjhz-S-V-7mbGb-pMQUwdS7MSZfuJFN-x";

		$MACCHIARO = "URLMS=".$URLMS."&URLDONE=".$URLDONE."&NUMORD=".$NUMORD."&IDNEGOZIO=".$IDNEGOZIO."&IMPORTO=".$IMPORTO."&VALUTA=".$VALUTA."&TCONTAB=".$TCONTAB."&TAUTOR=".$TAUTOR."&".$KEY;
		$MACCHIARO2 = "NUMORD=".$NUMORD."&IDNEGOZIO=".$IDNEGOZIO."&IMPORTO=".$IMPORTO."&VALUTA=".$VALUTA."&TCONTAB=".$TCONTAB."&TAUTOR=".$TAUTOR."&".$KEY;
		// MAC = UCASE(MD5(MACCHIARO))
		$MAC = strtoupper(md5($MACCHIARO));
		
		$URLSEND = "https://atpos.ssb.it/atpos/pagamenti/main?PAGE=MASTER&
		IMPORTO=".$IMPORTO."&
		VALUTA=".$VALUTA."&
		NUMORD=".$NUMORD."&
		IDNEGOZIO=".$IDNEGOZIO."&
		URLDONE=".urlencode($URLDONE)."&
		URLBACK=".urlencode($URLBACK)."&
		URLMS=".urlencode($URLMS)."&
		TAUTOR=".$TAUTOR."&
		TCONTAB=".$TCONTAB."&
		MAC=".$MAC;

		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
		<html>
		<head>
		<meta http-equiv="refresh" content="1;url='.$URLSEND.'">
		</head>
		<body style="background-color:#000000">
			<table width="100%" height="100%" style="background-color:#000000"><tr><td>&nbsp;</td></tr></table>
		</body>
		</html>';
		exit();
		
		$this->tEngine->assign('action_class_name', $this->className);
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}
}
?>