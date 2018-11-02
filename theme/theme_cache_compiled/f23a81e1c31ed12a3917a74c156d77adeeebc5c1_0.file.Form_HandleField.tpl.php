<?php
/* Smarty version 3.1.30, created on 2018-11-02 10:39:10
  from "/opt/kuink-dev/kuink-bridge-moodle/theme/adminlte/ui/control/Form_HandleField.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5bdc294e6a3873_46550232',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'f23a81e1c31ed12a3917a74c156d77adeeebc5c1' => 
    array (
      0 => '/opt/kuink-dev/kuink-bridge-moodle/theme/adminlte/ui/control/Form_HandleField.tpl',
      1 => 1540821053,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:./form/".((string)$_smarty_tpl->tpl_vars[\'fieldType\']->value).".tpl' => 3,
  ),
),false)) {
function content_5bdc294e6a3873_46550232 (Smarty_Internal_Template $_smarty_tpl) {
?>



<?php if ($_smarty_tpl->tpl_vars['field']->value['attributes']['disabled'] == "true") {?>
	<?php $_smarty_tpl->_assignInScope('disabledClass', "disabled");
?>
	<?php $_smarty_tpl->_assignInScope('disabledAttr', "disabled");
}
$_smarty_tpl->_assignInScope('fieldID', $_smarty_tpl->tpl_vars['field']->value['attributes']['id']);
$_smarty_tpl->_assignInScope('fieldName', $_smarty_tpl->tpl_vars['fieldID']->value);
$_smarty_tpl->_assignInScope('fieldGuid', $_smarty_tpl->tpl_vars['fieldID']->value);
?> 
<?php $_smarty_tpl->_assignInScope('fieldIcon', $_smarty_tpl->tpl_vars['fieldAttrs']->value['icon']);
?>


<?php $_smarty_tpl->_assignInScope('fieldRequired', false);
ob_start();
echo $_smarty_tpl->tpl_vars['fieldID']->value;
$_prefixVariable1=ob_get_clean();
$_smarty_tpl->_assignInScope('rule', $_smarty_tpl->tpl_vars['rules']->value[$_prefixVariable1]);
?>
	<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['rule']->value, 'item');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['item']->value) {
?>
		<?php if ($_smarty_tpl->tpl_vars['item']->value == 'required') {?>
			<?php $_smarty_tpl->_assignInScope('fieldRequired', true);
?>
		<?php }?>
	<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>



	
	
	<?php if (($_smarty_tpl->tpl_vars['field']->value['attributes']['_rowLength'] > 1) || ($_smarty_tpl->tpl_vars['insideColumn']->value == 1)) {?>
		<?php $_smarty_tpl->_assignInScope('labelSize', 2);
?> 
	<?php } else { ?>
		<?php $_smarty_tpl->_assignInScope('labelSize', 2);
?>
	<?php }?>
	
  <?php if ($_smarty_tpl->tpl_vars['fieldAttrs']->value['labelsize'] == 'medium') {?>
  	<?php $_smarty_tpl->_assignInScope('labelSize', 6);
?>
  <?php } elseif ($_smarty_tpl->tpl_vars['fieldAttrs']->value['labelsize'] == 'large') {?>
  	<?php $_smarty_tpl->_assignInScope('labelSize', 8);
?>
  <?php } elseif ($_smarty_tpl->tpl_vars['fieldAttrs']->value['labelsize'] == 'xlarge') {?>
  	<?php $_smarty_tpl->_assignInScope('labelSize', 10);
?>
 	<?php }?> 
 
 	

<?php $_smarty_tpl->_assignInScope('availableSpace', 12);
if ((($_smarty_tpl->tpl_vars['field']->value['attributes']['inline'] == 'false') && ($_smarty_tpl->tpl_vars['field']->value['attributes']['_rowLength'] > 1))) {?>
	
	<?php $_smarty_tpl->_assignInScope('fieldSize', ($_smarty_tpl->tpl_vars['availableSpace']->value/$_smarty_tpl->tpl_vars['field']->value['attributes']['_rowLength'])-$_smarty_tpl->tpl_vars['labelSize']->value+1);
} else { ?>
	<?php if (($_smarty_tpl->tpl_vars['field']->value['attributes']['inline'] != 'false') && ($_smarty_tpl->tpl_vars['field']->value['attributes']['_rowEnd'] == 1)) {?>
		
		<?php $_smarty_tpl->_assignInScope('fieldSize', ($_smarty_tpl->tpl_vars['availableSpace']->value/$_smarty_tpl->tpl_vars['field']->value['attributes']['_rowLength'])-$_smarty_tpl->tpl_vars['labelSize']->value-1);
?>
	<?php } else { ?>
		<?php $_smarty_tpl->_assignInScope('fieldSize', ($_smarty_tpl->tpl_vars['availableSpace']->value/$_smarty_tpl->tpl_vars['field']->value['attributes']['_rowLength'])-$_smarty_tpl->tpl_vars['labelSize']->value);
?>
	<?php }
}?>



<?php $_smarty_tpl->_assignInScope('fieldType', $_smarty_tpl->tpl_vars['field']->value['type']);
?>


<?php $_smarty_tpl->_assignInScope('labelClassComplement', '');
if ($_smarty_tpl->tpl_vars['fieldType']->value == 'Checkbox') {?>
	
	<?php $_smarty_tpl->_assignInScope('labelClassComplement', '');
}?>

<?php if ($_smarty_tpl->tpl_vars['fieldType']->value == 'Header' || $_smarty_tpl->tpl_vars['fieldType']->value == 'Column' || $_smarty_tpl->tpl_vars['fieldType']->value == 'Tab') {?>
	<?php $_smarty_tpl->_subTemplateRender("file:./form/".((string)$_smarty_tpl->tpl_vars['fieldType']->value).".tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>

	<?php if (($_smarty_tpl->tpl_vars['fieldAttrs']->value['close'] == 'true')) {?> 
		</div>
	<?php }?>	
<?php } else { ?>
	<?php if ($_smarty_tpl->tpl_vars['fieldAttrs']->value['_rowStart'] == 1) {?>
		
		<div class="form-group" id="<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
CG" style="margin-left: 10px;">	
			<div class="row">
	<?php }?>

			<?php if ($_smarty_tpl->tpl_vars['fieldAttrs']->value['labelposition'] == 'right') {?>
				
				<?php if ($_smarty_tpl->tpl_vars['fieldType']->value == 'Checkbox') {?>
					<?php $_smarty_tpl->_assignInScope('fieldSize', 1);
?>
					<?php $_smarty_tpl->_assignInScope('labelSize', $_smarty_tpl->tpl_vars['availableSpace']->value/$_smarty_tpl->tpl_vars['field']->value['attributes']['_rowLength']-1);
?>
				<?php }?>

				<div class="col-lg-<?php echo $_smarty_tpl->tpl_vars['fieldSize']->value;?>
 col-md-<?php echo $_smarty_tpl->tpl_vars['fieldSize']->value;?>
 col-sm-<?php echo $_smarty_tpl->tpl_vars['fieldSize']->value;?>
 col-xs-12">
					<?php $_smarty_tpl->_subTemplateRender("file:./form/".((string)$_smarty_tpl->tpl_vars['fieldType']->value).".tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>

				</div>			
			<?php }?>
			
			<?php if ($_smarty_tpl->tpl_vars['fieldType']->value != 'ActionGroup') {?>
				<!--div class="col-lg-<?php echo $_smarty_tpl->tpl_vars['labelSize']->value;?>
 col-md-<?php echo $_smarty_tpl->tpl_vars['labelSize']->value;?>
 col-sm-<?php echo $_smarty_tpl->tpl_vars['labelSize']->value;?>
 col-xs-3" style="<?php if ((($_smarty_tpl->tpl_vars['fieldAttrs']->value['inline'] == 'true') || ($_smarty_tpl->tpl_vars['nextFieldAttrs']->value['inline'] == 'true' && $_smarty_tpl->tpl_vars['nextFieldAttrs']->value != null))) {?>width: 100%;<?php }?>"-->
				<div class="col-lg-<?php echo $_smarty_tpl->tpl_vars['labelSize']->value;?>
 col-md-<?php echo $_smarty_tpl->tpl_vars['labelSize']->value;?>
 col-sm-<?php echo $_smarty_tpl->tpl_vars['labelSize']->value;?>
 col-xs-12">
					<?php if ($_smarty_tpl->tpl_vars['fieldType']->value != 'Hidden') {?>
						
						<label style="margin-top: 8px; <?php echo $_smarty_tpl->tpl_vars['labelClassComplement']->value;?>
" for="<?php echo $_smarty_tpl->tpl_vars['fieldGuid']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['field']->value['attributes']['label'];
if ($_smarty_tpl->tpl_vars['fieldRequired']->value == true) {
$_smarty_tpl->_assignInScope('hasRequiredFields', true ,false ,2);
?>&nbsp;<font style="color:red"><?php echo $_smarty_tpl->tpl_vars['sRequiredString']->value;?>
</font><?php }?></label>
						<?php if ($_smarty_tpl->tpl_vars['field']->value['attributes']['help'] != 'false' && $_smarty_tpl->tpl_vars['field']->value['attributes']['help'] != '' && $_smarty_tpl->tpl_vars['field']->value['attributes']['help'] != $_smarty_tpl->tpl_vars['fieldID']->value) {?>
							&nbsp;&nbsp;
							<a tabindex="-1"
								data-toggle="tooltip" title=""
								data-original-title="<?php echo $_smarty_tpl->tpl_vars['field']->value['attributes']['help'];?>
"
								data-placement="right"
								href="javascript:void(0);">
									<i class="fieldQuestionMark fa fa-info-circle" ></i>
							</a>
						<?php }?>
					<?php }?>
				</div>
			<?php }?>

			<?php if ($_smarty_tpl->tpl_vars['fieldAttrs']->value['labelposition'] == 'left') {?>
				<div class="col-lg-<?php echo $_smarty_tpl->tpl_vars['fieldSize']->value;?>
 col-md-<?php echo $_smarty_tpl->tpl_vars['fieldSize']->value;?>
 col-sm-<?php echo $_smarty_tpl->tpl_vars['fieldSize']->value;?>
 col-xs-12">
					<?php $_smarty_tpl->_subTemplateRender("file:./form/".((string)$_smarty_tpl->tpl_vars['fieldType']->value).".tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>

				</div>			
			<?php }?>
	<?php if (($_smarty_tpl->tpl_vars['fieldAttrs']->value['_rowEnd'] == 1)) {?> 
			</div>
		</div>
	<?php }?>
	
<?php }
}
}
