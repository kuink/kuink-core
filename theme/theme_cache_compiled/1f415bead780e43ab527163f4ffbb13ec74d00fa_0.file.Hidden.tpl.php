<?php
/* Smarty version 3.1.30, created on 2018-11-02 14:31:25
  from "/opt/kuink-dev/kuink-bridge-moodle/theme/adminlte/ui/control/form/Hidden.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5bdc5fbd0a7aa8_20495732',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '1f415bead780e43ab527163f4ffbb13ec74d00fa' => 
    array (
      0 => '/opt/kuink-dev/kuink-bridge-moodle/theme/adminlte/ui/control/form/Hidden.tpl',
      1 => 1495732824,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5bdc5fbd0a7aa8_20495732 (Smarty_Internal_Template $_smarty_tpl) {
?>
<input type="hidden" id="<?php echo $_smarty_tpl->tpl_vars['fieldGuid']->value;?>
" name="<?php echo $_smarty_tpl->tpl_vars['fieldName']->value;?>
" value="<?php echo $_smarty_tpl->tpl_vars['field']->value['value'];?>
" />
<?php }
}
