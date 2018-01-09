<?php
include_once(APP_ROOT.'/libs/ext/open_flash_chart/php-ofc-library/open-flash-chart.php');
class OpenChartPieImplement
{
	var $title;
	var $pie;
	var $y;
	var $x;
	var $x_labels;
	var $tooltip;
	var $chart;
	var $values;
	var $mapMonth = array(
							1 => 'Gennaio',
							2 => 'Febbraio',
							3 => 'Marzo',
							4 => 'Aprile',
							5 => 'Maggio',
							6 => 'Giugno',
							7 => 'Luglio',
							8 => 'Agosto',
							9 => 'Settembre',
							10 => 'Ottobre',
							11 => 'Novembre',
							12 => 'Dicembre',
							);
	
	function OpenChartPieImplement($title, $description, $data)
	{
		$this->title =  new title( $title );
		$this->title->set_style( "{font-size: 12px; color: #000000; text-align: center;}" );

		
		
		
		$this->pie = new pie();
		$this->pie->alpha(0.5)->add_animation( new pie_fade() )->add_animation( new pie_bounce(5) )
		    //->start_angle( 270 )
		    ->start_angle( 0 )
		    //->tooltip( '#val# of #total#<br>#percent# of 100%' )
		    ->tooltip( '#percent#' )
		    ->colours(array('#FF1414','#14FF14','#6773FF','#148400','#4DAA3D', '#1414FF', '#54D2FF', '#FFCF6E', '#8A008D', '#A697FF', '#FF8479', '#FF2F08'));

//		$this->pie = new pie();
//		$this->pie->set_alpha(0.6);
//		$this->pie->set_start_angle( 35 );
//		$this->pie->add_animation( new pie_fade() );
//		$this->pie->set_tooltip( '#val# of #total#<br>#percent# of 100%' );
//		$this->pie->set_colours( array('#FF1414','#14FF14','#1414FF','#148400','#4DAA3D', '#6773FF', '#54D2FF', '#FFCF6E', '#8A008D', '#A697FF', '#FF8479', '#FF2F08') );
		foreach ($data as $key => $v)
		{
			if(!empty($this->mapMonth[$key]) && !empty($v))
				$value[] = new pie_value($v, $this->mapMonth[$key].' '.$this->FormatEuro(str_replace(',','',$v)).' Euro');
		}
		$this->pie->set_values( $value );
		
		$this->chart = new open_flash_chart();
		$this->chart->set_title( $this->title );
//		$this->chart->set_bg_colour( '#FFFFFF' );
		$this->chart->add_element( $this->pie );
	}
	
	function setTitle($title)
	{
		$this->_title = $title;
	}
	
	function setValues($data)
	{
		foreach ($data as $key => $val)
		{
			if(!empty($this->mapMonth[$key]) && !empty($val))
				$value[] = new pie_value($val, $this->mapMonth[$key]);
		}
		$this->pie->set_values( $value );
	}
	
	function getChart()
	{
		return $this->chart;
	}
	
	function FormatEuro($str)
	{
		if(strstr($str, ","))
		{
			$exp_price = explode(",", $str);
		
			if(strlen($exp_price[1]) == 1)
				$return = $str."0";
			elseif(strlen($exp_price[1]) == 0)
				$return = $str.",00";
			else 
				$return = $str;
		}
		elseif(strstr($str, "."))
		{
			$exp_price = explode(".", $str);
		
			if(strlen($exp_price[1]) == 1)
				$return = $str."0";
			elseif(strlen($exp_price[1]) == 0)
				$return = $str.",00";
			else 
				$return = $str;
		}
		else
			$return = $str.",00";
		
		$return = str_replace(".", ",", $return);
		
		return $return;
	}
}
?>