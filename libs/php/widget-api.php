<?php
/**
 * File from WEBO Site InSight, WEBO Software (http://www.webogroup.com/)
 *
 **/

class SiteInSightWidgetAPI
{
	private $DB;
	private $widgetName;
	public $options;

	public function SiteInSightWidgetAPI($DB, $widgetName, $options)
	{
		$this->DB = $DB;
		$this->widgetName = $widgetName;
		$this->options = $options;
	}

	public function storeWidgetSettings($settings)
	{
		return $this->DB->storeWidgetSettings($this->widgetName, $settings);
	}

	public function storeWidgetData($fields)
	{
		return $this->DB->storeWidgetData($this->widgetName, $fields);
	}

	public function getWidgetData($condition = array(), $orderField = 'date', $orderDirection = 0)
	{
		return $this->DB->getWidgetData($this->widgetName, $condition, $orderField, $orderDirection);
	}
}
