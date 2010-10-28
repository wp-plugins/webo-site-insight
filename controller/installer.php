<?php
/**
 * File from WEBO Site InSight, WEBO Software (http://www.webogroup.com/)
 *
 **/
 
function SiteInSightGetLanguage()
{
	return 'en';
}

$lang = SiteInSightGetLanguage();

$basePath = dirname(realpath(dirname(__FILE__))) . '/';
include_once($basePath . 'libs/php/class.database.php');

$DB = new SiteInSightDB();

$DB->createMainTable();
$DB->createMainWidgetTable();
$DB->insertOption('lang', $lang);
$DB->insertOption('basePath', $basePath);
$DB->insertOption('siteURL', $url);
$DB->insertOption('backScriptURL', $backscript);
$DB->insertOption('adminURL', $adminUrl);

?>
