<!-- BEGIN main -->
<center>
<form action="{action_url}" name="fileprop" method="post">
<br>
<input type="hidden" name="return_to_path" value="{return_to_path}" />
<input type="hidden" name="formvar[full_filename]" value="{full_filename}" />
<input type="hidden" name="formvar[task]" value="" />
<input type="hidden" name="formvar[file_id]" value="{file_id}" />



<div style="border: 1px solid rgb(153, 153, 153); position: relative; width: 500px; height: 100%; z-index: 0; height: 400px;">

<table width="100%" height="100%" border="0" cellspacing="0" cellpading="0" class="row_off" style="z-index: 0; visibility: visible;">
	<tbody> <tr height="25">
		<th id="tab1" class="activetab" onclick="javascript:tab.display(1);"><a href="#" tabindex="0" accesskey="1" onfocus="tab.display(1);" onclick="tab.display(1); return(false);">{lang_general}</a></th>
		<!-- BEGIN perm_tabs -->
		<th id="tab2" class="activetab" onclick="javascript:tab.display(2);"><a href="#" tabindex="0" accesskey="2" onfocus="tab.display(2);" onclick="tab.display(2); return(false);">{lang_read_permissions}</a></th>
		<th id="tab3" class="activetab" onclick="javascript:tab.display(3);"><a href="#" tabindex="0" accesskey="3" onfocus="tab.display(3);" onclick="tab.display(3); return(false);">{lang_write_permissions}</a></th>
		<!-- END perm_tabs -->
		<!-- BEGIN custom_tabs -->
		<th id="tab4" class="activetab" onclick="javascript:tab.display(4);"><a href="#" tabindex="0" accesskey="4" onfocus="tab.display(4);" onclick="tab.display(4); return(false);">{lang_custom}</a></th>
		<!-- END custom_tabs -->
	</tr>
	<tr>
		<td colspan="{numofcols}" valign="top">


<!-- BEGIN OF MAIN CONTENT -->
			<div id="tabcontent1" class="inactivetab">
				<table width="100%" align="center" class="row_on" border="0">
					<tbody><tr class="th">
						<td width="50%" class="td_left">
							<b>{lang_general_properties}</b>
						</td>
						<td class="td_right">
							&nbsp;
						</td>
					</tr>
					<tr class="row_off">
						<td width="50%" class="td_left">
							{lang_filename}:
						</td>
						<td width="50%" class="td_right">
							<input type="text" size="30" name="formvar[filename]" value="{value_filename}">
						</td>
					</tr>
					<tr class="row_on">
						<td width="50%" class="td_left">
							{lang_filelocation}:
						</td>
						<td width="50%" class="td_right">
							{value_filelocation}
						</td>
					</tr>
					<tr class="row_off">
						<td width="50%" class="td_left">
							{lang_filetype}:
						</td>
						<td width="50%" class="td_right">
							{value_filetype}
						</td>
					</tr>
					<tr class="row_on">
						<td width="50%" class="td_left">
							{lang_owner}:
						</td>
						<td width="50%" class="td_right">
							{value_owner}
						</td>
					</tr>
					<tr class="row_off">
						<td width="50%" class="td_left">
							{lang_proper_id}:
						</td>
						<td width="50%" class="td_right">
							{value_proper_id}
						</td>
					</tr>

				</table>
				<table>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
				</table>
				<table width="100%" border="0" cellspacing="0" cellpading="1">
					<tr class="th">
						<td width="50%" class="td_left">
							<b>{lang_history}</b>
						</td>
						<td class="td_right">
							&nbsp;
						</td>
					</tr>
					<tr class="row_off">
						<td class="td_left">
							{lang_datetime_created}:
						</td>
						<td class="td_right">
							{value_datetime_created}
						</td>
					</tr>
					<tr class="row_off">
						<td class="td_left">
							{lang_datetime_modified}:
						</td>
						<td class="td_right">
							{value_datetime_modified}
						</td>
					</tr>
					<tr class="row_on">
						<td class="td_left">
							{lang_filehistory}:
						</td>
						<td class="td_right">
							<a href="{value_filehistory}">{lang_view_history}</a>
						</td>
					</tr>
				</table>
				<table>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
				</table>
				<table width="100%" border="0" cellspacing="0" cellpading="1">
					<tr class="th">
						<td width="50%" class="td_left">
							<b>{lang_comment}:</b>
						</td>
						<td class="td_right">
							&nbsp;
						</td>
					</tr>
					<tr class="row_off">
						<td class="td_left" colspan="2">
							<blockquote>
								<textarea name="formvar[comment]">{value_comment}</textarea>
							</blockquote>
						</td>
					</tr>
				</table>
				<table>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
				</table>
				<!-- BEGIN sharing -->
				<table width="100%" border="0" cellspacing="0" cellpading="1">
					<tr class="th">
						<td width="50%" class="td_left">
							<b>{lang_sharing}<b>
						</td>
						<td class="td_right">
							&nbsp;
						</td>
					</tr>
					<tr class="row_off">
						<td colspan="2" class="td_left">
							<input id='sharing_checkbox' type='checkbox' name='formvar[shared]' value='Y' onClick="change_sharing()"{sharing_checked}>{lang_activate_sharing}
						</td>
					</tr>
				</table>
				<!-- END sharing -->
			</div>

			<!-- BEGIN permissions -->
			<!-- The code for Read Permissions Tab -->

			<div id="tabcontent2" class="inactivetab">
				<table width="100%" border="0" cellspacing="0" cellpading="1">
					<tr class="th">
						<td width="50%" class="td_left">
							<b>{lang_auth_groups}<b>
						</td>
						<td class="td_right">
							&nbsp;
						</td>
					</tr>
					<tr class="row_off">
						<td colspan="2" class="td_left">
							{select_r_auth_groups}
						</td>
					</tr>
				</table>
				<table>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
				</table>
				<table width="100%" border="0" cellspacing="0" cellpading="1">
					<tr class="th">
						<td width="50%" class="td_left">
							<b>{lang_auth_users}<b>
						</td>
						<td class="td_right">
							&nbsp;
						</td>
					</tr>
					<tr class="row_off">
						<td colspan="2" class="td_left">
							{select_r_auth_users}
						</td>
					</tr>
				</table>
			</div>

			<!-- The code for Write Permissions Tab -->

			<div id="tabcontent3" class="inactivetab">
				<table width="100%" border="0" cellspacing="0" cellpading="1">
					<tr class="th">
						<td width="50%" class="td_left">
							<b>{lang_auth_groups}<b>
						</td>
						<td class="td_right">
							&nbsp;
						</td>
					</tr>
					<tr class="row_off">
						<td colspan="2" class="td_left">
							{select_w_auth_groups}
						</td>
					</tr>
				</table>
				<table>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
				</table>
				<table width="100%" border="0" cellspacing="0" cellpading="1">
					<tr class="th">
						<td width="50%" class="td_left">
							<b>{lang_auth_users}<b>
						</td>
						<td class="td_right">
							&nbsp;
						</td>
					</tr>
					<tr class="row_off">
						<td colspan="2" class="td_left">
							{select_w_auth_users}
						</td>
					</tr>
				</table>
			</div>
			<!-- END permissions -->

			<!-- BEGIN custom -->
			<div id="tabcontent4" class="inactivetab">
				<table width="100%" border="0" cellspacing="0" cellpading="1">
					<!-- BEGIN custom_row -->
					<tr class="th">
						<!-- BEGIN custom_data -->
						<td {tdopts}>
							{tdcontent}&nbsp;
						</td>
						<!-- END custom_data -->
					</tr>
					<!-- END custom_row -->
				</table>
			</div>
			<!-- END custom -->


<!-- END OF MAIN CONTENT -->
		</td>
	</tr>

	<tr>
		<td colspan="4" align="center" height="1%">
			<!-- input style="width: 50px;" name="is_global" value="{lang_ok}"  onclick="(document.forms[0].formvar['task']).value='ok'; document.forms[0].submit();" type="button" -->
			<input style="width: 50px;" value="{lang_ok}" name="ok" type="submit">
			<input style="width: 50px;" value="{lang_apply}" name="apply" type="submit">
			<input style="width: 50px;" value="{lang_close}" onclick="location=document.forms[0].return_to_path.value;" type="button">
		</td>
	</tr>

</table>

</div>
</form>
</center>
<!-- END main -->

