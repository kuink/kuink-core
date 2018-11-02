<?php
/* Smarty version 3.1.30, created on 2018-11-02 10:53:40
  from "/opt/kuink-dev/kuink-bridge-moodle/theme/adminlte/ui/control/form/DateTime.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5bdc2cb4c38e27_42997880',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '8cccd7dc5d3226933c29438030c813f29ef714a6' => 
    array (
      0 => '/opt/kuink-dev/kuink-bridge-moodle/theme/adminlte/ui/control/form/DateTime.tpl',
      1 => 1522249111,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5bdc2cb4c38e27_42997880 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_block_translate')) require_once '/opt/kuink-dev/kuink-core/lib/tools/smarty/plugins/block.translate.php';
?>
<div class="form-control" style="border: none;padding: 0;">
	<input type="hidden" name="<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
" id="<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
" value="<?php echo $_smarty_tpl->tpl_vars['field']->value['value'];?>
"/>
	<div class='input-group date' id='datetimepicker__<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
' data-date-format="YYYY/MM/DD HH:mm">
		<input type='text' class="form-control <?php echo $_smarty_tpl->tpl_vars['disabledClass']->value;?>
 _kuink_notSubmit" <?php echo $_smarty_tpl->tpl_vars['disabledAttr']->value;?>
 id="<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
_visible" name="<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
_visible"
			<?php if ($_smarty_tpl->tpl_vars['field']->value['attributes']['required'] == 'true') {?>
				data-bv-notempty data-bv-notempty-message="<?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('translate', array('app'=>"framework"));
$_block_repeat1=true;
echo smarty_block_translate(array('app'=>"framework"), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>
requiredField<?php $_block_repeat1=false;
echo smarty_block_translate(array('app'=>"framework"), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
"
			<?php }?>/>
			<span class="input-group-addon">
				<span class="fa fa-calendar"></span>
			</span>
			<span class="input-group-addon">
				<span class="glyphicon glyphicon-globe" data-toggle="<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
Tooltip" data-placement="bottom" title="<?php echo $_smarty_tpl->tpl_vars['personTimeZone']->value;?>
 GMT<?php if ($_smarty_tpl->tpl_vars['personTimeZoneOffset']->value < 0) {?>-<?php } else { ?>+<?php }
echo $_smarty_tpl->tpl_vars['personTimeZoneOffset']->value/3600;?>
"></span>
			</span>
	</div>
</div>

<?php echo '<script'; ?>
 type="text/javascript">

	$(function () {
	  $('[data-toggle="<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
Tooltip"]').tooltip()
	})

	$(function () {
		<?php if ($_smarty_tpl->tpl_vars['field']->value['attributes']['disabled'] != 'true') {?>
			$('#datetimepicker__<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
').datetimepicker({
				pickDate: true, 				// en/disables the date picker
				pickTime: true, 				// en/disables the time picker
				useMinutes: true, 			// en/disables the minutes picker
				useSeconds: false, 			// en/disables the seconds picker
				useCurrent: false, 			// when true, picker will set the value to the current date/time
				minuteStepping: 1, 			// set the minute stepping
				showTodayButton: false, // shows the today indicator
				language: 'pt', 				// sets language locale
				icons: {
					time: 'glyphicon glyphicon-time',
					//date: 'fa fa-calendar',
					today: 'glyphicon glyphicon-screenshot',
					up: 'glyphicon glyphicon-chevron-up',
					down: 'glyphicon glyphicon-chevron-down'
				},
				useStrict: false, 	// use "strict" when validating dates
				sideBySide: false, 	// show the date and time picker side by side
			});
			$('#datetimepicker__<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
').on("dp.change",function (e) {
				kuink_updateHiddenDateTime('<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
',<?php echo $_smarty_tpl->tpl_vars['personTimeZoneOffset']->value;?>
);
			});

			$('#<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
_visible').change(function(){
				kuink_updateHiddenDateTime('<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
',<?php echo $_smarty_tpl->tpl_vars['personTimeZoneOffset']->value;?>
);
				$('#datetimepicker__<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
').data("DateTimePicker").setDate($('#<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
_visible').val());
			});

		<?php }?>

		<?php if ($_smarty_tpl->tpl_vars['field']->value['attributes']['now'] == 'true' || $_smarty_tpl->tpl_vars['field']->value['value']) {?>
			kuink_updateVisibleDateTime('<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
',<?php echo $_smarty_tpl->tpl_vars['personTimeZoneOffset']->value;?>
);
		<?php }?>

		<?php if ($_smarty_tpl->tpl_vars['field']->value['attributes']['disabled'] != 'true') {?>
			$('#datetimepicker__<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
').data("DateTimePicker").setDate($('#<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
_visible').val());
		<?php }?>

	});

<?php echo '</script'; ?>
>
<?php }
}
