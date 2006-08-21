<!-- BEGIN message_main -->
<script language="JavaScript1.2">
self.focus();
</script>
<!-- {print_navbar} -->
<div id="navbarDIV" style="position:absolute; top:0px; height:30px; left:0px; right:0px; border: solid white 1px; border-right: solid black 1px; border-bottom: solid black 1px;">
        {navbar}
</div>
<div id="subjectDIV" style="position:absolute; background-color:#ffffff; top:32px; height:20px; width:100%; font-weight:bold; text-align:left; line-height:20px;">
	<span style="padding-left:2px;">{subject_data}</span>
</div>
<div id="headerDIV" style="position:absolute; background-color:#efefdf; top:52px; height:80px; left:0px; right:0px; border-top: 1px solid silver; border-bottom: 1px solid silver; overflow:hidden;">
	{header}
</div>
<div id="bodyDIV" style="position:absolute; background-color:white; top:134px; bottom:0px; width:100%; border-top: 1px solid #efefdf;">
	<iframe frameborder="no" scrolling="auto" style="width:100%; border:0px solid black; height:100%;" src="{url_displayBody}">
	</iframe>
</div>
<!-- END message_main -->

<!-- BEGIN message_main_attachment -->
<script language="JavaScript1.2">
self.focus();
</script>
<!-- {print_navbar} -->
<div id="navbarDIV" style="position:absolute; top:0px; height:30px; left:0px; right:0px; border: solid white 1px; border-right: solid black 1px; border-bottom: solid black 1px;">
        {navbar}
</div>
<div id="subjectDIV" style="position:absolute; background-color:#ffffff; top:32px; height:20px; width:100%; font-weight:bold; text-align:left; line-height:20px;">
	<span style="padding-left:2px;">{subject_data}</span>
</div>
<div id="headerDIV" style="position:absolute; background-color:#efefdf; top:52px; height:80px; left:0px; right:0px; border-top: 1px solid silver; border-bottom: 1px solid silver; overflow:hidden;">
	{header}
</div>
<div id="bodyDIV" style="position:absolute; background-color:white; top:134px; bottom:80px; width:100%; border-top: 1px solid #efefdf;">
	<iframe frameborder="no" scrolling="auto" style="border:0px solid black; width:100%; height:100%;" src="{url_displayBody}">
	</iframe>
</div>
<div id="attachmentDIV" style="position:absolute; background-color:#efefdf; bottom:0px; height:80px; width:100%; border-top: 1px solid silver; overflow:auto;">
<table border="0" width="100%" cellspacing="0">
{attachment_rows}
</table>
</div>
<!-- END message_main_attachment -->

<!-- BEGIN message_raw_header -->
<tr>
	<td bgcolor="white">
		<pre><font face="Arial" size="-1">{raw_header_data}</font></pre>
	</td>
</tr>
<!-- END message_raw_header -->

<!-- BEGIN message_navbar -->
<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tr class="navbarBackground">
		<td width="250px">
			<div class="parentDIV">
				{navbarButtonsLeft}
			</div>
		</td>
		<td>
			&nbsp;
		</td>
		<td width="60px">
			<div class="parentDIV">
				{navbarButtonsRight}
			</div>
		</td>
	</tr>
</table>
<!-- END message_navbar -->

<!-- BEGIN message_navbar_print -->
<html>
<body onload="javascript:window.print()">
<!-- END message_navbar_print -->

<!-- BEGIN message_attachement_row -->
<tr>
	<td valign="top">
		<a href="#" onclick="{link_view} return false;"><font size="2" face="{theme_font}">
		<b>{filename}</b></font><a>
	</td> 
	<td align="center">
		<font size="2" face="{theme_font}">
		{mimetype}
		</font>
	</td>
	<td align="center">
		<font size="2" face="{theme_font}">
		{size}
		</font>
	</td>
	<td width="10%" align="center">
		<font size="2" face="{theme_font}">
		<a href="{link_save}"><img src="{url_img_save}" title="{lang_save}"></a>
		</font>
	</td>
</tr>
<!-- END message_attachement_row -->

<!-- BEGIN message_cc -->
<tr>
	<td width="100" style="font-weight:bold; font-size:10px;">
		{lang_cc}:
	</td> 
	<td style="font-size:10px;" colspan="3">
		{cc_data}
	</td>
</tr>
<!-- END message_cc -->

<!-- BEGIN message_onbehalfof -->
<tr>
	<td width="100" style="font-weight:bold; font-size:10px; vertical-align:top;">
		{lang_on_behalf_of}:
	</td> 
	<td style="font-size:10px;" colspan="3">
		{onbehalfof_data}
	</td>
</tr>
<!-- END message_onbehalfof -->

<!-- BEGIN message_header -->
<table border="0" cellpadding="1" cellspacing="0" width="100%" style="padding-left:2px;" id="headerTable">
<tr>
	<td style="text-align:left; width:100px; font-weight:bold; font-size:10px;">
		{lang_from}:
	</td>
	<td style="font-size:10px;" colspan="2">
		{from_data}
	</td>
	<td style="font-size:10px;" align="right">
		<div id="moreDIV" onclick="toggleHeaderSize();" style="display:none; border:1px dotted black; width:10px; height:10px; line-height:10px; text-align:center; cursor: pointer;">
			<span id="toogleSPAN">+</span>
		</div>
	</td>
</tr>

{on_behalf_of_part}

<tr>
	<td style="font-weight:bold; font-size:10px;">
		{lang_date}:
	</td> 
	<td style="font-size:10px;" colspan="3">
		{date_received}
	</td>
</tr>

<tr>
	<td style="font-weight:bold; font-size:10px; vertical-align:top;">
		{lang_to}:
	</td> 
	<td style="font-size:10px;" colspan="3">
		{to_data}
	</td>
</tr>

{cc_data_part}

</table>
<!-- END message_header -->

<!-- BEGIN previous_message_block -->
<a href="{previous_url}">{lang_previous_message}</a>
<!-- END previous_message_block -->

<!-- BEGIN next_message_block -->
<a href="{next_url}">{lang_next_message}</a>
<!-- END next_message_block -->
