<!-- $Id$ -->
<script language="JavaScript">                                                                                                                                                                 
    self.name="first_Window";
    function addresses()                                                                                                                                                                       
    {                                                                                                                                                                                          
       Window1=window.open('{addresses_link}+document.prefs_form.to.value',"Search","width=800,height=600","scrolling=yes,toolbar=yes,resizable=yes");                                      
    }                                                                                                                                                                                          
   </script>      
    <p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>
      <hr noshade width="98%" align="center" size="1">

      <center>
       <form method="POST" name="prefs_form" action="{actionurl}">
        <table width="70%" border="0" cellspacing="2" cellpadding="2">
        <tr>       
          <td>{lang_select_tax}:</td>                                                                                                                
          <td><input type="text" name="tax" value="{tax}" size=6 maxlength=6>&nbsp;%</td>
         </tr>    
         <tr>                                                                                                                                                                                  
         <td><input type="button" value="{lang_address}" onClick="addresses();"></td>                                                                                                          
         <td><input type="hidden" name="address" value="{address_con}">                                                                                                                        
             <input type="text" name="addressname" size="50" value="{address_name}" readonly>&nbsp;&nbsp;&nbsp;Select per Button!</td>                                                         
         </tr>       
      </table> 
       <table width="39%" border="0" cellspacing="0" cellpadding="0">
         <tr valign="bottom">
          <td height="62" align=left>
           <input type="submit" name="submit" value="{lang_editsubmitb}">
            &nbsp;
           </form>
          </td>
         </tr>
         </table>
       </center>	
     </html>
