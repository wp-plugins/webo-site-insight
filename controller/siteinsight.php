<?php
/**
 * File from WEBO Site InSight, WEBO Software (http://www.webogroup.com/)
 *
 **/

class SiteInSightController
{

	private $widgetsSettings = array();
	private $widgetsInstances = array();
	private $activeWidgetsExists = true;
	private $widgetEvents = array('onStart', 'onBeforeEnd');
	private $widgetsPrefix = 'SiteInSightWidget';
	public $widgetAPI;
	private $DB;
	private $options = array();
	private $version = 0;

	public function SiteInSightController()
	{
		include_once(dirname(realpath(dirname(__FILE__))) . '/libs/php/class.database.php');
		$this->DB = new SiteInSightDB();
		$this->options = $this->DB->getOptions();
		$this->widgetsSettings = $this->DB->getWidgets();
		$this->version = @file_get_contents($this->options['basePath'] . 'version');
		include_once($this->options['basePath'] . 'libs/php/widget-api.php');
		if (empty($this->widgetsSettings))
		{
			$this->activeWidgetsExists = false;
		}
		foreach ($this->widgetsSettings as $widgetName => $widgetSettings)
		{
			if (is_file($this->options['basePath'] . "widgets/$widgetName/$widgetName.php"))
			{
				include_once($this->options['basePath'] . "widgets/$widgetName/$widgetName.php");
				$widgetName = $this->widgetsPrefix . $widgetName;
				if (class_exists($widgetName))
				{
					$this->widgetInstances[$widgetName] =  new $widgetName($widgetSettings);
				}
			}
		}
	}

	public function widgetEventHandler($eventType, $data = false)
	{
		if ($this->activeWidgetsExists && in_array($eventType, $this->widgetEvents))
		{
			if (empty($this->widgetsSettings))
			{
				$this->widgetsSettings = $this->DB->getWidgets();
				if (empty($this->widgetsSettings))
				{
					$this->activeWidgetsExists = false;
					return;
				}
			}
			foreach ($this->widgetsSettings as $widgetName => $widgetSettings)
			{
				$widgetClass = $this->widgetsPrefix . $widgetName;
				if (class_exists($widgetClass) && method_exists($widgetClass, $eventType))
				{
					if (empty($this->widgetInstances[$widgetName]) || !($this->widgetInstances[$widgetName] instanceof $widgetClass))
					{
						$this->widgetInstances[$widgetName] = new $widgetClass($this->widgetsSettings[$widgetName]);
					}
					if (empty($this->widgetAPI[$widgetName]))
					{
						$this->widgetAPI[$widgetName] = new SiteInSightWidgetAPI($this->DB, $widgetName, $this->options);
					}
					if ($data !== false)
					{
						$data = $this->widgetInstances[$widgetName]->$eventType($this->widgetAPI[$widgetName], $data);
					}
					else
					{
						$this->widgetInstances[$widgetName]->$eventType($this->widgetAPI[$widgetName]);
					}
				}
			}
			if ($data !== false)
			{
				return $data;
			}
		}
		if ($data !== false)
		{
			return $data;
		}
	}
}

?>
