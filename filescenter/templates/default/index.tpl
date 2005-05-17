<!-- BEGIN dhtml_externals -->
<link href="{css_dir}/htmlarea.css" rel="stylesheet" type="text/css">
<link href="{css_dir}/calendar.css" type="text/css" rel="stylesheet">
<!-- END dhtml_externals -->

	<!-- input name="share_path" type="hidden" -->

    <h1>{lang_path}: {disppath}</h1>

    <table border="0" width="100%">
        <tr>
          <td style="padding-right: 15px;" nowrap="nowrap" valign="top">
		  <div style="position: relative; width: 150px; overflow: auto; height: auto; background-color: white; border: #E8F0F0 1px solid; ">
		  {tree_path}
		  </div>
		  </td>

          <td valign="top" width="100%" nowrap="nowrap" height="100%">
		    <div align="left" width="100%" height="100%">
			  {folder_contents}
		    </div>
          </td>
        </tr>
    </table>
{opt_action_button}
