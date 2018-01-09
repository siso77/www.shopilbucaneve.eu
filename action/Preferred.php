<?php
include_once(APP_ROOT.'/beans/category.php');
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/magazzino.php");

class Preferred extends DBSmartyAction
{
	function Preferred()
	{
		parent::DBSmartyAction();

		if(!empty($_SERVER['HTTP_DELETE_SESSION']))
			unset($_SESSION[session_id()]);

		if(!empty($_REQUEST['delete']))
		{
			unset($_SESSION[session_id()]['preferred']['contents'][ $_REQUEST['id_content'] ]);
			foreach ($_SESSION[session_id()]['basket_preferred'] as $key => $value)
			{
				if($value['id'] == $_REQUEST['id_content'])
					unset($_SESSION[session_id()]['basket_preferred'][$key]);
			}
		}

		if(!empty($_REQUEST['id']))
		{
			$Bean = new magazzino();
			$where = " AND magazzino.id = ".$_REQUEST['id'];		
			$content = $Bean->dbSearch($this->conn, $where);			

			$_SESSION[session_id()]['preferred']['contents'][ $content[0]['id_content'] ] = $content[0];
			
			$contentInCart = $_SESSION[session_id()]['preferred']['contents'][ $content[0]['id_content'] ]; 
			$contentInCart['price_it_qty'] = $this->FormatEuro(str_replace(',', '.', $contentInCart['price_it'])*1);
			$contentInCart['price_discounted_it_qty'] = $this->FormatEuro(str_replace(',', '.', $contentInCart['price_discounted_it'])*1);
			$_SESSION[session_id()]['preferred']['contents'][ $content[0]['id_content'] ] = $contentInCart;
		}
//		if(!empty($_REQUEST['quantita']))
//		{
//			foreach ($_REQUEST['quantita'] as $key => $value) 
//			{
//				$_SESSION[session_id()]['basket_preferred'][$key]['id'] = $_REQUEST['id_to_basket'][$key];
//				$_SESSION[session_id()]['basket_preferred'][$key]['quantita'] = $_REQUEST['quantita'][$key];
//				$contentInCart = $_SESSION[session_id()]['preferred']['contents'][ $_SESSION[session_id()]['basket_preferred'][$key]['id'] ]; 
//				$contentInCart['price_it_qty'] = $this->FormatEuro(str_replace(',', '.', $contentInCart['price_it'])*$_SESSION[session_id()]['basket_preferred'][$key]['quantita']);
//				$contentInCart['price_discounted_it_qty'] = $this->FormatEuro(str_replace(',', '.', $contentInCart['price_discounted_it'])*$_SESSION[session_id()]['basket_preferred'][$key]['quantita']);
//				$_SESSION[session_id()]['preferred']['contents'][ $_SESSION[session_id()]['basket_preferred'][$key]['id'] ] = $contentInCart;
//			}
//		}
		
		$this->tEngine->assign('products', $_SESSION[session_id()]['preferred']);
		$this->tEngine->assign('basket', $_SESSION[session_id()]['basket_preferred']);
		
		$this->tEngine->assign('tpl_action', 'Preferred');
		$this->tEngine->display('Index');
	}
}
?>