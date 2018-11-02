<?php
/* Smarty version 3.1.30, created on 2018-11-02 14:31:25
  from "/opt/kuink-dev/kuink-bridge-moodle/theme/adminlte/ui/control/form/CodeEditor.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5bdc5fbd179593_28090604',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '0c6f4beb82e09a6398c0b02c0068ed3aa2294d91' => 
    array (
      0 => '/opt/kuink-dev/kuink-bridge-moodle/theme/adminlte/ui/control/form/CodeEditor.tpl',
      1 => 1527519400,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5bdc5fbd179593_28090604 (Smarty_Internal_Template $_smarty_tpl) {
?>

<link rel="stylesheet" href="kuink-core/lib/tools/codemirror/addon/hint/show-hint.css">
<link rel="stylesheet" href="kuink-core/lib/tools/codemirror/theme/ambiance.css">
<link rel="stylesheet" href="kuink-core/lib/tools/codemirror/lib/codemirror.css">
<?php echo '<script'; ?>
 src="kuink-core/lib/tools/codemirror/lib/codemirror.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="kuink-core/lib/tools/codemirror/addon/hint/show-hint.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="kuink-core/lib/tools/codemirror/addon/edit/closetag.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="kuink-core/lib/tools/codemirror/addon/hint/xml-hint.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="kuink-core/lib/tools/codemirror/mode/xml/xml.js"><?php echo '</script'; ?>
>



<style type="text/css">
  .CodeMirror { border: 1px solid #eee; width: 90%; height: auto }
  .CodeMirror-scroll { overflow-y: hidden; overflow-x: auto; }
</style>

<div class="controls">
	<textarea id="<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
" name="<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
" class="<?php echo $_smarty_tpl->tpl_vars['disabledClass']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['field']->value['value'];?>
</textarea>
</div>

<?php echo '<script'; ?>
>
      var <?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
_editor = CodeMirror.fromTextArea(document.getElementById("<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
"), {
            value: '',
            mode: 'application/xml',
            lineNumbers: true,
            autoCloseTags: true,
            viewportMargin: Infinity,
            foldGutter: true,
            gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"]
        });
      <?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
_editor.setOption("theme", "ambiance");

			__kuink.ajaxExecuteFunctionsBeforeLoad.push(() => {
					var codeEditorContent = <?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
_editor.getDoc().getValue();
					$("#<?php echo $_smarty_tpl->tpl_vars['_guid']->value;?>
 textarea#<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
").val(codeEditorContent);
			});
			__kuink.ajaxExecuteFunctionsAfterLoad.push(() => {
					<?php echo $_smarty_tpl->tpl_vars['fieldID']->value;?>
_editor.refresh();
			});
    <?php echo '</script'; ?>
>
<?php }
}
