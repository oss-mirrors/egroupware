<!-- BEGIN dhtml_externals -->
<script type="text/javascript" language="javascript">

function submit_to_handler()
{
      document.forms[0].task.value = 'GO_HANDLER';
  document.forms[0].action = "download.php";
  document.forms[0].submit();
}

function delete_items()
{
  var count = 0;
  var filename = new String;
  var path = new String;

  for (var i=0;i<document.forms[0].elements.length;i++)
  {
    if(document.forms[0].elements[i].type == 'checkbox' && document.forms[0].elements[i].name != 'dummy')
    {
      if (document.forms[0].elements[i].checked == true)
      {
        count++;
        path = document.forms[0].elements[i].value;
        filename = path.substring(path.lastIndexOf('/')+1, path.length);
      }
    }
  }
  switch (count)
  {
    case 0:
      alert("{lang_no_items_selected}");
      break;

    case 1:
      if (confirm("{lang_delete_confirmation}"))
      {
        document.forms[0].ftask.value = 'delete';
		document.forms[0].action = document.forms[0].action + "?menuaction=filescenter.ui_fm2.index";
        document.forms[0].submit();
      }
      break;

    default:
      if (confirm("{lang_delete_items_confirmation}"))
      {
        document.forms[0].ftask.value = 'delete';
		document.forms[0].action = document.forms[0].action + "?menuaction=filescenter.ui_fm2.index";
        document.forms[0].submit();
      }
      break;
  }
}

function create_archive()
{
  var count = 0;

  for (var i=0;i<document.forms[0].elements.length;i++)
  {
    if(document.forms[0].elements[i].type == 'checkbox' && document.forms[0].elements[i].name != 'dummy')
    {
      if (document.forms[0].elements[i].checked == true)
      {
        count++;
      }
    }
  }
  if (count == 0)
  {
    alert("{lang_no_items_selected}");
  }else
  {
    document.forms[0].task.value = 'create_archive';
	document.forms[0].action = document.forms[0].action + "?menuaction=filescenter.ui_fm2.compress";
    document.forms[0].submit();
  }
}

function extract()
{
  var count = 0;

  for (var i=0;i<document.forms[0].elements.length;i++)
  {
    if(document.forms[0].elements[i].type == 'checkbox' && document.forms[0].elements[i].name != 'dummy')
    {
      if (document.forms[0].elements[i].checked == true)
      {
        count++;
      }
    }
  }
  if (count == 0)
  {
    alert("{lang_no_items_selected}");
  }else if (count > 1)
  {
	alert("{lang_much_items_selected}");
  }
  {
    document.forms[0].task.value = 'extract';
	document.forms[0].action = document.forms[0].action + "?menuaction=filescenter.ui_fm2.decompress";

    document.forms[0].submit();
  }
}
</script>

<link href="{css_dir}/style.css" rel="stylesheet" type="text/css">
<link href="{css_dir}/htmlarea.css" rel="stylesheet" type="text/css">
<link href="{css_dir}/calendar.css" type="text/css" rel="stylesheet">
<!-- END dhtml_externals -->

  <div id="overDiv" style="position: absolute; visibility: hidden; z-index: 1000;"></div>

  <form name="filesystem" method="post" action="{form_action}" enctype="multipart/form-data" id="filesystem">
    <input name="path" value="{path}" type="hidden">
	<input name="formvar" value="" type="hidden">
	<input name="ftask" value="" type="hidden">
	<input name="return_to_path" value="{returntopath}" type="hidden">
	<!-- input name="share_path" type="hidden" -->

    <h1>{lang_path}: {path}</h1><input name="task" type="hidden">


    <table border="0" width="100%">
      <tbody>
        <tr>
          <td style="padding-right: 25px;" nowrap="nowrap" valign="top">
		  <br><br>
		  <div style="position: relative; width: 150px; height: auto; overflow: auto;">
		  {tree_path}
		  </div>
		  </td>

          <td valign="top" width="100%">

		  <div align="right">
			<table border="0" cellpadding="0" cellspacing="0">
			  <tbody>
				<tr>
				  <!-- BEGIN header_menu -->
				  <td valign="bottom" align="center" style="text-align: center;" width="{td_width}"><a class="go_small" href="{icon_link}" {icon_other}>{navbar_element}</a></td>
				  <!-- END header_menu -->
				</tr>
				{navbar_second_row}
			  </tbody>
			</table>

            <table border="0" cellpadding="0" cellspacing="0"
            width="100%">
				<thead>
					<tr>
					  <th class="TableHead2" width="16"><input onclick=
					  "javascript:invert_selection()" name="dummy"
					  type="checkbox"></th>
					  <!-- BEGIN files_header_tbl_field -->
					  <th class="TableHead2" nowrap="nowrap" {tdhoptions}><span class="lk2">{lang_fieldname}</span></th>
					  <!-- END files_header_tbl_field -->
					</tr>
				</thead>
				
				<tbody id="Tdirs">
				<!-- BEGIN dirs_tbl_row -->
                <tr id="{filename}" class="Table1">
                  <td><input onclick="javascript:item_click(this)"
                  name="files[]" value="{filename}" type="checkbox"></td>

				  <!-- BEGIN dirs_tbl_field -->
                  <td nowrap="nowrap" {tdoptions} class="Table1">{field_content}</td>
				  <!-- END dirs_tbl_field -->
                </tr>
				<!-- END dirs_tbl_row -->
				</tbody>

				<tbody id="Tfiles">
				<!-- BEGIN files_tbl_row -->
                <tr id="{filename}" class="Table1">
                  <td><input onclick="javascript:item_click(this)"
                  name="files[]" value="{filename}" type="checkbox"></td>

				  <!-- BEGIN files_tbl_field -->
                  <td nowrap="nowrap" {tdoptions} class="Table1">{field_content}</td>
				  <!-- END files_tbl_field -->
                </tr>
				<!-- END files_tbl_row -->
				</tbody>
				<tfoot>
                <tr>
                  <td colspan="99" class="go_small" height="18">{folder_information}</td>
                </tr>
				</tfoot>
            </table>
		  </div>


          </td>
        </tr>
      </tbody>
    </table>

  </form>
