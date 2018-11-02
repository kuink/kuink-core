<?php
/* Smarty version 3.1.30, created on 2018-11-02 10:48:00
  from "/opt/kuink-dev/kuink-bridge-moodle/theme/adminlte/ui/control/grid/pick.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5bdc2b60c3ee68_77190875',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '2ed600636fc10fee37ca7dafc064c598251b2024' => 
    array (
      0 => '/opt/kuink-dev/kuink-bridge-moodle/theme/adminlte/ui/control/grid/pick.tpl',
      1 => 1495732824,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5bdc2b60c3ee68_77190875 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!-- The pick class is just to jquery select all the pick checkboxes and not the others-->
<input type="checkbox" class="input-medium neon-pick" id="<?php echo $_smarty_tpl->tpl_vars['id']->value;
echo $_smarty_tpl->tpl_vars['multiSeparator']->value;
echo $_smarty_tpl->tpl_vars['fieldAttrs']->value['name'];?>
" name="<?php echo $_smarty_tpl->tpl_vars['id']->value;
echo $_smarty_tpl->tpl_vars['multiSeparator']->value;
echo $_smarty_tpl->tpl_vars['fieldAttrs']->value['name'];?>
" onclick='<?php echo $_smarty_tpl->tpl_vars['onPick']->value;?>
' value="<?php echo $_smarty_tpl->tpl_vars['value']->value['value'];?>
"></input>
<?php }
}
