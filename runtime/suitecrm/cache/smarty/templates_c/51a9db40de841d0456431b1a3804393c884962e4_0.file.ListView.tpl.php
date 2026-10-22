<?php
/* Smarty version 4.5.6, created on 2026-10-22 17:09:46
  from 'C:\Users\yishe\hackathon-crm\runtime\suitecrm\include\SugarFields\Fields\Base\ListView.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.6',
  'unifunc' => 'content_6ad9d2dad6ad64_97573518',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '51a9db40de841d0456431b1a3804393c884962e4' => 
    array (
      0 => 'C:\\Users\\yishe\\hackathon-crm\\runtime\\suitecrm\\include\\SugarFields\\Fields\\Base\\ListView.tpl',
      1 => 1785480520,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_6ad9d2dad6ad64_97573518 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_checkPlugins(array(0=>array('file'=>'C:\\Users\\yishe\\hackathon-crm\\runtime\\suitecrm\\include\\Smarty\\plugins\\function.sugar_fetch.php','function'=>'smarty_function_sugar_fetch',),));
?>

<?php echo smarty_function_sugar_fetch(array('object'=>$_smarty_tpl->tpl_vars['parentFieldArray']->value,'key'=>$_smarty_tpl->tpl_vars['col']->value),$_smarty_tpl);?>

<?php }
}
