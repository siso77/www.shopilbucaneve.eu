<?php
require_once 'Pager/Pager.php';

class MyPager
{
	var $pager;

	function MyPager($list, $row_for_page, $urlVar = 'pageID')
	{

		$params = array(
			'mode'       => 'Jumping',
			'perPage'    => $row_for_page,
			'delta'      => 5,
			'itemData'   => $list,
			'urlVar'	=> $urlVar
		);
		$this->pager = & Pager::factory($params);
	}

	function getData(){ return $this->pager->getPageData(); }
	
	function getLinks(){ return $this->pager->getLinks(); }
	
	function getLinksTag(){ return $this->pager->linkTags; }
}
/*					ESEMPIO DI UTILIZZO
class prova
{
	function prova()
	{
		$conn = MyDB::connect();
		$bean = new tab_comando();
		$list = $bean->dbGetAll($conn);
		$p = new MyPager($list);
		$links = $p->getLinks();

		echo $links['all'];

		echo $p->getLinksTag();

		print_r($p->getData());
	}
}
*/
?>