<?php
/**
 * File from WEBO Site InSight, WEBO Software (http://www.webogroup.com/)
 *
 **/

class SiteInSightAdmin
{
	private $widgetsSettings = array();
	private $widgetInstances = array();
	private $widgetEvents = array('onView', 'onActivate', 'onDeactivate');
	private $currentWidget = false;
	private $widgetsPrefix = 'SiteInSightWidget';
	private $DB;
	private $widgetAPI = array();
	private $addedOptions;
	private $activeWidgetsEsixts;
	private $options = array();
	private $skipRender = false;
	private $pageOptions = array('main', 'plginView');
	private $actions = array('none', 'widgetView', 'widgetUninstall', 'widgetActivate', 'widgetDeactivate');
	private $templateVariables = array();
	private $allWidgets = array();
	private $widgetGroups = array();
	private $version = 0;

	/*
	gets settings from DB, creates widget instances and render admin page if needed
	*/
	public function SiteInSightAdmin($action = 'none', $skipRender = false, $options = array())
	{
		include_once(dirname(realpath(dirname(__FILE__))) . '/libs/php/class.database.php');
		$this->DB = new SiteInSightDB();
		$this->options = $this->DB->getOptions();
		$this->version = @file_get_contents($this->options['basePath'] . 'version');
		include_once($this->options['basePath'] . 'libs/php/renderer.php');
		include_once($this->options['basePath'] . 'libs/php/widget-api.php');
		include_once($this->options['basePath'] . 'libs/php/lang/' . $this->options['lang'] . '.php');
		$this->widgetGroups = array('name' => array('0' => WSI_GROUP_NETWORK, '1' => WSI_GROUP_SERVER, '2' => WSI_GROUP_CLIENT),
							'help' => array('0' => WSI_GROUP_NETWORK_HELP, '1' => WSI_GROUP_SERVER_HELP, '2' => WSI_GROUP_CLIENT_HELP));
		$this->addedOptions = $options;
		$this->allWidgets = $this->DB->getWidgetsList();
		$dh = opendir($this->options['basePath'] . 'widgets');
		while ($filename = readdir($dh))
		{
			if (($filename != '.') && ($filename != '..') && (is_dir($this->options['basePath'] . "widgets/$filename")) && !isset($this->allWidgets[$filename]))
			{
				$this->widgetInstall($filename);
				$this->allWidgets[$filename] = array('active' => 0, 'settings' => array());
			}
		}
		closedir($dh);
		foreach ($this->allWidgets as $name => $info)
		{
			if (!empty($info['active']))
			{
				$this->widgetsSettings[$name] = $info['settings'];
			}
			elseif (!is_file($this->options['basePath'] . "widgets/$name/$name.php"))
			{
				$this->removeBadWidget($name);
			}
		}
		if (empty($this->widgetsSettings))
		{
			$this->activeWidgetsEsixts = false;
		}
		foreach ($this->widgetsSettings as $widgetName => $widgetSettings)
		{
			if (is_file($this->options['basePath'] . "widgets/$widgetName/$widgetName.php"))
			{
				include_once($this->options['basePath'] . "widgets/$widgetName/$widgetName.php");
				$widgetClass = $this->widgetsPrefix . $widgetName;
				if (class_exists($widgetClass))
				{
					$this->widgetInstances[$widgetName] =  new $widgetClass($widgetSettings);
				}
				else
				{
					$this->removeBadWidget($widgetName);
				}
			}
			else
			{
				$this->removeBadWidget($widgetName);
			}
		}
		$this->skipRender = $skipRender;
		$action = in_array($action, $this->actions) ? $action : 'none';
		$this->$action();
	}
	
	public function widgetInstall($widgetName)
	{
		if (!empty($this->widgetInstances[$widgetName]))
		{
			$this->setError(WSI_WIDGET_INSTALL_FAILED);
			return;
		}
		if (is_file($this->options['basePath'] . "widgets/$widgetName/$widgetName.php"))
		{
			include_once($this->options['basePath'] . "widgets/$widgetName/$widgetName.php");
			$widgetClass = $this->widgetsPrefix . $widgetName;
			if (class_exists($widgetClass))
			{
				$settings = array();
				$this->DB->addWidget($widgetName, $settings);
			}
			else
			{
				$this->setError(WSI_WIDGET_CLASS_NOT_FOUND);
			}
		}
		else
		{
			$this->setError(WSI_WIDGET_FILE_NOT_FOUND);
		}
	}

	public function widgetActivate()
	{
		if (empty($this->addedOptions['widget']))
		{
			$this->setError(WSI_WIDGET_ACTIVATE_FAILED);
			return;
		}
		$widgetName = $this->addedOptions['widget'];
		if (!empty($this->widgetInstances[$widgetName]))
		{
			$this->setError(WSI_WIDGET_ACTIVATE_FAILED);
			return;
		}
		if (is_file($this->options['basePath'] . "widgets/$widgetName/$widgetName.php"))
		{
			include_once($this->options['basePath'] . "widgets/$widgetName/$widgetName.php");
			$widgetClass = $this->widgetsPrefix . $widgetName;
			if (class_exists($widgetClass))
			{
				$this->widgetInstances[$widgetName] =  new $widgetClass(array());
				$this->allWidgets[$widgetName]['active'] = 1;
				$this->allWidgets[$widgetName]['settings'] = array();
				$this->widgetSettings[$widgetName] = array();
				if (method_exists($widgetClass, 'onActivate'))
				{
					if (empty($this->widgetAPI[$widgetName]))
					{
						$this->widgetAPI[$widgetName] = new SiteInSightWidgetAPI($this->DB, $widgetName, $this->options);
					}
					$widgetInfo = $this->widgetInstances[$widgetName]->onActivate($this->widgetAPI[$widgetName]);
					if ($widgetInfo === false)
					{
						$this->setError(WSI_WIDGET_ACTIVATE_FAILED);
						return;
					}
					if (!empty($widgetInfo['settings']) && is_array($widgetInfo['settings']))
					{
						$this->allWidgets[$widgetName]['settings'] = $widgetInfo['settings'];
						$this->widgetSettings[$widgetName] = $widgetInfo['settings'];
						$this->DB->storeWidgetSettings($widgetName, $widgetInfo['settings']);
					}
					if (!empty($widgetInfo['dataStructure']) && is_array($widgetInfo['dataStructure']))
					{
						$this->DB->createWidgetTable($widgetName, $widgetInfo['dataStructure']);
					}
				}
				$this->setPage(WSI_WIDGET_ACTIVATE_SUCCESS);
				$this->DB->activateWidget($widgetName);
			}
			else
			{
				$this->setError(WSI_WIDGET_ACTIVATE_FAILED);
			}
		}
		else
		{
			$this->setError(WSI_WIDGET_ACTIVATE_FAILED);
		}
	}

	public function widgetDeactivate()
	{
		if (empty($this->addedOptions['widget']))
		{
			$this->setError(WSI_WIDGET_DEACTIVATE_FAILED);
			return;
		}
		$widgetName = $this->addedOptions['widget'];
		if (empty($this->widgetInstances[$widgetName]))
		{
			$this->setError(WSI_WIDGET_DEACTIVATE_FAILED);
			return false;
		}
		$widgetClass = $this->widgetsPrefix . $widgetName;
		if (method_exists($widgetClass, 'onDeactivate'))
		{
			$result = $this->widgetInstances[$widgetName]->onDeactivate($this->widgetAPI[$widgetName]);
			if ($result === false)
			{
				$this->setError(WSI_WIDGET_DEACTIVATE_FAILED);
				return;
			}
		}
		$this->DB->deactivateWidget($widgetName);
		unset($this->widgetInstances[$widgetName]);
		unset($this->widgetSettings[$widgetName]);
		$this->allWidgets[$widgetName]['active'] = 0;
		$this->setPage(WSI_WIDGET_DEACTIVATE_SUCCESS);		
	}

	public function widgetUninstall($dropData = false)
	{
		if (empty($this->addedOptions['widget']))
		{
			$this->setError(WSI_WIDGET_UNINSTALL_FAILED);
			return;
		}
		$widgetName = $this->addedOptions['widget'];
		if (!empty($this->widgetInstances[$widgetName]))
		{
			$this->setError(WSI_WIDGET_UNINSTALL_FAILED);
			return false;
		}
		$widgetClass = $this->widgetsPrefix . $widgetName;
		if (method_exists($widgetClass, 'onDeactivate'))
		{
			$result = $this->widgetInstances[$widgetName]->onDeactivate($this->widgetAPI[$widgetName]);
			if ($result === false)
			{
				$this->setError(WSI_WIDGET_UNINSTALL_FAILED);
				return;
			}
		}

		if (is_dir($this->options['basePath'] . "widgets/$widgetName/"))
		{
			$this->recurse_rm($this->options['basePath'] . "widgets/$widgetName/");
		}
		if (is_dir($this->options['basePath'] . "widgets/$widgetName/"))
		{
			$this->setError(WSI_WIDGET_UNINSTALL_FAILED);
			return;
		}
		if ($dropData !== false)
		{
			$this->DB->deleteWidgetTable($widgetName);
		}
		unset($this->allWidgets[$widgetName]);
		$this->DB->deleteWidget($widgetName);
		$this->setPage(WSI_WIDGET_UNINSTALL_SUCCESS);
	}

	public function none()
	{
		$this->renderPage();
	}

	public function widgetView()
	{
		if (!empty($this->addedOptions['widget']))
		{
			$this->currentWidget = $this->addedOptions['widget'];
		}
		$this->renderPage('widgetView');
	}

	private function renderPage($page = 'main')
	{
		if (!$this->skipRender)
		{
			$widgetViews = array();
			foreach ($this->allWidgets as $widgetName => $info)
			{
				$widgetInstance = '';
				$widgetClass = $this->widgetsPrefix . $widgetName;
				if (!empty($this->widgetInstances[$widgetName]))
				{
					$widgetInstance = $this->widgetInstances[$widgetName];
					if (method_exists($widgetClass, 'onView'))
					{
						if (empty($this->widgetAPI[$widgetName]))
						{
							$this->widgetAPI[$widgetName] = new SiteInSightWidgetAPI($this->DB, $widgetName, $this->options);
						}
						$widgetViews[$widgetName] = $this->widgetInstances[$widgetName]->onView($this->widgetAPI[$widgetName]);
						if (!isset($widgetViews[$widgetName]['detailed']))
						{
							$widgetViews[$widgetName]['detailed'] = '';
						}
						if (!isset($widgetViews[$widgetName]['short']))
						{
							$widgetViews[$widgetName]['short'] = '';
						}
					}
				}
				else
				{
					if (!class_exists($widgetClass))
					{
						include_once($this->options['basePath'] . "widgets/$widgetName/$widgetName.php");
					}
					if (class_exists($widgetClass))
					{
						$widgetInstance = new $widgetClass();
					}
				}
				$widgetViews[$widgetName]['active'] = $info['active'];
				if (!empty($widgetInstance))
				{
					$widgetViews[$widgetName]['friendlyName'] = $widgetInstance->friendlyName;
					$widgetViews[$widgetName]['group'] = $widgetInstance->group;
					if (!isset($widgetViews[$widgetName]['group']) || ($widgetViews[$widgetName]['group'] < 0) || ($widgetViews[$widgetName]['group'] > 2))
					{
						$widgetViews[$widgetName]['group'] = 0;
					}
					$widgetViews[$widgetName]['groupName'] = $this->widgetGroups['name'][$widgetViews[$widgetName]['group']];
					$widgetViews[$widgetName]['groupHelp'] = $this->widgetGroups['help'][$widgetViews[$widgetName]['group']];
				}
			}
			$this->templateVariables['widgets'] = $widgetViews;
			$renderer = new SiteInSightRenderer($this->templateVariables, $this->options, $this->version);
			$renderer->render($page);
		}
		else
		{
			return;
		}
	}

	private function recurse_rm($path)
	{
		if (is_dir($path))
		{
			if (substr($path, strlen($path) - 1) != '/')
			{
				$path .= '/';
			}
			$dh = @opendir($path);
			while (($file = @readdir($dh)) !== false)
			{
				if (($file == '.') || ($file == '..'))
				{
					//do nothing
				}
				elseif (is_dir($path . $file))
				{
					recurse_rm($path . $file);
				}
				else
				{
					@unlink($path . $file);
				}
			}
			closedir($dh);
			@rmdir($path);
		}
		else
		{
			@unlink($path);
		}
	}

	private function setPage($message = '')
	{
		if (!empty($message))
		{
			$this->templateVariables['message'] = $message;
		}
		$this->renderPage();
	}

	private function setError($message)
	{
		if (!empty($message))
		{
			$this->templateVariables['error'] = $message;
		}
		$this->renderPage();
	}

	private function removeBadWidget ($widgetName)
	{
		if (empty($widgetName))
		{
			return;
		}
		$this->DB->deactivateWidget($widgetName);
		unset($this->widgetSettings[$widgetName]);
		$this->allWidgets[$widgetName]['active'] = 0;

		if (is_dir($this->options['basePath'] . "widgets/$widgetName/"))
		{
			$this->recurse_rm($this->options['basePath'] . "widgets/$widgetName/");
		}

		if (is_dir($this->options['basePath'] . "widgets/$widgetName/"))
		{
			$this->setError(WSI_WIDGET_UNINSTALL_FAILED);
			return;
		}
		if ($dropData !== false)
		{
			$this->DB->deleteWidgetTable($widgetName);
		}
		unset($this->allWidgets[$widgetName]);
		$this->DB->deleteWidget($widgetName);
	}
}
