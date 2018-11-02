<?php
/* Smarty version 3.1.30, created on 2018-11-02 11:19:02
  from "/opt/kuink-dev/kuink-bridge-moodle/theme/adminlte/ui/control/form/Column.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5bdc32a664e436_16329777',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '3428e9b3a90e7b0c5c9919fc9df07f82d2125bd1' => 
    array (
      0 => '/opt/kuink-dev/kuink-bridge-moodle/theme/adminlte/ui/control/form/Column.tpl',
      1 => 1525358752,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5bdc32a664e436_16329777 (Smarty_Internal_Template $_smarty_tpl) {
if ($_smarty_tpl->tpl_vars['insideHeader']->value == 1) {?>
	</div>
<?php }
if (($_smarty_tpl->tpl_vars['insideColumn']->value == 1)) {?>
	</div>
<?php } else { ?>
	<div class="row">
<?php }?>

<?php if (($_smarty_tpl->tpl_vars['tabs']->value[$_smarty_tpl->tpl_vars['tabIndex']->value]['columns'][$_smarty_tpl->tpl_vars['currentColumnGroup']->value] > 0)) {?>
	<?php $_smarty_tpl->_assignInScope('columnWidth', 12/$_smarty_tpl->tpl_vars['tabs']->value[$_smarty_tpl->tpl_vars['tabIndex']->value]['columns'][$_smarty_tpl->tpl_vars['currentColumnGroup']->value]);
} else { ?>
	<?php $_smarty_tpl->_assignInScope('columnWidth', 12);
}?>

<?php if ($_smarty_tpl->tpl_vars['field']->value['attributes']['close'] == 'false') {?>
	<div class="col-md-<?php echo $_smarty_tpl->tpl_vars['columnWidth']->value;?>
">
<?php }?>

<?php }
}
