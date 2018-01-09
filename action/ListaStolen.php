<?php
include_once(APP_ROOT.'/beans/stolen_content.php');
include_once(APP_ROOT.'/beans/images_stolen.php');
include_once(APP_ROOT.'/beans/users.php');
include_once(APP_ROOT.'/beans/users_anag.php');

class ListaStolen extends DBSmartyAction
{
	var $className;
	
	function ListaStolen()
	{
		parent::DBSmartyAction();

		$this->className = get_class($this);
		
		$BeanMercatino = new stolen_content();
		$List = $BeanMercatino->dbGetAllCustom($this->conn, new images_stolen());

		foreach ($List as $key => $value) 
		{
			$BeanUser = new users($this->conn, $value['id_user']);
			$BeanUserAnag = new users_anag($this->conn, $BeanUser->id_anag);
			$List[$key]['user_data']['name'] = $BeanUserAnag->name;
			$List[$key]['user_data']['surname'] = $BeanUserAnag->surname;
			$List[$key]['user_data']['phone'] = $BeanUserAnag->phone;
			$List[$key]['user_data']['mobile'] = $BeanUserAnag->mobile;
			$List[$key]['user_data']['email'] = $BeanUserAnag->email;
		}

		$p = new MyPager($List, $this->rowForPage);

		$this->tEngine->assign("data"	    , $p->getData());
		$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
		$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
		$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
		$this->tEngine->assign('numViewPage', $this->numViewPage);
		
		
		$this->tEngine->assign('action_class_name', $this->className);		
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}
}
?>