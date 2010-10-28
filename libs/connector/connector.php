<?php
class SiteInSightCMSDB
{
	private $wpdb;

	public function SiteInSightCMSDB()
	{
		global $wpdb;
		if(!$wpdb)
		{
			$WP_path = realpath(dirname(__FILE__) . '/../../../../../');
			$settings = file_get_contents($WP_path . '/wp-config.php');
			$settings = preg_replace('/^<\?php/', '', $settings);
			$settings = preg_replace('/require_once[^\n]*\n/', '', $settings);
			eval($settings);
			include_once($WP_path . '/wp-includes/load.php');
			include_once($WP_path . '/wp-includes/wp-db.php');
			$this->wpdb = $wpdb;
			$this->wpdb->prefix = $table_prefix;
		}
		else
		{
			$this->wpdb = $wpdb;
		}
	}

	public function getResult($query, $num_prefixes = 1)
	{
		$prefixArray = array();
		for ($i = 0; $i < $num_prefixes; $i++)
		{
			$prefixArray[] = $this->wpdb->prefix;
		}
		$query = vsprintf($query, $prefixArray);

		return $this->wpdb->get_results($query, 'ARRAY_A');
	}

	public function executeQuery($query, $num_prefixes = 1)
	{
		$prefixArray = array();
		for ($i = 0; $i < $num_prefixes; $i++)
		{
			$prefixArray[] = $this->wpdb->prefix;
		}
		$query = vsprintf($query, $prefixArray);
		return $this->wpdb->query($query);
	}
}
