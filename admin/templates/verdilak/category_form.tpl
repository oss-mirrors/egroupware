<!-- $Id$ -->
<!-- BEGIN form -->
<center>
<table border="0" width="80%" cellspacing="2" cellpadding="2">
<tr>
  <td colspan="1" align="center" bgcolor="#c9c9c9"><b>{title_categories}<b/></td>
</tr>
</table>
{message}
<table border="0" width="80%" cellspacing="2" cellpadding="2"> 
  <form name="form" action="{actionurl}" method="POST">
    <tr>
     <td>&nbsp;</td>
     <td><select name="cat_parent"><option value="">{lang_select_parent}</option>{category_list}</select></td>
    </tr>
    <tr>
     <td>{lang_name}:</td>
     <td><input name="cat_name" size="50" value="{cat_name}"></td>
    </tr>
    <tr>
     <td>{lang_descr}:</td>
     <td colspan=2><textarea name="cat_description" rows="4" cols="50" wrap="virtual">{cat_description}</textarea></td>
    </tr>
    </table>

<!-- BEGIN add -->
         <table width="50%" border="0" cellspacing="2" cellpadding="2">
         <tr valign="bottom">
          <td height="50" align="center">
           {hidden_vars}
           <input type="submit" name="submit" value="{lang_add}"></td>
          <td height="50" align="center">
           <input type="reset" name="reset" value="{lang_reset}"></form></td>
          <td height="50" align="center">
            <form method="POST" action="{doneurl}">
           {hidden_vars}
         <input type="submit" name="done" value="{lang_done}"></form></td>
         </tr>
         </table>
         </form>
         </center>
<!-- END add -->

<!-- BEGIN edit -->
         <table width="50%" border="0" cellspacing="2" cellpadding="2">
         <tr valign="bottom">
          <td height="50" align="center">
           {hidden_vars}
           <input type="submit" name="submit" value="{lang_edit}"></form></td>
          <td height="50" align="center">
            <form method="POST" action="{deleteurl}">
           {hidden_vars}
         <input type="submit" name="delete" value="{lang_delete}"></form></td>
          <td height="50" align="center">
            <form method="POST" action="{doneurl}">
           {hidden_vars}
         <input type="submit" name="done" value="{lang_done}"></form></td>
         </tr>
         </table>
         </center>
<!-- END edit -->
<!-- END form -->
