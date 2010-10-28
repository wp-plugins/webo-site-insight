<?php
/**
 * Widget Load Speed for WEBO Site InSight, WEBO Software (http://www.webogroup.com/)
 *
 **/

class SiteInSightWidgetloadSpeed {
	var $friendlyName = 'Load Speed';
	var $group = 2;


	function SiteInSightWidgetloadSpeed() {

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
		$average /= $total ? $total : 1;
		if ($average > 100000) {
			$average = round($average / 1000) . ' s';
		} elseif ($average > 10000) {
			$average = round($average / 100) / 10 . ' s';
		} elseif ($average > 1000) {
			$average = round($average / 10) / 100 . ' s';
		} else {
			$average = round($average) . ' ms';
		}
		return array(
			'short' => $average,
			'detailed' => $average
		);
	}

	function onBeforeEnd($WSI, $content) {
		$script = '<script type="text/javascript">(function(){window.__WSI_loadSpeed=new Date();window[/*@cc_on !@*/0?"attachEvent":"addEventListener"](/*@cc_on "on"+@*/"load",function(){new Image(1,1).src="'.
			$WSI->options['backScriptURL'] .
			'?widgetName=loadSpeed&time="+((new Date())-__WSI_loadSpeed)+"&random="+Math.random()},false)})()</script>';
		if (($head = strpos($content, '<head'))) {
			$head = strpos($content, '>', $head) + 1;
			$content = substr_replace($content, $script, $head, 0);
		} else {
			$content = preg_replace("@(<head[^>]*>)@is", "$1" . $script, $content);
		}
		return $content;
	}

}

?>
