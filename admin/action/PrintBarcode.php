<?php
class PrintBarcode extends DBSmartyAction
{
	var $className;
		
	function PrintBarcode()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);

		$code = !empty($_REQUEST['code']) ? $_REQUEST['code'] : null;
		
		if(!empty($code))
		{
			$BaseBarCodeGenerator = new BaseBarCodeGenerator();
			$BaseBarCodeGenerator->configureCode39($code);
		}

		if(!empty($_REQUEST['print']))
		{
			$this->tEngine->assign('BaseBarCodeGenerator', $BaseBarCodeGenerator);
			$this->tEngine->assign('print', true);
			$this->tEngine->assign('action_class_name', $this->className);
			$this->tEngine->display($this->className);
			exit();
		}
		elseif(!empty($code))
		{
			echo '<script type="text/javascript">
			window.open("'.WWW_ROOT.'?act=PrintBarcode&code='.$code.'&print=1");
			</script>';
		}
		else 
			$this->tEngine->assign('print', false);

		$this->tEngine->assign('action_class_name', $this->className);
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');		
	}
}
?>