<?php
/**
 * File from WEBO Site InSight, WEBO Software (http://www.webogroup.com/)
 *
 **/
 ?>
 
<div class="wsi-wrapper"><div class="wsi-header"><a href=""><img class="wsi-header-logo-img" src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" alt="WEBO Site Insight" /></a><span class="wsi-header-version">v<?php echo $this->version; ?></span><span class="wsi-header-language"></span></div>

<?php
if (!empty($this->variables['error'])) {
	include($this->options['basePath'] . "templates/message-error.php");
}

if (!empty($this->variables['message'])) { 
	include($this->options['basePath'] . "templates/message-ok.php");
}

if (!empty($this->variables['widgets'])) {

$activated_Widgets = array();
$deactivated_Widgets = array();

foreach ($this->variables['widgets'] as $name => $widgetInfo) {
	if (!empty($widgetInfo['active'])) {
		$activated_Widgets[$name] = $widgetInfo;
	}
	else {
		$deactivated_Widgets[$name] = $widgetInfo;
	} 
}
?>

<?php
if (!empty($activated_Widgets)) {
?>
<div class="wsi-group wsi-group-panel wsi-group-expanded">
	<div class="wsi-group-title">
		<a class="wsi-group-title-link" href="#">
			<span class="wsi-group-title-link-icon"></span><?php echo WSI_GROUP_MY; ?><span class="wsi-group-title-link-arrow"></span>
		</a>
	</div>
	
	<div class="wsi-widgets-short">
<?php
$once=1;
foreach ($activated_Widgets as $name => $widgetInfo) {
?>
		<div class="wsi-widget <?php echo $name; if($once) { $once = 0 ; ?> wsi-widget-active<?php }?>">
			<span class="wsi-widget-title"><?php echo $widgetInfo['friendlyName']; ?></span>
			<span class="wsi-widget-content-short"><?php echo $widgetInfo['short']; ?></span>
		</div>
<?php
}
?>
	</div><!--
	--><div class="wsi-widgets-detailed">
<?php
$once=1;
foreach ($activated_Widgets as $name => $widgetInfo) {
?>
		<div class="wsi-widget <?php echo $name; if($once) { $once = 0;?> wsi-widget-active<?php }?>">
			<div class="wsi-widget-title">
				<?php echo $widgetInfo['friendlyName']; ?>
				<span class="wsi-widget-controls"><a href="#" class="wsi-widget-controls-edit wsi-widget-controls-button"></a><a href="<?php echo $this->options['adminURL']; ?>&amp;WSI_ACTION=widgetDeactivate&amp;widget=<?php echo $name; ?>" class="wsi-widget-controls-hide wsi-widget-controls-button" title="<?php echo WSI_DEACTIVATE_WIDGET . ' ' . $widgetInfo['friendlyName']; ?>"></a></span>
			</div>
			<div class="wsi-widget-content-detailed">
				<p><?php echo $widgetInfo['detailed']; ?></p>
			</div>
		</div>
<?php
}
?>
	</div>
	<span class="wsi-group-line-top"></span>
</div>
<?php
}
?>

<?php
if (!empty($deactivated_Widgets)) {
?>
<div class="wsi-group wsi-group-widgets wsi-group-expanded">
	<div class="wsi-group-title"><a class="wsi-group-title-link" href="#"><span class="wsi-group-title-link-icon"></span><?php echo WSI_ADD_WIDGETS; ?><span class="wsi-group-title-link-arrow"></span></a></div>
<?php	
foreach ($deactivated_Widgets as $name => $widgetInfo) {
?>
		<div class="wsi-widget <?php echo $name; ?>">
			<span class="wsi-widget-title"><?php echo $widgetInfo['friendlyName']; ?>
				<span class="wsi-widget-controls">
					<a href="<?php echo $this->options['adminURL']; ?>&amp;WSI_ACTION=widgetUninstall&amp;widget=<?php echo $name; ?>" class="wsi-widget-controls-delete wsi-widget-controls-button" title="<?php echo WSI_UNINSTALL_WIDGET . ' ' . $widgetInfo['friendlyName']; ?>"></a>
				</span>
				<span class="wsi-widget-group-title"><?php echo $widgetInfo['groupName']; ?></span>
				<a href="<?php echo $this->options['adminURL']; ?>&amp;WSI_ACTION=widgetActivate&amp;widget=<?php echo $name; ?>" title="<?php echo WSI_ACTIVATE_WIDGET . ' ' . $widgetInfo['friendlyName']; ?>" class="wsi-widget-controls-add"></a>
			</span>
		</div>
<?php
	}
?>
	<span class="wsi-group-line-top"></span>
	<span class="wsi-clear"></span>
</div>
<?php
}

}
?>

<div class="wsi-footer"><a href="http://www.webogroup.com/"><img class="wsi-footer-logo-img" src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" alt="WEBO Software" /></a><span> &copy; 2010 <a class="wsi-footer-link" href="http://code.google.com/p/webo-site-insight/">WEBO Site Insight</a></span></div>
</div>

<script src="<?php echo dirname($this->options['backScriptURL']); ?>/libs/js/yass.min.js?<?php echo $version; ?>" type="text/javascript"></script>
<script src="<?php echo dirname($this->options['backScriptURL']); ?>/libs/js/webo-site-insight.js?<?php echo $version; ?>" type="text/javascript"></script>
