<?php

class Newsletter extends DBSmartyMailAction
{
	function Newsletter()
	{
		parent::DBSmartyMailAction();

		if(!empty($_REQUEST['customer']))
			$_SESSION['selected_customer'] = $_REQUEST['customer'];

		if(empty($_SESSION['newsletter_create']['index']))
			$_SESSION['newsletter_create']['index'] = 1;
		if(!empty($_REQUEST['new']))
			$_SESSION['newsletter_create'] = null;

		if(!empty($_REQUEST['addNews']))
			$this->addNews();
		elseif(!empty($_REQUEST['removeNews']))
			$this->removeNews();
		elseif(!empty($_REQUEST['step']))
		{
			if($_REQUEST['step'] == 1)
				$this->setpUno();
			elseif($_REQUEST['step'] == 2)
				$this->stepDue();
			elseif($_REQUEST['step'] == 3)
				$this->stepTre();		
			elseif($_REQUEST['step'] == 4)
				$this->stepQuattro();
			elseif($_REQUEST['step'] == 5)
				$this->stepCinque();
		}
		elseif(empty($_REQUEST['pageID']))
			$this->tEngine->assign('tpl_action', 'Newsletter');
			
		if(!empty($_REQUEST['search']) || !empty($_REQUEST['pageID']) || !empty($_REQUEST['order_by']) || !empty($_REQUEST['rowForPage']))
		{
			if($_SERVER['REQUEST_METHOD'] == 'POST' || !empty($_REQUEST['reset']) || !empty($_REQUEST['Go']) || !empty($_REQUEST['order_by']) || !empty($_REQUEST['search']))
				$this->searchNewsletter();
				
			$this->tEngine->assign('tpl_action', 'Newsletter_search');
		}

		$p 	   = new MyPager($_SESSION['search_newsletter']['result'], $this->rowForPage);
		$data  = $p->getData();
		$links = $p->getLinks();
		$this->tEngine->assign('newsletter' , is_array($data) ? $data : array());
		$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
		$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
		$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
		$this->tEngine->assign('numViewPage', $this->numViewPage);

		$this->assignNewsletterData();
		$this->tEngine->display('Index');
	}
	
	function removeNews()
	{
		$_SESSION['newsletter_create']['index'] = $_SESSION['newsletter_create']['index'] - 1;
		unset($_SESSION['newsletter_create'][$_SESSION['newsletter_create']['index']]);
		
		$this->assignNewsletterData();
		$this->tEngine->assign('tpl_action', 'Newsletter');
	}
	
	function addNews()
	{
		$this->setpUno();
		$_SESSION['newsletter_create'][$_SESSION['newsletter_create']['index']]['titolo'] = null;
		$_SESSION['newsletter_create'][$_SESSION['newsletter_create']['index']]['news']	 = null;
		$_SESSION['newsletter_create']['index'] = $_SESSION['newsletter_create']['index']+1;
		$this->assignNewsletterData();
		$this->tEngine->assign('tpl_action', 'Newsletter');					
	}
	
	function setpUno()
	{
		unset($_POST['step']);
		unset($_POST['addNews']);

		for($i=0;$i<=$_SESSION['newsletter_create']['index'];$i++)
		{
			if(!empty($_POST['titolo_'.$i]) && $_POST['titolo_'.$i] != 'Titolo News')
				$_SESSION['newsletter_create'][$i]['titolo'] = htmlentities($_POST['titolo_'.$i], ENT_QUOTES,  "UTF-8");
			if(!empty($_POST['news_'.$i]))
				$_SESSION['newsletter_create'][$i]['news'] 	 = str_replace('\\', '', $_POST['news_'.$i]);
		}
		$this->assignNewsletterData();
		$this->tEngine->assign('tpl_action', 'Newsletter_preview');					
	}
	
	function searchNewsletter()
	{
		if(!empty($_REQUEST['order_by']))
		{
			unset($_SESSION['search_newsletter']['result']);
			$_SESSION['search_newsletter']['order_by'] = $_REQUEST['order_by'];
		}
		if(!empty($_REQUEST['order_by']))
			$_SESSION['search_newsletter']['order_type'] = $_REQUEST['order_type'];
			
		if(!empty($_REQUEST['reset']))
		{
			unset($_SESSION['search_newsletter']['result']);
			unset($_SESSION['search_newsletter']['keys']);
			unset($_SESSION['search_newsletter']['order_type']);
			unset($_SESSION['search_newsletter']['order_by']);
		}
		if(!empty($_REQUEST['Go']))
		{
			unset($_SESSION['search_newsletter']['result']);
			unset($_SESSION['search_newsletter']['keys']);
			unset($_SESSION['search_newsletter']['order_type']);
			unset($_SESSION['search_newsletter']['order_by']);
		}
		include_once(APP_ROOT."/beans/newsletters.php");
		$beanNewsletters = new newsletters();
		if(!empty($_POST))
		{
			unset($_POST['search']);
			unset($_POST['categoria']);
//			$searchKeys['newsletter_data.titolo'] = $_POST['titolo'];
			$searchKeys['newsletters.object'] = $_POST['object'];
		}
		else
			$searchKeys = null;

		$_SESSION['search_newsletter']['keys'] = $searchKeys;
		$reloadSearch = false;
		if(!empty($_POST['titolo']) || !empty($_POST['object']) ||  !empty($_POST['date_last_modify'])) 
			$reloadSearch = true;

		if(empty($_SESSION['search_newsletter']['result']))
			$reloadSearch = true;
			
		if(empty($_SESSION['search_newsletter']['result']) || $reloadSearch)
			$_SESSION['search_newsletter']['result'] = $beanNewsletters->dbSearch(
																					$this->conn, 
																					$_SESSION['search_newsletter']['keys'], 
																					$_SESSION['search_newsletter']['order_by'], 
																					$_SESSION['search_newsletter']['order_type']);
	}	
	
	function stepDue()
	{
		$query = "SELECT * FROM newsletter_emails";
		$res = mysql_query($query, $this->conn->connection);
		
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
		    $emails[] = $row;
		}
		$this->tEngine->assign('emails', $emails);
		$this->tEngine->assign('selected_customer', $_SESSION['selected_customer']);
		$this->tEngine->assign('tpl_action', 'Newsletter_choise_user');
	}

	function stepTre()
	{
		include_once(APP_ROOT."/beans/newsletters_to_newsletter_data.php");
		include_once(APP_ROOT."/beans/newsletter_data.php");
		include_once(APP_ROOT."/beans/newsletters.php");

		if(!empty($_SESSION['newsletter_to_send']['id']))
			$idNewsletter = $_SESSION['newsletter_to_send']['id'];

		if(!empty($idNewsletter))
			$beanNewsletters = new newsletters($this->conn, $idNewsletter);
		else
			$beanNewsletters = new newsletters();
		$beanNewsletters->setDate_last_modify(date('Y-m-d H:i:s'));
		$beanNewsletters->setObject($_REQUEST['oggetto']);
		$idNewsletter = $beanNewsletters->dbStore($this->conn);

		if(empty($idNewsletter))
			$_SESSION['newsletter_to_send']['id'] = $idNewsletter;
		
		if(!empty($_REQUEST['save']))
		{
			for($i=0;$i<=$_SESSION['newsletter_create']['index'];$i++)
			{
				if(empty($_SESSION['newsletter_create'][$i]['titolo']))
					$_SESSION['newsletter_create'][$i]['titolo'] = ' ';
				$data = array('titolo' => $_SESSION['newsletter_create'][$i]['titolo'], 'news' => $_SESSION['newsletter_create'][$i]['news']);

				$beanNewslettersData = new newsletter_data($this->conn, $data);
				$idNewslettersData = $beanNewslettersData->dbStore($this->conn);
				$beanNewslettersToNewsletterData = new newsletters_to_newsletter_data($this->conn, array(
																										'id_newsletter_data'=>$idNewslettersData,
																										'id_newsletter'=>$idNewsletter
																										));
				$beanNewslettersToNewsletterData->dbStore($this->conn);
			}
		}

		if(empty($_REQUEST['save']))
		{
			$users = array();
			if(!empty($_POST['mail']))
			{
				$users = array(array('mail' => $_POST['mail']));
			}
			elseif(!empty($_POST['categoria']))
			{
				foreach ($_POST['categoria'] as $categoria)
				{
					if($categoria == 'all')
					{
						$query = "select * from newsletter_emails";
						$res = mysql_query($query, $this->conn->connection);

						while ($row = mysql_fetch_assoc($res))
							$users[] = array('mail' => $row['email']);
					}
					else
					{
						$query = "select * from newsletter_emails WHERE corso ='".$categoria."'";
						$res = mysql_query($query, $this->conn->connection);
						
						while ($row = mysql_fetch_assoc($res))
							$users[] = array('mail' => $row['email']);

// 						if(!empty($categoria))
// 							$users = array_merge($users, array(array('mail' => $categoria)));
					}
				}
			}
				
			$this->sendNewsletters($users);
		}
		if(empty($_REQUEST['save']))
			$this->tEngine->assign('is_send', true);
		else 
			$this->_redirect('?act=Newsletter&search=1&reset=1');
		$this->tEngine->assign('tpl_action', 'Newsletter_choise_user');
	}

	function stepQuattro()
	{
		unset($_SESSION['newsletter_create']);
		include_once(APP_ROOT."/beans/newsletters.php");
		$beanNewsletters = new newsletters();
		$searchKeys['newsletters.id'] = $_REQUEST['id'];
		$_SESSION['newsletter_to_send']['id'] = $_REQUEST['id'];
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
		
//		$_SESSION['newsletter_create']['id']	= $result[0]['id'];
		$_SESSION['newsletter_create']['index'] = $i;

		$this->assignNewsletterData();
		$this->tEngine->assign('tpl_action', 'Newsletter_preview');	
	}

	function stepCinque()
	{
		include_once(APP_ROOT."/beans/newsletters.php");
		$beanNewsletters = new newsletters();
		$beanNewsletters->dbDelete($this->conn, array($_REQUEST['id']), false);
		header('Location:'.WWW_ROOT.'?act=Newsletter&search=1&reset=1');		
	}
	
	function sendNewsletters($users)
	{
		$this->params["host"]  = EMAIL_ADMIN_HOST;
		$this->params["auth"]  = true;
		$this->params["username"]  = EMAIL_ADMIN_USERNAME;
		$this->params["password"]  = EMAIL_ADMIN_PASSWORD;

		$newsInSession = $_SESSION['newsletter_create'];
		$data 	= str_replace('ì', '&igrave;', strftime("%A %d %B %Y"));
		$titolo = $newsInSession[0]['titolo'];
		$news 	= $newsInSession[0]['news'];
		$idNewsletter = $_SESSION['newsletter_to_send']['id'];
		unset($newsInSession[0]);
//		include_once(APP_ROOT.'/style/template/web/Newsletter_to_send.phtml');
//		$this->setHtmlText($html);
//		$this->mail_factory();

		foreach ($users as $value)
		{
			require(APP_ROOT.'/style/template/web/Newsletter_to_send.phtml');
			$this->setHtmlText($html);
			$this->mail_factory();

			$hdrs = array(
						  "From" 		=> ''.EMAIL_ADMIN.'', 
						  "To" 			=> $value['mail'],
						  "Cc" 			=> "", 
						  "Bcc" 		=> "", 
						  "Subject" 	=> str_replace('\\', '', $_POST['oggetto']),
						  "Return-path" => EMAIL_ADMIN,
	//					  "Content-Type" => "text/plain; charset=utf-8",
						  "Mailed-by"			=> "tangando.it",
						  "Signed-by"			=> "tangando.it",
						  "Date"		=> date("r")
						  );
			$this->setHeaders($hdrs);

			$is_send = $this->sendMail($value['mail']);

			if(PEAR::isError($is_send))
				$emailError[] = $value['mail'];
		}

		if(is_array($emailError))
		{
			$hdr = array("From" 	=> "noreply@pro-bike.it", 
						  "To" 			=> EMAIL_ADMIN,
						  "Cc" 			=> "", 
						  "Bcc" 		=> "", 
						  "Subject" 	=> 'Errore invio email',
						  "Date"		=> date("r")
						);
			$this->setHeaders($hdr);
			$this->setHtmlText('Errore invio email per :\n');
			foreach ($emailError as $value)
				$this->setHtmlText($value.'\n');
			$this->mail_factory();
			$this->sendMail(EMAIL_ADMIN);
		}
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