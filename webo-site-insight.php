<?php
// ==============================================================================================
// Licensed under the GPLv3 license
// ==============================================================================================
// @author     WEBO Software (http://www.webogroup.com/)
// @version    0.2.0
// @copyright  Copyright &copy; 2009-2010 WEBO Software, All Rights Reserved
// ==============================================================================================
/*
Plugin Name: WEBO Site InSight
Plugin URI: http://code.google.com/p/webo-site-insight/
Description: WEBO Site InSight
Author: WEBO Software
Version: 0.2.0
Author URI: http://www.webogroup.com/
*/
	if (!function_exists('site_insight_add_css')) {
		function site_insight_add_css() {
			$version = @file_get_contents(realpath(ABSPATH) . '/wp-content/plugins/webo-site-insight/version');
			echo '<link rel="stylesheet" type="text/css" href="../wp-content/plugins/webo-site-insight/libs/css/webo-site-insight.css?' . $version . '"/>';
		}
	}
	if (!function_exists('site_insight_add_menu')) {
/* general function to add all items to admin section */
		function site_insight_add_menu () {
			if ($_GET['page'] == 'site_insight_manager') {
				add_action('admin_print_styles','site_insight_add_css');
			}
			add_menu_page('WEBO Site InSight', 'WEBO Site InSight', 8, 'site_insight_manager', 'site_insight_manager', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACEklEQVQ4T3WST0iTcRjHf3nwtNOC/twW6kGvMXArYd1EN9pi4C5BJHRYSO80oqRAGsgGleDy1Nb8c8j0IpMVYjDqEhP6g77uUnpZHhL0IKRbyz3t8wuHbe4LDzw838/nfbcxpWqSzea8y8vZz/Pz7wrT0+kyw86NrpavJpNZt6RSH8xY7LWMj8+eOHQwsHVyMrm4Mzr6UqLRSVla+ihbW9tSLP7Ww86NDiaZTO3+95CpqUVzeHhCIpFJDTcKHQwsjpYXFt73GMZTGRwck3z+Z0P5KDCwOLgqFpv91N//WGZm0nVwqfRHDg/LdXdYHFxlGE8KgcAD2dj4IWtrm6JOuSrf8a0G4/G0HpJIvNEdDCwOrvL775XdbkMODgoavNw1IM5Lt/XbbRcCetg7HUHdEVgcXNXdPVB2uW7J/v6/B8zNZfSbjNBzsZ726GHnRkdgcXCVz3e3YLdfl1xuU5fFYknOnPVqIRp9pYedGx2BxcFVwWDkS3u7X8LhePVHevgoIefOX6t81KIedm5HgcXBVeHwix6bzS1tbT4xze8ayOe3qz8eYedGYFpargoOrv4v9PXdN63WK9LR4ZfV1W/SKHQwsDjVf+LIyITF6by509zcKRZLl4RCz2RlZV329n7pYedGB+Nw3NjFUcfDobf3jtnUZBelLp44dDB18vEMDY15PZ7Q19ZWb6HyxjLDzo2ulv8LN6Bqnkiu8fYAAAAASUVORK5CYII=');
		}
	}
/* add hook to admin panel and all styles */
	if (is_admin()) {
		add_action('admin_menu', 'site_insight_add_menu');
	}
	if (!function_exists('site_insight_manager'))
	{
		function site_insight_manager()
		{
			include_once(ABSPATH . 'wp-content/plugins/webo-site-insight/controller/admin.php');
			$action = !empty($_GET['WSI_ACTION']) ? $_GET['WSI_ACTION'] : 'none';
			$admin = new SiteInSightAdmin($action, false, $_GET);
		}
	}
	if (!function_exists('site_insight_shutdown'))
	{
		function site_insight_shutdown($content)
		{
			global $site_insight_controller;
			$content = $site_insight_controller->widgetEventHandler('onBeforeEnd', $content);
			return $content;
		}
	}
	if (!function_exists('site_insight_init'))
	{
		function site_insight_init()
		{
			global $site_insight_controller;
			global $wp_did_header;
			if (!isset($wp_did_header))
			{
				return;
			}
			include_once(ABSPATH . 'wp-content/plugins/webo-site-insight/controller/siteinsight.php');
			$site_insight_controller = new SiteInSightController();
			$site_insight_controller->widgetEventHandler('onStart');
			ob_start('site_insight_shutdown');
		}
	}
	add_action('plugins_loaded', 'site_insight_init');
	
	if (!function_exists('site_insight_activate'))
	{
		function site_insight_activate()
		{
			$url = get_bloginfo('url');
			$url_parts = explode('://', $url, 2);
			$url_parts = explode('/', $url_parts[1], 2);
			$url = $url_parts[1];
			if (substr($url, -1) != '/')
			{
				$url .= '/';
			}
			$adminUrl = $url . 'wp-admin/admin.php?page=site_insight_manager';
			$backscript = $url . 'wp-content/plugins/webo-site-insight/back-script.php';
			include_once(ABSPATH . 'wp-content/plugins/webo-site-insight/controller/installer.php');
		}
	}
	register_activation_hook(__FILE__, 'site_insight_activate');
	if (!function_exists('site_insight_deactivate')) {
/* main deactivation function */
		function site_insight_deactivate () {
			include_once(ABSPATH . 'wp-content/plugins/webo-site-insight/libs/php/class.database.php');
			$DB = new SiteInSightDB();
			$widgets = $DB->getWidgetsList(false);
			foreach($widgets as $name => $value)
			{
				$DB->deleteWidgetTable($name);
			}
			$DB->dropMainWidgetTable();
			$DB->dropMainTable();
		}
	}
	register_deactivation_hook(__FILE__, 'site_insight_deactivate');
?>
