<?php
include_once(APP_ROOT."/beans/ecm_ordini.php");


class AjaxEcmSetOption extends DBSmartyAction
{
	function AjaxEcmSetOption()
	{
		parent::DBSmartyAction();
				
		if(!empty($_REQUEST['id']))
		{
			$Bean = new ecm_ordini();
			$Bean->dbGetOne($this->conn, $_REQUEST['id']);

			if(!empty($Bean->id))
			{
				switch ($_REQUEST['action'])
				{
					case 'pagato':
						if($Bean->pagato == 1)
							$Bean->setPagato(0);
						else
							$Bean->setPagato(1);
					break;
					case 'spedito':
						if($Bean->spedito == 1)
							$Bean->setSpedito(0);
						else
							$Bean->setSpedito(1);
					break;
					case 'fatturato':
						if($Bean->fatturato == 1)
							$Bean->setFatturato(0);
						else
							$Bean->setFatturato(1);
					break;
				}
				$Bean->dbStore($this->conn);
			}
		}
		
		//echo $this->tEngine->fetch('shared/DivGetGiacenze');
	}
}
?>