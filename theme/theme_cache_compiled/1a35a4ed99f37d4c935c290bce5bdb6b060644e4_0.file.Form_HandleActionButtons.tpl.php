<?php
/* Smarty version 3.1.30, created on 2018-11-02 10:39:28
  from "/opt/kuink-dev/kuink-bridge-moodle/theme/adminlte/ui/control/Form_HandleActionButtons.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5bdc29600552c3_40232376',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '1a35a4ed99f37d4c935c290bce5bdb6b060644e4' => 
    array (
      0 => '/opt/kuink-dev/kuink-bridge-moodle/theme/adminlte/ui/control/Form_HandleActionButtons.tpl',
      1 => 1517503379,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5bdc29600552c3_40232376 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_modifier_replace')) require_once '/opt/kuink-dev/kuink-core/lib/tools/smarty/plugins/modifier.replace.php';
?>




<?php $_smarty_tpl->_assignInScope('buttonAttrs', $_smarty_tpl->tpl_vars['button']->value['attributes']);
$_smarty_tpl->_assignInScope('buttonType', $_smarty_tpl->tpl_vars['buttonAttrs']->value['type']);
$_smarty_tpl->_assignInScope('buttonIcon', $_smarty_tpl->tpl_vars['buttonAttrs']->value['icon']);
$_smarty_tpl->_assignInScope('group', $_smarty_tpl->tpl_vars['buttonAttrs']->value['group']);
?>

<?php $_smarty_tpl->_assignInScope('iconColor', "icon-white");
$_smarty_tpl->_assignInScope('buttonClass', "btn");
?>

<?php if ($_smarty_tpl->tpl_vars['buttonType']->value == "submit" || $_smarty_tpl->tpl_vars['buttonAttrs']->value['decoration'] == "primary") {?>
	<?php $_smarty_tpl->_assignInScope('buttonClass', "btn btn-primary");
}?>

	      	<?php if ($_smarty_tpl->tpl_vars['buttonType']->value == "cancel") {?>
		        <?php $_smarty_tpl->_assignInScope('buttonClass', "btn");
?>
		        <?php $_smarty_tpl->_assignInScope('iconColor', '');
?>
  	      <?php } elseif ($_smarty_tpl->tpl_vars['buttonType']->value == "add") {?>
  	       	  <?php $_smarty_tpl->_assignInScope('buttonType', "submit");
?>
  	       	  <?php $_smarty_tpl->_assignInScope('buttonIcon', "plus");
?>
  	          <?php $_smarty_tpl->_assignInScope('iconColor', "icon-white");
?>
  	          <?php $_smarty_tpl->_assignInScope('buttonClass', "btn btn-success");
?>
  	      <?php } elseif ($_smarty_tpl->tpl_vars['buttonType']->value == "execute") {?>
  	       	  <?php $_smarty_tpl->_assignInScope('buttonType', "submit");
?>
  	       	  <?php $_smarty_tpl->_assignInScope('buttonIcon', "play");
?>
  	          <?php $_smarty_tpl->_assignInScope('iconColor', "icon-white");
?>
  	          <?php $_smarty_tpl->_assignInScope('buttonClass', "btn btn-primary");
?>
  	      <?php } elseif ($_smarty_tpl->tpl_vars['buttonType']->value == "save") {?>
  	       	  <?php $_smarty_tpl->_assignInScope('buttonType', "submit");
?>
  	       	  <?php $_smarty_tpl->_assignInScope('buttonIcon', "check");
?>
  	          <?php $_smarty_tpl->_assignInScope('iconColor', "icon-white");
?>
  	          <?php $_smarty_tpl->_assignInScope('buttonClass', "btn btn-primary");
?>
  	      <?php } elseif ($_smarty_tpl->tpl_vars['buttonType']->value == "delete") {?>
  	       	  <?php $_smarty_tpl->_assignInScope('buttonType', "submit");
?>
  	       	  <?php $_smarty_tpl->_assignInScope('buttonIcon', "remove");
?>
  	          <?php $_smarty_tpl->_assignInScope('iconColor', "icon-white");
?>
  	          <?php $_smarty_tpl->_assignInScope('buttonClass', "btn btn-danger");
?>
  	      <?php } elseif ($_smarty_tpl->tpl_vars['buttonType']->value == "search") {?>
  	       	  <?php $_smarty_tpl->_assignInScope('buttonType', "submit");
?>
  	       	  <?php $_smarty_tpl->_assignInScope('buttonIcon', "search");
?>
  	          <?php $_smarty_tpl->_assignInScope('iconColor', "icon-white");
?>
  	          <?php $_smarty_tpl->_assignInScope('buttonClass', "btn btn-primary");
?>
          <?php } elseif ($_smarty_tpl->tpl_vars['buttonType']->value == "back") {?>
  	       	  <?php $_smarty_tpl->_assignInScope('buttonType', "cancel");
?>
  	       	  <?php $_smarty_tpl->_assignInScope('buttonIcon', "chevron-left");
?>
  	          <?php $_smarty_tpl->_assignInScope('iconColor', '');
?>
  	          <?php $_smarty_tpl->_assignInScope('buttonClass', "btn");
?>
          <?php } elseif ($_smarty_tpl->tpl_vars['buttonType']->value == "continue") {?>
  	       	  <?php $_smarty_tpl->_assignInScope('buttonType', "submit");
?>
  	       	  <?php $_smarty_tpl->_assignInScope('buttonIcon', "chevron-right");
?>
  	          <?php $_smarty_tpl->_assignInScope('iconColor', "icon-white");
?>
  	          <?php $_smarty_tpl->_assignInScope('buttonClass', "btn btn-primary");
?>
          <?php } elseif ($_smarty_tpl->tpl_vars['buttonType']->value == "list") {?>
  	       	  <?php $_smarty_tpl->_assignInScope('buttonType', "submit");
?>
  	       	  <?php $_smarty_tpl->_assignInScope('buttonIcon', "list");
?>
  	          <?php $_smarty_tpl->_assignInScope('iconColor', "icon-white");
?>
  	          <?php $_smarty_tpl->_assignInScope('buttonClass', "btn btn-primary");
?>
          <?php } elseif ($_smarty_tpl->tpl_vars['buttonType']->value == "view") {?>
  	       	  <?php $_smarty_tpl->_assignInScope('buttonType', "submit");
?>
  	       	  <?php $_smarty_tpl->_assignInScope('buttonIcon', "file");
?>
  	          <?php $_smarty_tpl->_assignInScope('iconColor', "icon-white");
?>
  	          <?php $_smarty_tpl->_assignInScope('buttonClass', "btn btn-primary");
?>
          <?php } elseif ($_smarty_tpl->tpl_vars['buttonType']->value == "update") {?>
  	       	  <?php $_smarty_tpl->_assignInScope('buttonType', "submit");
?>
  	       	  <?php $_smarty_tpl->_assignInScope('buttonIcon', "pencil");
?>
  	          <?php $_smarty_tpl->_assignInScope('iconColor', "icon-white");
?>
  	          <?php $_smarty_tpl->_assignInScope('buttonClass', "btn btn-primary");
?>
          <?php } elseif ($_smarty_tpl->tpl_vars['buttonType']->value == "send") {?>
  	       	  <?php $_smarty_tpl->_assignInScope('buttonType', "submit");
?>
  	       	  <?php $_smarty_tpl->_assignInScope('buttonIcon', "arrow-right");
?>
  	          <?php $_smarty_tpl->_assignInScope('iconColor', "icon-white");
?>
  	          <?php $_smarty_tpl->_assignInScope('buttonClass', "btn btn-danger");
?>
  	      <?php } elseif ($_smarty_tpl->tpl_vars['buttonType']->value == "validate") {?>
  	       	  <?php $_smarty_tpl->_assignInScope('buttonType', "submit");
?>
  	       	  <?php $_smarty_tpl->_assignInScope('buttonIcon', "ok");
?>
  	          <?php $_smarty_tpl->_assignInScope('iconColor', "icon-white");
?>
  	          <?php $_smarty_tpl->_assignInScope('buttonClass', "btn btn-success");
?>
          <?php } elseif ($_smarty_tpl->tpl_vars['buttonType']->value == "refresh") {?>
              <?php $_smarty_tpl->_assignInScope('buttonType', "submit");
?>
              <?php $_smarty_tpl->_assignInScope('buttonIcon', "refresh");
?>
              <?php $_smarty_tpl->_assignInScope('iconColor', "icon-white");
?>
              <?php $_smarty_tpl->_assignInScope('buttonClass', "btn btn-warning");
?>
          <?php } elseif ($_smarty_tpl->tpl_vars['buttonType']->value == "print") {?>
              <?php $_smarty_tpl->_assignInScope('buttonType', "cancel");
?>
              <?php $_smarty_tpl->_assignInScope('buttonIcon', "print");
?>
              <?php $_smarty_tpl->_assignInScope('iconColor', "icon-white");
?>
              <?php $_smarty_tpl->_assignInScope('buttonClass', "btn btn-primary");
?>
	      <?php }?>

        <?php if ($_smarty_tpl->tpl_vars['buttonAttrs']->value['decoration'] == "secundary") {?>
          <?php $_smarty_tpl->_assignInScope('buttonClass', "btn btn-info");
?>
          <?php $_smarty_tpl->_assignInScope('iconColor', "icon-white");
?>
        <?php } elseif ($_smarty_tpl->tpl_vars['buttonAttrs']->value['decoration'] == "success") {?>
          <?php $_smarty_tpl->_assignInScope('buttonClass', "btn btn-success");
?>
          <?php $_smarty_tpl->_assignInScope('iconColor', "icon-white");
?>
        <?php } elseif ($_smarty_tpl->tpl_vars['buttonAttrs']->value['decoration'] == "warning") {?>
          <?php $_smarty_tpl->_assignInScope('buttonClass', "btn btn-warning");
?>
          <?php $_smarty_tpl->_assignInScope('iconColor', "icon-white");
?>
        <?php } elseif ($_smarty_tpl->tpl_vars['buttonAttrs']->value['decoration'] == "danger") {?>
          <?php $_smarty_tpl->_assignInScope('buttonClass', "btn btn-danger");
?>
          <?php $_smarty_tpl->_assignInScope('iconColor', "icon-white");
?>
        <?php } elseif ($_smarty_tpl->tpl_vars['buttonAttrs']->value['decoration'] == "inverse") {?>
          <?php $_smarty_tpl->_assignInScope('buttonClass', "btn btn-inverse");
?>
          <?php $_smarty_tpl->_assignInScope('iconColor', "icon-white");
?>
        <?php }?>

<?php $_smarty_tpl->_assignInScope('buttonActionUrl', ((string)$_smarty_tpl->tpl_vars['form']->value['baseUrl']));
if ($_smarty_tpl->tpl_vars['buttonAttrs']->value['action'] != '') {?>
	<?php $_smarty_tpl->_assignInScope('buttonActionUrl', ((string)$_smarty_tpl->tpl_vars['form']->value['baseUrl'])."&action=".((string)$_smarty_tpl->tpl_vars['buttonAttrs']->value['action']));
}
if ($_smarty_tpl->tpl_vars['buttonAttrs']->value['event'] != '') {?>
	<?php $_smarty_tpl->_assignInScope('buttonActionUrl', ((string)$_smarty_tpl->tpl_vars['form']->value['baseUrl'])."&event=".((string)$_smarty_tpl->tpl_vars['buttonAttrs']->value['event']));
}?>

<?php echo '<script'; ?>
>
	$(document).ready(function() {

		//Handle confirm message
		var confirmMessage = '';
		<?php $_tmp_array = isset($_smarty_tpl->tpl_vars['buttonAttrs']) ? $_smarty_tpl->tpl_vars['buttonAttrs']->value : array();
if (!is_array($_tmp_array) || $_tmp_array instanceof ArrayAccess) {
settype($_tmp_array, 'array');
}
$_tmp_array['confirm'] = smarty_modifier_replace($_smarty_tpl->tpl_vars['buttonAttrs']->value['confirm'],'"',"'");
$_smarty_tpl->_assignInScope('buttonAttrs', $_tmp_array);
?>
		<?php if ($_smarty_tpl->tpl_vars['buttonAttrs']->value['confirm'] != 'false' && $_smarty_tpl->tpl_vars['buttonAttrs']->value['confirm'] != '') {?>			
			<?php $_smarty_tpl->_assignInScope('keywords', explode("\n",$_smarty_tpl->tpl_vars['buttonAttrs']->value['confirm']));
?>
			<?php $_smarty_tpl->_assignInScope('firstChunk', 1);
?>
			<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['keywords']->value, 'keyword');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['keyword']->value) {
?>
				<?php if ($_smarty_tpl->tpl_vars['firstChunk']->value == 1) {?>
					confirmMessage = "<?php echo $_smarty_tpl->tpl_vars['keyword']->value;?>
";
					<?php $_smarty_tpl->_assignInScope('firstChunk', 0);
?>
				<?php } else { ?>
					confirmMessage = confirmMessage + "\n<?php echo $_smarty_tpl->tpl_vars['keyword']->value;?>
";
				<?php }?>
			<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>
		
		<?php } else { ?>
				confirmMessage = "<?php echo $_smarty_tpl->tpl_vars['buttonAttrs']->value['confirm'];?>
";
		<?php }?>
		
		if (confirmMessage!='' && confirmMessage!='false')
			__kuink.controlAddKey('<?php echo $_smarty_tpl->tpl_vars['_idContext']->value;?>
','<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
', '<?php echo $_smarty_tpl->tpl_vars['buttonAttrs']->value['id'];?>
', 'confirm', confirmMessage);
		
		
		$("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
 #<?php echo $_smarty_tpl->tpl_vars['buttonAttrs']->value['id'];?>
").attr("onclick", function() {
			return "javascript: result = setFormAction_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
('<?php echo $_smarty_tpl->tpl_vars['buttonActionUrl']->value;?>
', '', '<?php echo $_smarty_tpl->tpl_vars['buttonType']->value;?>
', '<?php echo $_smarty_tpl->tpl_vars['buttonAttrs']->value['id'];?>
', false);";
		});
	});
<?php echo '</script'; ?>
>

<?php $_smarty_tpl->_assignInScope('disabledClass', '');
$_smarty_tpl->_assignInScope('disabledAttr', '');
if ($_smarty_tpl->tpl_vars['buttonAttrs']->value['disabled'] == "true") {?>
	<?php $_smarty_tpl->_assignInScope('disabledClass', "disabled");
?>
	<?php $_smarty_tpl->_assignInScope('disabledAttr', "disabled");
}?>

<button type="<?php echo $_smarty_tpl->tpl_vars['buttonType']->value;?>
" class="btn-flat <?php echo $_smarty_tpl->tpl_vars['buttonPrintableClass']->value;?>
 <?php echo $_smarty_tpl->tpl_vars['buttonDefaultClass']->value;?>
 <?php echo $_smarty_tpl->tpl_vars['buttonClass']->value;?>
 <?php echo $_smarty_tpl->tpl_vars['disabledClass']->value;?>
" <?php echo $_smarty_tpl->tpl_vars['disabledAttr']->value;?>
 id="<?php echo $_smarty_tpl->tpl_vars['buttonAttrs']->value['id'];?>
" onclick=""><?php if ($_smarty_tpl->tpl_vars['buttonIcon']->value != '') {?><i class="fa fa-<?php echo $_smarty_tpl->tpl_vars['buttonIcon']->value;?>
 <?php echo $_smarty_tpl->tpl_vars['iconColor']->value;?>
"></i><?php }?>&nbsp;<?php echo $_smarty_tpl->tpl_vars['buttonAttrs']->value['label'];?>
</button>
<?php }
}
