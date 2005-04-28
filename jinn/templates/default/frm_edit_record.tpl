<!-- BEGIN form_header -->
   <script language="javascript" type="text/javascript">
function img_popup(img,pop_width,pop_height,attr)
{
   options="width="+pop_width+",height="+pop_height+",location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no";
   parent.window.open("{popuplink}&path="+img+"&attr="+attr, "pop", options);
}
</script>
<div id="dhtmltooltip"></div>
<form method="post" name="frm" action="{form_action}" enctype="multipart/form-data" {form_attributes}>
{where_string_form}
<!-- END form_header -->

<!-- BEGIN js -->
<script language="JavaScript">
<!--

function onSubmitForm() {

{submit_script}

return true;
}

var offsetxpoint=-60 //Customize x offset of tooltip
var offsetypoint=20 //Customize y offset of tooltip
var ie=document.all
var ns6=document.getElementById && !document.all
var enabletip=false
if (ie||ns6)
var tipobj=document.all? document.all["dhtmltooltip"] : document.getElementById? document.getElementById("dhtmltooltip") : ""

function ietruebody()
{
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


//-->

</script>
<!-- END js -->

<!-- BEGIN table_header -->
<table align="" cellspacing="2" cellpadding="2" style="background-color:#ffffff;border:solid 1px #cccccc;width:570px;">
<!-- END table_header -->

<!-- BEGIN rows -->
<tr><td bgcolor="{row_color}" valign="top" nowrap="nowrap">{fieldname}&nbsp;{tipmouseover}</td>
<td bgcolor="{row_color}">{input}</td></tr>
<!-- END rows -->

<!-- BEGIN many_to_many -->
<tr><td bgcolor="{m2mrow_color}" valign="top">{m2mfieldname}</td>
<td bgcolor="{m2mrow_color}">
	<table cellspacing="0" cellpadding="3" border="1">
		<tr>
		   <td valign=top>{sel1_all_from}<br/>
	  			<select onDblClick="{on_dbl_click1}" multiple size="5" name="{sel1_name}">
				{sel1_options}	
				</select>
			</td>
			
			<td align="center" valign="top">{lang_add_remove}<br/><br/>
				<input onClick="{on_dbl_click1}" type="button" value=" &gt;&gt; " name="add">
				<br/>
				<input onClick="{on_dbl_click2}" type="button" value=" &lt;&lt; " name="remove">
			</td>
			
			<td valign="top">{lang_related}<br/>
				<select onDblClick="{on_dbl_click2}" multiple size="5" name="{sel2_name}">
				<!-- does this br belong here --><br/>
				{sel2_options}
				</select>

				<input type="hidden" name="{m2m_rel_string_name}" value="{m2m_rel_string_val}">
				<input type="hidden" name="{m2m_opt_string_name}">
			</td>
		</tr>
	</table>


		<!--{m2minput}-->
	</td>
</tr>
<!-- END many_to_many -->



<!-- BEGIN form_buttons -->
	<table style="background-color:#ffffff">
		<tr>
		<td><input type="submit" name="reopen" value="{save_button}"></td>
<td><input type="{save_and_add_new_button_submit}" {save_and_add_new_button_onclick} name="add_new" value="{save_and_add_new_button}"></td>
		<td><input type="submit" name="save" value="{save_and_return_button}"></td>
		<td style="visibility:{btn_delete}"><input type="hidden" name="delete"> <input type="submit"  name="delete" value="{delete}"></td>
		<td>{cancel}</td>
		</tr>
	</table>
<!-- END form_buttons -->

<!-- BEGIN form_footer -->
{hiddenfields}
{jsmandatory}
</form>
<!-- END form_footer -->




