<!-- BEGIN info_edit -->
{doSearchFkt}
{info_css}
     <p class=action>{lang_info_action}<br>
     <hr noshade width="98%" align="center" size="1">

     <center>{error_list}</center>

     <center>
       <form method="POST" name="EditorForm" action="{actionurl}" enctype="multipart/form-data">
        {common_hidden_vars}
        <table width="90%" border="0" cellspacing="0" cellpadding="2">

         <tr>
           <td>{lang_type}:</td>
           <td width="40%">{type_list}</td>

           <td>{lang_owner}:</td>
           <td>{owner_info}</td>
         </tr>
         <tr>
           <td>{project_title}</td>
           <td colspan="3">{project} {project_nojs}</td>
         </tr>
         <tr>
          <td>{addr_title}</td>
           <td colspan="3">{addr} {addr_nojs}</td>
         </tr>
         <tr>
          <td>{event_title}</td>
           <td colspan="3">{event} {event_nojs}</td>
         </tr>
{linkto}
         <tr>
            <td colspan="4"><hr size="1"></td>
         </tr>
         <tr>
           <td>{lang_prfrom}:</td>
           <td colspan="3"><input name="from" size="64" maxlength="64" value="{fromval}"></td>
         </tr>
         <tr>
           <td>{lang_praddr}:</td>
           <td colspan="3"><input name="addr" size="64" maxlength="64" value="{addrval}"></td>
         </tr>
         <tr>
            <td colspan="4"><hr size="1"></td>
         </tr>
         <tr>
           <td>{lang_prsubject}:</td>
           <td colspan="3"><input name="subject" size="64" maxlength="64" value="{subjectval}"></td>
         </tr>
         <tr>
           <td valign="top">{lang_prdesc}:</td>
           <td colspan="3"><textarea name="des" rows=4 cols=50 wrap="VIRTUAL">{descval}</textarea></td>
         </tr>
         <tr>
            <td colspan="4"><hr size="1"></td>
         </tr>

         <tr>
           <td>{lang_file}</td>
           <td><input type="file" name="attachfile" value="{file_val}"></td>

           <td>{lang_comment}</td>
           <td><input name="filecomment" size="30" maxlength="64" value="{comment_val}"></td>
         </tr>
         <tr>
            <td colspan="4"><hr size="1"></td>
         </tr>

         <tr>
           <td width="15%">{lang_start_date}:</td>
           <td width="40%">{start_select_date}</td>

           <td width="15%">{lang_selfortoday}</td>
           <td>{selfortoday}</td>
         </tr>

         <tr>
          <td>{lang_end_date}:</td>
          <td>{end_select_date}</td>

           <td>{lang_dur_days}</td>
           <td><input name="dur_days" size="3" maxlength="2" value="">&nbsp;{dur_days}</td>
         </tr>
         <tr>
            <td colspan="4"><hr size="1"></td>
         </tr>
         <tr>
            <td>{lang_status}:</td>
            <td>{status_list}</td>

            <td>{lang_category}</td>
             <td><select name="info_cat"><option value="0">{lang_none}</option>{cat_list}</select></td>
         </tr>
         <tr>
           <td>{lang_priority}:</td>
           <td>{priority_list}</td>

           <td>{lang_confirm}</td>
           <td>{confirm_list}</td>
         </tr>

         <tr>
           <td>{lang_responsible}:</td>
           <td>{responsible_list}</td>

           <td>{lang_access_type}:</td>
           <td>{access_list}</td>
         </tr>

         </table>

         <table width="90%" border="0">
         <tr valing=bottom height=100>
          <td>{edit_button}</td>
          <td>{cancel_button}</td>
          <td width="80%" align=right>{delete_button}</td>
         </tr>
         </table>
       </form>
     </center>
<!-- END info_edit -->