<?php
/*
File from WEBO Site InSight, WEBO Software (http://www.webogroup.com/)
Interface for database. Sends requests and recieves results using CMS database engine.
Contains different methods that product core uses (create table for widget, load results, save options to database, etc).
*/

class SiteInSightDB
{
	private $getWidgetsQuery = "SELECT name, settings FROM %sSiteInSight_widgets";//vars: dbprefix
	private $getOptionsQuery = "SELECT name, value FROM %sSiteInSight";//vars: dbprefix
	private $getWidgetsListQuery = "SELECT name, active, settings FROM %sSiteInSight_widgets";//vars: dbprefix
	private $widgetTableCreateQuery = "CREATE TABLE %sSiteInSight_%s (`id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY, `date` BIGINT NOT NULL, KEY `date` (`date`) %s)";//vars: dbprefix, widget name, fields list
	private $widgetTableDropQuery = "DROP TABLE IF EXISTS %sSiteInSight_%s";//vars: dbprefiix, widget name
	private $widgetSettingsStoreQuery = "UPDATE %sSiteInSight_widgets SET settings = '%s' WHERE name = '%s'";//vars: dbprefiix, serialized settings array, widget name
	private $widgetAddQuery = "INSERT INTO %sSiteInSight_widgets (name, settings) VALUES ('%s', '%s')";//vars: dbprefiix, widget name, serialized settings array
	private $widgetDeleteQuery = "DELETE FROM %sSiteInSight_widgets WHERE name = '%s'";//vars: dbprefiix, widget name
	private $widgetDataStoreQuery = "INSERT INTO %sSiteInSight_%s (date %s) VALUES ('%s' %s)";//vars: dbprefiix, widget name, fields list, current timestamp, values list
	private $widgetGetDataQuery = "SELECT * FROM %sSiteInSight_%s WHERE %s ORDER BY `%s` %s";//vars: dbprefix, widget name, WHERE condition, ORDER field, order direction
	private $updateOptionQuery = "UPDATE %sSiteInSight SET %s = '%s'";//vars: dbprefix, setting name, setting value
	private $insertOptionQuery = "INSERT INTO %sSiteInSight (name, value) VALUES ('%s', '%s')";//vars: dbprefix, setting name, setting value
	private $createMainTableQuery = "CREATE TABLE %sSiteInSight (`id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR (255), `value` VARCHAR(255))";//vars: dbprefix
	private $dropMainTableQuery = "DROP TABLE IF EXISTS %sSiteInSight";//vars: dbprefix
	private $dropMainWidgetTableQuery = "DROP TABLE IF EXISTS %sSiteInSight_widgets";//vars: dbprefix
	private $createMainWidgetTableQuery = "CREATE TABLE %sSiteInSight_widgets (`id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR (255), `settings` TEXT, `active` TINYINT NOT NULL DEFAULT 0, `ordering` INT NOT NULL DEFAULT 1)";//vars: dbprefix
	private $deactivateWidgetQuery = "UPDATE %sSiteInSight_widgets SET active = 0 WHERE name = '%s'";//vars:dbprefix, widget name
	private $activateWidgetQuery = "UPDATE %sSiteInSight_widgets SET active = 1 WHERE name = '%s'";//vars:dbprefix, widget name
	private $CMSDB;

	public function SiteInSightDB()
	{
		include_once(dirname(realpath(dirname(__FILE__))) . '/connector/connector.php');
		$this->CMSDB = new SiteInSightCMSDB();
	}

	/*
	gets core SiteInSight options from datebase
	*/
	public function getOptions()
	{
		$resultArray = $this->CMSDB->getResult($this->getOptionsQuery);
		$options = array();
		if (!is_array($resultArray))
		{
			return $options;
		}
		foreach ($resultArray as $key => $option)
		{
			$options[$option['name']] = $option['value'];
		}
		return $options;
	}

	/*
	updates option value
	*/
	public function updateOption($name, $value)
	{
		$name = mysql_escape_string($name);
		$value = mysql_escape_string($value);
		return $this->CMSDB->executeQuery(sprintf($this->updateOptionQuery, '%s', $this->escapeInput($name), $this->escapeInput($value)));
	}

	/*
	inserts new option, used during installation only
	*/
	public function insertOption($name, $value)
	{
		$name = mysql_escape_string($name);
		$value = mysql_escape_string($value);
		return $this->CMSDB->executeQuery(sprintf($this->insertOptionQuery, '%s', $this->escapeInput($name), $this->escapeInput($value)));
	}

	public function activateWidget($widgetName)
	{
		return $this->CMSDB->executeQuery(sprintf($this->activateWidgetQuery, '%s', $this->escapeInput(mysql_escape_string($widgetName))));
	}

	public function deactivateWidget($widgetName)
	{
		return $this->CMSDB->executeQuery(sprintf($this->deactivateWidgetQuery, '%s', $this->escapeInput(mysql_escape_string($widgetName))));
	}

	/*
	get widgets list from database
	*/
	public function getWidgets($activeOnly = true)
	{
		$sql = $this->getWidgetsQuery;
		if ($activeOnly)
		{
			$sql .= ' WHERE active = 1';
		}
		$sql .= ' ORDER BY ordering';
		$resultArray = $this->CMSDB->getResult($sql);
		$widgets = array();
		if (!is_array($resultArray))
		{
			return $widgets;
		}
		foreach ($resultArray as $key => $widget)
		{
			$widgets[$widget['name']] = unserialize($widget['settings']);
		}
		return $widgets;
	}

	public function getWidgetsList()
	{
		$resultArray = $this->CMSDB->getResult($this->getWidgetsListQuery);
		$widgets = array();
		if (!is_array($resultArray))
		{
			return $widgets;
		}
		foreach ($resultArray as $key => $widget)
		{
			$widgets[$widget['name']] = array('active' => $widget['active'], 'settings' => $widget['settings']);
		}
		return $widgets;
	}

	/*
	stores widget's settings
	*/
	public function storeWidgetSettings($widgetName, $settings)
	{
		return $this->CMSDB->executeQuery(sprintf($this->widgetSettingsStoreQuery, '%s', $this->escapeInput(mysql_escape_string(serialize($settings))), $this->escapeInput(mysql_escape_string($widgetName))));
	}
	
	/*
	stores widget's data
	*/
	public function storeWidgetData($widgetName, $fields)
	{
		if (!is_array($fields))
		{
			return false;
		}
		$fieldsList = '';
		$valuesList = '';
		foreach ($fields as $field => $value)
		{
			$fieldsList .= ', `' . mysql_escape_string($field) . '`';
			$valuesList .= ", '" . mysql_escape_string($value) . "'";
		}
		return $this->CMSDB->executeQuery(sprintf($this->widgetDataStoreQuery, '%s', $this->escapeInput($widgetName), $this->escapeInput($fieldsList), gmmktime(), $this->escapeInput($valuesList)));
	}

	/*
	gets widget's data
	*/
	public function getWidgetData($widgetName, $condition = array(), $orderField = 'date', $orderDirection = 0)
	{
		$orderDirection = (empty($orderDirection)) ? 'ASC' : 'DESC';
		$condition = $this->getCondition($condition);
		if (empty($condition))
		{
			$oneDayAgo = time() - 86400;
			$condition = "date > $oneDayAgo";
		}
		return $this->CMSDB->getResult(sprintf($this->widgetGetDataQuery, '%s', $this->escapeInput($widgetName), $this->escapeInput($condition), $this->escapeInput(mysql_escape_string($orderField)), $this->escapeInput($orderDirection)));
	}

	private function getCondition($conditions = array())
	{
		$conditionLine = '(';
		if (is_array($conditions) && !empty($conditions))
		{
			$i = 0;
			foreach ($conditions as $cond)
			{
				if (!empty($cond['type']) && isset($cond['value']) && (!empty($cond['field']) || ($cond['type'] == 'complex')))
				{
					if ($i != 0)
					{
						$conditionLine .= (empty($cond['and'])) ? ' OR ' : ' AND ';
					}
					$i++;
					if (!empty($cond['field']) && ($cond['type'] != 'complex'))
					{
						$conditionLine .= '`' . mysql_escape_string($cond['field']) . '`';
					}
					switch ($cond['type'])
					{
						//not equal
						case "ne":
							$conditionLine .= " != '" . mysql_escape_string($cond['value']) . "'";
							break;
						//lower than
						case "lt":
							$conditionLine .= " < '" . mysql_escape_string($cond['value']) . "'";
							break;
						//greater than
						case "gt":
							$conditionLine .= " > '" . mysql_escape_string($cond['value']) . "'";
							break;
						//like
						case "like":
							$conditionLine .= " LIKE '" . mysql_escape_string($cond['value']) . "'";
							break;
						//complex condition - need recursion
						case "complex":
							$conditionLine .= $this->getCondition($cond['value']);
							break;
						//equal - default
						case "eq":
						default:
							$conditionLine .= " = '" . mysql_escape_string($cond['value']) . "'";
							break;
					}
				}
			}
		}
		else
		{
			return '';
		}
		$conditionLine .= ')';
		return $conditionLine;
	}

	/*
	creates table for widget data
	*/
	public function createWidgetTable($widgetName, $fields = array())
	{
		if (!is_array($fields))
		{
			return false;
		}
		$fieldList = '';
		foreach ($fields as $name => $params)
		{
			$name = mysql_escape_string($name);
			$fieldList .= ", `$name` ";
			if (!empty($params['type']))
			{
				$type = strtolower($params['type']);
				if ($type == 'int')
				{
					$fieldList .= 'INT NOT NULL DEFAULT 0';
				}
				elseif ($type == 'bigint')
				{
					$fieldList .= 'BIGINT NOT NULL DEFAULT 0';
				}
				elseif ($type = 'tinyint')
				{
					$fieldList .= 'TINYINT NOT NULL DEFAULT 0';
				}
				else
				{
					$fieldList .= "VARCHAR(255) NOT NULL DEFAULT ''";
				}
			}
			else
			{
				$fieldList .= "VARCHAR(255) NOT NULL DEFAULT ''";
			}
			if (!empty($params['key']))
			{
				$fieldList .= ", KEY `$name` (`$name`)";
			}
		}
		return $this->CMSDB->executeQuery(sprintf($this->widgetTableCreateQuery, '%s', $this->escapeInput($widgetName), $this->escapeInput($fieldList)));
	}
	
	/*
	deletes table for widget data
	*/
	public function deleteWidgetTable($widgetName)
	{
		return $this->CMSDB->executeQuery(sprintf($this->widgetTableDropQuery, '%s', $this->escapeInput($widgetName)));
	}
	
	/*
	executes query, true or false returned
	*/
	public function executeQuery($query, $num_prefixes = 1)
	{
		return $this->CMSDB->executeQuery($query, $num_prefixes);
	}
	
	/*
	executes query, result array returned
	*/
	public function getResult($query, $num_prefixes = 1)
	{
		return $this->CMSDB->getResult($query, $num_prefixes);
	}
	
	/*
	inserts widget record to widgets table
	*/
	public function addWidget($widgetName, $settings = array())
	{
		return $this->CMSDB->executeQuery(sprintf($this->widgetAddQuery, '%s', $this->escapeInput(mysql_escape_string($widgetName)), $this->escapeInput(mysql_escape_string(serialize($settings)))));
	}

	/*
	deletes widget record from widgets table
	*/
	public function deleteWidget($widgetName)
	{
		return $this->CMSDB->executeQuery(sprintf($this->widgetDeleteQuery, '%s', $this->escapeInput(mysql_escape_string($widgetName))));
	}

	public function createMainTable()
	{
		return $this->CMSDB->executeQuery($this->createMainTableQuery);
	}

	private function escapeInput($str)
	{
		return str_replace('%', '%%', $str);
	}

	public function dropMainTable()
	{
		return $this->CMSDB->executeQuery($this->dropMainTableQuery);
	}

	public function createMainWidgetTable()
	{
		return $this->CMSDB->executeQuery($this->createMainWidgetTableQuery);
	}

	public function dropMainWidgetTable()
	{
		return $this->CMSDB->executeQuery($this->dropMainWidgetTableQuery);
	}
}

?>
