<!-- BEGIN dhtml_externals -->
<link href="{css_dir}/htmlarea.css" rel="stylesheet" type="text/css">
<link href="{css_dir}/calendar.css" type="text/css" rel="stylesheet">
<!-- END dhtml_externals -->

	<!-- input name="share_path" type="hidden" -->

	<center>
	<div style="overflow: auto; width: 98%; text-align: left;">
	
    <p class="font_title">{lang_path}: {disppath}</p>
    <table border="0" width="100%">
        <tr>
          <td style="padding-right: 0px;" nowrap="nowrap" valign="top">
		  <div style="position: relative; width: 180px; overflow: auto; min-height: 550px; height: 100%; background-color: white; border: #DCDCDC 1px solid; ">
		  {tree_path}
		  </div>
		  </td>

          <td valign="top" width="100%" nowrap="nowrap" height="100%">
		    <div align="left" style="height: 100%; min-height: 550px; width: 100%;">
			  {folder_contents}
		    </div>
          </td>
        </tr>
    </table>
	</div>
	</center>
{opt_action_button}

