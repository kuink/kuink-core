<?php
/* Smarty version 3.1.30, created on 2018-11-02 10:39:10
  from "/opt/kuink-dev/kuink-bridge-moodle/theme/adminlte/ui/control/Form.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5bdc294e63c838_67772140',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '71effc27d35ac7fd272a6ee012a955d7317ea896' => 
    array (
      0 => '/opt/kuink-dev/kuink-bridge-moodle/theme/adminlte/ui/control/Form.tpl',
      1 => 1538044454,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:./Form_HandleField.tpl' => 1,
    'file:./Form_HandleActionButtons.tpl' => 1,
  ),
),false)) {
function content_5bdc294e63c838_67772140 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_block_translate')) require_once '/opt/kuink-dev/kuink-core/lib/tools/smarty/plugins/block.translate.php';
?>


<?php echo '<script'; ?>
 type="text/javascript">
	//Add this control to the manager
	__kuink.controlAdd('<?php echo $_smarty_tpl->tpl_vars['_idContext']->value;?>
', '<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
');

	function setFormAction_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
(actionUrl, confirmMessage, buttonType, buttonId, clearFormData){
		//set the action in the form
		$("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").attr('action', actionUrl);
		confirmMessage = __kuink.controlGetKey('<?php echo $_smarty_tpl->tpl_vars['_idContext']->value;?>
','<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
', '<?php echo $_smarty_tpl->tpl_vars['buttonAttrs']->value['id'];?>
', 'confirm', 'false');

		// get all buttons and store their states
		//Bootstrap will disable all buttons, so we need the previous state to restore if the 
		formButtons = $("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").find("button").parent().children();
		$(formButtons).each(function() {
			if (this.id != '') {
				__kuink.controlAddKey('<?php echo $_smarty_tpl->tpl_vars['_idContext']->value;?>
', '<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
', this.id, 'disabled', $(this).attr('disabled'));
			}
		});

		__kuink.controlAddKey('<?php echo $_smarty_tpl->tpl_vars['_idContext']->value;?>
', '<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
', '_buttonPressed', 'type', buttonType);
		__kuink.controlAddKey('<?php echo $_smarty_tpl->tpl_vars['_idContext']->value;?>
', '<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
', '_buttonPressed', 'id', buttonId);
		
		$("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").attr('kuink-data-button-pressed', buttonType);
		$("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").attr('kuink-data-button-pressed-id', buttonId);
		$("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").attr('kuink-data-actionGroup-clear', (clearFormData == undefined)?false :  clearFormData);
	};

	$(document).ready(function() {
			$("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").bootstrapValidator({
				onError: function(e) {
					var buttonType = $("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").attr('kuink-data-button-pressed');
					var url = $("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").attr('action');
					var buttonId = __kuink.controlGetKey('<?php echo $_smarty_tpl->tpl_vars['_idContext']->value;?>
', '<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
', '_buttonPressed', 'id');
					
					if(buttonType=='cancel') {
						$("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").kuinkSubmit({
							'url' 						: url + '&modal=embed',
							'idContext'			: '<?php echo $_smarty_tpl->tpl_vars['_idContext']->value;?>
',
							'method' 					: 'get',
							'button_id' 			: buttonId,
							'formGuid' 			  : '<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
'
						});					
					}
				},
			onSuccess: function(e) {
				// Call kuink submit center
				var url = $("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").attr('action');
				var buttonId = __kuink.controlGetKey('<?php echo $_smarty_tpl->tpl_vars['_idContext']->value;?>
', '<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
', '_buttonPressed', 'id');
				
				var clearFormData = $("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").attr('kuink-data-actionGroup-clear');

				if (confirm!='' && confirm!='false')
					if (confirm!='true')
						confirmMessage = confirm;
					else
						confirm = true;
					
				$("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").kuinkSubmit({
					'url' 						: url + '&modal=embed',
					'idContext'				: '<?php echo $_smarty_tpl->tpl_vars['_idContext']->value;?>
',
					'method' 					: 'post',
					//'target'					: '<?php echo $_smarty_tpl->tpl_vars['_idContext']->value;?>
_wrapper',
					'processData'			: false,
					'contentType'			: false,
					'button_id' 			: buttonId,
					'formGuid' 			  : '<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
'
				});

				e.preventDefault();
			}
		});

		$("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").submit(function(e){
			return false;
		});

	});

	function formActionField_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
(confirm, confirm_message, location, button_id) {

		var notSubmit = $("._kuink_notSubmit");
			$.each(notSubmit, function(index, value) {
			value.disabled = 'true';
		});
		var formData = new FormData(document.querySelector("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
"));


		//Call kuink submit center
		$("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").kuinkSubmit({
			'url' 						: location + '&modal=embed',
			'id_context' 			: '<?php echo $_smarty_tpl->tpl_vars['_idContext']->value;?>
',
			'method' 					: 'post',
			'processData'			: false,
			'contentType'			: false,
			'data' 						: formData,
			'confirm'					: confirm,
			'confirm_message'	: confirm_message,
			'button_id' 			: button_id
		});
	}

	//Applying rules to the form
	var rules_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
 = <?php echo $_smarty_tpl->tpl_vars['jsonDynamicRules']->value;?>
;

	/** generic apply rules **/
	function applyRule_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
(field, attr, attrValue) {
		if (attr=='disabled') {
			if (attrValue == 'true')
				$("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").find("#"+field).attr('disabled','disabled');
			else
				$("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").find("#"+field).removeAttr('disabled');
		} else if (attr=='visible') {
			if (attrValue == 'true') {
				$("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").find("#"+field).removeAttr('disabled');
				$("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").find("#"+field+"CG").fadeIn('slow');
			} else {
				$("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").find("#"+field+"CG").fadeOut();
				$("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").find("#"+field).attr('disabled','disabled');
			}
		} else if (attr=='value') {
			if(attrValue != '' && attrValue != null) {
				if(attrValue == '__kuink_clear'){
					$("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").find("#"+field).val('');
				}
				else {
					$("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").find("#"+field).val(attrValue);
				}
			}
		}
		return;
	}


	/**
	 * Handle <?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
 rules
	 **/
	function handle_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_rules( changedId ){

		$(rules_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
).each(function(i,obj){
					if (obj.datasource!=''){
						var apiUrl = obj.datasource;
						var apiParams = obj.datasourceparams;

						/** Verify if the modified parameter is in this datasource params **/
						var makeCall = false;
						if (Object.keys(apiParams).length == 0){
							makeCall = true;
						}
						for(var param in apiParams) {
							if (param==changedId && makeCall==false){
								makeCall=true;
							}
						}
						if (makeCall == true){

									for(var param in apiParams) {
										var paramValue = $("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").find("#"+param).val();
										param = '$'+param;
										while (apiUrl.search('\\'+param)!=-1){
											apiUrl = apiUrl.replace(param,paramValue);
										}
									}
									
									//TODO:Ugly hack find the source of this problem... probably in Form.php
									apiUrl = apiUrl.replace('mod/kuink/','');
									apiUrl = apiUrl.replace('mod/neon/','');
									console.log(apiUrl);
									$.ajax({
										url: apiUrl,
										type: 'get',
										success: function (data, status) {

											$("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").find("#"+obj.field).find('option').remove().end();
											$("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").find("#"+obj.field).find('option').remove().end();
											$("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").find("#"+obj.field).append('<option></option>');
											$.each(data, function (index, value) {
											$("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").find("#"+obj.field).append('<option value="'+value[obj.bindid]+'">'+value[obj.bindvalue]+'</option>');

											if (data.length == 1) {
												$("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").find("#"+obj.field).prop('selectedIndex', 1);
											}

											if (obj.field != changedId)
												handle_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_rules(obj.field);

									});

									},
									error: function (xhr, err) {
										console.debug("Desc: " + err);
									}
							});

						}

					/** continue with condition rules **/
					} else if (eval(obj.condition)){
							applyRule_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
(obj.field, obj.attr, obj.value_true);
					}else{
							applyRule_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
(obj.field, obj.attr, obj.value_false);
					}
			});
	 }

	 /** make magic things happen **/
	 $(document).ready(function(){
		//when form is loaded
		handle_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_rules();

		//when  form is changed
		$("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").change(function(event){
			 var changedId = event.target.id;
				handle_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_rules(changedId);
		 });
	 });

		//Place a copy of default button in first place in a position outside of visible screen
		$(function(){
				$("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").each(function () {
						var thisform = $(this);
						thisform.prepend(thisform.find('button.default').clone().css({
								position: 'absolute',
								left: '-999px',
								top: '-999px',
								height: 0,
								width: 0
						}));
				});
		});

	function processClientAction_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
(clientAction) {
		switch(clientAction)
		{
		case 'print':
			window.print();
			break;
		default:
			alert('Client side action ' + clientAction + ' not defined.');
		}
	}
<?php echo '</script'; ?>
>

<?php if ((count($_smarty_tpl->tpl_vars['buttonActions']->value) > 0) || ($_smarty_tpl->tpl_vars['form']->value['title'] != '') || (count($_smarty_tpl->tpl_vars['fields']->value) > 0)) {?>
<div class="box">
	
	<?php if ($_smarty_tpl->tpl_vars['form']->value['title'] != '') {?>
	<div class="box-header">
		<i class="fa fa-th-large">&nbsp;</i>
		<h3 class="box-title"><?php echo $_smarty_tpl->tpl_vars['form']->value['title'];?>
</h3>
	</div>
	<?php }?>
	<?php if (!isset($_smarty_tpl->tpl_vars['hasRequiredFields']->value)) {?>
		<?php $_smarty_tpl->_assignInScope('hasRequiredFields', false);
?>
	<?php }?>

	<!-- /.box-header -->
	<!-- form start -->
	<form role="form" id="<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
" class="kuink-control"
			action="<?php echo $_smarty_tpl->tpl_vars['form']->value['baseUrl'];?>
"
			method="post" enctype="multipart/form-data"
			data-bv-message="<?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('translate', array('app'=>"framework"));
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
			data-bv-feedbackicons-valid=""
			data-bv-feedbackicons-invalid=""
			data-bv-feedbackicons-validating=""
			data-bv-live="enabled"
			data-disable="false">
			<?php if (count($_smarty_tpl->tpl_vars['fields']->value) > 0) {?>
				<div class="box-body ">
					<?php if (($_smarty_tpl->tpl_vars['freeze']->value == '0') && ($_smarty_tpl->tpl_vars['buttonsPosition']->value == 'top' || $_smarty_tpl->tpl_vars['buttonsPosition']->value == 'both')) {?>
						<?php echo '<script'; ?>
>
							$(document).ready(function(){
								var html = $("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").find(".btn-group").html();
								$("#firstButtonGroup<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").html(html);
								$("#firstButtonGroup<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").addClass("btn-group");
							})
						<?php echo '</script'; ?>
>
						<div id="firstButtonGroup<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
" style="float: right;"></div>
					<?php }?>

				<?php $_smarty_tpl->_assignInScope('insideColumn', 0);
?>
				<?php $_smarty_tpl->_assignInScope('insideHeader', 0);
?>
				<?php $_smarty_tpl->_assignInScope('currentColumnGroup', 0);
?>
				<?php $_smarty_tpl->_assignInScope('insideTab', 0);
?>
				<?php $_smarty_tpl->_assignInScope('prevFieldAttrs', null);
?>
				<?php $_smarty_tpl->_assignInScope('fieldIndex', 0);
?> 
				<?php $_smarty_tpl->_assignInScope('fieldsIndexedArray', null);
?> 
				<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['fields']->value, 'field', false, 'fieldID');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['fieldID']->value => $_smarty_tpl->tpl_vars['field']->value) {
?> 
					<?php $_tmp_array = isset($_smarty_tpl->tpl_vars['fieldsIndexedArray']) ? $_smarty_tpl->tpl_vars['fieldsIndexedArray']->value : array();
if (!is_array($_tmp_array) || $_tmp_array instanceof ArrayAccess) {
settype($_tmp_array, 'array');
}
$_tmp_array[] = $_smarty_tpl->tpl_vars['field']->value['attributes'];
$_smarty_tpl->_assignInScope('fieldsIndexedArray', $_tmp_array);
?>
				<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

				<?php $_smarty_tpl->_assignInScope('tabIndex', -1);
?>
				<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['fields']->value, 'field', false, NULL, 'handleFieldForEach', array (
));
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['field']->value) {
?>
					<?php $_smarty_tpl->_assignInScope('fieldAttrs', $_smarty_tpl->tpl_vars['field']->value['attributes']);
?>
					<?php if ($_smarty_tpl->tpl_vars['fieldIndex']->value > 0) {?>
						<?php $_smarty_tpl->_assignInScope('prevFieldAttrs', $_smarty_tpl->tpl_vars['fieldsIndexedArray']->value[$_smarty_tpl->tpl_vars['fieldIndex']->value-1]);
?>
					<?php }?>
					<?php if ($_smarty_tpl->tpl_vars['fieldIndex']->value < count($_smarty_tpl->tpl_vars['fields']->value)) {?>
						<?php $_smarty_tpl->_assignInScope('nextFieldAttrs', $_smarty_tpl->tpl_vars['fieldsIndexedArray']->value[$_smarty_tpl->tpl_vars['fieldIndex']->value+1]);
?>
					<?php }?>
					<?php if ($_smarty_tpl->tpl_vars['field']->value['type'] == 'Tab') {?>
						<?php $_smarty_tpl->_assignInScope('currentColumnGroup', 0);
?> 					
						<?php if ($_smarty_tpl->tpl_vars['insideTab']->value == 0) {?>
							<?php if ($_smarty_tpl->tpl_vars['hasTabs']->value == 1) {?>
								
								<div class="tabbable tabs-<?php echo $_smarty_tpl->tpl_vars['tabsPosition']->value;?>
" id="<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
Tab">
									<ul id="<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
TabList" class="nav nav-tabs" style="margin-bottom:10px;">
									<?php $_smarty_tpl->_assignInScope('firstTab', 1);
?>
									<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['tabs']->value, 'tab', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['tab']->value) {
?>
										<?php if ($_smarty_tpl->tpl_vars['key']->value != -1) {?>
											<?php $_smarty_tpl->_assignInScope('tabClass', '');
?>
											<?php if ($_smarty_tpl->tpl_vars['firstTab']->value == 1) {?>
												<?php $_smarty_tpl->_assignInScope('tabClass', 'active');
?>
											<?php }?>
											<li class="<?php echo $_smarty_tpl->tpl_vars['tabClass']->value;?>
"><a href="#<?php echo $_smarty_tpl->tpl_vars['tab']->value['id'];?>
" data-toggle="tab"><?php echo $_smarty_tpl->tpl_vars['tab']->value['label'];?>
</a></li>
											<?php $_smarty_tpl->_assignInScope('firstTab', 0);
?>
										<?php }?>
									<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

									</ul>

									<div id="<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
TabContent" class="tab-content">
							<?php }?>
						<?php }?>
					<?php }?>
					<?php if ($_smarty_tpl->tpl_vars['field']->value['attributes']['close'] == 'true') {?>
						<?php $_smarty_tpl->_assignInScope('currentColumnGroup', $_smarty_tpl->tpl_vars['currentColumnGroup']->value+1);
?>
					<?php }?>					

					
					<?php $_smarty_tpl->_subTemplateRender("file:./Form_HandleField.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>


					<?php if ($_smarty_tpl->tpl_vars['field']->value['type'] == 'Header') {?>
						<?php $_smarty_tpl->_assignInScope('insideHeader', 1);
?>
					<?php }?>
					<?php if ($_smarty_tpl->tpl_vars['field']->value['type'] == 'Column') {?>
						<?php if ($_smarty_tpl->tpl_vars['insideHeader']->value == 1) {?>
							<?php $_smarty_tpl->_assignInScope('insideHeader', 0);
?>
						<?php }?>
						<?php if ($_smarty_tpl->tpl_vars['field']->value['attributes']['close'] == 'false') {?>
							<?php $_smarty_tpl->_assignInScope('insideColumn', 1);
?>
						<?php } else { ?>
							<?php $_smarty_tpl->_assignInScope('insideColumn', 0);
?>
							<?php $_smarty_tpl->_assignInScope('currentColumnGroup', $_smarty_tpl->tpl_vars['currentColumnGroup']->value+1);
?>
						<?php }?>
					<?php }?>
					<?php if ($_smarty_tpl->tpl_vars['field']->value['type'] == 'Tab') {?>
						<?php if ($_smarty_tpl->tpl_vars['insideHeader']->value == 1) {?>
							<?php $_smarty_tpl->_assignInScope('insideHeader', 0);
?>
						<?php }?>
						<?php if ($_smarty_tpl->tpl_vars['insideColumn']->value == 1) {?>
							<?php $_smarty_tpl->_assignInScope('insideColumn', 0);
?>
						<?php }?>
						<?php $_smarty_tpl->_assignInScope('tabIndex', $_smarty_tpl->tpl_vars['tabIndex']->value+1);
?>
						<?php $_smarty_tpl->_assignInScope('insideTab', 1);
?>
					<?php }?>
					<?php $_smarty_tpl->_assignInScope('fieldIndex', $_smarty_tpl->tpl_vars['fieldIndex']->value+1);
?>
				<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

				<input type="hidden" name="_FORM_LIST_FIELDS" id="_FORM_LIST_FIELDS" value="<?php echo $_smarty_tpl->tpl_vars['listFormFields']->value;?>
"/>
				<?php if ($_smarty_tpl->tpl_vars['insideHeader']->value == 1) {?>
					</div>
				<?php }?>
				<?php if ($_smarty_tpl->tpl_vars['insideColumn']->value == 1) {?>
						</div>
					</div>
				<?php }?>
				<?php if ($_smarty_tpl->tpl_vars['insideTab']->value == 1) {?>
						</div>
					</div>
				<?php }?>
			</div>
			<?php if ($_smarty_tpl->tpl_vars['hasRequiredFields']->value == true) {?>
				<span class="badge"><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('translate', array('app'=>"framework"));
$_block_repeat1=true;
echo smarty_block_translate(array('app'=>"framework"), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>
requiredFieldsMessage<?php $_block_repeat1=false;
echo smarty_block_translate(array('app'=>"framework"), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
 <?php echo $_smarty_tpl->tpl_vars['sRequiredString']->value;?>
</span>
			<?php }?>
		<?php }?>
		<!-- /.box-body -->
		<?php if (count($_smarty_tpl->tpl_vars['buttonActions']->value) > 0) {?>
			<div style="clear:both"></div>
			<div class="box-footer">
				<div class="btn-group">
					<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['buttonActions']->value, 'button', false, 'buttonID');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['buttonID']->value => $_smarty_tpl->tpl_vars['button']->value) {
?>
						<?php $_smarty_tpl->_subTemplateRender("file:./Form_HandleActionButtons.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>

					<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

				</div>
			</div>
		<?php }?>
	</form>
</div>
<?php }
}
}
