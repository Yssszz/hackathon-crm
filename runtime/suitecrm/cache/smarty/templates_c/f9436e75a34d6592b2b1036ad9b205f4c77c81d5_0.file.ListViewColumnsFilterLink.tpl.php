<?php
/* Smarty version 4.5.6, created on 2026-10-22 17:09:46
  from 'C:\Users\yishe\hackathon-crm\runtime\suitecrm\include\ListView\ListViewColumnsFilterLink.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.6',
  'unifunc' => 'content_6ad9d2dad55ac7_28504993',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'f9436e75a34d6592b2b1036ad9b205f4c77c81d5' => 
    array (
      0 => 'C:\\Users\\yishe\\hackathon-crm\\runtime\\suitecrm\\include\\ListView\\ListViewColumnsFilterLink.tpl',
      1 => 1785480520,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_6ad9d2dad55ac7_28504993 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_checkPlugins(array(0=>array('file'=>'C:\\Users\\yishe\\hackathon-crm\\runtime\\suitecrm\\include\\Smarty\\plugins\\function.sugar_translate.php','function'=>'smarty_function_sugar_translate',),));
?>
<ul class="clickMenu selectmenu SugarActionMenu columnsFilterLink listViewLinkButton listViewLinkButton_<?php echo $_smarty_tpl->tpl_vars['action_menu_location']->value;?>
" onclick="columnsFilter.onOpen();">
    <li class="sugar_action_button">
        <a href="#" class="glyphicon glyphicon-th-list" data-toggle="modal" data-target=".modal-columns-filter" title='<?php echo smarty_function_sugar_translate(array('label'=>"LBL_COLUMN_CHOOSER"),$_smarty_tpl);?>
'></a>
    </li>
</ul>
<?php }
}
