<!-- BEGIN admin.tpl -->
<br>
   <form method="POST" action="{action_url}">
   <table border="0" align="center" cellspacing="1" cellpadding="1">
    <tr bgcolor="#EEEEEE">
     <td>{lang_ownernotification}</td>
     <td><input type="checkbox" name="ownernotification"{ownernotification}></td>
    </tr>
    <tr bgcolor="#EEEEEE">
     <td>{lang_groupnotification}</td>
     <td><input type="checkbox" name="groupnotification"{groupnotification}></td>
    </tr>
    <tr bgcolor="#EEEEEE">
     <td>{lang_assignednotification}</td>
     <td><input type="checkbox" name="assignednotification"{assignednotification}></td>
    </tr>

    <tr bgcolor="#EEEEEE">
     <td>{lang_assignmentnotification}</td>
     <td><input type="checkbox" name="assignmentnotification"{assignmentnotification}></td>
    </tr>

    <tr bgcolor="#EEEEEE">
     <td>{lang_assignmentgroupnotification}</td>
     <td><input type="checkbox" name="assignmentgroupnotification"{assignmentgroupnotification}></td>
    </tr>

    <tr bgcolor="#EEEEEE">
     <td>{lang_email2assignednotification}</td>
     <td><input type="checkbox" name="email2assignednotification"{email2assignednotification}></td>
    </tr>

    <tr bgcolor="#EEEEEE">
     <td>{lang_email2assignmentnotification}</td>
     <td><input type="checkbox" name="email2assignmentnotification"{email2assignmentnotification}></td>
    </tr>

    <tr bgcolor="#EEEEEE">
     <td>{lang_email2assignmentgroupnotification}</td>
     <td><input type="checkbox" name="email2assignmentgroupnotification"{email2assignmentgroupnotification}></td>
    </tr>

    <tr bgcolor="#EEEEEE">
     <td>{lang_email2highpriorityassignednotification}</td>
     <td><input type="checkbox" name="email2highpriorityassignednotification"{email2highpriorityassignednotification}></td>
    </tr>

    <tr bgcolor="#EEEEEE">
     <td>{lang_email2highpriorityassignmentnotification}</td>
     <td><input type="checkbox" name="email2highpriorityassignmentnotification"{email2highpriorityassignmentnotification}></td>
    </tr>

    <tr bgcolor="#EEEEEE">
     <td>{lang_email2highpriorityassignmentgroupnotification}</td>
     <td><input type="checkbox" name="email2highpriorityassignmentgroupnotification"{email2highpriorityassignmentgroupnotification}></td>
    </tr>

    <tr>
      <td colspan="3" align="center" height="40">
       <input type="submit" name="submit" value="{lang_submit}"> &nbsp;
       <input type="submit" name="cancel" value="{lang_cancel}">
     </td>
    </tr>
   </table>
   </form>
