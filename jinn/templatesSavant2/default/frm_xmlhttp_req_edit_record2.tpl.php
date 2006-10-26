<form method="post" id="frm<?=$this->m2oid?>" name="frm<?=$this->m2oid?>" action="" enctype="multipart/form-data">
   <?=$this->extrahiddens?>
   <input type="hidden" name="submitted" value="true" />
   <?php if($this->where_string_form):?>
   <input type="hidden" name="where_string" value="<?=$this->where_string_form?>" />
   <?php endif?>	

   <div style="">	
   <table class="xxxeditrecordtable"  style="border-spacing: 0px;">

	  <?php foreach($this->form_rows as $r):?>
	  <tr id="TR<?=$r[fieldname]?>" xxxonmousedown="rowactive('TR<?=$r[fieldname]?>');">

		 <?php if($r[single_col]):?>
		 <td colspan="2" style="" valign="top">
			<span style="font-weight:bold"><?=$r[display_name]?></span><br/>
			<?=$r[field_help_info]?>
			<br/>
			<?=$r[input]?>
		 </td>
		 <?php else:?>
		 <td style="font-weight:bold;width:200px;" valign="top" ><?=$r[display_name]?>
			<br/>
			<span style="font-weight:normal"><?=$r[field_help_info]?></span>
		 </td>
		 <td style=""><?=$r[input]?></td>
		 <?php endif?>

	  </tr>
	  <?php endforeach?>
   </table>
</div>
</form>
