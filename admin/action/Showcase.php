<?php
include_once(APP_ROOT.'/beans/content.php');
include_once(APP_ROOT.'/beans/magazzino.php');
include_once(APP_ROOT.'/beans/category.php');

class Showcase extends DBSmartyAction
{
	var $className;
	
	function Showcase()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);
		if(!empty($_REQUEST['add']))
		{
			$BeanContent = new content();
			$List = $BeanContent->dbSearch($this->conn, ' AND is_in_evidence > 0 ', new magazzino());
			$order = (count($List) > 0) ? count($List) : 1;
			$order++;
			$BeanContent = new content();
			$List = $BeanContent->dbSearch($this->conn, ' AND content.id = '.$_REQUEST['id'], new magazzino());
			$BeanContent->fast_edit($this->conn, $_REQUEST['id'], 'is_in_evidence', $order);
			$this->_redirect('?act=Showcase');
		}
		
		
		$BeanCategory = new category();
		$category = $BeanCategory->dbGetCategoryTree($this->conn, 'name', 'ASC');
		foreach ($category as $cat)
		{
			$catIds = null;
			foreach ($cat['sub_category'] as $subCat)
				$catIds .= $subCat['id'].',';
			
			$catIds = substr($catIds, 0, -1);
			$BeanContent = new content();
			$List[$cat['name']] = $BeanContent->dbSearch($this->conn, ' AND content.id_category IN('.$catIds.') AND is_in_evidence > 0 ORDER BY is_in_evidence ASC ', new magazzino());
			$List[$cat['name']]['parent_id'] = $cat['id'];
		}
		
		$this->tEngine->assign('data', $List);
		$this->tEngine->assign('action_class_name', $this->className);
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}
}
?>