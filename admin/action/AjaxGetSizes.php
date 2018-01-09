<?php
include_once(APP_ROOT.'/beans/size_type.php');
include_once(APP_ROOT.'/beans/sizes.php');

class AjaxGetSizes extends DBSmartyAction
{
	function AjaxGetSizes()
	{
		parent::DBSmartyAction();

		if(!empty($_REQUEST['id_size_type']))
		{
			$BeanSize = new sizes();
			$data = $BeanSize->dbSearch($this->conn, ' WHERE id_type='.$_REQUEST['id_size_type']);
			$this->tEngine->assign('data', $data);
		}
		elseif(!empty($_REQUEST['id_size']))
		{
			$BeanSize = new sizes($this->conn, $_REQUEST['id_size']);
			$this->tEngine->assign('data', $BeanSize->dbGetAllByIdSizeType($this->conn, $BeanSize->getId_type()));
			
			$idSize = $_REQUEST['id_size'];
			$idSizeType = $BeanSize->getId_type();
			
			$BeanSizeType = new size_type();
			$this->tEngine->assign('size_types', $BeanSizeType->dbGetAll($this->conn, 'id', 'asc'));

			
			$this->tEngine->assign('id_size', $idSize);
			$this->tEngine->assign('id_size_type', $idSizeType);
			$this->tEngine->assign('contenuto_precaricato', true);
		}
		if(!empty($_REQUEST['display_type']))
			$this->tEngine->assign('display_type', true);

		$this->tEngine->display('AjaxGetSizes');
	}
}
?>