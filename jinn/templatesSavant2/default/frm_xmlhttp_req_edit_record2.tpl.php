<form method="post" name="frm" action="" enctype="multipart/form-data" onSubmit="return onSubmitForm()">
    
   <?=$this->extrahiddens?>
   <input type="hidden" name="submitted" value="true" />
   <?php if($this->where_string_form):?>
   <input type="hidden" name="where_string" value="<?=$this->where_string_form?>" />
   <?php endif?>	

   <table align="" class="editrecordtable" style="border-spacing: 0px;" cellpadding="0" cellspacing="0">

	  <?php foreach($this->form_rows as $r):?>
	  <tr id="TR<?=$r[fieldname]?>" onmousedown="rowactive('TR<?=$r[fieldname]?>');">
		 <?php if($this->edit_object && $r[editfieldlink]):?>
		 <?php if($r[visible]=='hide'):?>
		 <td style="" id="visible<?=$r[fieldname]?>">
			<a href="javascript:void(0);" onclick="toggleVisible('<?=$r[fieldname]?>','visible')"><img src="<?=$this->img_eyehidden?>" alt="" /></a>
		 </td>
		 <?php else:?>
		 <td style="" id="visible<?=$r[fieldname]?>">
			<a href="javascript:void(0);" onclick="toggleVisible('<?=$r[fieldname]?>','hide')"><img src="<?=$this->img_eyevisible?>" alt="" /></a>
		 </td>
		 <?php endif?>

		 <td style="" >
			<a href="javascript:void(0);" onclick="parent.window.open('<?=$r[editfieldlink]?>' , 'poplang_code', 'width=600,height=500,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no')"><img src="<?=$this->img_edit?>" alt="" /></a>
		 </td>
		 <?php endif?>
		 <td style="">
			<?php if($r[tooltip_mouseover]):?>
			<img src="<?=$this->tooltip_img?>" <?=$r[tooltip_mouseover]?> alt="" />
			<?php endif?>
		 </td>

		 <?php if($r[single_col]):?>
		 <td colspan="2" style="font-weight:bold" valign="top" nowrap="nowrap">
			<span style="font-weight:bold"><?=$r[display_name]?></span><br/>
			<?=$r[input]?>
		 </td>
		 <?php else:?>
		 <td style="font-weight:bold" valign="top" nowrap="nowrap"><?=$r[display_name]?></td>
		 <td style=""><?=$r[input]?></td>
		 <?php endif?>

	  </tr>
	  <?php endforeach?>

   </table>
   </form>
