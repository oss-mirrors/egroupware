<!-- $Id$ -->

<form method="post" action="{form_action}">
 <table border=0 bgcolor="#EEEEEE" align="center" width="80%">
  <tr bgcolor="{th_bg}">
   <td colspan="4">&nbsp;<b>{lang_header}</b></td>
  </tr>

  <tr>
   <td>
    {lang_url}&nbsp;
    {mail_this_link}
   </td>
   <td colspan="3">{input_url}&nbsp;</td>
  </tr>
 
  <tr>
   <td>{lang_name}</td>
   <td colspan="3">{input_name}&nbsp;</td>
  </tr>
 
  <tr>
   <td>{lang_desc}</td>
   <td colspan="3">{input_desc}&nbsp;</td>
  </tr>
 
  <tr>
   <td>{lang_keywords}</td>
   <td colspan="3">{input_keywords}&nbsp;</td>
  </tr>
 
  <tr>
   <td rowspan="2">&nbsp;</td>
   <td width="25%" align="center">{lang_category}&nbsp; &nbsp;{category_image}</td>
   <td width="25%" align="center">{lang_subcategory}&nbsp; &nbsp;{subcategory_image}</a></td>
   <td width="25%" align="center">{lang_rating}</td>
  </tr>

  <tr>
   <td width="25%" align="center">{input_category}</td>
   <td width="25%" align="center">{input_subcategory}</td>
   <td width="25%" align="center">{input_rating}</td>
  </tr>
 
  <tr>
   <td>{lang_access}</td>
   <td colspan="3">{input_access}</td>
  </tr>

{info}
 
  <tr>
   <td colspan="4" align="right">
    {delete_button}
    {edit_button}
    &nbsp;&nbsp;&nbsp;
    {cancel_button}
    {form_link}
   </td>
  </tr>
 </table>
</form>
