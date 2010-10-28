<?php
/**
 * File from WEBO Site InSight, WEBO Software (http://www.webogroup.com/)
 *
 **/

class SiteInSightRenderer
{
	private $variables = array();
	private $options = array();
	private $version = 0;

	public function SiteInSightRenderer($variables, $options, $version)
	{
		$this->variables = $variables;
		$this->options = $options;
		$this->version = $version;
	}
	
	public function render($page)
	{
		if (!empty($page) && is_file($this->options['basePath'] . "templates/$page.php"))
		{
			include($this->options['basePath'] . "templates/$page.php");
		}
	}
}
?>
