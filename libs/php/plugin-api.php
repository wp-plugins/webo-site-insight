<?php
/**
 * File from WEBO Site InSight, WEBO Software (http://www.webogroup.com/)
 *
 **/

class SiteInSightPluginAPI
{
	private $DB;
	private $pluginName;
	public $options;

	public function SiteInSightPluginAPI($DB, $pluginName, $options)
	{
		$this->DB = $DB;
		$this->pluginName = $pluginName;
		$this->options = $options;
	}

	public function storePluginSettings($settings)
	{
		return $this->DB->storePluginSettings($this->pluginName, $settings);
	}

	public function storePluginData($fields)
	{
		return $this->DB->storePluginData($this->pluginName, $fields);
	}

	public function getPluginData($condition = array(), $orderField = 'date', $orderDirection = 0)
	{
		return $this->DB->getPluginData($this->pluginName, $condition, $orderField, $orderDirection);
	}
}
