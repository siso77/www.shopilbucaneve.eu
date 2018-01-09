<?php

class NuovoRappresentante extends DBSmartyAction
{
	function NuovoRappresentante()
	{
		parent::DBSmartyAction();
		
		if(
			!empty($_REQUEST['rappresentante_nome']) && 
			!empty($_REQUEST['rappresentante_cognome']) &&
			!empty($_REQUEST['rappresentante_indirizzo']) &&
			!empty($_REQUEST['rappresentante_citta']) &&
			!empty($_REQUEST['rappresentante_cap']) &&
			!empty($_REQUEST['rappresentante_cellulare']) &&
			!empty($_REQUEST['rappresentante_fisso']) &&
			!empty($_REQUEST['rappresentante_email']) &&
			!empty($_REQUEST['rappresentante_percentuale_provvigioni']) 
			)
		{
				$data = array('nome' => $_REQUEST['rappresentante_nome'], 
							'cognome' => $_REQUEST['rappresentante_cognome'],
							'indirizzo' => $_REQUEST['rappresentante_indirizzo'],
							'citta' => $_REQUEST['rappresentante_citta'],
							'cap' => $_REQUEST['rappresentante_cap'],
							'cellulare' => $_REQUEST['rappresentante_cellulare'],
							'fisso' => $_REQUEST['rappresentante_fisso'],
							'email' => $_REQUEST['rappresentante_email'],
							'percentuale_provvigioni' => $_REQUEST['rappresentante_percentuale_provvigioni'],
							'operatore'=> $_SESSION['LoggedUser']['username']);
				
				include_once(APP_ROOT."/beans/rappresentante.php");
				$BeanRappresentante = new rappresentante($this->conn, $data);
				
				$search = " AND nome = '".$_REQUEST['rappresentante_nome']."' AND cognome = '".$_REQUEST['rappresentante_cognome']."' AND email = '".$_REQUEST['rappresentante_email']."'";
				$RappresentanteFound = $BeanRappresentante->dbSearch($this->conn, $search);

				if(empty($RappresentanteFound))
					$return['id_rappresentante'] = $BeanRappresentante->dbStore($this->conn);
		}
		
		$this->_redirect('?act=ListaRappresentanti&reload_search=1&nome='.$_REQUEST['rappresentante_nome'].'&cognome='.$_REQUEST['rappresentante_cognome']);
		
	}
}
?>