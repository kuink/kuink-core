<?php
/* Smarty version 3.1.30, created on 2018-11-02 10:47:53
  from "/opt/kuink-dev/kuink-bridge-moodle/theme/adminlte/ui/control/form/Static.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5bdc2b591c54d1_61095454',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '11f16a4fd3335ade4f91b94a4c47db05e49d1d38' => 
    array (
      0 => '/opt/kuink-dev/kuink-bridge-moodle/theme/adminlte/ui/control/form/Static.tpl',
      1 => 1519748684,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5bdc2b591c54d1_61095454 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!-- <label class="control-label" for="<?php echo $_smarty_tpl->tpl_vars['fieldName']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['field']->value['value'];?>
</label> -->
<span><?php echo $_smarty_tpl->tpl_vars['field']->value['value'];?>
</span>
<input id="<?php echo $_smarty_tpl->tpl_vars['fieldGuid']->value;?>
" name="<?php echo $_smarty_tpl->tpl_vars['fieldName']->value;?>
" type="hidden" disabled="true" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['field']->value['original'], ENT_QUOTES, 'UTF-8', true);?>
"/>

<?php }
}
