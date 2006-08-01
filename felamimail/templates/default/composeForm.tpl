<!-- BEGIN header -->
<script language="JavaScript1.2">
	var folderSelectURL		="{folder_select_url}";
	var displayFileSelectorURL	="{file_selector_url}";
	var composeID			="{compose_id}";

	var activityImagePath		= '{ajax-loader}';


	self.focus();
  
	self.name="first_Window";
	
	function addybook() {
		Window1=window.open('{link_addressbook}',"{lang_search}","width=800,height=600,toolbar=no,scrollbars=yes,status=yes,resizable=yes");
	}
	
	function attach_window(url) {
		awin = window.open(url,"attach","width=500,height=400,toolbar=no,resizable=yes");
	}
</script>

<center>
<form method="post" name="doit" action="{link_action}" ENCTYPE="multipart/form-data">
<input type="hidden" id="saveAsDraft" name="saveAsDraft" value="0">
<TABLE WIDTH="100%" CELLPADDING="1" CELLSPACING="0" style="border: solid #aaaaaa 1px; border-right: solid black 1px; border-bottom: solid black 1px;">
	<tr class="navbarBackground">
		<td align="right" width="170px">
			<div class="parentDIV">
				<button class="menuButton" type="submit" value="{lang_send}" name="send" style="width: auto; color: black;">
					<img src="{img_mail_send}" style="vertical-align: middle;"> <b>{lang_send}</b>
				</button>
				<button class="menuButton" type="button" onclick="fm_compose_saveAsDraft();" title="{lang_save_as_draft}">
					<img src="{img_fileexport}">
				</button>
				<button class="menuButton" type="button" onclick="addybook();" title="{lang_addressbook}">
					<img src="{addressbookImage}">
				</button>
				<button class="menuButton" type="button" onclick="fm_compose_displayFileSelector();" title="{lang_attachments}">
					<img src="{img_attach_file}">
				</button>
			</div>
		</td>
		<td align="right">
			<label>{lang_receive_notification} <input type="checkbox" name="disposition" value="1" /></label>
			&nbsp;
			{lang_priority}
			<select name="priority">
				<option value="1">{lang_high}</option>
				<option value="3" selected>{lang_normal}</option>
				<option value="5">{lang_low}</option>
			</select>
			&nbsp;
		</td>
	</tr>
</table>
<table style="clear:left; width:100%;" border="0" cellspacing="0" cellpading="1">
<tr class="row_on">
	<td align="left" style="width:90px;">
		<b>{lang_identity}</b>
	</td>
	<td align="left">
		{select_from}
	</td>
</tr>
</table>

<div id="addressDIV" class="row_on" style="width:100%; border: solid black 0px; overflow: auto; padding: 0px; margin: 0px; text-align: left;">
<table id="addressTable" style="width:100%;" border="0" cellspacing="0" cellpading="0"><tbody id="addressRows">{destinationRows}</tbody></table>
</div>

<table style="width:100%;" border="0" cellspacing="0" cellpading="1">
<tr class="row_on">
	<td align="left" style="width:90px;">
		<b>{lang_subject}</b>
	</td>
	<td align="left">
		<input style="width:99%;" id="fm_compose_subject" onkeypress="return keycodePressed(KEYCODE_ENTER);" class="input_text" onkeyup="updateTitle(this.value)" type="text" style="width:450px;" name="subject" value="{subject}" onfocus="startCaptureEventSubjects(this)">
	</td>
</tr>
</table>
<div id="resultBox" class="resultBoxHidden"></div>
<!-- END header -->

<!-- BEGIN body_input -->
<table style="width:660px;" border="0" cellspacing="0" cellpading="0">
<tr>
	<td style="width:90px;">
		&nbsp;<br>
	</td>
	<td>
		{errorInfo}<br>
	</td>
</tr>
</table>
		<TEXTAREA class="input_text" name="body" rows="20" cols="76" wrap="hard">{body}</TEXTAREA>
<fieldset class="bordertop"><legend>{lang_signature}</legend>
		<TEXTAREA class="input_text" NAME=signature ROWS=5 COLS="76" WRAP=HARD>{signature}</TEXTAREA>
</fieldset>
<!-- END body_input -->

<!-- BEGIN attachment -->
<script language="javascript1.2">
// position cursor in top form field
///////////////////////////////////////////////////////////////////////document.doit.{focusElement}.focus();
//sString = document.doit.{focusElement}.innerHTML;
//document.doit.{focusElement}.innerHTML = sString;
</script>

<fieldset class="bordertop"><legend>{lang_attachments}</legend>
<div id="divAttachments" sstyle="border:1px solid black; width:660px;">
<table width="100%" border="0" cellspacing="1" cellpading="0">
{attachment_rows}
</table>
</div>
</fieldset>

</form>
</center>
<!-- END attachment -->

<!-- BEGIN attachment_row -->
<tr bgcolor="{row_color}">
	<td>
		{name}
	</td>
	<td>
		{type}
	</td>
	<td>
		{size}
	</td>
	<td align="center">
		<input type="checkbox" name="attachment[{attachment_number}]" value="{lang_remove}" title="{lang_remove}">
	</td>
</tr>
<!-- END attachment_row -->

<!-- BEGIN attachment_row_bold -->
<tr bgcolor="{th_bg}">
	<td>
		<b>{name}</b>
	</td>
	<td>
		<b>{type}</b>
	</td>
	<td>
		<b>{size}</b>
	</td>
	<td align="center">
		<input class="text" type="submit" name="removefile" value="{lang_remove}">
	</td>
</tr>
<!-- END attachment_row_bold -->

<!-- BEGIN destination_row -->
<tr class="row_on" id="masterRow">
	<td align="right" style="width:90px;">
		{select_destination}
	</td>
	<td align="left" valign="bottom" sstyle="width:455px;">
		<input class="input_text" onkeypress="return disabledKeyCodes(disabledKeys1);" autocomplete="off" type=text style="width:99%;" name="address[]" value="{address}" onfocus="initResultBox(this)" onblur="stopCapturingEvents()">
	</td>
	<td style="width:25px;" valign="bottom">
		<!-- <button type="image" onclick="deleteTableRow(this); return false;"><img src="{img_clear_left}" title="{lang_remove}"></button> -->
		<div class="divButton" style="background-image: url({img_clear_left});" onclick="deleteTableRow(this);" title="{lang_remove}"></div>
	</td>
	<td style="width:25px;" valign="bottom">
		<span style="display:none;" valign="bottom" class="selectFolder">
		<!-- <button type="image" onclick="fm_compose_selectFolder(); return false;"><img src="{img_fileopen}" alt="{lang_select_folder}"></button> -->
		 <div class="divButton" style="background-image: url({img_fileopen});" onclick="fm_compose_selectFolder();" title="{lang_select_folder}"></div>
		</span>
	</td>
</tr>
<!-- END destination_row -->

<!-- BEGIN fileSelector -->
<div id="fileSelectorDIV1" style="height:80px; border:0px solid red; background-color:white; padding:0px; margin:0px;">
<form method="post" enctype="multipart/form-data" name="fileUploadForm" action="{file_selector_url}">
	<table style="width:100%;">
		<tr>
			<td style="text-align:center;">
				<span id="statusMessage">&nbsp;</span>
			</td>
		</tr>
		<tr>
			<td style="text-align:center;">
				<input id="addFileName" name="addFileName" size="50" sstyle="width:450px;" type="file" onchange="fm_compose_addFile()"/>
			</td>
		</tr>
		<tr>
			<td style="text-align:center;">
				{lang_max_uploadsize}: {max_uploadsize}
			</td>
		</tr>
	</table>
</form>
</div>
<div id="fileSelectorDIV2" style="position:absolute; display:none; height:80px; width:100%; border:0px solid red; top:0px; left:0px; text-align:right; vertical-align:bottom; background:white;">
<table border="0" style="margin-left:140px; height:100%;"><tr><td><img src="{ajax-loader}"></td><td><span id="statusMessage" style="height:100%; width:100%; text-align:center;border:0px solid green; top:30px; left:0px;">{lang_adding_file_please_wait}</span></td></tr></table>
</div>
<!-- END fileSelector -->
