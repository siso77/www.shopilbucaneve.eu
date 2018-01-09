<?php
include_once(APP_ROOT."/beans/ecm_ordini.php");
include_once(APP_ROOT."/beans/ecm_ordini_magazzino.php");
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT."/beans/users.php");
include_once(APP_ROOT."/beans/users_anag.php");

class DDTEcm extends DBSmartyAction
{
	var $className;
	
	function DDTEcm()
	{
		parent::DBSmartyAction();

		$this->className = get_class($this);
		
		include_once(APP_ROOT."/beans/index_ddt.php");
		$BeanIndexFattura = new index_ddt();
		$index_ddt = $BeanIndexFattura->dbGetAll($this->conn);
		$numero_fattura = $index_ddt[0]['id'];
		if(!empty($_SESSION[$this->className]['is_invoiced']))
			$numero_fattura = $numero_fattura - 1;

		$BeanEcmOrdini = new ecm_ordini($this->conn, $_REQUEST['id_ordine']);
		
		$BeanEcmOrdiniMagazzino = new ecm_ordini_magazzino();
		$products = $BeanEcmOrdiniMagazzino->dbGetAllByIdOrdine($this->conn, $_REQUEST['id_ordine'], new magazzino(), new content());

		$BeanUser = new users($this->conn, $BeanEcmOrdini->getId_user());
		$BeanUserAnag = new users_anag($this->conn, $BeanUser->getId_anag());

		$pathDoc = APP_ROOT.'/fatture/'.$BeanUser->getId().'/';

		if(empty($_SESSION[$this->className]['is_invoiced']))
		{
			$BeanIndexFattura->fast_edit($this->conn, $index_ddt[0]['id']);
			$_SESSION[$this->className]['is_invoiced'] = 1;
		}	

		$this->createInvoice($numero_fattura, $BeanUser->vars(), $BeanUserAnag->vars(), $BeanEcmOrdini->vars(), $products);
		
		$this->tEngine->assign('user', $BeanUser->vars());
		$this->tEngine->assign('user_anag', $BeanUserAnag->vars());
		$this->tEngine->assign('ordine', $BeanEcmOrdini->vars());
		$this->tEngine->assign('products', $products);
		
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}

	function createInvoice($numero_fattura, $user, $userAnag, $ordine, $products)
	{
		$userAnag['nome'] = $userAnag['name'];
		$userAnag['cognome'] = $userAnag['surname'];
		$userAnag['indirizzo'] = $userAnag['address'];
		$userAnag['fisso'] = $userAnag['phone'];
		$userAnag['provincia'] = $userAnag['province'];
		$userAnag['citta'] = $userAnag['city'];
		
		$pathDoc = APP_ROOT.'/fatture/'.$user['id'].'/';
		$wwwPathDoc = WWW_ROOT.'fatture/'.$user['id'].'/';
		if(!is_dir(APP_ROOT.'/fatture/'.$user['id']))
			mkdir(APP_ROOT.'/fatture/'.$user['id'], 0755, true);

		foreach ($products as $key => $prod)
		{
			$data[] = $prod['magazzino'];
			
			$products[$key]['tipo_pagamento'] = $products[$key]['importo'];
			$products[$key]['personal_price'] = $products[$key]['importo'];
			$products[$key]['total'] = $products[$key]['importo'];  
			$ordine['totale'] += $products[$key]['importo'];
			unset($products[$key]['magazzino']);
			unset($products[$key]['content']);
		}
	
		$param['invoiceNum'] 	= $numero_fattura;
		$param['customer'] 		= array_merge_recursive($user, $userAnag);
		$param['data'] 			= $data;
		$param['sale'] 			= $products;
		$param['rif_scontrino'] = '';
		$param['bean_vendite'] 	= $ordine;
		$param['FATTURA_TAX_IVA'] = FATTURA_TAX_IVA;
		$param['IVA'] 			  = IVA;
		$param['IVA_INTEGRATORI'] = IVA_INTEGRATORI;
		$param['WWW_ROOT'] 		  = WWW_ROOT;
		$param['data_rif_scontrino'] = '';

		if($mezzo == 'mezzo_cedente')
			$param['mezzo_cedente'] = $mezzo;
		if($mezzo == 'mezzo_cessionario')
			$param['mezzo_cessionario'] = $mezzo;
		if($mezzo == 'mezzo_vettore')
			$param['mezzo_vettore'] = $mezzo;
		if($mezzo == 'mezzo_altro')
			$param['mezzo_altro'] = $mezzo;
		
		$_SESSION['invoice_'.$numero_fattura] = $param;
		$_SESSION['curr_invoice_num'] 	  = $numero_fattura;
		
		ob_start();
			include(APP_ROOT.'/libs/TemplateClass/Template_DDT_DOC.php');
			$msWord = ob_get_contents();
		ob_end_clean();
		$fp = fopen($pathDoc.$numero_fattura.'.doc', 'w+');
		fwrite($fp, $msWord);
		fclose($fp);
//_dump($wwwPathDoc.$numero_fattura.'.doc');
//echo $msWord;
//exit();

		echo '<script>
				window.location.href = "'.$wwwPathDoc.$numero_fattura.'.doc";
				setTimeout("window.close();",1000);
			</script>';
		exit();
	}
}
?>