<?php
/* Smarty version 3.1.30, created on 2018-11-02 10:39:10
  from "/opt/kuink-dev/kuink-bridge-moodle/theme/adminlte/ui/control/form/Select.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5bdc294e6eb310_89278990',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'c42b9bb355ac15a14861e2f9941e541ab1da2a8c' => 
    array (
      0 => '/opt/kuink-dev/kuink-bridge-moodle/theme/adminlte/ui/control/form/Select.tpl',
      1 => 1538043398,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5bdc294e6eb310_89278990 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_block_translate')) require_once '/opt/kuink-dev/kuink-core/lib/tools/smarty/plugins/block.translate.php';
$_smarty_tpl->_assignInScope('fieldAttrs', $_smarty_tpl->tpl_vars['field']->value['attributes']);
?>

<div class="controls">
	<?php echo '<script'; ?>
 type="text/javascript">
		/**
		* Action in javascript to submit this select
		**/
		function submit_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
(){
			<?php $_smarty_tpl->_assignInScope('urlSuff', "&action=".((string)$_smarty_tpl->tpl_vars['fieldAttrs']->value['action']));
?>
			<?php if ($_smarty_tpl->tpl_vars['fieldAttrs']->value['event'] != '') {?>
				<?php $_smarty_tpl->_assignInScope('urlSuff', "&event=".((string)$_smarty_tpl->tpl_vars['fieldAttrs']->value['event']));
?>
			<?php }?>

			result = setFormAction_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
('<?php echo html_entity_decode($_smarty_tpl->tpl_vars['form']->value["baseUrl"]);
echo $_smarty_tpl->tpl_vars['urlSuff']->value;?>
','', '', '', false);
			$("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").submit();
		}
	<?php echo '</script'; ?>
>

	<?php if ($_smarty_tpl->tpl_vars['fieldAttrs']->value['searchable'] == "dynamic") {?>
		

			<?php $_smarty_tpl->_assignInScope('datasourceParams', explode(",",$_smarty_tpl->tpl_vars['fieldAttrs']->value['datasource-params']));
?>

      <?php $_smarty_tpl->_assignInScope('width', "100%");
?>

	<?php echo '<script'; ?>
 type="text/javascript">
					jQuery(document).ready(function() {
					var kuinkFunctionRaw = "<?php echo $_smarty_tpl->tpl_vars['fieldAttrs']->value['datasource'];?>
";
					var kuinkFunction = kuinkFunctionRaw.replace("call:", "");

	$("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
 #<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
").select2({
		placeholder: '<?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('translate', array('app'=>"framework"));
$_block_repeat1=true;
echo smarty_block_translate(array('app'=>"framework"), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>
search<?php $_block_repeat1=false;
echo smarty_block_translate(array('app'=>"framework"), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
',
		allowClear: true,
		ajax: {
			url: "<?php echo $_smarty_tpl->tpl_vars['_apiCompleteUrl']->value;?>
"+kuinkFunction,
			dataType: 'json',
			delay: 250,
		quietMillis: 500,
			data: function (term, page) {
				return {
					<?php echo $_smarty_tpl->tpl_vars['datasourceParams']->value[0];?>
: term
					<?php if (isset($_smarty_tpl->tpl_vars['datasourceParams']->value[1])) {?>
					,pagesize: 10,
					<?php echo $_smarty_tpl->tpl_vars['datasourceParams']->value[1];?>
: page-1
					<?php }?>
				};
			},
			results: function (data, page) {
				// parse the results into the format expected by Select2.
				// since we are using custom formatting functions we do not need to
				// alter the remote JSON data
				var results = [];
        var dataToProcess = data;

        if (typeof data.records != 'undefined')
          dataToProcess = data.records;

				 $.each(dataToProcess, function(index, item){
				 var bindresults = "<?php echo $_smarty_tpl->tpl_vars['fieldAttrs']->value['bindresults'];?>
";
				 var fields  = bindresults.split(',');
				 for (var i=0; i<fields.length; i++)
						fields[i] = item[fields[i].trim()];

						results.push({
							id: item.<?php echo $_smarty_tpl->tpl_vars['fieldAttrs']->value['bindid'];?>
,
							text: item.<?php echo $_smarty_tpl->tpl_vars['fieldAttrs']->value['bindvalue'];?>

							<?php if ($_smarty_tpl->tpl_vars['fieldAttrs']->value['bindimage']) {?>
							,image: item.<?php echo $_smarty_tpl->tpl_vars['fieldAttrs']->value['bindimage'];?>

							<?php }?>
							<?php if ($_smarty_tpl->tpl_vars['fieldAttrs']->value['bindresults']) {?>
							,results: fields
							<?php }?>
						});
					});
					var more = (page * 10) < data.total; // whether or not there are more results available
				return {
					results: results
					<?php if (isset($_smarty_tpl->tpl_vars['datasourceParams']->value[1])) {?>
					, more: more
					<?php }?>
				};
			},
			cache: true
		},
		minimumInputLength: 3,
		formatResult: formatData_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
,
		initSelection: initSelection_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
,
		width: "<?php echo $_smarty_tpl->tpl_vars['width']->value;?>
"
	});
					});

		function formatData_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
 (data) {
			var markup = '<div class="clearfix">';

		if (data.image) {
			<?php if (($_smarty_tpl->tpl_vars['_imageUrl']->value == '')) {?>
				<?php $_smarty_tpl->_assignInScope('imageSrc', 'stream.php?type=photo&guid=');
?>
			<?php } else { ?>
				<?php $_smarty_tpl->_assignInScope('imageSrc', $_smarty_tpl->tpl_vars['_photoUrl']->value);
?>
			<?php }?>

			markup += '<div class="">' +
			'<img src="<?php echo $_smarty_tpl->tpl_vars['imageSrc']->value;?>
' + data.image + <?php if (($_smarty_tpl->tpl_vars['_imageUrl']->value != '')) {?>'.jpg' + <?php }?>'" style="max-width: 10%" />' +
			'</div>';

		}

		if (data.results)
		{
				markup += '<div class="">' + '<div class="clearfix">';
				var arrayLength = data.results.length;
				for (var i=0; i<arrayLength; i++)
					markup += '<div class="">' + data.results[i] + '</div>';

				markup +='</div></div></div>';
		}
			else
			if (data.text)
				markup +=
				'<div class="">' +
				'<div class="clearfix">' +
				'<div class="">' + data.text + '</div>' +
				'</div></div></div>';

			return markup;
		}

				function initSelection_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
(element, callback)
				{
					var kuinkFunctionRaw = "<?php echo $_smarty_tpl->tpl_vars['fieldAttrs']->value['datasource-initial'];?>
";
					var kuinkFunction = kuinkFunctionRaw.replace("call:", "");
					var id = element.val();
					if(id !== "") {
					return $.getJSON("<?php echo $_smarty_tpl->tpl_vars['_apiCompleteUrl']->value;?>
"+kuinkFunction+"&<?php echo $_smarty_tpl->tpl_vars['fieldAttrs']->value['bindid'];?>
=" + element.val(), null, function(data) {
							return callback({ id:data.<?php echo $_smarty_tpl->tpl_vars['fieldAttrs']->value['bindid'];?>
, text: data.<?php echo $_smarty_tpl->tpl_vars['fieldAttrs']->value['bindvalue'];?>
});
					});
				}
			}

			<?php echo '</script'; ?>
>

      <?php $_smarty_tpl->_assignInScope('onchange', '');
?>
      <?php if ($_smarty_tpl->tpl_vars['fieldAttrs']->value['action'] != '') {?>
        <?php $_smarty_tpl->_assignInScope('onchange', "onchange=\"javascript: submit_".((string)$_smarty_tpl->tpl_vars['_guid']->value)."_".((string)$_smarty_tpl->tpl_vars['fieldID']->value)."();\"");
?>
      <?php }?>

			<input type="hidden" id="<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
" name="<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
" class="<?php echo $_smarty_tpl->tpl_vars['disabledClass']->value;?>
 select2 select2-offscreen input-<?php echo $_smarty_tpl->tpl_vars['inputSize']->value;?>
" <?php echo $_smarty_tpl->tpl_vars['disabledAttr']->value;?>
 <?php echo $_smarty_tpl->tpl_vars['onchange']->value;?>
 value="<?php echo $_smarty_tpl->tpl_vars['field']->value['value'];?>
" />
	 <?php } else { ?>

		<?php if ($_smarty_tpl->tpl_vars['fieldAttrs']->value['searchable'] == "true" || $_smarty_tpl->tpl_vars['fieldAttrs']->value['searchable'] == "static") {?>

		<!-- only run this code if attribute is setted -->
		<?php echo '<script'; ?>
 type="text/javascript">
				jQuery(document).ready(function() {
						jQuery("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
 #<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
").select2(
								{
										width: "100%"
								}
						);
				});
		<?php echo '</script'; ?>
>

	
	<?php if ($_smarty_tpl->tpl_vars['fieldAttrs']->value['action'] != '') {?>
				<select id="<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
" name="<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
" class="<?php echo $_smarty_tpl->tpl_vars['disabledClass']->value;?>
 select2 select2-offscreen input-<?php echo $_smarty_tpl->tpl_vars['inputSize']->value;?>
 form-control" <?php echo $_smarty_tpl->tpl_vars['disabledAttr']->value;?>
 onchange="javascript: submit_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
();" <?php if ($_smarty_tpl->tpl_vars['fieldRequired']->value == true) {?>data-bv-notempty data-bv-notempty-message="<?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('translate', array('app'=>"framework"));
$_block_repeat1=true;
echo smarty_block_translate(array('app'=>"framework"), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>
requiredField<?php $_block_repeat1=false;
echo smarty_block_translate(array('app'=>"framework"), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
"<?php }?>>
	<?php } else { ?>
				<select id="<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
" name="<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
" class="<?php echo $_smarty_tpl->tpl_vars['disabledClass']->value;?>
 select2 select2-offscreen input-<?php echo $_smarty_tpl->tpl_vars['inputSize']->value;?>
 form-control" <?php echo $_smarty_tpl->tpl_vars['disabledAttr']->value;?>
 <?php if ($_smarty_tpl->tpl_vars['fieldRequired']->value == true) {?>data-bv-notempty data-bv-notempty-message="<?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('translate', array('app'=>"framework"));
$_block_repeat1=true;
echo smarty_block_translate(array('app'=>"framework"), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>
requiredField<?php $_block_repeat1=false;
echo smarty_block_translate(array('app'=>"framework"), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
"<?php }?>>
	<?php }?>

<?php } else { ?>


	
	<?php if ($_smarty_tpl->tpl_vars['fieldAttrs']->value['action'] != '') {?>
				<select id="<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
" name="<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
" class="<?php echo $_smarty_tpl->tpl_vars['disabledClass']->value;?>
 form-control" <?php echo $_smarty_tpl->tpl_vars['disabledAttr']->value;?>
 onchange="javascript: submit_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
();" <?php if ($_smarty_tpl->tpl_vars['fieldRequired']->value == true) {?>data-bv-notempty data-bv-notempty-message="<?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('translate', array('app'=>"framework"));
$_block_repeat1=true;
echo smarty_block_translate(array('app'=>"framework"), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>
requiredField<?php $_block_repeat1=false;
echo smarty_block_translate(array('app'=>"framework"), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
"<?php }?>>
	<?php } else { ?>
				<select id="<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
" name="<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
" class="<?php echo $_smarty_tpl->tpl_vars['disabledClass']->value;?>
 form-control" <?php echo $_smarty_tpl->tpl_vars['disabledAttr']->value;?>
 <?php if ($_smarty_tpl->tpl_vars['fieldRequired']->value == true) {?>data-bv-notempty data-bv-notempty-message="<?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('translate', array('app'=>"framework"));
$_block_repeat1=true;
echo smarty_block_translate(array('app'=>"framework"), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>
requiredField<?php $_block_repeat1=false;
echo smarty_block_translate(array('app'=>"framework"), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
"<?php }?>>
	<?php }?>

<?php }?>

<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['field']->value['options'], 'optionLabel', false, 'optionValue');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['optionValue']->value => $_smarty_tpl->tpl_vars['optionLabel']->value) {
if (isset($_smarty_tpl->tpl_vars['field']->value['value']) && $_smarty_tpl->tpl_vars['field']->value['value'] != '' && $_smarty_tpl->tpl_vars['field']->value['value'] == $_smarty_tpl->tpl_vars['optionValue']->value) {?>
	<option value="<?php echo $_smarty_tpl->tpl_vars['optionValue']->value;?>
" selected><?php echo $_smarty_tpl->tpl_vars['optionLabel']->value;?>
</option>
<?php } else { ?>
	<option value="<?php echo $_smarty_tpl->tpl_vars['optionValue']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['optionLabel']->value;?>
</option>
<?php }
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

</select>
<?php }?>


</div>
<?php }
}
