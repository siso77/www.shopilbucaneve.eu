<?php
include_once(APP_ROOT."/beans/ecm_ordini_magazzino.php");
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT."/beans/content.php");

class AjaxEcmGetProduct extends DBSmartyAction
{
	function AjaxEcmGetProduct()
	{
		parent::DBSmartyAction();

		if(!empty($_REQUEST['id_user']))
		{
			include_once(APP_ROOT."/beans/users.php");
			include_once(APP_ROOT."/beans/users_anag.php");
			$BeanUsers = new users();
			$BeanUsers->dbGetOne($this->conn, $_REQUEST['id_user']);
			$userData = $BeanUsers->vars();

			$BeanUsersAnag = new users_anag($this->conn, $userData['id_anag']);
			$userAnagData = $BeanUsersAnag->vars();

			$this->tEngine->assign('id_user', $_REQUEST['id_user']);
			$this->tEngine->assign('user_data', $userAnagData);
			
			echo $this->tEngine->fetch('shared/DivEcmGetProduct');
			exit();
		}
		
		if(!empty($_REQUEST['id_order']))
		{
			$beanOrderMagazzino = new ecm_ordini_magazzino();
			$data = $beanOrderMagazzino->dbGetAllByIdOrdine($this->conn, $_REQUEST['id_order'], new magazzino(), new content());

			$this->tEngine->assign('id_order', $_REQUEST['id_order']);
			$this->tEngine->assign('data', $data);
			
			echo $this->tEngine->fetch('shared/DivEcmGetProduct');
			exit();
		}
	}
}
?>