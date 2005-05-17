<!-- BEGIN main -->
<div style="overflow: auto; width: 100%; height: 100%;">
<center>
<form action="{action_url}" name="form1" method="post" enctype="multipart/form-data">
<input type="hidden" name="return_to_path" value="{return_to_path}" />
<input type="hidden" name="path" value="{path}" />


<br>

<div id="divMain">

<table width="100%" height="100%" border="0" cellspacing="0" cellpading="0" class="row_off" style="z-index: 0; visibility: visible;">
	<tbody> <tr>
		<td class="th" align="center"><b>{lang_upload}</b></td>
	</tr>
	<tr>
		<td colspan="4" valign="top">
<!-- BEGIN OF MAIN CONTENT -->
				<table width="100%" align="center" class="row_on" border="0">
					<tr class="row_on">
						<td colspan="4" class="td_left">
							<b>{path_information}</b><br>
							({lang_upload_max_filesize})
						</td>
					</tr>
					<tr class="th">
						<td width="50%" align="center">
							<b>{lang_filename}<b>
						</td>
						<!--{opt_col} -->
						
						<td width="20%" align="center">
							<b>{lang_prefix}<b>
						</td>
						<td width="20%" align="center">
							<b>{lang_type}<b>
						</td> 
						<td width="10%" align="center">
							&nbsp;
						</td>
					</tr>
					<tr class="row_on" id="attach">
						<td class="td_left">
							<input name="file0" type=file style="width: 150px;">
						</td>
						<!-- {opt_data_col} -->

						<td align="center">
							{file_prefix0}
						</td>
						
						<td align="center">
							{file_type0}
						</td>
						
						<td align="center">
							<input type="button" onclick="removeUpload(0)" value="{lang_strremove}">
						</td>
					</tr>
				</table>
				<span onclick="addNewUpload('{lang_strremove}')" class="lk">{lang_upload_anotherfile}</span>

<!-- END OF MAIN CONTENT -->
		</td>
	</tr>

	<tr>
<!-- BEGIN browse_fc -->
		<td colspan="4">
<!-- BEGIN OF MAIN CONTENT -->
			<div>
				<table width="100%" align="center" class="row_on" border="0">
					<tr class="th">
						<td width="50%" align="center">
							<b>{lang_filename}<b>
						</td>
						<!--{opt_col} -->
						
						<td width="20%" align="center">
							<b>{lang_prefix}<b>
						</td>
						<td width="20%" align="center">
							<b>{lang_type}<b>
						</td> 
						<td width="10%" align="center">
							&nbsp;
						</td>
					</tr>
					<tr class="row_on" id="attachFC">
						<td class="td_left">
							<nobr><input name="fcfile0" type="text" id="fcfile0" style="width: 150px;"><input type="button" value="{lang_strfromfilescenter}" onClick="fromFilescenter('fcfile0')"></nobr>
						</td>
						<!-- {opt_data_col} -->

						<td align="center">
							{file_fcprefix0}
						</td>
						
						<td align="center">
							{file_fctype0}
						</td>
						
						<td align="center">
							<input type="button" onclick="removeFCUpload(0)" value="{lang_strremove}">
						</td>
					</tr>
				</table>
				<span onclick="addNewFCUpload('{lang_strremove}','{lang_strfromfilescenter}')" class="lk">{lang_upload_anotherfile}</span>
			</div>

<!-- END OF MAIN CONTENT -->
		</td>
	</tr>
<!-- END browse_fc -->
	<tr>
		<td colspan="4" align="right" height="1%">
			<input style="width: 50px;" value="{lang_cancelb}" onclick="window.close();" type="button" />
			<input style="width: 50px;" value="{lang_uploadb}" name="ok" type="submit" onclick="fcShowProgress()" />

		</td>
	</tr>

</table>

</div>

</form>
<br />
<div id="fcProgress" style="visibility: hidden;"><img src="{fc_progress_path}" /></div>
<script language="javascript">
	function fcShowProgress()
	{
		var fcProgress = Element('fcProgress');
		fcProgress.style.visibility = '';
	}
</script>
</center>
</div>
<script>
	document.body.style.padding = 0;
	document.body.style.margin = 0;
</script>
<!-- END main -->
