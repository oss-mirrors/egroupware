<!-- $Id$ -->
<script language="JavaScript">
	self.name="first_Window";
	function abook()
	{
		Window1=window.open('{addressbook_link}',"Search","width=800,height=600,toolbar=no,scrollbars=yes,resizable=yes");
	}
</script>
<br><br>
<p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>
<hr noshade width="98%" align="center" size="1">
<center>
<form method="POST" name="projects_form" action="{actionurl}">
<table width="80%" border="0" cellspacing="2" cellpadding="2">
	<tr>

<!-- BEGIN book -->

		<td>{lang_select_tax}:</td>
		<td><input type="text" name="prefs[tax]" value="{tax}" size="6" maxlength="6">&nbsp;%</td>
	</tr>
		<td>{lang_bill}:</td>
		<td>{bill}</td>
	</tr>
	<tr>
		<td><input type="button" value="{lang_address}" onClick="abook();"></td>
		<td><input type="hidden" name="abid" value="{abid}">
			<input type="text" name="name" size="50" value="{name}" readonly>&nbsp;&nbsp;&nbsp;{lang_select}</td>
	</tr>
</table>
<table width="39%" border="0" cellspacing="2" cellpadding="2">
	<tr valign="bottom">
		<td height="50" align="left">
			<input type="submit" name="submit" value="{lang_save}">
			</form>
		</td>
	</tr>
</table>
</center>

<!-- END book -->

<!-- BEGIN no -->

		<td>{lang_no_prefs}</td>
	</tr>
	<tr valign="bottom">
		<td height="50" align="left">
			<input type="submit" name="done" value="{lang_done}">
			</form>
		</td>
	</tr>
</table>
</center>

<!-- END no -->
