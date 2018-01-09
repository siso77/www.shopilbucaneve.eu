<?php
class LastNewsletter extends DBSmartyMailAction
{
	function LastNewsletter()
	{
		parent::DBSmartyMailAction();

		if(empty($_REQUEST['id']))
			$_REQUEST['id'] = 100;

		unset($_SESSION['newsletter_create']);
		include_once(APP_ROOT."/beans/newsletters.php");
		$beanNewsletters = new newsletters();
		$searchKeys['newsletters.id'] = $_REQUEST['id'];
		$result = $beanNewsletters->dbSearch($this->conn, $searchKeys);
		$i=0;
		foreach ($result as $val)
		{
			foreach ($val['news_data'] as $res)
			{
				$_SESSION['newsletter_create'][$i]['titolo'] = $res['titolo'];
				$_SESSION['newsletter_create'][$i]['news'] 	 = $res['news'];
				$i++;
			}
		}

		$_SESSION['newsletter_create']['index'] = $i;
	
		$this->assignNewsletterData();
		$this->tEngine->display('LastNewsletter');	
	}

	function assignNewsletterData()
	{
		$assign = $_SESSION['newsletter_create'];
		$this->tEngine->assign('titolo', $assign[0]['titolo']);
		$this->tEngine->assign('news', $assign[0]['news']);
		unset($assign[0]);
		$this->tEngine->assign('news_added', $assign);
	}
}
?>