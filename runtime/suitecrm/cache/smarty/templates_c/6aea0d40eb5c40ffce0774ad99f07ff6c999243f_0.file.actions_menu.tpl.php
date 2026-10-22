<?php
/* Smarty version 4.5.6, created on 2026-10-22 17:09:49
  from 'C:\Users\yishe\hackathon-crm\runtime\suitecrm\themes\SuiteP\include\MySugar\tpls\actions_menu.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.6',
  'unifunc' => 'content_6ad9d2ddc85bb3_95069674',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '6aea0d40eb5c40ffce0774ad99f07ff6c999243f' => 
    array (
      0 => 'C:\\Users\\yishe\\hackathon-crm\\runtime\\suitecrm\\themes\\SuiteP\\include\\MySugar\\tpls\\actions_menu.tpl',
      1 => 1785480520,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_6ad9d2ddc85bb3_95069674 (Smarty_Internal_Template $_smarty_tpl) {
?><ul class="dropdown-menu tab-actions">
    <?php if (!$_smarty_tpl->tpl_vars['lock_homepage']->value) {?>

        <li>
            <input class="button addDashlets" type="button" name="Edit"  data-toggle="modal" data-target=".modal-add-dashlet" value="<?php echo $_smarty_tpl->tpl_vars['lblAddDashlets']->value;?>
">
        </li>
        <li>
            <input class="button addDashboard" type="button" name="Edit"  data-toggle="modal" data-target=".modal-add-dashboard" value="<?php echo $_smarty_tpl->tpl_vars['lblAddTab']->value;?>
">
        </li>
        <li>
            <input class="button addDashboard" type="button" name="Edit"  data-toggle="modal" data-target=".modal-edit-dashboard" value="<?php echo $_smarty_tpl->tpl_vars['app']->value['LBL_EDIT_TAB'];?>
">
        </li>
    <?php }?>
</ul>
<?php }
}
