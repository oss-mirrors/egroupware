<!-- $Id$ -->
<script language="JavaScript">
	self.name="first_Window";
	function abook()
	{
		Window1=window.open('{addressbook_link}',"Search","width=800,height=600,toolbar=no,scrollbars=yes,resizable=yes");
	}
</script>

<center>
{bill_message}
<form method="POST" name="app_form" action="{actionurl}">
<table width="97%" border="0" cellspacing="2" cellpadding="2">

<!-- BEGIN book -->
	<tr bgcolor="{row_off}">
		<td><input type="button" value="{lang_address}" onClick="abook();"></td>
		<td><input type="hidden" name="abid" value="{abid}">
			<input type="text" name="name" size="50" value="{name}" readonly></td>
	</tr>
	<tr height="5">
		<td>&nbsp;</td>
	</tr>
	<tr bgcolor="{row_on}">
		<td>{lang_select_tax}:</td>
		<td><input type="text" name="prefs[tax]" value="{tax}" size="6" maxlength="6">&nbsp;%</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td valign="top">{lang_bill}:</td>
		<td><input type="hidden" name="prefs[oldbill]" value="{oldbill}">{bill}</td>
	</tr>
	<tr height="5">
		<td>&nbsp;</td>
	</tr>
	<tr bgcolor="{th_bg}">
		<td colspan="2"><b>{lang_layout}</b></td>
	</tr>
	<tr bgcolor="{row_on}">
		<td>{lang_select_font}:</td>
		<td><select name="prefs[ifont]">{ifont}</select></td>
	</tr>
	<tr bgcolor="{row_off}">
		<td>{lang_select_mysize}:</td>
		<td><select name="prefs[mysize]">{mysize}</select></td>
	</tr>
	<tr bgcolor="{row_on}">
		<td>{lang_select_allsize}:</td>
		<td><select name="prefs[allsize]">{allsize}</select></td>
	</tr>
	<tr height="5">
		<td>&nbsp;</td>
	</tr>
<!-- END book -->

<!-- BEGIN all -->
	<tr bgcolor="{th_bg}">
		<td colspan="2"><b>{lang_notifications}</b></td>
	</tr>
	<tr bgcolor="{row_off}">
		<td>{lang_notify_mstone}:</td>
		<td><input type="checkbox" name="prefs[notify_mstone]" value="True" {notify_mstone_selected}></td>
	</tr>
	<tr bgcolor="{row_on}">
		<td>{lang_notify_pro}:</td>
		<td><input type="checkbox" name="prefs[notify_pro]" value="True" {notify_pro_selected}></td>
	</tr>
	<tr bgcolor="{row_off}">
		<td>{lang_notify_assign}:</td>
		<td><input type="checkbox" name="prefs[notify_assign]" value="True" {notify_assign_selected}></td>
	</tr>
	<tr bgcolor="{row_off}">
		<td>{lang_homepage_display}:</td>
		<td><input type="checkbox" name="prefs[homepage_display]" value="True" {homepage_display_selected}></td>
	</tr>
	<tr valign="bottom" height="50">
		<td align="left">
			<input type="submit" name="save" value="{lang_save}">
		</td>
		<td align="right">
			<input type="submit" name="done" value="{lang_done}"></td>
	</tr>
</table>
</form>
</center>
<!-- END all -->
