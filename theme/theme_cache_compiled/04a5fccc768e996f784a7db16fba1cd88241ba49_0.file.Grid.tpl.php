<?php
/* Smarty version 3.1.30, created on 2018-11-02 10:39:10
  from "/opt/kuink-dev/kuink-bridge-moodle/theme/adminlte/ui/control/Grid.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5bdc294e826326_30541231',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '04a5fccc768e996f784a7db16fba1cd88241ba49' => 
    array (
      0 => '/opt/kuink-dev/kuink-bridge-moodle/theme/adminlte/ui/control/Grid.tpl',
      1 => 1527261111,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:./grid/".((string)$_smarty_tpl->tpl_vars[\'colType\']->value).".tpl' => 1,
  ),
),false)) {
function content_5bdc294e826326_30541231 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->ext->_tplFunction->registerTplFunctions($_smarty_tpl, array (
  'helpModal' => 
  array (
    'compiled_filepath' => '/opt/kuink-dev/kuink-core/theme/theme_cache_compiled/04a5fccc768e996f784a7db16fba1cd88241ba49_0.file.Grid.tpl.php',
    'uid' => '04a5fccc768e996f784a7db16fba1cd88241ba49',
    'call_name' => 'smarty_template_function_helpModal_10646943515bdc294e71ce55_81807876',
  ),
));
if (!is_callable('smarty_block_translate')) require_once '/opt/kuink-dev/kuink-core/lib/tools/smarty/plugins/block.translate.php';
?>



<?php if ($_smarty_tpl->tpl_vars['freeze']->value == 'true') {?>
	<div id="properties_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-table">&nbsp;</i><?php echo $_smarty_tpl->tpl_vars['title']->value;?>
</h4>
				</div>
				<div class="modal-body">
					<ul class="list-group">
						<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['columnAttributes']->value, 'colAttr');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['colAttr']->value) {
?>
							<?php if ($_smarty_tpl->tpl_vars['colAttr']->value['inline'] != 'true') {?>
								<li class="list-group-item">
									<input type="checkbox" id="<?php echo $_smarty_tpl->tpl_vars['colAttr']->value['name'];?>
" class="<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
_hidden" <?php if ($_smarty_tpl->tpl_vars['colAttr']->value['hidden'] != 'true') {?>checked<?php }?>>
										<?php echo $_smarty_tpl->tpl_vars['colAttr']->value['label'];?>

									</input>
								</li>
							<?php }?>
						<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

					</ul>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('translate', array());
$_block_repeat1=true;
echo smarty_block_translate(array(), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>
back<?php $_block_repeat1=false;
echo smarty_block_translate(array(), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
</button>
					<button type="button" class="btn btn-primary" onclick=" var hidden=''; $('.<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
_hidden').each(function(){ if (!this.checked) hidden=hidden+this.id+';'; }); window.location = '<?php echo $_smarty_tpl->tpl_vars['baseUrl']->value;?>
&<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
_hidden='+hidden;"><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('translate', array());
$_block_repeat1=true;
echo smarty_block_translate(array(), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>
save<?php $_block_repeat1=false;
echo smarty_block_translate(array(), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
</button>
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
<?php }?>

<div class="box">
	<form class="form-horizontal" id="kuink_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
" action="" method="post" enctype="multipart/form-data">
		<div class="box-header">
			<div class="pull-right box-tools">

				<?php $_smarty_tpl->_assignInScope('count', 0);
?>
				<?php if ($_smarty_tpl->tpl_vars['freeze']->value != 'true') {?>
					<?php if ($_smarty_tpl->tpl_vars['globalActions']->value) {?>
						<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['globalActions']->value, 'action_desc', false, 'action_name');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['action_name']->value => $_smarty_tpl->tpl_vars['action_desc']->value) {
?>

							<?php $_smarty_tpl->_assignInScope('buttonClass', "btn btn-small");
?>
							<?php if ($_smarty_tpl->tpl_vars['count']->value == 0) {?>
								<?php $_smarty_tpl->_assignInScope('buttonClass', "btn btn-small btn-primary");
?>
								<?php $_smarty_tpl->_assignInScope('count', $_smarty_tpl->tpl_vars['count']->value+1);
?>
								<div class="btn-group">
							<?php }?>
							<button type="submit" class="<?php echo $_smarty_tpl->tpl_vars['buttonClass']->value;?>
" id="<?php echo $_smarty_tpl->tpl_vars['action_name']->value;?>
" onclick="javascript: gridActionField_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
(false, '', '<?php echo $_smarty_tpl->tpl_vars['baseUrl']->value;?>
&action=<?php echo $_smarty_tpl->tpl_vars['action_name']->value;?>
', '<?php echo $_smarty_tpl->tpl_vars['action_name']->value;?>
');return false;">
								<?php if ($_smarty_tpl->tpl_vars['buttonAttrs']->value['icon'] != '') {?>
									<i class="fa fa-<?php echo $_smarty_tpl->tpl_vars['buttonAttrs']->value['icon'];?>
" ></i>
								<?php }?>
								<?php echo $_smarty_tpl->tpl_vars['action_desc']->value;?>

							</button>
						<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

						<?php if ($_smarty_tpl->tpl_vars['count']->value > 0) {?>
							</div>
						<?php }?>
					<?php }?>
				<?php }?>

				<!-- Hide/Show grid not editable columns -->
				<?php if ($_smarty_tpl->tpl_vars['freeze']->value == 'true') {?>
					<a href="#properties_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
" class="btn btn-default" data-toggle="modal" data-target="#properties_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
"><i class="fa fa-bars"></i></a>
				<?php }?>
			</div>
			<i class="fa fa-table">&nbsp;</i>
			<h3 class="box-title"><?php echo $_smarty_tpl->tpl_vars['title']->value;?>
</h3>
		</div>

		<div class="gridBlock_<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
 kuink-grid">

			<?php $_smarty_tpl->_assignInScope('multiSeparator', '-');
?>

			

			<!-- Handle required fields -->

			<?php echo '<script'; ?>
 type="text/javascript">
				function validateRequiredFields_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
() {
					var reqErrors;
					reqErrors = false;
					var reqFocus;
					reqFocus = '';
					<?php if (count($_smarty_tpl->tpl_vars['requiredColumns']->value) > 0) {?>
						<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['data']->value, 'block');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['block']->value) {
?>
							<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['block']->value, 'row');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['row']->value) {
?>
								<?php $_smarty_tpl->_assignInScope('id', $_smarty_tpl->tpl_vars['row']->value['id']['value']);
?>
								<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['row']->value, 'value');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['value']->value) {
?>
									if ($("#<?php echo $_smarty_tpl->tpl_vars['id']->value;
echo $_smarty_tpl->tpl_vars['multiSeparator']->value;?>
CHANGED").val() == 1) {
										<?php if ($_smarty_tpl->tpl_vars['requiredColumns']->value[$_smarty_tpl->tpl_vars['value']->value['attributes']['name']] == $_smarty_tpl->tpl_vars['value']->value['attributes']['name']) {?>
											var <?php echo $_smarty_tpl->tpl_vars['value']->value['attributes']['name'];?>
_<?php echo $_smarty_tpl->tpl_vars['id']->value;?>
 = $("#<?php echo $_smarty_tpl->tpl_vars['id']->value;
echo $_smarty_tpl->tpl_vars['multiSeparator']->value;
echo $_smarty_tpl->tpl_vars['value']->value['attributes']['name'];?>
").val();
											reqErrors = reqErrors || (<?php echo $_smarty_tpl->tpl_vars['value']->value['attributes']['name'];?>
_<?php echo $_smarty_tpl->tpl_vars['id']->value;?>
 == '');
											if (<?php echo $_smarty_tpl->tpl_vars['value']->value['attributes']['name'];?>
_<?php echo $_smarty_tpl->tpl_vars['id']->value;?>
 == '') {
												$("#<?php echo $_smarty_tpl->tpl_vars['id']->value;
echo $_smarty_tpl->tpl_vars['multiSeparator']->value;
echo $_smarty_tpl->tpl_vars['value']->value['attributes']['name'];?>
CG").addClass("has-error");
												if (reqFocus == '')
													reqFocus = "#<?php echo $_smarty_tpl->tpl_vars['id']->value;
echo $_smarty_tpl->tpl_vars['multiSeparator']->value;
echo $_smarty_tpl->tpl_vars['value']->value['attributes']['name'];?>
";
											} else
												$("#<?php echo $_smarty_tpl->tpl_vars['id']->value;
echo $_smarty_tpl->tpl_vars['multiSeparator']->value;
echo $_smarty_tpl->tpl_vars['value']->value['attributes']['name'];?>
CG").removeClass("has-error");
										<?php }?>
									}
								<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

							<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

						<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

					<?php }?>
					$(reqFocus).focus();

					return !reqErrors;
				}

				<?php if ($_smarty_tpl->tpl_vars['refreshable']->value == 'true' && !$_smarty_tpl->tpl_vars['refreshing']->value) {?>
					$(function(){
						$( ".gridBlock_<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
" ).kuink_refreshControl(
							{
								url: "<?php echo $_smarty_tpl->tpl_vars['refreshUrl']->value;?>
",
								interval: <?php echo $_smarty_tpl->tpl_vars['refreshInterval']->value;?>

							}
						);

					});
				<?php }?>

				function gridActionField_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
(confirm, confirm_message, location, button_id, extraParams='') {
					if (confirm == 'true' || confirm == true)
						__kuink.controlAddKey('<?php echo $_smarty_tpl->tpl_vars['_idContext']->value;?>
','kuink_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
', button_id, 'confirm', confirm_message);

					var formButtons = $("#kuink_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").find("button").parent().children();
					$(formButtons).each(function() {
						if (this.id != '') {
							var button = $("#kuink_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").children().find("#"+this.id);
							__kuink.controlAddKey('<?php echo $_smarty_tpl->tpl_vars['_idContext']->value;?>
', 'kuink_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
', this.id, 'disabled', $(this).attr('disabled'));
						}
					});

					// get form data
					
					var reqValidate = validateRequiredFields_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
();
					if (!reqValidate)
						return false;
											
					$("#kuink_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").kuinkSubmit({
						'url' 			: location+'&modal=embed',
						'idContext'	: '<?php echo $_smarty_tpl->tpl_vars['_idContext']->value;?>
',
						'method' 		: <?php if ($_smarty_tpl->tpl_vars['freeze']->value == 'false') {?>'post'<?php } else { ?>'get'<?php }?>,
						'processData': false,
						'contentType': false,
						'button_id' : button_id,
						'formGuid'	: 'kuink_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
'
					});

				}

				$(document).ready(function() {
					$("#kuink_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
").submit(function(e){
						return false;
					});
				});

				//Function to toggle all the pick checkboxes
				function <?php echo $_smarty_tpl->tpl_vars['name']->value;?>
ToggleChecked(status) {
					$(".neon-pick").each( function() {
						$(this).attr("checked",status);
						var id = $(this).attr("id");
						var idParts = id.split("<?php echo $_smarty_tpl->tpl_vars['multiSeparator']->value;?>
"); //id is number-fieldname, ans we need just the number
						if( $(this).attr("checked") == "checked")
							$("#"+idParts[0]+"<?php echo $_smarty_tpl->tpl_vars['multiSeparator']->value;?>
SELECTED").val(1);
						else
							$("#"+idParts[0]+"<?php echo $_smarty_tpl->tpl_vars['multiSeparator']->value;?>
SELECTED").val(0);
					})
				}

				//Function to toggle all the pick checkboxes
				function <?php echo $_smarty_tpl->tpl_vars['name']->value;?>
ToggleChecked2(status, style) {
					$("#kuink_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
 ."+style).each( function() {
						$(this).attr("checked",status);
						var id = $(this).attr("id");
						var idParts = id.split("<?php echo $_smarty_tpl->tpl_vars['multiSeparator']->value;?>
"); //id is number-fieldname, ans we need just the number
						if( $(this).attr("checked") == "checked") {
							document.getElementById($(this).attr("id")).checked = true;
							setChanged(idParts[0]);
						} else
							document.getElementById($(this).attr("id")).checked = false;
					})
				}

			<?php echo '</script'; ?>
>

			<input type="hidden" name="_FORM_TYPE" value="multi"/>

			<!-- script to catch changed fields in form -->
			<?php echo '<script'; ?>
>
				function setChanged( id ) {
					fieldId = "#"+id+"<?php echo $_smarty_tpl->tpl_vars['multiSeparator']->value;?>
CHANGED";
					$(fieldId).val(1);
				}

				function setPick( id, check ) {
					fieldId = "#"+id+"<?php echo $_smarty_tpl->tpl_vars['multiSeparator']->value;?>
SELECTED";
					if (check)
						$(fieldId).val(1);
					else
						$(fieldId).val(0);
				}

				function setChecked( id, check ) {
					fieldId = "#"+id+"<?php echo $_smarty_tpl->tpl_vars['multiSeparator']->value;?>
SELECTED";
					if (check)
						$(fieldId).val(1);
					else
						$(fieldId).val(0);
				}

			<?php echo '</script'; ?>
>

			<table id="<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
" class="<?php if ($_smarty_tpl->tpl_vars['tree']->value == 'true') {?>tree<?php }?> table table-striped table-condensed table-bordered table-hover kuink-control-grid">
				<thead>
					<tr>
						<?php $_smarty_tpl->_assignInScope('index', 0);
?>
						<?php $_smarty_tpl->_assignInScope('numInlines', 0);
?>
						<?php $_smarty_tpl->_assignInScope('countHeaders', 0);
?>
						<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['headers']->value, 'header');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['header']->value) {
?>
							<?php $_smarty_tpl->_assignInScope('countHeaders', $_smarty_tpl->tpl_vars['countHeaders']->value+1);
?>
						<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

						<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['headers']->value, 'header');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['header']->value) {
?>
							<?php
 while (($_smarty_tpl->tpl_vars['columnAttributes']->value[$_smarty_tpl->tpl_vars['index']->value]['inline'] == 'true')) {?>
								<?php $_smarty_tpl->_assignInScope('index', $_smarty_tpl->tpl_vars['index']->value+1);
?>
							<?php }?>

							<?php if ($_smarty_tpl->tpl_vars['columnAttributes']->value[$_smarty_tpl->tpl_vars['index']->value]['hidden'] != "true") {?>
								<th class="col_<?php echo $_smarty_tpl->tpl_vars['columnAttributes']->value[$_smarty_tpl->tpl_vars['index']->value]['name'];?>
">
									<?php if ($_smarty_tpl->tpl_vars['columnAttributes']->value[$_smarty_tpl->tpl_vars['index']->value]['type'] == 'pick') {?>
										<input type="checkbox" class="input-medium" id="<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
_selectall" name="<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
_selectall" onclick="<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
ToggleChecked(this.checked);" value="0">
									<?php }?>
									<?php if ($_smarty_tpl->tpl_vars['columnAttributes']->value[$_smarty_tpl->tpl_vars['index']->value]['type'] == 'checkbox') {?>
										<input type="checkbox" class="input-medium" id="<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
_selectall" name="<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
_selectall" onclick="<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
ToggleChecked2(this.checked,'grid-class-<?php echo $_smarty_tpl->tpl_vars['columnAttributes']->value[$_smarty_tpl->tpl_vars['index']->value]['name'];?>
');" value="0">
									<?php }?>

									<?php echo $_smarty_tpl->tpl_vars['header']->value;?>


									<?php if ($_smarty_tpl->tpl_vars['columnAttributes']->value[$_smarty_tpl->tpl_vars['index']->value]['sortable'] != 'false' && $_smarty_tpl->tpl_vars['columnAttributes']->value[$_smarty_tpl->tpl_vars['index']->value]['sortable'] != '') {?>

										<span style="display:inline;">
											
											<?php if (isset($_smarty_tpl->tpl_vars['sort']->value[$_smarty_tpl->tpl_vars['index']->value]['sort']) && $_smarty_tpl->tpl_vars['sort']->value[$_smarty_tpl->tpl_vars['index']->value]['sort'] == 'asc') {?>
												<a href="javascript: void(0)"  onclick="gridActionField_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
(false, '', '<?php echo $_smarty_tpl->tpl_vars['baseUrl']->value;?>
&<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
_sort=<?php echo $_smarty_tpl->tpl_vars['index']->value;?>
_desc', '')"><i class="fa fa-sort-asc"></i></a>
											<?php } elseif (isset($_smarty_tpl->tpl_vars['sort']->value[$_smarty_tpl->tpl_vars['index']->value]['sort']) && $_smarty_tpl->tpl_vars['sort']->value[$_smarty_tpl->tpl_vars['index']->value]['sort'] == 'desc') {?>
												<a href="javascript: void(0)"  onclick="gridActionField_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
(false, '', '<?php echo $_smarty_tpl->tpl_vars['baseUrl']->value;?>
&<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
_sort=<?php echo $_smarty_tpl->tpl_vars['index']->value;?>
_rem', '')"><i class="fa fa-sort-desc"></i></a>
											<?php } else { ?>
												<a href="javascript: void(0)"  onclick="gridActionField_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
(false, '', '<?php echo $_smarty_tpl->tpl_vars['baseUrl']->value;?>
&<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
_sort=<?php echo $_smarty_tpl->tpl_vars['index']->value;?>
_asc', '')"><i class="fa fa-sort"></i></a>
											<?php }?>
										</span>
									<?php }?>

									<?php if ($_smarty_tpl->tpl_vars['columnAttributes']->value[$_smarty_tpl->tpl_vars['index']->value]['help'] != '') {?>
										<?php $_smarty_tpl->_assignInScope('modalData', array('fieldID'=>$_smarty_tpl->tpl_vars['columnAttributes']->value[$_smarty_tpl->tpl_vars['index']->value]['name'],'helpText'=>$_smarty_tpl->tpl_vars['columnAttributes']->value[$_smarty_tpl->tpl_vars['index']->value]['help']));
?>
										<?php $_smarty_tpl->ext->_tplFunction->callTemplateFunction($_smarty_tpl, 'helpModal', array('modalData'=>$_smarty_tpl->tpl_vars['modalData']->value), true);?>


										<a data-toggle="modal" href="#helpModal_<?php echo $_smarty_tpl->tpl_vars['columnAttributes']->value[$_smarty_tpl->tpl_vars['index']->value]['name'];?>
" ><i class="icon icon-question-sign"></i></a>
									<?php }?>
								</th>
							<?php }?>
							<?php $_smarty_tpl->_assignInScope('index', $_smarty_tpl->tpl_vars['index']->value+1);
?>
						<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

					</tr>
				</thead>
				<tbody>
					<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['data']->value, 'block');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['block']->value) {
?>
						<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['block']->value, 'row');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['row']->value) {
?>
							<?php $_smarty_tpl->_assignInScope('tr_active_class', '');
?>
							<?php if ($_smarty_tpl->tpl_vars['row']->value['__active'] == 1) {?>
								<?php $_smarty_tpl->_assignInScope('tr_active_class', 'row-highlight');
?>
							<?php }?>

							<?php $_smarty_tpl->_assignInScope('first', null);
?>
							<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['row']->value, 'value');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['value']->value) {
?>
								<?php $_smarty_tpl->_assignInScope('first', $_smarty_tpl->tpl_vars['value']->value);
?>
								<?php break 1;?>
							<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>


							<tr class="<?php if ($_smarty_tpl->tpl_vars['tree']->value == 'true') {?>treegrid-<?php echo $_smarty_tpl->tpl_vars['first']->value['attributes']['treeid'];?>
 <?php if ($_smarty_tpl->tpl_vars['first']->value['attributes']['treeparentid'] != '') {?>treegrid-parent-<?php echo $_smarty_tpl->tpl_vars['first']->value['attributes']['treeparentid'];
}
}?>">
								<?php $_smarty_tpl->_assignInScope('index', 0);
?>
								<?php $_smarty_tpl->_assignInScope('id', $_smarty_tpl->tpl_vars['row']->value['id']['value']);
?>

								<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['row']->value, 'value');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['value']->value) {
?>
									<?php $_smarty_tpl->_assignInScope('fieldAttrs', $_smarty_tpl->tpl_vars['value']->value['attributes']);
?>
									<?php $_smarty_tpl->_assignInScope('onChange', "javascript: setChanged(".((string)$_smarty_tpl->tpl_vars['id']->value).");");
?>
									<?php $_smarty_tpl->_assignInScope('onPick', "javascript: setPick(".((string)$_smarty_tpl->tpl_vars['id']->value).", this.checked);");
?>

									<?php $_smarty_tpl->_assignInScope('colType', $_smarty_tpl->tpl_vars['value']->value['attributes']['type']);
?>

									
									<?php if ($_smarty_tpl->tpl_vars['fieldAttrs']->value['hidden'] != "true") {?>
										<?php if ($_smarty_tpl->tpl_vars['fieldAttrs']->value['nowrap'] == "true") {?>
											<td nowrap="true" style="white-space: nowrap; overflow-x: auto;">
										<?php } else { ?>
											<td>
										<?php }?>


										<?php if ($_smarty_tpl->tpl_vars['tree']->value == 'true' && $_smarty_tpl->tpl_vars['index']->value == 0 && $_smarty_tpl->tpl_vars['first']->value['attributes']['treeparentid'] == '') {?>
											<span class="treegrid-indent"></span>
											<i class="treegrid-expander"></i>
										<?php }?>


										<?php $_smarty_tpl->_assignInScope('disabledClass', '');
?>
										<?php $_smarty_tpl->_assignInScope('disabledAttr', '');
?>

										<?php if ($_smarty_tpl->tpl_vars['value']->value['attributes']['disabled'] == 'true') {?>
											<?php $_smarty_tpl->_assignInScope('disabledClass', 'disabled');
?>
											<?php $_smarty_tpl->_assignInScope('disabledAttr', 'disabled');
?>
										<?php } else { ?>
											<?php if ($_smarty_tpl->tpl_vars['value']->value['attributes']['conditionalfield'] != '') {?>
												<?php if ($_smarty_tpl->tpl_vars['row']->value[$_smarty_tpl->tpl_vars['value']->value['attributes']['conditionalfield']] == $_smarty_tpl->tpl_vars['value']->value['attributes']['conditionalvalue']) {?>
													<?php $_smarty_tpl->_assignInScope('disabledClass', 'disabled');
?>
													<?php $_smarty_tpl->_assignInScope('disabledAttr', 'disabled');
?>
												<?php }?>
											<?php }?>
										<?php }?>

										<?php if ($_smarty_tpl->tpl_vars['fieldAttrs']->value['visible'] == "true" || $_smarty_tpl->tpl_vars['fieldAttrs']->value['visible'] == '') {?>
											<?php if ($_smarty_tpl->tpl_vars['colType']->value != '') {?>
												<?php $_smarty_tpl->_assignInScope('inputSize', $_smarty_tpl->tpl_vars['value']->value['attributes']['inputsize']);
?>
												<?php $_smarty_tpl->_subTemplateRender("file:./grid/".((string)$_smarty_tpl->tpl_vars['colType']->value).".tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>

											<?php } else { ?>
												<span style="overflow-x: auto;">
													<?php if (count($_smarty_tpl->tpl_vars['value']->value['constructor']) == 0) {?>
														<?php echo $_smarty_tpl->tpl_vars['value']->value['value'];?>

													<?php } else { ?>
														<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['value']->value['constructor'], 'actionAttribute');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['actionAttribute']->value) {
?>
															<?php echo $_smarty_tpl->tpl_vars['actionAttribute']->value['separator'];?>
<a href="javascript: void(0)" <?php echo $_smarty_tpl->tpl_vars['actionAttribute']->value['tooltip'];?>
 class="<?php echo $_smarty_tpl->tpl_vars['actionAttribute']->value['class'];?>
" onclick="gridActionField_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
(<?php echo $_smarty_tpl->tpl_vars['actionAttribute']->value['confirm'];?>
, '<?php echo $_smarty_tpl->tpl_vars['actionAttribute']->value['confirm_message'];?>
', '<?php echo $_smarty_tpl->tpl_vars['actionAttribute']->value['location'];?>
', '<?php echo $_smarty_tpl->tpl_vars['actionAttribute']->value['name'];?>
')"><span nowrap="true" style="white-space: nowrap; overflow-x: auto;"><?php echo $_smarty_tpl->tpl_vars['actionAttribute']->value['label'];?>
</span></a>
														<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

													<?php }?>
												</span>
											<?php }?>
										<?php }?>
										</td>
									<?php }?>

									
									<?php if ($_smarty_tpl->tpl_vars['fieldAttrs']->value['post'] == "true") {?>
										<input type="hidden" id="<?php echo $_smarty_tpl->tpl_vars['id']->value;
echo $_smarty_tpl->tpl_vars['multiSeparator']->value;
echo $_smarty_tpl->tpl_vars['fieldAttrs']->value['name'];?>
" name="<?php echo $_smarty_tpl->tpl_vars['id']->value;
echo $_smarty_tpl->tpl_vars['multiSeparator']->value;
echo $_smarty_tpl->tpl_vars['fieldAttrs']->value['name'];?>
" value="<?php echo $_smarty_tpl->tpl_vars['value']->value['value'];?>
"/>
									<?php }?>

									<?php $_smarty_tpl->_assignInScope('index', $_smarty_tpl->tpl_vars['index']->value+1);
?>
								<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

								<?php if ($_smarty_tpl->tpl_vars['supressChangedField']->value != 1) {?>
									<input type="hidden" id="<?php echo $_smarty_tpl->tpl_vars['id']->value;
echo $_smarty_tpl->tpl_vars['multiSeparator']->value;?>
CHANGED" name="<?php echo $_smarty_tpl->tpl_vars['id']->value;
echo $_smarty_tpl->tpl_vars['multiSeparator']->value;?>
CHANGED" value="0"/>
									<input type="hidden" id="<?php echo $_smarty_tpl->tpl_vars['id']->value;
echo $_smarty_tpl->tpl_vars['multiSeparator']->value;?>
SELECTED" name="<?php echo $_smarty_tpl->tpl_vars['id']->value;
echo $_smarty_tpl->tpl_vars['multiSeparator']->value;?>
SELECTED" value="0"/>
								<?php }?>
							</tr>
						<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

					<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

					<?php if ($_smarty_tpl->tpl_vars['extendEdit']->value == 'true') {?>
						<?php $_smarty_tpl->_assignInScope('index', 0);
?>
						<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['headers']->value, 'header');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['header']->value) {
?>
							
							<?php if ($_smarty_tpl->tpl_vars['columnAttributes']->value[$_smarty_tpl->tpl_vars['index']->value]['hidden'] != "true") {?>
								<?php if ($_smarty_tpl->tpl_vars['columnAttributes']->value[$_smarty_tpl->tpl_vars['index']->value]['inline'] != "true") {?>
									<td>
										<?php if ($_smarty_tpl->tpl_vars['columnAttributes']->value[$_smarty_tpl->tpl_vars['index']->value]['type'] != "static" && $_smarty_tpl->tpl_vars['columnAttributes']->value[$_smarty_tpl->tpl_vars['index']->value]['type'] != "checkbox") {?>
											<div id="div_extendedit-<?php echo $_smarty_tpl->tpl_vars['columnAttributes']->value[$_smarty_tpl->tpl_vars['index']->value]['name'];?>
CG" class="control-group">
												<textarea class="input-sma " id="_extendedit-<?php echo $_smarty_tpl->tpl_vars['columnAttributes']->value[$_smarty_tpl->tpl_vars['index']->value]['name'];?>
" name="_extendedit-<?php echo $_smarty_tpl->tpl_vars['columnAttributes']->value[$_smarty_tpl->tpl_vars['index']->value]['name'];?>
" rows="4" cols="20" ></textarea>
												<div id="tt_extendedit-<?php echo $_smarty_tpl->tpl_vars['columnAttributes']->value[$_smarty_tpl->tpl_vars['index']->value]['name'];?>
">
												</div>
											</div>
										<?php } else { ?>
											&nbsp;
										<?php }?>
									</td>
								<?php }?>
							<?php }?>

							<?php $_smarty_tpl->_assignInScope('index', $_smarty_tpl->tpl_vars['index']->value+1);
?>
						<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

						
						<td>
							<?php echo '<script'; ?>
>
								//Expand values to fields in the grid
								function extendedEditAddValues() {
									var extendedFields = $("[id^='_extendedit']");
									for (i=0; i<extendedFields.length; i++)
									{
										var error="";
										var value=extendedFields[i].value;
										var arrValue=value.split("\n");
										var extendId=$(extendedFields[i]).attr('id').replace("_extendedit-","");
										if (arrValue != "") {
											var targetFields = $("[id$="+extendId+"]");
											for (j=0; j<targetFields.length-1; j++) {
												if (j < arrValue.length && arrValue[j]!="") {
													if ($(targetFields[j]).is('select')) {
														//Select field
														if (!$(targetFields[j]).attr('disabled')) {
															var found = false;
															$("#"+$(targetFields[j]).attr("id")+" option:contains('"+arrValue[j]+"')").each(function(){
																if($(this).text() == arrValue[j]){
																	found = true;
																	$(this).attr('selected','selected');
																}
															});
															if (!found)
																error=error+arrValue[j]+"\n";
														}
													} else {
														//Other fields than select
														//works for inputs but must be refactor for other types
														if (!$(targetFields[j]).attr('disabled'))
															$(targetFields[j]).val(arrValue[j]);
													}
													//Set to change to be treated as a changed value
													var itemId=$(targetFields[j]).attr("id").replace("-"+extendId,"");
													setChanged(itemId);
												}
											}
										}
										//Clear the extendfield
										if (error != "" ) {
											$("#div"+$(extendedFields[i]).attr("id")+"CG").removeClass("success");
											$("#div"+$(extendedFields[i]).attr("id")+"CG").addClass("error");
											$("#tt"+$(extendedFields[i]).attr("id")).html('<i class="fa fa-warning icon-black" style="color: #b94a48"></i>&nbsp;'+error);
										} else {
											if (arrValue != "") {
												//Only if the extendedit was not empty
												$("#div"+$(extendedFields[i]).attr("id")+"CG").removeClass("error");
												$("#div"+$(extendedFields[i]).attr("id")+"CG").addClass("success");
												$("#tt"+$(extendedFields[i]).attr("id")).html('<i class="fa fa-check-circle icon-black" style="color: #468847"></i>');
											}
										}
										$(extendedFields[i]).val("");

									}
								}
							<?php echo '</script'; ?>
>

							<a id="extendEditAction" type="submit" class=" btn btn-info " onclick="javascript: extendedEditAddValues(); return(false);"><i class="fa fa-arrow-circle-up icon-white"></i></a>
						</td>
					<?php }?>
				</tbody>
			</table>

			<?php if ($_smarty_tpl->tpl_vars['exportable']->value == 'true') {?>
				<div style="float:right; padding:3px">
					<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['exportTypes']->value, 'exportType');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['exportType']->value) {
?>
						<a href="<?php echo $_smarty_tpl->tpl_vars['baseUrl']->value;?>
&action=<?php echo $_smarty_tpl->tpl_vars['action']->value;?>
&exporttype=<?php echo $_smarty_tpl->tpl_vars['exportType']->value;?>
&export=<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
&_nocache=<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
"><span class="label label-info"><?php echo $_smarty_tpl->tpl_vars['exportType']->value;?>
</span></a>
					<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

				</div>
			<?php }?>

			<?php if ($_smarty_tpl->tpl_vars['freeze']->value != 'true') {?>
				<?php if ($_smarty_tpl->tpl_vars['globalActions']->value) {?>
					<div class="form-actions">
						<div class="btn-group">
							<?php $_smarty_tpl->_assignInScope('count', 0);
?>
							<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['globalActions']->value, 'action_desc', false, 'action_name');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['action_name']->value => $_smarty_tpl->tpl_vars['action_desc']->value) {
?>

								<?php $_smarty_tpl->_assignInScope('buttonClass', "btn btn-small");
?>
								<?php if ($_smarty_tpl->tpl_vars['count']->value == 0) {?>
									<?php $_smarty_tpl->_assignInScope('buttonClass', "btn btn-small btn-primary");
?>
									<?php $_smarty_tpl->_assignInScope('count', $_smarty_tpl->tpl_vars['count']->value+1);
?>
									<div class="btn-group">
								<?php }?>

								<button type="submit" class="<?php echo $_smarty_tpl->tpl_vars['buttonClass']->value;?>
" id="<?php echo $_smarty_tpl->tpl_vars['action_name']->value;?>
" onclick="javascript: gridActionField_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
(false, '', '<?php echo $_smarty_tpl->tpl_vars['baseUrl']->value;?>
&action=<?php echo $_smarty_tpl->tpl_vars['action_name']->value;?>
', '<?php echo $_smarty_tpl->tpl_vars['action_name']->value;?>
');return false;">
									<?php if ($_smarty_tpl->tpl_vars['buttonAttrs']->value['icon'] != '') {?>
										<i class="fa fa-<?php echo $_smarty_tpl->tpl_vars['buttonAttrs']->value['icon'];?>
" ></i>
									<?php }?>
									<?php echo $_smarty_tpl->tpl_vars['action_desc']->value;?>

								</button>
							<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

							<?php if ($_smarty_tpl->tpl_vars['count']->value > 0) {?>
								</div>
							<?php }?>
						</div>
					</div>
				<?php }?>
			<?php }?>
			<?php if ($_smarty_tpl->tpl_vars['isPageable']->value == "true") {?>
				<div class="pull-left" style="margin: 5px 5px 5px 5px">

					<ul style="width: 100%" class="pagination pagination-sm no-margin pull-right">
						<?php if ($_smarty_tpl->tpl_vars['pageCurrent']->value > 0) {?>
							<li><a href="javascript: gridActionField_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
(false, '', '<?php echo $_smarty_tpl->tpl_vars['baseUrl']->value;?>
&<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
_page=<?php echo $_smarty_tpl->tpl_vars['pageCurrent']->value-1;?>
', '');"><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('translate', array('app'=>"framework"));
$_block_repeat1=true;
echo smarty_block_translate(array('app'=>"framework"), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>
previous<?php $_block_repeat1=false;
echo smarty_block_translate(array('app'=>"framework"), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
</a></li>
							<li><a href="javascript: gridActionField_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
(false, '', '<?php echo $_smarty_tpl->tpl_vars['baseUrl']->value;?>
&<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
_page=0', '');">1</a></li>
						<?php } else { ?>
							<li class=""><a disabled="disabled" href="javascript:void(0);"><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('translate', array('app'=>"framework"));
$_block_repeat1=true;
echo smarty_block_translate(array('app'=>"framework"), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>
previous<?php $_block_repeat1=false;
echo smarty_block_translate(array('app'=>"framework"), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
</a></li>
							<li class="active"><a disabled="disabled" href="javascript:void(0);">1</a></li>
						<?php }?>

						<?php if ($_smarty_tpl->tpl_vars['pageTotal']->value > 10 && $_smarty_tpl->tpl_vars['pageCurrent']->value > 5) {?>
							<li class=""><a disabled="disabled" href="javascript:void(0);">...</a></li>
						<?php }?>

						<?php $_smarty_tpl->_assignInScope('start', "2");
?>
						<?php $_smarty_tpl->_assignInScope('end', ((string)$_smarty_tpl->tpl_vars['pageTotal']->value));
?>
						<?php if ($_smarty_tpl->tpl_vars['pageCurrent']->value > 5 && $_smarty_tpl->tpl_vars['pageTotal']->value > 10) {?>
							<?php $_smarty_tpl->_assignInScope('start', $_smarty_tpl->tpl_vars['pageCurrent']->value-2);
?>
						<?php }?>

						<?php if ($_smarty_tpl->tpl_vars['pageCurrent']->value < $_smarty_tpl->tpl_vars['pageTotal']->value-5 && $_smarty_tpl->tpl_vars['pageTotal']->value > 10) {?>
							<?php $_smarty_tpl->_assignInScope('end', $_smarty_tpl->tpl_vars['pageCurrent']->value+5);
?>
						<?php }?>

						<?php
$_smarty_tpl->tpl_vars['i'] = new Smarty_Variable(null, $_smarty_tpl->isRenderingCache);$_smarty_tpl->tpl_vars['i']->step = 1;$_smarty_tpl->tpl_vars['i']->total = (int) ceil(($_smarty_tpl->tpl_vars['i']->step > 0 ? $_smarty_tpl->tpl_vars['end']->value-1+1 - ($_smarty_tpl->tpl_vars['start']->value) : $_smarty_tpl->tpl_vars['start']->value-($_smarty_tpl->tpl_vars['end']->value-1)+1)/abs($_smarty_tpl->tpl_vars['i']->step));
if ($_smarty_tpl->tpl_vars['i']->total > 0) {
for ($_smarty_tpl->tpl_vars['i']->value = $_smarty_tpl->tpl_vars['start']->value, $_smarty_tpl->tpl_vars['i']->iteration = 1;$_smarty_tpl->tpl_vars['i']->iteration <= $_smarty_tpl->tpl_vars['i']->total;$_smarty_tpl->tpl_vars['i']->value += $_smarty_tpl->tpl_vars['i']->step, $_smarty_tpl->tpl_vars['i']->iteration++) {
$_smarty_tpl->tpl_vars['i']->first = $_smarty_tpl->tpl_vars['i']->iteration == 1;$_smarty_tpl->tpl_vars['i']->last = $_smarty_tpl->tpl_vars['i']->iteration == $_smarty_tpl->tpl_vars['i']->total;?>
							<?php if ($_smarty_tpl->tpl_vars['pageCurrent']->value+1 == $_smarty_tpl->tpl_vars['i']->value) {?>
								<li class="active"><a disabled="disabled" href="javascript:void(0);"><?php echo $_smarty_tpl->tpl_vars['i']->value;?>
</a></li>
							<?php } else { ?>
								<li><a href="javascript: gridActionField_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
(false, '', '<?php echo $_smarty_tpl->tpl_vars['baseUrl']->value;?>
&<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
_page=<?php echo $_smarty_tpl->tpl_vars['i']->value-1;?>
', '');"><?php echo $_smarty_tpl->tpl_vars['i']->value;?>
</a></li>
							<?php }?>
						<?php }
}
?>


						<?php if ($_smarty_tpl->tpl_vars['pageTotal']->value > 10 && $_smarty_tpl->tpl_vars['pageCurrent']->value < $_smarty_tpl->tpl_vars['pageTotal']->value-5) {?>
							<li class=""><a disabled="disabled" href="javascript:void(0);">...</a></li>
						<?php }?>

						<?php if ($_smarty_tpl->tpl_vars['pageCurrent']->value < $_smarty_tpl->tpl_vars['pageTotal']->value-1) {?>
							<?php if ($_smarty_tpl->tpl_vars['pageTotal']->value != 1) {?>
								<li><a href="javascript: gridActionField_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
(false, '', '<?php echo $_smarty_tpl->tpl_vars['baseUrl']->value;?>
&<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
_page=<?php echo $_smarty_tpl->tpl_vars['pageTotal']->value-1;?>
', '');"><?php echo $_smarty_tpl->tpl_vars['pageTotal']->value;?>
</a></li>
							<?php }?>
							<li><a href="javascript: gridActionField_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
(false, '', '<?php echo $_smarty_tpl->tpl_vars['baseUrl']->value;?>
&<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
_page=<?php echo $_smarty_tpl->tpl_vars['pageCurrent']->value+1;?>
', '');"><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('translate', array('app'=>"framework"));
$_block_repeat1=true;
echo smarty_block_translate(array('app'=>"framework"), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>
next<?php $_block_repeat1=false;
echo smarty_block_translate(array('app'=>"framework"), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
</a></li>
						<?php } else { ?>
							<?php if ($_smarty_tpl->tpl_vars['pageTotal']->value != 1) {?>
								<li class="active">&nbsp;<a href="javascript: gridActionField_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
(false, '', '<?php echo $_smarty_tpl->tpl_vars['baseUrl']->value;?>
&<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
_page=<?php echo $_smarty_tpl->tpl_vars['pageTotal']->value-1;?>
', '');"><?php echo $_smarty_tpl->tpl_vars['pageTotal']->value;?>
</a></li>
							<?php }?>
							<li class=""><a disabled="disabled" href="javascript:void(0);"><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('translate', array('app'=>"framework"));
$_block_repeat1=true;
echo smarty_block_translate(array('app'=>"framework"), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>
next<?php $_block_repeat1=false;
echo smarty_block_translate(array('app'=>"framework"), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
</a></li>
						<?php }?>

						<li>&nbsp;
							<select id="<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
_pageselector" name="<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
_pageselector" class="form-control input-small" onchange="javascript: gridActionField_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
(false, '', '<?php echo $_smarty_tpl->tpl_vars['baseUrl']->value;?>
&<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
_pagesize='+this.value+'&<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
_page=0', '');" style="width: 80px; height: 28px; padding:0px 6px; display:inline;">
								<option value="10">10</option>
								<option value="20">20</option>
								<option value="50">50</option>
								<option value="100">100</option>
							</select>
							<?php echo '<script'; ?>
>$('#<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
_pageselector option[value=<?php echo $_smarty_tpl->tpl_vars['pageSize']->value;?>
]').attr('selected', 'selected');<?php echo '</script'; ?>
>
						</li>

						<li class="active">
							<a disabled="disabled"><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('translate', array('app'=>"framework"));
$_block_repeat1=true;
echo smarty_block_translate(array('app'=>"framework"), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>
total<?php $_block_repeat1=false;
echo smarty_block_translate(array('app'=>"framework"), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
:&nbsp;<?php echo $_smarty_tpl->tpl_vars['recordsTotal']->value;?>
</a>
						</li>
					</ul>
				</div>
			<?php } else { ?>
				<span class="label label-default"><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('translate', array('app'=>"framework"));
$_block_repeat1=true;
echo smarty_block_translate(array('app'=>"framework"), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>
total<?php $_block_repeat1=false;
echo smarty_block_translate(array('app'=>"framework"), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
: <?php echo $_smarty_tpl->tpl_vars['recordsTotal']->value;?>
</span>
			<?php }?>
			<?php if ($_smarty_tpl->tpl_vars['legend']->value != '') {?>
				<strong><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('translate', array('app'=>"framework"));
$_block_repeat1=true;
echo smarty_block_translate(array('app'=>"framework"), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>
legend<?php $_block_repeat1=false;
echo smarty_block_translate(array('app'=>"framework"), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
</strong>: <?php echo $_smarty_tpl->tpl_vars['legend']->value;?>

			<?php }?>

			
		</div>
	</form>
</div>

<?php if ($_smarty_tpl->tpl_vars['tree']->value == 'true') {?>
	<?php echo '<script'; ?>
 type="text/javascript">
		$(document).ready(function() {
			$('.tree[id=<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
]').treegrid({

				<?php if ($_smarty_tpl->tpl_vars['treecollapsed']->value == 'true') {?>
					'initialState': 'collapsed'
				<?php } else { ?>
					'initialState': 'expanded'
				<?php }?>
			});
		});
	<?php echo '</script'; ?>
>
<?php }
}
/* smarty_template_function_helpModal_10646943515bdc294e71ce55_81807876 */
if (!function_exists('smarty_template_function_helpModal_10646943515bdc294e71ce55_81807876')) {
function smarty_template_function_helpModal_10646943515bdc294e71ce55_81807876($_smarty_tpl,$params) {
foreach ($params as $key => $value) {
$_smarty_tpl->tpl_vars[$key] = new Smarty_Variable($value, $_smarty_tpl->isRenderingCache);
}?>          
	<div class="modal hide" id="helpModal_<?php echo $_smarty_tpl->tpl_vars['modalData']->value['fieldID'];?>
">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal">×</button>
			<h3>Ajuda</h3>
		</div>
		<div class="modal-body">
			<p><?php echo $_smarty_tpl->tpl_vars['modalData']->value['helpText'];?>
</p>
		</div>
		<div class="modal-footer">
			<a href="#" class="btn" data-dismiss="modal">Fechar</a>
		</div>
	</div>
<?php
}}
/*/ smarty_template_function_helpModal_10646943515bdc294e71ce55_81807876 */
}
