<!-- BEGIN main -->
<center>
<form action="{action_url}" name="form1" method="post" enctype="multipart/form-data">
<br>
<input type="hidden" name="return_to_path" value="{return_to_path}" />



<div style="border: 1px solid rgb(153, 153, 153); position: relative; width: 90%; height: 100%; z-index: 0; height: 400px;">

<table width="100%" height="100%" border="0" cellspacing="0" cellpading="0" class="row_off" style="z-index: 0; visibility: visible;">
	<tbody> <tr height="25">
		<th id="tab1" class="activetab" onclick="javascript:tab.display(1);"><a href="#" tabindex="0" accesskey="1" onfocus="tab.display(1);" onclick="tab.display(1); return(false);">{lang_upload}</a></th>
	</tr>
	<tr>
		<td colspan="3" valign="top">


<!-- BEGIN OF MAIN CONTENT -->
			<div id="tabcontent1" class="inactivetab">
				<table width="100%" align="center" class="row_on" border="0">
					<tbody>
					<tr class="th">
						<td colspan="3" class="td_left">
							<b>{lang_upload_files}<b>
						</td>
					</tr>
					<tr class="th">
						<td width="45%" align="center">
							<b>{lang_filename}<b>
						</td>
						<td width="20%" align="center">
							<b>{lang_prefix}<b>
						</td>
						<td width="20%" align="center">
							<b>{lang_type}<b>
						</td> 
						<td width="15%" align="center">
							&nbsp;
						</td>
					</tr>
					<tr class="row_off" id="attach">
						<td class="td_left">
							<input name="file0" type=file size="50">
						</td>

						<td align="center">
							{file_prefix0}
						</td>
						
						<td align="center">
							{file_type0}
						</td>
						
						<td align="center">
							<span class="lk" onclick="removeUpload(0)">{lang_strremove}</span>
						</td>
					</tr>
				</table>
				<span onclick="addNewUpload('{lang_strremove}')" class="lk">{lang_upload_anotherfile}</span>
			</div>

<!-- END OF MAIN CONTENT -->
		</td>
	</tr>

	<tr>
		<td colspan="4" align="center" height="1%">
			<!-- input style="width: 50px;" name="is_global" value="{lang_ok}"  onclick="(document.forms[0].formvar['task']).value='ok'; document.forms[0].submit();" type="button" -->
			<input style="width: 50px;" value="{lang_uploadb}" name="ok" type="submit">
			<input style="width: 50px;" value="{lang_cancelb}" onclick="location=document.forms[0].return_to_path.value;" type="button">
		</td>
	</tr>

</table>

</div>
</form>
</center>
<!-- END main -->

