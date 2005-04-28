<!-- BEGIN header -->
<!-- javascript file -->
<script language=JavaScript src="jinn/js/jinn/display_func.js" type=text/javascript></script>
<!-- javascript file -->

	<table cellpadding="0" cellspacing="0" style="border:solid 1px #cccccc">
<tr>
<!--	<td>
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td><input type="button" name="direction" value="<<"></td>
					<td><input type="button" name="direction" value="<"></td>
					<td><input type="button" name="direction" value=">"></td>
					<td><input type="button" name="direction" value=">>">
					</td>
				</tr>
			</table>
	</td>-->
	<td align="center" style="padding-left:20px;">
		<form action="{menu_action}" method="post">{search_for}&nbsp;<input type="text" size="20" name="quick_filter" value="{search_string}">
		<input type="hidden" name="quick_filter_hidden" value="1">
		<input type="submit" value="{search}">
		</form>	
	</td>
	<td align="center" style="padding-left:20px;">
		<form name="filterform" action="{filter_action}" method="post">{filter_text}&nbsp;
			<select name="filtername" onChange="document.filterform.action = document.filterform.refresh_url.value; submit();">
				{filter_list}
			</select>
			<input type="hidden" name="refresh_url" value="{refresh_url}">
			<input type="submit" value="{filter_edit}">
		</form>	
	</td>
</tr>
</table>

<script language="javascript" type="text/javascript">
<!--

function submit_multi_del()
{
   if(countSelectedCheckbox()==0)
   {
	  alert('{lang_select_checkboxes}');
   }
   else
   {

	  if(window.confirm('{colfield_lang_confirm_delete_multiple}'))
	  {
		 document.frm.action.value='del';
		 document.frm.submit();
	  }
	  else
	  {
		 document.frm.action.value='none';

	  }
   }

}

function submit_multi_edit()
{
   if(countSelectedCheckbox()==0)
   {
	  alert('{lang_select_checkboxes}');
   }
   else
   {
	  document.frm.action.value='edit';
	  document.frm.submit();
   }
}

function submit_multi_view()
{
   if(countSelectedCheckbox()==0)
   {
	  alert('{lang_select_checkboxes}');
   }
   else
   {
	  document.frm.action.value='view';
	  document.frm.submit();
   }
}

function submit_multi_export()
{
   if(countSelectedCheckbox()==0)
   {
	  alert('{lang_select_checkboxes}');
   }
   else
   {
	  document.frm.action.value='export';
	  document.frm.submit();
   }
}

function img_popup(img,pop_width,pop_height,attr)
{
options="width="+pop_width+",height="+pop_height+",location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no";
parent.window.open("{popuplink}&path="+img+"&attr="+attr, "pop", options);
}

//-->
</script>
<br/>
<div style="background-color:#ffffff;border:solid 1px #cccccc;">
<table border="0" cellspacing="1" cellpadding="0" align="center" width="100%" >
	<tr>
		<td style="padding:2px;border-bottom:solid 1px #006699" align="left">
			<table cellspacing="0" cellpadding="0" width="100%">
				<tr>
				<td style="font-size:12px;font-weight:bold;">{table_title}</td>
				<td style="font-size:10px;font-weight:normal" align="center">{pager}</td>
				<td style="font-size:10px;font-weight:normal" align="right">{total_records} - {rec_per_page}</td>
				</tr>
			</table>
			</td>
		</tr>
</table>

<form name="frm" action="{list_form_action}" method="post">
<input type="hidden" name="action" value="none">
<table border="0" cellspacing="1" cellpadding="0" width="100%" style="padding-bottom:3px;border-bottom:solid 1px #006699">
<tr>
<td bgcolor="{th_bg}" colspan="5"  valign="top" style="font-weight:bold;padding:3px 5px 3px 5px;">{lang_Actions}</td>
{colnames}
</tr>
<!-- END header -->

<!-- BEGIN column_name -->
<td bgcolor="{colhead_bg_color}" style="font-weight:bold;padding:3px;" align="center"><a href="{colhead_order_link}">{colhead_name}&nbsp;{colhead_order_by_img}</a>{tipmouseover}</td>
<!-- END column_name -->

<!-- BEGIN column_field -->
<td bgcolor="{colfield_bg_color}" valign="top" style="padding:0px 2px 0px 2px">{colfield_value}</td>
<!-- END column_field -->

<!-- BEGIN row -->
<tr valign="top">

<td bgcolor="{colfield_bg_color}" width="1%" align="left"><input style="border-style:none;" type="checkbox" name="{colfield_check_name}" value="{colfield_check_val}"/></td>
<td bgcolor="{colfield_bg_color}" align="left" width="1%"><a title="{colfield_lang_view}" href="{colfield_view_link}"><img src="{colfield_view_img_src}" alt="{colfield_lang_view}" /></a></td>

<td bgcolor="{colfield_bg_color}" align="left" width="1%"><a title="{colfield_lang_edit}" href="{colfield_edit_link}"><img src="{colfield_edit_img_src}" alt="{colfield_lang_edit}" /></a></td>

<td bgcolor="{colfield_bg_color}" align="left" width="1%"><a title="{colfield_lang_copy}" href="{colfield_copy_link}" onClick="return window.confirm('{colfield_lang_confirm_copy_one}')"><img src="{colfield_copy_img_src}" alt="{colfield_lang_copy}" /></a></td>

<td bgcolor="{colfield_bg_color}" align="left" width="1%"><a title="{colfield_lang_delete}" href="{colfield_delete_link}" onClick="return window.confirm('{colfield_lang_confirm_delete_one}')"><img src="{colfield_delete_img_src}" alt="{colfield_lang_delete}" /></a></td>

{colfields}

</tr>
<!-- END row -->

<!-- BEGIN empty_row -->
<tr><td colspan="{colspan}">&nbsp;{lang_no_records}</td></tr>		   
<!-- END empty_row -->

<!-- BEGIN emptyfooter --> 
<tr><td colspan="">&nbsp;</td></tr>		   
</table>
<table width="100%" cellspacing="1" cellpadding="0">
<tr valign="top" bgcolor="{colhead_bg_color}"><td >&nbsp;</td></tr>
</table>
</form>
</div>
<!-- END emptyfooter -->

<!-- BEGIN footer --> 
</table>


<table width="100%" cellspacing="1" cellpadding="0">
<tr valign="top" bgcolor="{colhead_bg_color}">

<td width="1%" bgcolor="{colhead_bg_color}" align="left"><input title="{colfield_lang_check_all}" type="checkbox" name="CHECKALL" id="CHECKALL" value="TRUE" onclick="doCheckAll(this)" /></td>

<td width="1%" bgcolor="{colhead_bg_color}" align="left"><a title="{colfield_lang_view_sel}" href="javascript:submit_multi_view()"><img src="{colfield_view_img_src}" alt="{colfield_lang_view_sel}" /></a></td>

<td width="1%" bgcolor="{colhead_bg_color}" align="left"><a title="{colfield_lang_edit_sel}" href="javascript:submit_multi_edit()"><img src="{colfield_edit_img_src}" alt="{colfield_lang_edit_sel}" /></a></td>

<td width="1%" bgcolor="{colhead_bg_color}" align="left"><a title="{colfield_lang_delete_sel}" href="javascript:submit_multi_del()" ><img src="{colfield_delete_img_src}" alt="{colfield_lang_delete_sel}" /></a></td>

<td width="1%" bgcolor="{colhead_bg_color}" align="left"><a title="{colfield_lang_export_sel}" href="javascript:submit_multi_export()" ><img src="{colfield_export_img_src}" alt="{colfield_lang_export_sel}" /></a></td>

<td >&nbsp;{lang_actions_to_apply_on_selected}</td>

</tr>
</table>
</form>
</div>
<!-- END footer -->
