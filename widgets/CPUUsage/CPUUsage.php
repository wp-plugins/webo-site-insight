<?php
/**
 * Widget CPU Usage for WEBO Site InSight, WEBO Software (http://www.webogroup.com/)
 *
 **/

class SiteInSightWidgetCPUUsage {

	var $friendlyName = 'CPU Usage';
	var $group = 1;

	var $time;
	function SiteInSightWidgetCPUUsage() {
		
	}

	function onStart ($WSI) {
		$this->time = time() + microtime();
	}

	function onActivate($WSI) {
		return array(
			'dataStructure' => array(
				'time' => array(
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
			$average += $v['time'];
			$total++;
		}
		$average /= ($total ? $total : 1);
		$average = round($average) . ' ms';
		return array('short' => $average, 'detailed' => $average);
	}

	function onBeforeEnd($WSI, $content) {
		$WSI->storeWidgetData(array('time' => 1000 * (time() + microtime() - $this->time)));
		return $content;
	}

}

?>
