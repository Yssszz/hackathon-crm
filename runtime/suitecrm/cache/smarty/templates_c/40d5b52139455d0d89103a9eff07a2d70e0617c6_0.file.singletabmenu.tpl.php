<?php
/* Smarty version 4.5.6, created on 2026-10-22 17:10:01
  from 'C:\Users\yishe\hackathon-crm\runtime\suitecrm\themes\SuiteP\include\SubPanel\tpls\singletabmenu.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.6',
  'unifunc' => 'content_6ad9d2e9e54e01_96816538',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '40d5b52139455d0d89103a9eff07a2d70e0617c6' => 
    array (
      0 => 'C:\\Users\\yishe\\hackathon-crm\\runtime\\suitecrm\\themes\\SuiteP\\include\\SubPanel\\tpls\\singletabmenu.tpl',
      1 => 1785480520,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_6ad9d2e9e54e01_96816538 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_checkPlugins(array(0=>array('file'=>'C:\\Users\\yishe\\hackathon-crm\\runtime\\suitecrm\\include\\Smarty\\plugins\\function.sugar_getimage.php','function'=>'smarty_function_sugar_getimage',),));
?>


<?php echo '<script'; ?>
 type="text/javascript">
SUGAR.util.doWhen("typeof get_module_name != 'undefined'", function()
{
	SUGAR.subpanelUtils.currentSubpanelGroup = '<?php echo $_smarty_tpl->tpl_vars['startSubPanel']->value;?>
';

	SUGAR.subpanelUtils.subpanelMoreTab = '<?php echo $_smarty_tpl->tpl_vars['moreTab']->value;?>
';

	SUGAR.subpanelUtils.subpanelMaxSubtabs = '<?php echo $_smarty_tpl->tpl_vars['maxSubtabs']->value;?>
';

	SUGAR.subpanelUtils.subpanelHtml = new Array();

	SUGAR.subpanelUtils.loadedGroups = Array();
	SUGAR.subpanelUtils.loadedGroups.push('<?php echo $_smarty_tpl->tpl_vars['startSubPanel']->value;?>
');

	SUGAR.subpanelUtils.subpanelSubTabs = new Array();
	SUGAR.subpanelUtils.subpanelGroups = new Array();
<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['othertabs']->value, 'tab');
$_smarty_tpl->tpl_vars['tab']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['tab']->value) {
$_smarty_tpl->tpl_vars['tab']->do_else = false;
$_smarty_tpl->_assignInScope('notFirst', '0');?>
	SUGAR.subpanelUtils.subpanelGroups['<?php echo $_smarty_tpl->tpl_vars['tab']->value['key'];?>
'] = [<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['tab']->value['tabs'], 'subtab');
$_smarty_tpl->tpl_vars['subtab']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['subtab']->value) {
$_smarty_tpl->tpl_vars['subtab']->do_else = false;
if ($_smarty_tpl->tpl_vars['notFirst']->value != 0) {?>, <?php } else {
$_smarty_tpl->_assignInScope('notFirst', '1');
}?>'<?php echo $_smarty_tpl->tpl_vars['subtab']->value['key'];?>
'<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['otherMoreSubMenu']->value[$_smarty_tpl->tpl_vars['tab']->value['key']]['tabs'], 'subtab');
$_smarty_tpl->tpl_vars['subtab']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['subtab']->value) {
$_smarty_tpl->tpl_vars['subtab']->do_else = false;
?>, '<?php echo $_smarty_tpl->tpl_vars['subtab']->value['key'];?>
'<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>];
<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>

<?php $_smarty_tpl->_assignInScope('notFirst', '0');?>
	SUGAR.subpanelUtils.subpanelTitles = <?php echo $_smarty_tpl->tpl_vars['subpanelTitlesJSON']->value;?>
;

	SUGAR.subpanelUtils.tabCookieName = get_module_name() + '_sp_tab';

	SUGAR.subpanelUtils.showLinks = <?php echo $_smarty_tpl->tpl_vars['showLinks']->value;?>
;

	SUGAR.subpanelUtils.requestUrl = 'index.php?to_pdf=1&module=MySettings&action=LoadTabSubpanels&loadModule=<?php echo $_REQUEST['module'];?>
&record=<?php echo $_REQUEST['record'];?>
&subpanels=';
});
<?php echo '</script'; ?>
>


<?php if (!empty($_smarty_tpl->tpl_vars['sugartabs']->value)) {?>
<ul id="groupTabs" class="nav nav-tabs">
<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['sugartabs']->value, 'tab', false, 'i');
$_smarty_tpl->tpl_vars['tab']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['i']->value => $_smarty_tpl->tpl_vars['tab']->value) {
$_smarty_tpl->tpl_vars['tab']->do_else = false;
?>
	<li id="<?php echo $_smarty_tpl->tpl_vars['tab']->value['label'];?>
_sp_tab" class="<?php if ($_smarty_tpl->tpl_vars['tab']->value['type'] == 'current') {?>active<?php }?>">
		<a class="<?php echo $_smarty_tpl->tpl_vars['tab']->value['type'];?>
" href="javascript:SUGAR.subpanelUtils.loadSubpanelGroup('<?php echo $_smarty_tpl->tpl_vars['tab']->value['label'];?>
');"><?php echo $_smarty_tpl->tpl_vars['tab']->value['label'];?>
</a>
	</li>
<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);
if (!empty($_smarty_tpl->tpl_vars['moreMenu']->value)) {?>
	<li>
		<div id='MorePanelHandle' onmouseover='SUGAR.subpanelUtils.menu.tbspButtonMouseOver(this.id,"","",0);'>
			<?php echo smarty_function_sugar_getimage(array('name'=>"blank",'ext'=>".gif",'width'=>"16",'height'=>"16",'alt'=>$_smarty_tpl->tpl_vars['app_strings']->value['LBL_MORE'],'other_attributes'=>'border="0" '),$_smarty_tpl);?>

		</div>
	</li>
<?php }?>
</ul>
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="subpanelTabForm">
	<tr>
		<td>
<?php }?>

<?php if ($_smarty_tpl->tpl_vars['showLinks']->value == 'true') {?>
<table cellpadding="0" cellspacing="0" width='100%'>
	<tr height="20">
		<td class="subpanelSubTabBar" colspan="100" id="subpanelSubTabs">
			<table border="0" cellpadding="0" cellspacing="0" height="20" width="100%" class="subTabs">
				<tbody>
				<tr>
<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['subtabs']->value, 'tab');
$_smarty_tpl->tpl_vars['tab']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['tab']->value) {
$_smarty_tpl->tpl_vars['tab']->do_else = false;
if (!empty($_smarty_tpl->tpl_vars['notFirst']->value) && ($_smarty_tpl->tpl_vars['notFirst']->value != 0) && ($_smarty_tpl->tpl_vars['notFirst']->value != 1)) {?>
					<td width='1'> | </td>
<?php } else {
$_smarty_tpl->_assignInScope('notFirst', '2');
}?>
					<td nowrap="nowrap">
						<a href='#<?php echo $_smarty_tpl->tpl_vars['tab']->value['key'];?>
' class='subTabLink'><?php echo $_smarty_tpl->tpl_vars['tab']->value['label'];?>
</a>
					</td>
<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);
if (!empty($_smarty_tpl->tpl_vars['otherMoreSubMenu']->value[$_smarty_tpl->tpl_vars['moreSubMenuName']->value]['tabs'])) {?>
					<td nowrap="nowrap"> | &nbsp;<span class="subTabMore" id="MoreSub<?php echo $_smarty_tpl->tpl_vars['moreSubMenuName']->value;?>
PanelHandle" style="margin-left:2px; cursor: pointer; cursor: hand;" align="absmiddle" onmouseover="SUGAR.subpanelUtils.menu.tbspButtonMouseOver(this.id,'','',0);">&gt;&gt;</span></td>
<?php }?>
					<td width='100%'>&nbsp;</td>
				</tr>
				</tbody>
			</table>
		</td>
	</tr>
</table>
<?php }?>

<?php if (!empty($_smarty_tpl->tpl_vars['moreMenu']->value)) {?>
<div id="MorePanelMenu" class="menu">
<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['moreMenu']->value, 'tab');
$_smarty_tpl->tpl_vars['tab']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['tab']->value) {
$_smarty_tpl->tpl_vars['tab']->do_else = false;
?>
	<a href="javascript:SUGAR.subpanelUtils.loadSubpanelGroupFromMore('<?php echo $_smarty_tpl->tpl_vars['tab']->value['label'];?>
');" class="menuItem" id="<?php echo $_smarty_tpl->tpl_vars['tab']->value['label'];?>
_sp_mm" parentid="MorePanelMenu" onmouseover="hiliteItem(this,'yes'); closeSubMenus(this);" onmouseout="unhiliteItem(this);"><?php echo $_smarty_tpl->tpl_vars['tab']->value['label'];?>
</a>
<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
</div>
<?php }?>

<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['otherMoreSubMenu']->value, 'group');
$_smarty_tpl->tpl_vars['group']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['group']->value) {
$_smarty_tpl->tpl_vars['group']->do_else = false;
if (!empty($_smarty_tpl->tpl_vars['group']->value['tabs'])) {?>
<div id="MoreSub<?php echo $_smarty_tpl->tpl_vars['group']->value['key'];?>
PanelMenu" class="menu">
<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['group']->value['tabs'], 'subtab');
$_smarty_tpl->tpl_vars['subtab']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['subtab']->value) {
$_smarty_tpl->tpl_vars['subtab']->do_else = false;
?>
	<a href="#<?php echo $_smarty_tpl->tpl_vars['subtab']->value['key'];?>
" class="menuItem" parentid="MoreSub<?php echo $_smarty_tpl->tpl_vars['group']->value['key'];?>
PanelMenu" onmouseover="hiliteItem(this,'yes'); closeSubMenus(this);" onmouseout="unhiliteItem(this);"><?php echo $_smarty_tpl->tpl_vars['subtab']->value['label'];?>
</a>
<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
</div>
<?php }
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>


<?php }
}
