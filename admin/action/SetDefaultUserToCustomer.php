<?php
include_once(APP_ROOT."/beans/customer.php");
include_once(APP_ROOT."/beans/users.php");
include_once(APP_ROOT."/beans/users_anag.php");

class SetDefaultUserToCustomer extends DBSmartyAction
{
	var $className;
	var $operatore = 'default_association_procedure';
	
	function SetDefaultUserToCustomer()
	{
		parent::DBSmartyAction();

		$this->className = get_class($this);
exit('Funzionalita non abilitata!');
		$BeanCustomer = new customer();
		$customers = $BeanCustomer->dbGetAll($this->conn);
		
		foreach ($customers as $customer)
		{
			
			if(!empty($customer['p_iva']))
			{
				$BeanUsers = new users();
				$userExists = $BeanUsers->dbSearch($this->conn, " AND username ='".$customer['p_iva']."'");
// 				if(!empty($userExists) && $userExists != array())
// 				{
// 					$BeanUsers = new users($this->conn, $userExists[0]['id']);
// 					if(!empty($customer['email']))
// 						$BeanUsers->setUsername($customer['email']);
// 					$BeanUsers->setId_customer($customer['id']);
// 					$BeanUsers->dbStore($this->conn);
// 				}
// 				else
				if(empty($userExists) && $userExists == array())
				{
					$BeanUserAnag = new users_anag();
					$BeanUserAnag->setName($customer['ragione_sociale']);
					$BeanUserAnag->setSurname('-');
					$BeanUserAnag->setEmail(strtolower($customer['email']));
					$BeanUserAnag->setAddress(strtolower($customer['indirizzo']));
					$BeanUserAnag->setCity(strtolower($customer['citta']));
					$BeanUserAnag->setCap(strtolower($customer['cap']));
					$BeanUserAnag->setProvince(strtoupper($customer['provincia']));
					$BeanUserAnag->setNation(strtoupper($customer['stato']));
					$BeanUserAnag->setPhone($customer['fisso']);
					$BeanUserAnag->setMobile($customer['cellulare']);
					$id_user_anag = $BeanUserAnag->dbStore($this->conn);
					
					$BeanUsers = new users();
					$BeanUsers->setUsername($customer['p_iva']);

					if(!empty($customer['p_iva']))
						$BeanUsers->setPassword(md5($customer['p_iva']).PASSWORD_SALT);
					else
						$BeanUsers->setPassword(md5($customer['customer_code']).PASSWORD_SALT);
	
					if(!empty($id_user_anag))
						$BeanUsers->setId_anag($id_user_anag);

					$BeanUsers->setId_customer($customer['id']);

					$BeanUsers->setIs_newsletter_subscribed(1);
					$BeanUsers->setIs_t_c_accepted(1);
					$BeanUsers->setOperatore($this->operatore);
					$BeanUsers->setData_inserimento_riga(date('Y-m-d'));
					$BeanUsers->setData_modifica_riga(date('Y-m-d'));
					$BeanUsers->setId_type(3);
					$id_user = $BeanUsers->dbStore($this->conn);

					$Bean = new users();
					$Bean->dbGetOneNotActive($this->conn, $id_user);
					$Bean->setIs_active(1);
					$Bean->dbStore($this->conn);
				}
			}
		}
		$this->_redirect('?act=ListaUtenti');
	}
}
?>