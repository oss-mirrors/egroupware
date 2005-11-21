<!-- BEGIN message_main -->
<script language="JavaScript1.2">
self.focus();
</script>
<!-- {print_navbar} -->
<table border="0" width="100%" cellspacing="0" style="border: solid white 1px; border-right: solid black 1px; border-bottom: solid black 1px;">
<tr>
	<td>
		{navbar}
	</td>
</tr>
</table>
<br>
<table border="0" cellpadding="1" cellspacing="0" width="100%" style="table-layout:fixed">

<tr class="th">
	<td style="font-weight:bold;">
		{subject_data}
	</td>
</tr>
</table>
<br>
<table width="100%" border="0" cellspacing="0" cellpading="0" bgcolor="white">
	<tr>
		<th id="tab1" class="activetab" onclick="javascript:tab.display(1);"><a href="#" tabindex="0" accesskey="1" onfocus="tab.display(1);" onclick="tab.display(1); return(false);" style="font-size:10px;">{lang_Message}</a></th>
		<th id="tab2" class="activetab" onclick="javascript:tab.display(2);"><a href="#" tabindex="0" accesskey="2" onfocus="tab.display(2);" onclick="tab.display(2); return(false);" style="font-size:10px;">{lang_Attachment} ({attachment_count})</a></th>
		<th id="tab3" class="activetab" onclick="javascript:tab.display(3);"><a href="#" tabindex="0" accesskey="3" onfocus="tab.display(3);" onclick="tab.display(3); return(false);" style="font-size:10px;">{lang_Header_Lines}</a></th>
	</tr>
</table>
<div id="tabcontent1" class="inactivetab" bgcolor="white">
<table border="0" width="100%" cellspacing="0" cellpading="0" bgcolor="white" style="table-layout:fixed">
<tr>
	<td>
		&nbsp;
	</td>
</tr>
<tr>
	<td>
{header}
	</td>
</tr>
<tr>
	<td bgcolor="white">
<div class="body">
<!-- Body Begin -->
{body}
<!-- Body End -->
</div>
	</td>
</tr>
</table>
</div>

<div id="tabcontent2" class="inactivetab">
<table border="0" width="100%" cellspacing="0" bgcolor="white">
	<tr bgcolor="{bg01}">
		<td align="center">
			Name
		</td>
		<td align="center">
			Type
		</td>
		<td align="center">
			Size
		</td>
		<td align="center">
			&nbsp;
		</td>
	</tr>
{attachment_rows}
</table>
</div>

<div id="tabcontent3" class="inactivetab">
<table border="0" width="100%" cellspacing="0" bgcolor="white">
	<tr>
		<td>
			<pre>{rawheader}</pre>
		</td>
	</tr> 
</tr>
</table>
</div>

<!-- END message_main -->

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
		<td width="300px"align="left">
			&nbsp;
		</td>
		<td width="55px" align="right">
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
	<td style="font-size:10px;">
		{cc_data}
	</td>
</tr>
<!-- END message_cc -->

<!-- BEGIN message_org -->
<tr>
	<td width="100" style="font-weight:bold; font-size:10px;">
		{lang_organisation}:
	</td> 
	<td style="font-size:10px;">
		{organization_data}
	</td>
</tr>
<!-- END message_org -->

<!-- BEGIN message_onbehalfof -->
<tr>
	<td width="100" style="font-weight:bold; font-size:10px;">
		{lang_on_behalf_of}:
	</td> 
	<td style="font-size:10px;">
		{onbehalfof_data}
	</td>
</tr>
<!-- END message_onbehalfof -->

<!-- BEGIN message_header -->
<table border="0" cellpadding="1" cellspacing="0" width="100%" style="table-layout:fixed">

<table border="0" cellpadding="1" cellspacing="0" width="100%">
<tr cclass="row_on">
	<td style="text-align:left; width:120px; font-weight:bold; font-size:10px;">
		{lang_from}:
	</td>
	<td style="font-weight:bold; font-size:10px;">
		{from_data}
	</td>
</tr>

{on_behalf_of_part}

{org_part}

<tr cclass="row_off">
	<td style="font-weight:bold; font-size:10px;">
		{lang_to}:
	</td> 
	<td style="font-size:10px;">
		{to_data}
	</td>
</tr>

{cc_data_part}

<tr cclass="row_on">
	<td style="font-weight:bold; font-size:10px;">
		{lang_date}:
	</td> 
	<td style="font-size:10px;">
		{date_data}
	</td>
</tr>

</table>
<br>
<!-- END message_header -->

<!-- BEGIN previous_message_block -->
<a href="{previous_url}">{lang_previous_message}</a>
<!-- END previous_message_block -->

<!-- BEGIN next_message_block -->
<a href="{next_url}">{lang_next_message}</a>
<!-- END next_message_block -->
