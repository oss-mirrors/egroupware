<!-- BEGIN header -->
<!-- javascript file -->
<div id="dhtmltooltip"></div>
<script language=JavaScript src="jinn/js/jinn/display_func.js" type=text/javascript></script>
<!-- javascript file -->

	<table cellpadding="0" cellspacing="0" style="border:solid 1px #cccccc">
<tr>
	<td><form action="{menu_action}" method="post">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td><input type="submit" name="direction" value="<<"></td>
					<td><input type="submit" name="direction" value="<"></td>
					<td><input type="submit" name="direction" value=">"></td>
					<td><input type="submit" name="direction" value=">>">
						<input type="hidden" name="limit_start" value="{limit_start}">
						<input type="hidden" name="limit_stop" value="{limit_stop}">
						<input type="hidden" name="search" value="{search_string}">
						<input type="hidden" name="orderby" value="{orderby}">
					</td>
				</tr>
			</table>
		</form>
	</td>
	<td align="center" style="padding-left:20px;">
		<form action="{menu_action}" method="post">{search_for}&nbsp;<input type="text" size="8" name="search" value="{search_string}">
		<input type="submit" value="{search}">
		<input type="hidden" name="limit_start" value="0">
		<input type="hidden" name="limit_stop" value="30">
		</form>	
	</td>
</tr>
</table>

<script language="javascript" type="text/javascript">
<!--

/***********************************************
* Cool DHTML tooltip script- © Dynamic Drive DHTML code library (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
***********************************************/

var offsetxpoint=-60 //Customize x offset of tooltip
var offsetypoint=20 //Customize y offset of tooltip
var ie=document.all
var ns6=document.getElementById && !document.all
var enabletip=false
if (ie||ns6)
var tipobj=document.all? document.all["dhtmltooltip"] : document.getElementById? document.getElementById("dhtmltooltip") : ""

function ietruebody(){
return (document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body
}

function tooltip(thetext, thecolor, thewidth){
if (ns6||ie){
if (typeof thewidth!="undefined") tipobj.style.width=thewidth+"px"
if (typeof thecolor!="undefined" && thecolor!="") tipobj.style.backgroundColor=thecolor
tipobj.innerHTML=thetext
enabletip=true
return false
}
}

function positiontip(e){
if (enabletip){
var curX=(ns6)?e.pageX : event.x+ietruebody().scrollLeft;
var curY=(ns6)?e.pageY : event.y+ietruebody().scrollTop;
//Find out how close the mouse is to the corner of the window
var rightedge=ie&&!window.opera? ietruebody().clientWidth-event.clientX-offsetxpoint : window.innerWidth-e.clientX-offsetxpoint-20
var bottomedge=ie&&!window.opera? ietruebody().clientHeight-event.clientY-offsetypoint : window.innerHeight-e.clientY-offsetypoint-20

var leftedge=(offsetxpoint<0)? offsetxpoint*(-1) : -1000

//if the horizontal distance isn't enough to accomodate the width of the context menu
if (rightedge<tipobj.offsetWidth)
//move the horizontal position of the menu to the left by it's width
tipobj.style.left=ie? ietruebody().scrollLeft+event.clientX-tipobj.offsetWidth+"px" : window.pageXOffset+e.clientX-tipobj.offsetWidth+"px"
else if (curX<leftedge)
tipobj.style.left="5px"
else
//position the horizontal position of the menu where the mouse is positioned
tipobj.style.left=curX+offsetxpoint+"px"

//same concept with the vertical position
if (bottomedge<tipobj.offsetHeight)
tipobj.style.top=ie? ietruebody().scrollTop+event.clientY-tipobj.offsetHeight-offsetypoint+"px" : window.pageYOffset+e.clientY-tipobj.offsetHeight-offsetypoint+"px"
else
tipobj.style.top=curY+offsetypoint+"px"
tipobj.style.visibility="visible"
}
}

function hidetooltip(){
if (ns6||ie){
enabletip=false
tipobj.style.visibility="hidden"
tipobj.style.left="-1000px"
tipobj.style.backgroundColor=''
tipobj.style.width=''
}
}

document.onmousemove=positiontip

// END tooltip code 



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
<tr><td style="font-size:12px;font-weight:bold;padding:2px;border-bottom:solid 1px #006699" align="left">{table_title}</td></tr>
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

<!--<td width="1%"  bgcolor="{colhead_bg_color}" align="left"><a title="{colfield_lang_view_sel}" href="#"><img src="{colfield_view_img_src}" alt="{colfield_lang_view_sel}" /></a></td>-->

<td width="1%" bgcolor="{colhead_bg_color}" align="left"><a title="{colfield_lang_edit_sel}" href="javascript:submit_multi_edit()"><img src="{colfield_edit_img_src}" alt="{colfield_lang_edit_sel}" /></a></td>

<td width="1%" bgcolor="{colhead_bg_color}" align="left"><a title="{colfield_lang_delete_sel}" href="javascript:submit_multi_del()" ><img src="{colfield_delete_img_src}" alt="{colfield_lang_delete_sel}" /></a></td>
<td >&nbsp;{lang_actions_to_apply_on_selected}</td>

</tr>
</table>
</form>
</div>
<!-- END footer -->
