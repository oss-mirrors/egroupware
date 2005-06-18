<!-- BEGIN main -->
<div style="overflow: auto; width: 100%; height: 100%;">
<center>
<form action="{action_url}" name="form1" method="post" enctype="multipart/form-data">
<input type="hidden" name="return_to_path" value="{return_to_path}" />
<input type="hidden" name="path" value="{path}" />

<div style="display: none;">
{file_prefix0}
{file_type0}
</div>

<div id="divMain">

<table width="100%" height="100%" border="0" cellspacing="0" cellpading="0" class="row_off" style="z-index: 0; visibility: visible;">
	<tbody> <tr>
		<td class="th" align="center"><b>{lang_upload}</b></td>
	</tr>
	<tr>
		<td colspan="4" valign="top" align="right">
<!-- BEGIN OF MAIN CONTENT -->
				<table width="100%" align="center" class="row_on" border="0">
					<tbody id="attach">
						<tr class="row_on">
							<td colspan="4" class="td_left">
								<b>{path_information}</b><br>
								({lang_upload_max_filesize})
							</td>
						</tr>
						{row_title}
					</tbody>
				</table>
				<input type="button" onclick="addNewUpload({upload_options0})" value="{lang_upload_anotherfile}">

<!-- END OF MAIN CONTENT -->
		</td>
	</tr>

	<tr>
<!-- BEGIN browse_fc -->
		<td colspan="4" align="right">
<!-- BEGIN OF MAIN CONTENT -->
			<div>
				<table width="100%" align="center" class="row_on" border="0">
					<tbody id="attachFC">
						{row_title}
					</tbody>
				</table>
				<input type="button" onclick="addNewUpload({upload_options1})" value="{lang_upload_anotherfile}">
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
	{extra_javascript}
</script>
<!-- END main -->

<!-- BEGIN row_title_block -->
<tr class="th">
	<td width="50%" align="center">
		<b>{lang_filename}<b>
	</td>
	{extra_cols}
	<td width="10%" align="center">
		&nbsp;
	</td>
</tr>
<!-- END row_title_block -->

<!-- BEGIN col_title_block -->
	<td width="20%" align="center">
		<b>{lang_caption}<b>
	</td> 
<!-- END col_title_block -->
