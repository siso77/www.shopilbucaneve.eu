<?php
include_once(APP_ROOT.'/libs/ext/open_flash_chart/php-ofc-library/open-flash-chart.php');
class OpenChartImplement
{
	var $title;
	var $bar_stack;
	var $y;
	var $x;
	var $x_labels;
	var $tooltip;
	var $chart;
	
	function OpenChartImplement($title, $description)
	{
		$animation_1	= isset($_GET['animation_1'])?$_GET['animation_1']:'pop';
		$delay_1    	= isset($_GET['delay_1'])?$_GET['delay_1']:0.5;
		$cascade_1		= isset($_GET['cascade_1'])?$_GET['cascade_1']:1;
		
		$this->title =  new title( $title );
		$this->title->set_style( "{font-size: 12px; color: #000000; text-align: center;}" );
		
		$this->bar_stack = new bar_stack();
		$this->bar_stack->set_colours( array( '#C4D318', '#50284A' ) );
		$this->bar_stack->set_keys(array(
		        new bar_stack_key( '#C4D318', $description, 13 ),
		//        new bar_stack_key( '#50284A', 'Valore a confronto', 13 )
		        ));
		$this->bar_stack->set_on_show(new bar_on_show($animation_1, $cascade_1, $delay_1));
		        
		$this->y = new y_axis();
		$this->x = new x_axis();
		$this->x_labels = new x_axis_labels();
		$this->x_labels->rotate(0);
		
		$this->tooltip = new tooltip();
		$this->tooltip->set_hover();
		
		$this->chart = new open_flash_chart();
		$this->chart->set_title( $this->title );
		$this->chart->set_bg_colour( '#FFFFFF' );
		$this->chart->add_element( $this->bar_stack );
		$this->chart->set_x_axis( $this->x );
		$this->chart->add_y_axis( $this->y );
		$this->chart->set_tooltip( $this->tooltip );
	}
	
	function setTitle($title)
	{
		$this->_title = $title;
	}
	
	function setValues($data, $yLegend = 'Valore in EURO', $xLegend = 'Giorni del Mese')
	{
		$tmp = 0;
		$index = 0;
		foreach ($data as $key => $value)
		{
			if($tmp < $value)
				$tmp = $value+100;

			$this->bar_stack->append_stack(array(round($value)));
			$x_label = $index;
			$index++;
		}

		$this->y->set_range( 0, $tmp );

		$this->x->offset(false)->steps(1);
		$this->y->set_steps(10);
		$this->x_labels->set_labels($x_label);
		$this->x->set_labels($this->x_labels);

		
		$y_legend = new y_legend( $yLegend );
		$y_legend->set_style( '{font-size: 22px; color: #778877}' );
		$this->chart->set_y_legend( $y_legend );

		$x_legend = new x_legend( $xLegend );
		$x_legend->set_style( '{font-size: 22px; color: #778877}' );
		$this->chart->set_x_legend( $x_legend );
	}
	
	function getChart()
	{
		return $this->chart;
	}
}
?>