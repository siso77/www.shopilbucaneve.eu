<?php
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/users.php");


class AjaxSetOption extends DBSmartyAction
{
	function AjaxSetOption()
	{
		parent::DBSmartyAction();
				
		if(!empty($_REQUEST['id_user']))
		{
			$BeanUtenti = new users($this->conn, $_REQUEST['id_user']);
			$BeanUtenti->setIs_agent($_REQUEST['codice_agente']);
			$BeanUtenti->dbStore($this->conn);
			
			echo 'Modifica Avvenuta con successo!
					<script>
					setTimeout(\'document.location.href="'.WWW_ROOT.'?act=ListaUtenti&reset=1"\', 1500);
					</script>
					';
		}
		elseif(!empty($_REQUEST['id_content']))
		{
			$BeanContent = new content();
			$BeanContent->dbGetOne($this->conn, $_REQUEST['id_content']);

			if(!empty($BeanContent->id))
			{
				switch ($_REQUEST['action'])
				{
					case 'is_in_ecommerce':
						if($BeanContent->is_in_ecommerce == 1)
							$BeanContent->setIs_in_ecommerce(0);
						else
							$BeanContent->setIs_in_ecommerce(1);
					break;
					case 'is_in_evidence':
						if($BeanContent->is_in_evidence == 1)
							$BeanContent->setIs_in_evidence(0);
						else
							$BeanContent->setIs_in_evidence(1);
					break;
					case 'is_invisible':
						if($BeanContent->is_invisible == 1)
							$BeanContent->setIs_invisible(0);
						else
							$BeanContent->setIs_invisible(1);
					break;
					case 'is_in_offer':
						if($BeanContent->is_in_offer == 1)
							$BeanContent->setIs_in_offer(0);
						else
							$BeanContent->setIs_in_offer(1);
					break;
				}
				$BeanContent->dbStore($this->conn);
			}
			$BeanContent = new content();
			$BeanContent->dbGetOne($this->conn, $_REQUEST['id_content']);
			echo "";
			exit();
		}

		//echo $this->tEngine->fetch('shared/DivGetGiacenze');
	}
}
?>