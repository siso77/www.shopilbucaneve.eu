<?php
class StaticPageController extends SmartyAction
{
	function StaticPageController()
	{
		parent::SmartyAction();
		
		switch ($_REQUEST['article'])
		{
			case 1:
				$intlKeyTitle 		 	= 'filosofia_title';
				$intlKeyBody 		 	= 'filosofia_body';
				$intlKeyBody2 		 	= 'filosofia_body_2';
				$intlKeyBodyEvidence 	= 'filosofia_body_evidence';
				$intlKeyParagraphTitle 	= 'filosofia_paragraph_title';
			break;
			case 2:
				$intlKeyTitle 		 = 'radici_title';
				$intlKeyBody 		 = 'radici_body';
				$intlKeyBody2 		 = 'radici_body_2';
				$intlKeyBodyEvidence = 'radici_body_evidence';
				$intlKeyParagraphTitle 	= '';
			break;
			case 3:
				$intlKeyTitle 		 = 'organizzazione_title';
				$intlKeyBody 		 = 'organizzazione_body';
				$intlKeyBody2 		 = 'organizzazione_body_2';
				$intlKeyBodyEvidence = 'organizzazione_body_evidence';
				$intlKeyParagraphTitle 	= '';
			break;
			case 4:
				$intlKeyTitle 		 = 'novita_title';
				$intlKeyBody 		 = 'novita_body';
				$intlKeyBody2 		 = 'novita_body_2';
				$intlKeyBodyEvidence = 'novita_body_evidence';
				$intlKeyParagraphTitle 	= '';
			break;
		}
		$this->tEngine->assign('key_title', $intlKeyTitle);
		$this->tEngine->assign('key_body', $intlKeyBody);
		$this->tEngine->assign('key_body_2', $intlKeyBody2);
		$this->tEngine->assign('key_body_evidence', $intlKeyBodyEvidence);
		$this->tEngine->assign('key_body_evidence', $intlKeyBodyEvidence);
		$this->tEngine->assign('key_paragraph_title', $intlKeyParagraphTitle);
		$this->tEngine->assign('tpl_action', 'static/StaticPageController');
		$this->tEngine->display('Index');
	}
}
?>