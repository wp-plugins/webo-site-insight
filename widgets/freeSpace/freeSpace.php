<?php
/**
 * Widget Free Disk Space for WEBO Site InSight, WEBO Software (http://www.webogroup.com/)
 *
 **/

class SiteInSightWidgetfreeSpace {
	var $friendlyName = 'Free Disk Space';
	var $group = 1;

	function SiteInSightWidgetfreeSpace() {

	}

	function onView($WSI) {
		$freeSpace = disk_free_space(realpath(dirname(__FILE__)));
		$type = 'b';
		if ($freeSpace > 1024)
		{
			$freeSpace = $freeSpace / 1024;
			$type = 'Kb';
			if ($freeSpace > 1024)
			{
				$freeSpace = $freeSpace / 1024;
				$type = 'Mb';
				if ($freeSpace > 1024)
				{
					$freeSpace = $freeSpace / 1024;
					$type = 'Gb';
				}
			}
		}
		$freeSpace = round($freeSpace);
		return array(
			'short' => $freeSpace . ' ' . $type,
			'detailed' => $freeSpace . ' ' . $type
		);
	}

}

?>
