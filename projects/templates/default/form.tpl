<!-- $Id$ -->
<script language="JavaScript">
    self.name="first_Window";
    function addressbook()
    {
       Window1=window.open('{addressbook_link}',"Search","width=800,height=600","scrolling=yes,toolbar=yes,resizable=yes");
    }
   </script>
      <p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>
      <hr noshade width="98%" align="center" size="1">

      <center>
       <form method="POST" name="projects_form" action="{actionurl}">
        {common_hidden_vars}
        {error}
        <table width="85%" border="0" cellspacing="1" cellpadding="3">
          <tr>
          <td>{lang_num}:</td>
          <td><input type="text" name="num" value="{num}"></td>
         </tr>
         <tr>
          <td>{lang_title}:</font></td>
          <td><input type="text" name="title" size="50" value="{title}"></td>
         </tr>
         <tr>
         <td>{lang_descr}:</td> 
	 <td colspan="2"><textarea name="descr" rows=4 cols=50 wrap="VIRTUAL">{descrval}</textarea></td>
         </tr>
         <tr>
	 <td><input type="button" value="{lang_customer}" onClick="addressbook();"></td>
	 <td><input type="hidden" name="customer" value="{customer_con}">
	     <input type="text" name="customername" size="50" value="{customer_name}" readonly>&nbsp;&nbsp;&nbsp;{lang_select}</td>
	 </tr>
	 <tr>
	 <td>{lang_coordinator}:</td>
	 <td><select name="coordinator">{coordinator_list}</select></td>
	 </tr>
	 <tr>
	 <td>{lang_access_type}:</td>
	 <td><select name="access">{access_list}</select></td>
	 </tr>
	 <tr>
	 <td>{lang_which_groups}:</td>
	 <td><select name="n_groups[]" multiple>{group_list}</select></td>
	 </tr>
	 <tr>
          <td>{lang_status}:</td>
          <td><select name="status">{status_list}</select></td>
         </tr>
         <tr>
          <td>{lang_budget}:</td>
          <td><input type="text" name="budget" value="{budget}"></td>
         </tr>
         <tr>
          <td>{lang_date}:</td>
          <td>{date_formatorder}</td>
         </tr>
         <tr>
          <td>{lang_end_date}:</td>
          <td>{end_date_formatorder}</td>
         </tr>
         <tr>
          <td>{lang_bookable_activities}:</td>
          <td><select name="ba_activities[]" multiple>{ba_activities_list}</select></td>
         </tr>
         <tr>
          <td>{lang_billable_activities}:</td>
          <td><select name="bill_activities[]" multiple>{bill_activities_list}</select></td>
         </tr>
         </table>
         
         <!-- BEGIN add -->
         
         <table width="75%" border="0" cellspacing="1" cellpadding="3">
         <tr valign="bottom">
          <td height="62"><input type="submit" name="submit" value="{lang_addsubmitb}"></td>
          <td height="62"><input type="reset" name="reset" value="{lang_addresetb}"></td>
         </tr>
         </table>
         </form>
         </center>
         
        <!-- END add -->
        
        <!-- BEGIN edit -->
         
         <table width="75%" border="0" cellspacing="1" cellpadding="3">
         <tr valign="bottom">
          <td height="62"><input type="submit" name="submit" value="{lang_editsubmitb}">
                </form></td>
          <td height="62">
           	<form method="POST" action="{deleteurl}">
                {common_hidden_vars}
                <input type="submit" name="delete" value="{lang_editdeleteb}">
            </form></td>
          </tr>
         </table>
	</center>

        <!-- END edit -->


