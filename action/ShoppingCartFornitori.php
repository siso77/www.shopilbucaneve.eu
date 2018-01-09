<?php
include_once(APP_ROOT."/beans/giacenze_fornitori.php");
include_once(APP_ROOT."/beans/ecm_basket.php");
include_once(APP_ROOT."/beans/ecm_basket_magazzino_fornitori.php");

class ShoppingCartFornitori extends DBSmartyAction
{	
	function ShoppingCartFornitori()
	{
		parent::DBSmartyAction();
// unset($_SESSION[session_id()]['basket_fornitori']);
// unset($_SESSION[session_id()]['cart']);
// unset($_SESSION[session_id()]);

		if(!empty($_REQUEST['add_partenza']) && !empty($_REQUEST['is_ajax']))
		{
			if(!empty($_REQUEST['data_partenza']))
				$_SESSION[session_id()]['partenza_fornitori'] = $_REQUEST['data_partenza'];
			exit();
		}
		
		if(empty($_SESSION['LoggedUser']))
		{
			if(!empty($_REQUEST['is_ajax']))
				echo "<script>document.location.href = '".WWW_ROOT."?act=Login';</script>";
			else
				$this->_redirect('?act=Login');
		}
		if(!empty($_SERVER['HTTP_DELETE_SESSION']))
			unset($_SESSION[session_id()]);
			
		if(!empty($_REQUEST['params']))
			$this->tEngine->assign('params_banking', $_REQUEST['params']);

		if(!empty($_REQUEST['delete']))
		{
			$BeanGiacenze = new giacenze_fornitori($this->conn, $_REQUEST['id_magazzino']);
			$giacenza = $BeanGiacenze->vars();
				
			$beanBasket = new ecm_basket();
// 			$basket = $beanBasket->dbGetOneByCustomerAndDate($this->conn, $_SESSION['LoggedUser']['id'], date('Y-m-d'));
			$basket = $beanBasket->dbGetOneByCustomerAndDate($this->conn, $_SESSION['LoggedUser']['id']);

			foreach ($_SESSION[session_id()]['basket_fornitori'] as $key => $value)
			{
				if($value['giacenza']['id'] == $_REQUEST['id_magazzino'])
				{
					$beanBasketMagazzino = new ecm_basket_magazzino_fornitori();
					$basketMagazzino = $beanBasketMagazzino->dbGetOneByBasketAndMagazzino($this->conn, $basket['id'], $giacenza['id']);

					if(!empty($basketMagazzino))
						$beanBasketMagazzino->dbDelete($this->conn, array($basketMagazzino['id']), false);

					unset($_SESSION[session_id()]['basket_fornitori'][$key]);
				}
			}

			$i = 1;
			foreach ($_SESSION[session_id()]['basket_fornitori'] as $key => $value)
			{
				if($key != 'n_carrelli' && $key != 'perc_occupazione')
				{
					$tmp_session[$i] = $value;
					$i++;
				}
			}


			if(count($_SESSION[session_id()]['basket_fornitori']) == 0)
				unset($_SESSION[session_id()]['ecm_basket_fornitori']);

			unset($_SESSION[session_id()]['basket_fornitori']);
			$_SESSION[session_id()]['basket_fornitori'] = $tmp_session;
				
			if(!empty($_REQUEST['delete_from_box']))
			{
				$this->_redirect('Magazzino-Online/Lista-Prodotti.html');
				exit();
			}
			if(empty($_REQUEST['is_ajax']))
			{
				$this->_redirect('?act=ShoppingCart');
				exit();
			}
		}
		
		if(!empty($_REQUEST['refresh_qty']))
		{
			if(empty($_SESSION[session_id()]['basket_fornitori']['n_carrelli']))
				$_SESSION[session_id()]['basket_fornitori']['n_carrelli'][]['volume_occupato'] = 0;

			foreach ($_REQUEST['id_mag'] as $key => $id_mag)
			{
				$BeanGiacenze = new giacenze_fornitori($this->conn, $id_mag);
				$giacenza = $BeanGiacenze->vars();
				
				$beanBasket = new ecm_basket();
				$basket = $beanBasket->dbGetOneByCustomerAndDate($this->conn, $_SESSION['LoggedUser']['id'], date('Y-m-d'));
				
				$beanBasketMagazzino = new ecm_basket_magazzino_fornitori();
				$basketMagazzino = $beanBasketMagazzino->dbGetOneByBasketAndMagazzino($this->conn, $basket['id'], $giacenza['id']);
				if(!empty($basketMagazzino))
				{
					$beanBasketMagazzino = new ecm_basket_magazzino_fornitori($this->conn, $basketMagazzino['id']);
					$beanBasketMagazzino->setId_basket($basket['id']);
					$beanBasketMagazzino->setId_magazzino($giacenza['id']);
					$beanBasketMagazzino->setQuantita($_REQUEST['quantita'][$key]);
				}
				$beanBasketMagazzino->dbStore($this->conn);

				$in_session = false;
				foreach ($_SESSION[session_id()]['basket_fornitori'] as $k => $value)
				{
					$qty_scatola = null;
					if(!empty($_REQUEST['quantita_pianale'][$key]))
						$exp = explode(' x ', $giacenza['qta_pianale']);
					else
						$exp = explode(' x ', $giacenza['qta_scatola']);
					$qty_scatola = $exp[1];
					if($value['giacenza']['id'] == $giacenza['id'])
					{
						$in_session = true;
						$_SESSION[session_id()]['basket_fornitori'][$k]['giacenza'] = $giacenza;
						$_SESSION[session_id()]['basket_fornitori'][$k]['volume'] = $giacenza['volume_singolo'];
						//$_SESSION[session_id()]['basket_fornitori'][$k]['basket_qty']['quantita'] = $_REQUEST['quantita'][$key];
						if(!empty($_REQUEST['quantita_pianale'][$key]))
						{
							$_SESSION[session_id()]['basket_fornitori'][$k]['basket_qty']['sel_quantita'] = null;
							$_SESSION[session_id()]['basket_fornitori'][$k]['basket_qty']['sel_quantita_pianale'] = $_REQUEST['quantita_pianale'][$key];
							$_SESSION[session_id()]['basket_fornitori'][$k]['basket_qty']['quantita'] = $_REQUEST['quantita_pianale'][$key];
						}
						else 
						{
							$_SESSION[session_id()]['basket_fornitori'][$k]['basket_qty']['sel_quantita'] = $_REQUEST['quantita'][$key];
							$_SESSION[session_id()]['basket_fornitori'][$k]['basket_qty']['sel_quantita_pianale'] = null;
							$_SESSION[session_id()]['basket_fornitori'][$k]['basket_qty']['quantita'] = $_REQUEST['quantita'][$key];
						}
						$_SESSION[session_id()]['basket_fornitori'][$k]['basket_qty']['qty_scatola'] = $qty_scatola;
						$_SESSION[session_id()]['basket_fornitori'][$k]['price_it_qty'] = (str_replace(',', '.', $giacenza['prezzo_acquisto']) * $qty_scatola * $_REQUEST['quantita'][$key] );
					}
				}
			}
			$this->_redirect('?act=ShoppingCart');
		}
		else
		{
			$beanBasket = new ecm_basket();
			$basket = $beanBasket->dbGetOneByCustomerAndDate($this->conn, $_SESSION['LoggedUser']['id'], date('Y-m-d'));
	
			if(empty($_REQUEST['quantita']))
				$_REQUEST['quantita'] = 1;
			
			if(!empty($_REQUEST['quantita']) || !empty($_REQUEST['quantita_pianale']))
			{
				if(empty($_SESSION[session_id()]['basket_fornitori']['n_carrelli']))
					$_SESSION[session_id()]['basket_fornitori']['n_carrelli'][]['volume_occupato'] = 0;
	
				$BeanGiacenze = new giacenze_fornitori($this->conn, $_REQUEST['id_mag']);
				$giacenza = $BeanGiacenze->vars();
	
				
				$beanBasket = new ecm_basket();
				$basket = $beanBasket->dbGetOneByCustomerAndDate($this->conn, $_SESSION['LoggedUser']['id'], date('Y-m-d'));
				if(empty($basket))
				{
					$beanBasket->setId_user($_SESSION['LoggedUser']['id']);
					$beanBasket->setOperatore(ECM_OPERATORE);
					$beanBasket->dbStore($this->conn);
					$basket = $beanBasket->vars();
				}
				
				$beanBasketMagazzino = new ecm_basket_magazzino_fornitori();
				$basketMagazzino = $beanBasketMagazzino->dbGetOneByBasketAndMagazzino($this->conn, $basket['id'], $giacenza['id']);
				if(empty($basketMagazzino))
				{
					$beanBasketMagazzino = new ecm_basket_magazzino_fornitori();
					$beanBasketMagazzino->setId_basket($basket['id']);
					$beanBasketMagazzino->setId_magazzino($giacenza['id']);
					if(!empty($_REQUEST['quantita_pianale']))
						$beanBasketMagazzino->setQuantita_pianale($_REQUEST['quantita_pianale']);
					else
						$beanBasketMagazzino->setQuantita($_REQUEST['quantita']);
				}
				else
				{
					$beanBasketMagazzino = new ecm_basket_magazzino_fornitori($this->conn, $basketMagazzino['id']);
					$beanBasketMagazzino->setId_basket($basket['id']);
					$beanBasketMagazzino->setId_magazzino($giacenza['id']);
					if(!empty($_REQUEST['quantita_pianale']))
						$beanBasketMagazzino->setQuantita_pianale($_REQUEST['quantita_pianale']);
					else
						$beanBasketMagazzino->setQuantita($_REQUEST['quantita']);
				}
				$beanBasketMagazzino->dbStore($this->conn);
	
				$_SESSION[session_id()]['ecm_basket_fornitori'] = $basket['id'];
				$BeanMagazzino = new giacenze_fornitori($this->conn, $_REQUEST['id_mag']);
				
				$in_session = false;
				foreach ($_SESSION[session_id()]['basket_fornitori'] as $k => $value)
				{
					if($value['giacenza']['id'] == $giacenza['id'])
					{
						$qty_scatola = null;
						$qty_scatola = $giacenza['qta_scatola'];					
						$in_session = true;
						$_SESSION[session_id()]['basket_fornitori'][$k]['giacenza'] = $giacenza;
						$_SESSION[session_id()]['basket_fornitori'][$k]['volume'] = $giacenza['volume_singolo'];
						if(!empty($_REQUEST['quantita_pianale']))
						{
							$_SESSION[session_id()]['basket_fornitori'][$k]['basket_qty']['quantita'] = $_REQUEST['quantita_pianale'];
							$_SESSION[session_id()]['basket_fornitori'][$k]['basket_qty']['sel_quantita'] = null;
							$_SESSION[session_id()]['basket_fornitori'][$k]['basket_qty']['sel_quantita_pianale'] = $_REQUEST['quantita_pianale'];
						}
						else
						{
							$_SESSION[session_id()]['basket_fornitori'][$k]['basket_qty']['quantita'] = $_REQUEST['quantita'];
							$_SESSION[session_id()]['basket_fornitori'][$k]['basket_qty']['sel_quantita'] = $_REQUEST['quantita'];
							$_SESSION[session_id()]['basket_fornitori'][$k]['basket_qty']['sel_quantita_pianale'] = null;
						}
						$_SESSION[session_id()]['basket_fornitori'][$k]['basket_qty']['qty_scatola'] = $qty_scatola;
						$_SESSION[session_id()]['basket_fornitori'][$k]['price_it_qty'] = (str_replace(',', '.', $giacenza['prezzo_acquisto']) * $qty_scatola * $_REQUEST['quantita'] );
					}
				}

				if(!$in_session)
				{
					$qty_scatola = null;
					if(!empty($_REQUEST['quantita_pianale']))
						$exp = explode(' x ', $giacenza['qta_pianale']);
					else
						$exp = explode(' x ', $giacenza['qta_scatola']);
					$qty_scatola = $exp[1];					
					
					$index = count($_SESSION[session_id()]['basket_fornitori'])-1;
					$_SESSION[session_id()]['basket_fornitori'][$index]['giacenza'] = $giacenza;
					$_SESSION[session_id()]['basket_fornitori'][$index]['volume'] = $giacenza['volume_singolo'];
					if(!empty($_REQUEST['quantita_pianale']))
					{
						$_SESSION[session_id()]['basket_fornitori'][$index]['basket_qty']['quantita'] = $_REQUEST['quantita_pianale'];
						$_SESSION[session_id()]['basket_fornitori'][$index]['basket_qty']['sel_quantita'] = null;
						$_SESSION[session_id()]['basket_fornitori'][$index]['basket_qty']['sel_quantita_pianale'] = $_REQUEST['quantita_pianale'];
					}
					else
					{
						$_SESSION[session_id()]['basket_fornitori'][$index]['basket_qty']['quantita'] = $_REQUEST['quantita'];
						$_SESSION[session_id()]['basket_fornitori'][$index]['basket_qty']['sel_quantita'] = $_REQUEST['quantita'];
						$_SESSION[session_id()]['basket_fornitori'][$index]['basket_qty']['sel_quantita_pianale'] = null;
					}
					$_SESSION[session_id()]['basket_fornitori'][$index]['basket_qty']['qty_scatola'] = $qty_scatola;
					$_SESSION[session_id()]['basket_fornitori'][$index]['price_it_qty'] = (str_replace(',', '.', $giacenza['prezzo_acquisto']) * $qty_scatola * $_REQUEST['quantita'] );
				}
			}
		}

		$this->tEngine->assign('basket', $_SESSION[session_id()]['basket_fornitori']);
		$this->calcolaVolumiCarrelloNl();

		if(!empty($_REQUEST['is_ajax']))
		{
			echo "<script>jQuery('#pencentuale_occupazione_f1_top').html( 'Percentuale Occupazione Carrello ".$_SESSION[session_id()]['basket_fornitori']['perc_occupazione']." % - N. Carrelli ".$_SESSION[session_id()]['basket_fornitori']['n_carrelli']."');</script>";
			echo "<script>jQuery('#pencentuale_occupazione_f1_bottom').html( 'Percentuale Occupazione Carrello ".$_SESSION[session_id()]['basket_fornitori']['perc_occupazione']." % - N. Carrelli ".$_SESSION[session_id()]['basket_fornitori']['n_carrelli']."');</script>";
			exit();

// 			echo $this->tEngine->fetch('shared/BoxCart');
// 			echo "<script>jQuery('#amount').html( '".count($_SESSION[session_id()]['basket_fornitori'])."' );</script>";
// 			exit();
		}
		else
		{
			$this->_redirect('?act=ShoppingCart');
// 			$this->tEngine->assign('basket', $_SESSION[session_id()]['basket_fornitori']);
// 			$this->tEngine->assign('tpl_action', 'ShoppingCart');
// 			$this->tEngine->display('Index');
		}
	}
}
?>