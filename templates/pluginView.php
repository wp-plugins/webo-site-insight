<?php
/**
 * File from WEBO Site InSight, WEBO Software (http://www.webogroup.com/)
 *
 **/
?><h1>WEBO Site InSight - plugin view <?php echo $this->variables['currentPlugin']; ?></h1>
<?php
if (!empty($this->variables['error']))
{
	include($this->options['basePath'] . "templates/error-message.php");
}

if (!empty($this->variables['message']))
{
	include($this->options['basePath'] . "templates/message.php");
}

if (!empty($this->variables['pluginViews']['full']))
{
	foreach ($this->variables['pluginViews']['full'] as $name => $value)
	{
		?><p>
		<?php
		echo $value;
		?></p><p>
		<?php
		echo $name;
		?></p>-----------------------------------------------<br/>
		<?php
	}
}

if (!empty($this->variables['pluginViews']['small']))
{
	foreach ($this->variables['pluginViews']['small'] as $name => $value)
	{
		?><p>
		<?php
		echo $value;
		?></p><p>
		<?php
		echo $name;
		?></p>
		<?php
	}
}
?>
