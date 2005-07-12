	  <style>
		 label
		 {
			   margin: 5px;
		 }
		 input
		 {
			   color:#000;
			   padding-right:5px;
			   padding-left:5px;
			   padding-top:2px;
			   padding-bottom:2px;
		 }
		 tr.rij1
		 {
			   background-color:#E8F0F0;
		 }
		 tr.rij2
		 {
			   background-color:#DDDDDD;
		 }
		 td
		 {
			   padding:5px;
		 }
	  </style>
	  
   	  <script>
		 function insertValue(id)
		 {
			   select = document.getElementById('sel'+id);
			   text = document.getElementById('text'+id);
			   text.focus();
 			   tinyMCE.execInstanceCommand('text'+id,'mceInsertContent',false,'%%'+select.value+'%%');
		 }
	  </script>
	  <?
	  if(is_array($this->val))
	  {
		 		 
		if($this->val[r_html] ==1)
		{
		 	$checked='checked';
		}
		else
		{
		   $checked='';
		}
		 
	  }
	  else
	  {
		 
		 $checked='checked';
	  }
   	  ?>
	  <form method="post" name="frm" action="<?=$this->form_action;?>" enctype="multipart/form-data">
   <input type='hidden' value='<?=$this->obj_id;?>'name='obj_id'>
   <?php
	  if($this->val[r_id] != '')
	  {
		 echo('<input type=\'hidden\' value=\''.$this->val[r_id].'\'name=\'report_id\'>');
 	  }
	  ?>
   <table>
	  		<tr valign='top' class='rij2'>
			   <td><label><?=lang('Name');?></label></td>
			   <td><input type='text' name='name' id='name' value='<?=$this->val[r_name];?>'></td><td></td></tr>
			<tr valign='top' class='rij1'>
			   <td></td>
			   <td><input type='checkbox' <?=$checked;?> id = 'g_html' name='g_html'>&nbsp;<?=lang('Generate html start en end tags');?></td>
			   <td></td>
			</tr>  
			<tr valign='top' class='rij2'>
			   <td><label><?=lang('title for HTML-page');?></label></td>
			   <td><input type='text' value='<?=$this->val[r_html_title];?>' id='r_html_title' name='r_html_title'></td>
			   <td></td>
			</tr>
			<tr valign='top' class='rij1'>
			  
			   <td><label><?=lang('Header');?></label></td>
			   <td><?=$this->text1;?></td>
			   <td>
				  <select name="sel1" multiple="multiple" size="6" id='sel1'>
					 <?=$this->attibutes?>
				  </select><br><br>
				  <input type='button' value='<<' onclick="insertValue('1')">
				  
			   </td>
			</tr>
			<tr valign='top' class='rij2'>
			   <td><label><?=lang('Body');?></label></td>
			   <td><?=$this->text2;?></td>
			   <td>
				  <select name="sel2" multiple="multiple" size="6" id='sel2'>
					 <?=$this->attibutes?>
				  </select>
				  <br><br>
				  <input type='button' value='<<' onclick="insertValue('2')">
		
				</td>
			</tr>
			<tr valign='top' class='rij1'>
			   <td><label><?=lang('Footer');?></label></td>
			   <td><?=$this->text3;?></td>
			   <td>
				  <select name="sel3" multiple="multiple" size="6" id='sel3'>
					 <?=$this->attibutes?>
				  </select>
				  <br><br>
				  <input type='button' value='<<' onclick="insertValue('3')">

				  			   </td>
			</tr>
			<tr valign='top' class='rij2'>
			   <td></td><td> 
				  <input type='submit' value='<?=lang('Save');?>' >
				  <input type='reset' value='<?=lang('Reset');?>'>
 					<input type="button" value="Close" onClick="self.close()">
			   </td><td></td>
	  </table>
	 </form>
<!--</body>
</html>-->
