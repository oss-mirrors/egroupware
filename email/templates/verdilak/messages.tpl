<script>
  function do_action(act)
  {
     flag = 0;
     for (i=0; i<document.delmov.elements.length; i++) {
        //alert(document.delmov.elements[i].type);
        if (document.delmov.elements[i].type == "checkbox") {
           if (document.delmov.elements[i].checked) {
              flag = 1;
           }
        }
     }
     if (flag != 0) {
        document.delmov.what.value = act;
        document.delmov.submit();
     } else {
        alert("{lang_select_message_first}");
        document.delmov.tofolder.selectedIndex = 0;
     }
  }

  function check_all()
  {
     for (i=0; i<document.delmov.elements.length; i++) {
        if (document.delmov.elements[i].type == "checkbox") {
           if (document.delmov.elements[i].checked) {
              document.delmov.elements[i].checked = false;
           } else {
              document.delmov.elements[i].checked = true;
           }
        }
     }
  }
</script>

<form name="switchbox" action="{form_action}" method="post">
 <table border="0">
  <tr bgcolor="{th_bg}">
   <td><input type="checkbox"></td>
   <td>&nbsp;</td>
   <td>&nbsp;{sort_date}</td>
   <td>&nbsp;{sort_subject}</td>
   <td>&nbsp;{sort_sender}</td>
   <td>&nbsp;{sort_size}</td>
  </tr>

{rows}

 </table>
</form>

