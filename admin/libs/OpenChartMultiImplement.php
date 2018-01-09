<?php
include_once(APP_ROOT.'/libs/ext/open_flash_chart/php-ofc-library/open-flash-chart.php');
class OpenChartMultiImplement
{
	var $title;
	var $bar_stack;
	var $y;
	var $x;
	var $x_labels;
	var $tooltip;
	var $chart;
	
	function OpenChartMultiImplement($title, $description, $List)
	{
		$animation_1	= isset($_GET['animation_1'])?$_GET['animation_1']:'pop';
		$delay_1    	= isset($_GET['delay_1'])?$_GET['delay_1']:0.5;
		$cascade_1		= isset($_GET['cascade_1'])?$_GET['cascade_1']:1;
		
		$this->title =  new title( $title );
		$this->title->set_style( "{font-size: 12px; color: #000000; text-align: center;}" );
		
		$this->chart = new open_flash_chart();
		$this->chart->set_title( $this->title );
		$this->chart->set_bg_colour( '#FFFFFF' );
		foreach($List as $k => $val)
		{
			$color = '#'.substr(rand()*16777215, 0, 6);
			$s = null;
			$s = new scatter_line( $color, 3 );
			$def = new hollow_dot();
			$def->size(3)->halo_size(2)->tooltip($k.' #val# Euro');
			$s->set_default_dot_style( $def );
			$v = array();
			$x = 0.0;
			$y = 0;
			foreach ($val as $key => $value)
			{
				if($tmp < round($value))
					$tmp = round($value);

				$v[] = new scatter_value( $key, $value );
			}
			$s->set_values( $v );
			$s->set_key( $k, 10 );
			$this->chart->add_element( $s );		
		}

		$y = new x_axis();
		$y->set_range( 0, $tmp );
		$y->offset(false)->steps(10);
		$this->chart->add_y_axis( $y );
	}
	
	function setTitle($title)
	{
		$this->_title = $title;
	}
	
	function setValues($data, $yLegend = 'Valore in EURO', $xLegend = 'Giorni del Mese') {}
	
	function getChart()
	{
		return $this->chart;
	}
}
?>