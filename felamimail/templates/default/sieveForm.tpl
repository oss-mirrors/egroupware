<!-- BEGIN header -->
<script language="JavaScript1.2">

function submitRuleList(action)
{
	document.rulelist.rulelist_action.value = action;
	//alert(document.rulelist.rulelist_action.value);
	document.rulelist.submit();
}

function createScript()
{
	var newscript = prompt('Please supply a name for your new script','');
	if (newscript)
	{
		document.addScript.newScriptName.value = newscript;
		document.addScript.submit();
	}
}

</script>
<center>
<i>Scripts available for this account.</i><br>
<br>
<form method='post' action='{action_add_script}' name='addScript'>
<table border="1" width="95%">
	<tr>
		<td colspan="5" style='text-align : right;'>
			<a href="javascript:createScript();">{lang_add_script}</a>
		</td>
	</tr>
	{scriptrows}
</table>
<input type='hidden' name='newScriptName'>
</form>
<br>
<hr width="95%">
<table border='0' width='100%'>
<tr>
<td>
{lang_rule}: <a href="javascript:submitRuleList('enable');">{lang_enable}</a> 
<a href="javascript:submitRuleList('disable');">{lang_disable}</a> 
<a href="javascript:submitRuleList('delete');">{lang_delete}</a>
</td>
<td style='text-align : right;'>
<a href="{url_add_rule}">{lang_add_rule}</a>
</td>
</tr>
<form name='rulelist' method='post' action='{action_rulelist}'>
<input type='hidden' name='rulelist_action' value='unset'>
<table width="100%" border="0" cellpadding="2" cellspacing="1">
	<thead>
		<tr>
			<th width="5%">&nbsp;</th>
			<th width="10%">Status</th>
			<th width="80%">{lang_rule}</th>
			<th width="5%">Order</th>
		</tr>
	</thead>
		{filterrows}
	<tbody>
	</tbody>
</table>
</form>
</center>
<!-- END header -->

<!-- BEGIN scriptrow -->
<tr>
	<td class="body">
		Script {scriptnumber}
	</td>
	<td class="body" align="right">
		{scriptname}
	</td>
	<td class="body" align="right">
		<a href={link_deleteScript}>{lang_delete}</a>
	</td>
	<td class="body" align="right">
		<a href={link_editScript}>{lang_edit}</a>
	</td>
	<td class="body" align="right">
		<a href={link_activateScript}>{lang_activate}</a>{active}
	</td>
</tr>
<!-- END scriptrow -->

<!-- BEGIN filterrow -->
<tr class="enabledrule" onmouseover="javascript:style.backgroundColor='#e5e5e5'" onmouseout="javascript:style.backgroundColor='#FFFFFF'" style="background-color: rgb(255, 255, 255);">
	<td>
		<input type="checkbox" name="ruleID[]" value="{ruleID}">
	</td>
	<td class="enabled">
		{filter_status}
	</td>
	<td>
		<a class="rule" href="{url_edit_rule}" onmouseover="window.status='Edit This Rule'; return true;" onmouseout="window.status='';">{filter_text}</a>
	</td>
	<td nowrap="nowrap">
		<a href="{url_increase}"><img src="{url_up}" alt="Move rule up" border="0" onmouseover="window.status='Move rule up'; return true;" onmouseout="window.status='';"></a>
		<a href="{url_decrease}"><img src="{url_down}" alt="Move rule down" border="0" onmouseover="window.status='Move rule down'; return true;" onmouseout="window.status='';"></a>
	</td>
</tr>
<!-- END filterrow -->