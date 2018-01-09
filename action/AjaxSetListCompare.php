<?php

include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/magazzino.php");

class AjaxSetListCompare extends DBSmartyAction
{
	function AjaxSetListCompare()
	{
		parent::DBSmartyAction();

		if(!empty($_REQUEST['get_help']))
			$this->getHelp();
		else
		{
			$BeanContent = new content();
			$where = " AND content.id = ".$_REQUEST['id_content'];
			$List = $BeanContent->dbSearch($this->conn, $where, new magazzino());
			$_SESSION[session_id()]['compared']['contents'][$List[0]['id']] = $List[0];
			
			echo '('.count($_SESSION[session_id()]['compared']['contents']).')&nbsp;';
			//<a href="'.WWW_ROOT.'?act=ListCompare&delete=1">Svuota</a>
		}
		exit();
	}
	
	function getHelp()
	{
		$html = '
		<dir id="sidebar">										
			<div id="nav_menu-3" class="widget_nav_menu widget" style="background:transparent url(../images/border_dot_light.png) repeat-x left top">
				<div class="winner">
				<h4 class="wtitle">Confronta Prodotti</h4><br>
					<div class="menu-abbigliamento-container">
					<ul id="menu-abbigliamento" class="menu">
						<li id="menu-item-115" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-115">
							Hai aggiunto il prodotto al confronto,<br> 
							ora scegli altri prodotti da confrontare oppure vedi i prodotti che hai aggiunto cliccando sul link dentro il "MIO PRO BIKE" Prodotti a Confronto
						</li>
						<li id="menu-item-115" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-115">
							<br><br>
							<img src="'.WWW_ROOT.IMG_DIR.'/web/example.png" alt="" src="" style="width:200px">
						</li>
					</ul>
					<ul id="menu-abbigliamento" class="menu">
						<li id="menu-item-115" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-115">';
				if($_REQUEST['offer'])
					$html .= '<a href="javascript:void(0);" onclick="javascript:$(\'#hlp_offer_'.$_REQUEST['id_content'].'\').hide();">Chiudi</a>';
				else
					$html .= '<a href="javascript:void(0);" onclick="javascript:$(\'#hlp_'.$_REQUEST['id_content'].'\').hide();">Chiudi</a>';
				$html .= '</li>
					</ul>
					</div>
				</div>
			</div>
		</dir>';

		if(count($_SESSION[session_id()]['compared']['contents']) == 1)
			echo $html;
			
		exit();
	}
}
