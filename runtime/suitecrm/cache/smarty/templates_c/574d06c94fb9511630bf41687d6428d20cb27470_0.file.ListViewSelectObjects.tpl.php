<?php
/* Smarty version 4.5.6, created on 2026-10-22 17:09:46
  from 'C:\Users\yishe\hackathon-crm\runtime\suitecrm\themes\SuiteP\include\ListView\ListViewSelectObjects.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.6',
  'unifunc' => 'content_6ad9d2dabde359_69001472',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '574d06c94fb9511630bf41687d6428d20cb27470' => 
    array (
      0 => 'C:\\Users\\yishe\\hackathon-crm\\runtime\\suitecrm\\themes\\SuiteP\\include\\ListView\\ListViewSelectObjects.tpl',
      1 => 1785480520,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_6ad9d2dabde359_69001472 (Smarty_Internal_Template $_smarty_tpl) {
?><div class="selectedRecords label hidden"><?php echo $_smarty_tpl->tpl_vars['APP']->value['LBL_LISTVIEW_SELECTED_OBJECTS'];?>
</div><div class="selectedRecords value hidden"><?php echo $_smarty_tpl->tpl_vars['TOTAL_ITEMS_SELECTED']->value;?>
</div>
<input type='hidden' id='selectCountTop' name='selectCount[]' value='<?php echo $_smarty_tpl->tpl_vars['TOTAL_ITEMS_SELECTED']->value;?>
' />

<?php echo '<script'; ?>
>

    $(document).ready(function () {
        setInterval(function () {
            sListView.toggleSelected();
        }, 100);
    });

<?php echo '</script'; ?>
><?php }
}
