<?php
class Currency
{
	static public function getFormatDiscount($price, $percentSale)
	{		
		$return['discount']	= ( $price * $percentSale / 100);
		$return['total_discounted'] = $price - $return['discount'];
		
		return $return;
	}
	
	static public function FormatDateFromMysql($date)
	{
		$exp = explode('-', $date);
		return $exp[2].'/'.$exp[1].'/'.$exp[0];
	}	
	
	static public function FormatEuro($str)
	{
		$str = round(str_replace(',', '.', $str), 2);

		if(strstr($str, "."))
		{
			$exp_price = explode(".", $str);

			if(strlen($exp_price[1]) == 1)
				$return = $str."0";
			elseif(strlen($exp_price[1]) == 0)
				$return = $exp_price[0].",00";
			else 
				$return = $str;
		}
		else
			$return = $str.",00";

		$return = str_replace(".", ",", $return);
		if(empty($_SESSION['label_currency']))
			$_SESSION['label_currency'] = '&euro;';
		return $return.''.$_SESSION['label_currency'];
	}
	
	static public function getPriceByQty($giacenza, $quantita)
	{
		$mapQuantita[1] = $giacenza['quantita_1'];
		$mapQuantita[2] = $giacenza['quantita_2'];
		$mapQuantita[3] = $giacenza['quantita_3'];
		$mapQuantita[4] = $giacenza['quantita_4'];
		$mapQuantita[5] = $giacenza['quantita_5'];
		$mapQuantita[6] = $giacenza['quantita_6'];
		$mapQuantita[7] = $giacenza['quantita_7'];
		$mapQuantita[8] = $giacenza['quantita_8'];
		$mapQuantita[9] = $giacenza['quantita_9'];
		
		$all_empty = false;
		foreach ($mapQuantita as $key => $qta)
		{
			if($qta == 0)
				$all_empty = true;
		}
		if($all_empty)
			return 'prezzo_0';

		foreach ($mapQuantita as $key => $qta)
		{
			if($quantita < $qta)
			{
				if($key == 1)
					return 'prezzo_0';
			}
			elseif($quantita >= $qta)
			{
				if($key == 9)
					return 'prezzo_9';
				elseif($quantita < $mapQuantita[$key+1])
					return 'prezzo_'.$key;
			}
			else
			{
				continue;
			}
		}
	}
}
?>