<?php
/* Smarty version 3.1.30, created on 2018-11-02 14:31:20
  from "/opt/kuink-dev/kuink-bridge-moodle/theme/adminlte/ui/control/HtmlContainer.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5bdc5fb8b24353_90226918',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'c437992ed3f7ad5bccd745c75e0a4173c6c2365c' => 
    array (
      0 => '/opt/kuink-dev/kuink-bridge-moodle/theme/adminlte/ui/control/HtmlContainer.tpl',
      1 => 1511567056,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5bdc5fb8b24353_90226918 (Smarty_Internal_Template $_smarty_tpl) {
?>
<style>
    .form-horizontal h4 {
        margin-top: 15px;
     }
    .static-control-group {
        margin-top: 5px;
    }


#htmlBlock.well {
  padding: 0px 0px 15px 0px;
  border: none;
}


.htmlBlock {
  width: 100%;
  margin: 5px 0;
  border: 1px solid #E8E8E8;
}

.htmlBlock .htmlTitle::after  {
  content: "<?php echo $_smarty_tpl->tpl_vars['title']->value;?>
";
  position: relative;
  top: 1px;
  left: 1px;
  padding: 3px 7px;
  font-size: 13px;
  font-weight: bold;
  background-color: #f3f3f3;
  /**border: 1px solid #ddd;**/
  border: 1px solid #E8E8E8;
  color: #0072aa;
  -webkit-border-radius: 4px 0 4px 0;
  -moz-border-radius: 4px 0 4px 0;
  border-radius: 4px 0 4px 0;
}

</style>


<?php if ($_smarty_tpl->tpl_vars['title']->value != '') {?>
<div id="htmlBlock" class="htmlBlock">
  <div class="htmlTitle" style="display: block;"></div>
<?php }?>

<?php if ($_smarty_tpl->tpl_vars['border']->value == "true") {?>
	<?php $_smarty_tpl->_assignInScope('class', 'well' ,true);
}?>

<div class="<?php echo $_smarty_tpl->tpl_vars['class']->value;?>
" style="<?php echo $_smarty_tpl->tpl_vars['style']->value;?>
">
	<?php if ($_smarty_tpl->tpl_vars['label']->value != '') {?>
    <h4><?php echo $_smarty_tpl->tpl_vars['label']->value;?>
</h4>
  <?php }?>

  <?php echo $_smarty_tpl->tpl_vars['value']->value;?>

</div>

<?php if ($_smarty_tpl->tpl_vars['title']->value != '') {?>
</div>
<?php }
}
}
