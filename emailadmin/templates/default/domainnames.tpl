<!-- BEGIN main -->
<center>
<form>
<table border="0" cellspacing="1" cellpading="0" width="95%">
<tr>
	<td width="10%" valign="top">
		<table border="0" cellspacing="1" cellpading="0" width="100%">
		{menu_rows}
		<tr>
			<td>
				&nbsp;
			</td>
		</tr>
		<tr bgcolor="{done_row_color}">
			<td>
				<a href="{done_link}">{lang_done}</a>
			</td>
		</tr>
		</table>
	</td>
	<td width="90%" valign="top">
		<table width="100%" cellspacing="1" cellpading="0" border="0">
		<tr bgcolor="{th_bg}">
			<td colspan="2">
				Domains we receive email for
			</td>
		</tr>
		<tr bgcolor="{bg_01}">
			<td width="50%" rowspan="5" align="center">
				{rcpt_selectbox}
			</td>
			<td width="50%" align="center">
				<input type="text" size="30">
			</td>
		</tr>
		<tr bgcolor="{bg_02}">
			<td width="50%" align="center">
				<input type="submit" value="<-- {lang_add}">
			</td>
		</tr>
		<tr bgcolor="{bg_01}">
			<td width="50%" align="center">
				<input type="checkbox" name="local_domain">{lang_add_to_local}
			</td>
		</tr>
		<tr bgcolor="{bg_02}">
			<td width="50%" align="center">
				&nbsp;
			</td>
		</tr>
		<tr bgcolor="{bg_01}">
			<td width="50%" align="center">
				<input type="submit" value="{lang_remove} -->">
			</td>
		</tr>
		<tr bgcolor="{th_bg}">
			<td colspan="2">
				Domains which email we handle local
			</td>
		</tr>
		<tr bgcolor="{bg_01}">
			<td width="50%" rowspan="4" align="center">
				{locals_selectbox}
			</td>
			<td width="50%" align="center">
				<input type="text" size="30">
			</td>
		</tr>
		<tr bgcolor="{bg_02}">
			<td width="50%" align="center">
				<input type="submit" value="<-- {lang_add}">
			</td>
		</tr>
		<tr bgcolor="{bg_01}">
			<td width="50%" align="center">
				&nbsp;
			</td>
		</tr>
		<tr bgcolor="{bg_02}">
			<td width="50%" align="center">
				<input type="submit" value="{lang_remove} -->">
			</td>
		</tr>
		</table>
	</td>
</tr>
</table>
</form>
</center>
<!-- END main -->

<!-- BEGIN menu_row -->
<tr bgcolor="{menu_row_color}">
	<td>
		<nobr><a href="{menu_link}">{menu_description}</a><nobr>
	</td>
</tr>
<!-- END menu_row -->

<!-- BEGIN menu_row_bold -->
<tr bgcolor="{menu_row_color}">
	<td>
		<nobr><b><a href="{menu_link}">{menu_description}</a></b><nobr>
	</td>
</tr>
<!-- END menu_row_bold -->

<!-- BEGIN selectbox_rcpthosts -->
<!-- END selectbox_rcpthosts -->

<!-- BEGIN selectbox_locals -->
<!-- END selectbox_locals -->
