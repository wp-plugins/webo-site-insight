<?php
/**
 * Widget RAM Usage for WEBO Site InSight, WEBO Software (http://www.webogroup.com/)
 *
 **/

class SiteInSightWidgetRAMUsage {
	var $friendlyName = 'RAM Usage';
	var $group = 1;

	function SiteInSightWidgetRAMUsage() {

	}
	
	function onActivate($WSI) {
		return array(
			'dataStructure' => array(
				'ram' => array(
					'type' => 'int',
					'key' => true
				)
			)
		);
	}

	function onView($WSI) {
		$data = $WSI->getWidgetData();
		$average = 0;
		$total = 0;
		foreach ($data as $k => $v) {
			$average += $v['ram'];
			$total++;
		}
		$average /= ($total ? $total : 1);
		$average = round(100 * $average / 1024 / 1024) / 100 . ' Mb';
		return array('short' => $average, 'detailed' => $average);
	}

	function onBeforeEnd($WSI, $content) {
		$WSI->storeWidgetData(array('ram' => @memory_get_usage()));
		return $content;
	}

}

?>
