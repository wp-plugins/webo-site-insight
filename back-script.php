<?php
/**
 * File from WEBO Site InSight, WEBO Software (http://www.webogroup.com/)
 *
 **/

if (isset($_GET['widgetName']))
{
	include_once(realpath(dirname(__FILE__)) . "/libs/php/class.database.php");
	$db = new SiteInSightDB();
	$fields = array();
	foreach($_GET as $key => $value)
	{
		if ($key != 'widgetName' && $key != 'random')
		{
			$fields[$key] = $value;
		}
	}
	$db->storeWidgetData($_GET['widgetName'], $fields);
}
?>
