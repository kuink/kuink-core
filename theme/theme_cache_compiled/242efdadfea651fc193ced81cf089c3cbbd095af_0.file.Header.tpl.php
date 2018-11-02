<?php
/* Smarty version 3.1.30, created on 2018-11-02 14:31:20
  from "/opt/kuink-dev/kuink-bridge-moodle/theme/adminlte/ui/control/form/Header.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5bdc5fb8bfa006_45154754',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '242efdadfea651fc193ced81cf089c3cbbd095af' => 
    array (
      0 => '/opt/kuink-dev/kuink-bridge-moodle/theme/adminlte/ui/control/form/Header.tpl',
      1 => 1517503583,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5bdc5fb8bfa006_45154754 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_block_translate')) require_once '/opt/kuink-dev/kuink-core/lib/tools/smarty/plugins/block.translate.php';
if ($_smarty_tpl->tpl_vars['insideHeader']->value == 1) {?>
	</div>
<?php }?>

<div id="<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['fieldGuid']->value;?>
" name="<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['fieldName']->value;?>
" class="page-header" style="clear:both">
	<?php if ($_smarty_tpl->tpl_vars['field']->value['attributes']['collapsible'] == 'true') {?>
		<?php echo '<script'; ?>
>
			$(document).ready(function(){
				var showText = $("#showHide_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['fieldGuid']->value;?>
").attr("showText");
				var hideText = $("#showHide_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['fieldGuid']->value;?>
").attr("hideText");

				<?php if ($_smarty_tpl->tpl_vars['field']->value['attributes']['collapsed'] == 'true') {?>
					$("#showHide_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['fieldGuid']->value;?>
").html(showText);
				<?php } else { ?>
					$("#showHide_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['fieldGuid']->value;?>
").html(hideText);
				<?php }?>
			});

			function showHideHeader_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['fieldGuid']->value;?>
() {
				var showText = $("#showHide_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['fieldGuid']->value;?>
").attr("showText");
				var hideText = $("#showHide_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['fieldGuid']->value;?>
").attr("hideText");

				if( $("#head_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['fieldGuid']->value;?>
").css("display") == "block") {
					$("#showHide_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['fieldGuid']->value;?>
").html(showText);
					$("#head_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['fieldGuid']->value;?>
").fadeOut();
				} else {
					$("#showHide_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['fieldGuid']->value;?>
").html(hideText);
					$("#head_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['fieldGuid']->value;?>
").fadeIn("slow");
				}

			}
		<?php echo '</script'; ?>
>
	<?php }?>

	<?php $_smarty_tpl->_assignInScope('styleDisplay', 'block');
?>
	<?php if ($_smarty_tpl->tpl_vars['field']->value['attributes']['collapsed'] == 'true' && $_smarty_tpl->tpl_vars['field']->value['attributes']['collapsible'] == 'true') {?>
		<?php $_smarty_tpl->_assignInScope('styleDisplay', 'none');
?>
	<?php }?>
	<h3><?php echo $_smarty_tpl->tpl_vars['field']->value['attributes']['label'];?>
 <?php if ($_smarty_tpl->tpl_vars['field']->value['attributes']['collapsible'] == 'true') {?>
		<small><a id="showHide_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['fieldGuid']->value;?>
" showText="<?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('translate', array('app'=>"framework"));
$_block_repeat1=true;
echo smarty_block_translate(array('app'=>"framework"), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>
show<?php $_block_repeat1=false;
echo smarty_block_translate(array('app'=>"framework"), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
" hideText="<?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('translate', array('app'=>"framework"));
$_block_repeat1=true;
echo smarty_block_translate(array('app'=>"framework"), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>
hide<?php $_block_repeat1=false;
echo smarty_block_translate(array('app'=>"framework"), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
" href="javascript:void(0);" onmousedown='showHideHeader_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['fieldGuid']->value;?>
();'></a></small>
	<?php }?></h3>
</div>

<div id="head_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['fieldGuid']->value;?>
" name="head_<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['fieldName']->value;?>
" style="display:<?php echo $_smarty_tpl->tpl_vars['styleDisplay']->value;?>
">
<?php }
}
