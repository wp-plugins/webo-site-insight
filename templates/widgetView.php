<?php
/**
 * File from WEBO Site InSight, WEBO Software (http://www.webogroup.com/)
 *
 **/
?><h1>WEBO Site InSight - widget view <?php echo $this->variables['currentWidget']; ?></h1>
<?php
if (!empty($this->variables['error']))
{
	include($this->options['basePath'] . "templates/error-message.php");
}

if (!empty($this->variables['message']))
{
	include($this->options['basePath'] . "templates/message.php");
}

if (!empty($this->variables['widgetViews']['full']))
{
	foreach ($this->variables['widgetViews']['full'] as $name => $value)
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

if (!empty($this->variables['widgetViews']['small']))
{
	foreach ($this->variables['widgetViews']['small'] as $name => $value)
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
