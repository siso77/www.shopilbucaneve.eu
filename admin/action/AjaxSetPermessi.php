<?php
include_once(APP_ROOT."/beans/users_type.php");

class AjaxSetPermessi extends DBSmartyAction
{
	function AjaxSetPermessi()
	{
		parent::DBSmartyAction();

		$BeanUserType = new users_type($this->conn, $_REQUEST['id_user_type']);
		$permessi = $BeanUserType->getPermessi();

		$exp = explode('|', $permessi);
		foreach ($exp as $key => $val)
		{
			$ex = explode('@', $val);
			if($ex[0] == $_REQUEST['action'])
			{
				if($ex[1] == 0)
					$exp[$key] = $ex[0].'@1';
				else
					$exp[$key] = $ex[0].'@0';
			}
		}
		$permessi = implode('|', $exp);
		$BeanUserType->setPermessi($permessi);
		$BeanUserType->dbStore($this->conn);
		echo '<script>$.fancybox.close( true );</script>';
		echo 'Modifica effettuata con successo';
	}
}
?>