<!-- BEGIN pre_block -->
<form name="filterform" action="{form_action}" method="post">
	<table cellpadding="0" cellspacing="0" style="border:solid 1px #cccccc">
		<tr>
			<td align="center">{field_label}:</td>
			<td align="center">{operator_label}:</td>
			<td align="center">{value_label}:</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
<!-- END pre_block -->
<!-- BEGIN column_block -->
		<tr>
			<td align="center" style="padding-left:20px;">
				<select name="field{element}">
				{fields}
				</select>
			</td>
			<td align="center" style="padding-left:20px;">
				<select name="operator{element}">
				{operators}
				</select>
			</td>
			<td align="center" style="padding-left:20px;">
				<input type="text" name="value{element}" value="{value}"/>
			</td>
		</tr>
<!-- END column_block -->
<!-- BEGIN post_block -->
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td align="right">{name_label}:</td>
			<td align="center" style="padding-left:20px;">
				<input type="text" name="filtername" value="{filtername}"/>
			</td>
			<td align="center" style="padding-left:20px;">
				<input type="submit" name="submit" value="{submit}"/>
			</td>
		</tr>
		<tr height="50">
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" align="left">
				<input type="hidden" name="listurl" value="{list_url}"/>
				<input type="hidden" name="deleteurl" value="{delete_url}"/>
				<input type="submit" name="submit" value="{delete}" onClick="return onDelete();"/>
				<input type="submit" name="submit" value="{submit_exit}" onClick="document.filterform.action = document.filterform.listurl.value;"/>
			</td>
		</tr>
	</table>
</form>
<script language="javascript">
	<!--
		function onDelete()
		{
			if(document.filterform.filtername.value == 'sessionfilter')
			{
				alert('{sessionfilter_alert}');
				return false;
			}
			else
			{
				if(confirm('{delete_confirm}'))
				{
					document.filterform.action = document.filterform.deleteurl.value; 
					return true;
				} 
				else 
				{
					return false;
				}
			}
		}
	-->	
</script>

<!-- END post_block -->
