<?php
/* Smarty version 4.5.6, created on 2026-10-22 17:10:01
  from 'C:\Users\yishe\hackathon-crm\runtime\suitecrm\modules\Emails\templates\displayIndicatorField.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.6',
  'unifunc' => 'content_6ad9d2e9d894a0_11482405',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '0c8c0862065ccd31dd6bf9c4fc0b461ceac65883' => 
    array (
      0 => 'C:\\Users\\yishe\\hackathon-crm\\runtime\\suitecrm\\modules\\Emails\\templates\\displayIndicatorField.tpl',
      1 => 1785480520,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_6ad9d2e9d894a0_11482405 (Smarty_Internal_Template $_smarty_tpl) {
?>
<div class="email-indicator">
    <?php if ($_smarty_tpl->tpl_vars['bean']->value) {?>
        <?php if ($_smarty_tpl->tpl_vars['bean']->value['status'] == 'unread') {?>
            <div class="email-new"></div>
        <?php }?>
        <?php if ($_smarty_tpl->tpl_vars['bean']->value['is_imported'] == true && $_smarty_tpl->tpl_vars['bean']->value['inbound_email_record'] == $_REQUEST['inbound_email_record']) {?>
            <div class="email-imported"><span class="glyphicon glyphicon-ok"></span></div>
        <?php }?>
        <?php if ($_smarty_tpl->tpl_vars['bean']->value['flagged'] == 1) {?>
            <span class="email-flagged">!</span>
        <?php }?>
    <?php }?>
</div>
<?php }
}
