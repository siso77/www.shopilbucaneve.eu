<?php
include_once(APP_ROOT."/beans/users.php");
include_once(APP_ROOT."/beans/users_anag.php");

class NuovoUtente extends DBSmartyAction
{
	function NuovoUtente()
	{
		parent::DBSmartyAction();

		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if(!empty($_REQUEST['id']))
				$BeanUser = new users($this->conn, $_REQUEST['id']);
			else
				$BeanUser = new users($this->conn);
				
			if($_REQUEST['password'] != $_REQUEST['cnf_password'])
			{
				$_SESSION['ControllerError']['message'] = 'Errore: Il campo Password non coincide con il campo Conferma Password';
				header('Location:'.WWW_ROOT.'?act=ControllerError');
				exit();
			}
			else 
			{
				unset($_REQUEST['id']);
				if(empty($_REQUEST['password']))
					unset($_REQUEST['password']);
				else
					$_REQUEST['password'] = md5($_REQUEST['password']).PASSWORD_SALT;
	
				$_REQUEST['id_type'] = $_REQUEST['type'];
				
				$userExists = $BeanUser->getId_anag();
				if(!empty($userExists))
				{
					$BeanUserAnag = new users_anag($this->conn, $BeanUser->getId_anag());
					$BeanUserAnag->fill($_REQUEST);
					$BeanUserAnag->dbStore($this->conn);
				}
				else
				{
					$BeanUserAnag = new users_anag();
					$BeanUserAnag->fill($_REQUEST);
					$idAnag = $BeanUserAnag->dbStore($this->conn);
					$_REQUEST['id_anag'] = $idAnag;
				}

				$_REQUEST['operatore'] = $_SESSION['LoggedUser']['username'];
				$BeanUser->fill($_REQUEST);
				$BeanUser->setIs_active(1);
				$idUser = $BeanUser->dbStore($this->conn);
				
				$BeanUser = new users();
				$BeanUser->dbGetOneNotActive($this->conn, $idUser);
				$BeanUser->setIs_active(1);
				$BeanUser->dbStore($this->conn);
			}
			header('Location:'.WWW_ROOT.'?act=ListaUtenti&reset=1');
		}

		$this->tEngine->assign('tpl_action', 'NuovoUtente');
		$this->tEngine->display('Index');
	}
}
?>
