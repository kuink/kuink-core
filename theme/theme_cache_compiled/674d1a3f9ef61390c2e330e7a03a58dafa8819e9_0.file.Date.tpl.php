<?php
/* Smarty version 3.1.30, created on 2018-11-02 12:17:08
  from "/opt/kuink-dev/kuink-bridge-moodle/theme/adminlte/ui/control/form/Date.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5bdc40449e83b0_36064846',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '674d1a3f9ef61390c2e330e7a03a58dafa8819e9' => 
    array (
      0 => '/opt/kuink-dev/kuink-bridge-moodle/theme/adminlte/ui/control/form/Date.tpl',
      1 => 1522249140,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5bdc40449e83b0_36064846 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_block_translate')) require_once '/opt/kuink-dev/kuink-core/lib/tools/smarty/plugins/block.translate.php';
?>
<div class="form-control" style="border: none;padding: 0;">
	<input type="hidden" name="<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
" id="<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
" value="<?php echo $_smarty_tpl->tpl_vars['field']->value['value'];?>
"/>
	<div class='input-group date' id='datetimepicker__<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
' data-date-format="YYYY/MM/DD">
		<input type='text' class="form-control <?php echo $_smarty_tpl->tpl_vars['disabledClass']->value;?>
 _kuink_notSubmit"  <?php echo $_smarty_tpl->tpl_vars['disabledAttr']->value;?>
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
		<?php }?> />	
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
				pickDate: true, 		// disables the date picker
				pickTime: false, 		// disables the time picker
				useMinutes: false, 	// disables the minutes picker
				useSeconds: false, 	// disables the seconds picker
				useCurrent: false, 	// when true, picker will set the value to the current date/time
				minuteStepping: 1, 	// set the minute stepping
				showToday: true, 		// shows the today indicator
				language: 'pt', 		// sets language locale
				defaultDate: "", 		// sets a default date, accepts js dates, strings and moment objects
				disabledDates: [], 	// an array of dates that cannot be selected
				enabledDates: [], 	// an array of dates that can be selected
				icons: {
					time: 'glyphicon glyphicon-time',
					date: 'glyphicon glyphicon-calendar',
					up:   'glyphicon glyphicon-chevron-up',
					down: 'glyphicon glyphicon-chevron-down'
				},
				useStrict: false, 			//use "strict" when validating dates
				sideBySide: true, 			//show the date and time picker side by side
				daysOfWeekDisabled: [] 	//for example use daysOfWeekDisabled: [0,6] to disable weekends
			});
			$('#datetimepicker__<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
').on("dp.change",function (e) {
				kuink_updateHiddenDate('<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
',<?php echo $_smarty_tpl->tpl_vars['personTimeZoneOffset']->value;?>
);
			});

			$('#<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
_visible').change(function(){
				kuink_updateHiddenDate('<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
',<?php echo $_smarty_tpl->tpl_vars['personTimeZoneOffset']->value;?>
);
				$('#datetimepicker__<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
').data("DateTimePicker").setDate($('#<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
_visible').val());
			});

			$('#<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
_visible').inputmask('yyyy/mm/dd');
		<?php }?>
		<?php if ($_smarty_tpl->tpl_vars['field']->value['attributes']['now'] == 'true' || $_smarty_tpl->tpl_vars['field']->value['value']) {?>
			kuink_updateVisibleDate('<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
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
