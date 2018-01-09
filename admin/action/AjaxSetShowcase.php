<?php
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT."/beans/content.php");

class AjaxSetShowcase extends DBSmartyAction
{
	function AjaxSetShowcase()
	{
		parent::DBSmartyAction();

		if(!empty($_REQUEST['remove']) && !empty($_REQUEST['id_content']))
		{
			$BeanContent = new content();
			$BeanContent->fast_edit($this->conn, $_REQUEST['id_content'], 'is_in_evidence', 0);
		}
		if(!empty($_REQUEST['id_content']))
		{
			$index = 1;
			foreach ($_REQUEST['id_content'] as $id)
			{
				$BeanContent = new content();
				$BeanContent->fast_edit($this->conn, $id, 'is_in_evidence', $index);
				$index++;
			}
		}
		//echo $this->tEngine->fetch('shared/DivGetGiacenze');
	}
}
?>