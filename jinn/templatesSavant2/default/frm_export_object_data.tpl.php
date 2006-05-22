<!-- BEGIN first_block -->
<h3><?=$this->title?>&nbsp;:&nbsp;<span style="background-color:#dddddd">&nbsp;<?=$this->objectname?>&nbsp;</span></h3>
<hr/>

<form name="frm" method="post" action="<?=$this->action?>">
   <table cellpadding="10">
	  <tr>
		 <td valign="top">
			<?=$this->export?><br/>
			<br/>
			<input <?=$this->source_1_disabled?> type="radio" name="source" value="filtered" <?=$this->source_1_checked?>/>&nbsp;<?=$this->source_1_label?><br/>
			<input <?=$this->source_2_disabled?> type="radio" name="source" value="unfiltered" <?=$this->source_2_checked?>/>&nbsp;<?=$this->source_2_label?><br/>
			<input <?=$this->source_3_disabled?> type="radio" name="source" value="selected" <?=$this->source_3_checked?>/>&nbsp;<?=$this->source_3_label?><br/>
			<br/>
			<input class="egwbutton"  type="submit" name="do_csv" value="<?=$this->submit?>"/>
			<input class="egwbutton"  type="button" value="<?=$this->cancel?>" onClick="history.back();"/>
		 </td>
		 <td style="border:1px solid;">
			<table>
			   <tr>
				  <td>
					 <?=$this->field_names_row_label?>
				  </td>
				  <td>
					 <input type="checkbox" name="field_names_row" value="true" <?=$this->field_names_row_checked?>/>
				  </td>
			   </tr>
			   <tr>
				  <td>
					 <?=$this->field_terminator_label?>
				  </td>
				  <td>
					 <input type="text" size="1" name="field_terminator" value="<?=$this->field_terminator?>"/>
				  </td>
			   </tr>
			   <tr>
				  <td>
					 <?=$this->field_wrapper_label?>
				  </td>
				  <td>
					 <input type="text" size="1" name="field_wrapper" value="<?=$this->field_wrapper?>"/>
				  </td>
			   </tr>
			   <tr>
				  <td>
					 <?=$this->escape_character_label?>
				  </td>
				  <td>
					 <input type="text" size="1" name="escape_character" value="<?=$this->escape_character?>"/>
				  </td>
			   </tr>
			   <tr>
				  <td>
					 <?=$this->row_terminator_label?>
				  </td>
				  <td>
					 <input type="text" size="1" name="row_terminator" value="<?=$this->row_terminator?>"/>
				  </td>
			   </tr>
			</table>
			<hr/>
			<table width="100%">
			   <tr>
				  <td width="21">
					 <input type="radio" name="columns" value="all" <?=$this->all_checked?>/>
				  </td>
				  <td colspan="2">
					 <?=$this->all_columns_label?>
				  </td>
			   </tr>
			   <tr>
				  <td width="21">
					 <input type="radio" name="columns" value="select" <?=$this->select_checked?>/>
				  </td>
				  <td colspan="2">
					 <?=$this->select_columns_label?>
				  </td>
			   </tr>
			   <!-- END first_block -->

			   <!-- BEGIN columns -->
			   <?php foreach($this->cols_arr as $col_arr):?>
			   <tr>
				  <td>
				  </td>
				  <td width="21">
					 <input type="checkbox" name="col_<?=$col_arr[column]?>" value="<?=$col_arr[column]?>" <?=$col_arr[checked]?>/>
				  </td>
				  <td bgcolor="#dddddd">
					 <?=$col_arr[column_label]?>
				  </td>
			   </tr>
			   <?php endforeach?>
			   <!-- END columns -->

			   <!-- BEGIN second_block -->
			</table>
			<hr/>
			<table>
			   <tr>
				  <td>
					 <?=$this->load_profile_label?>
				  </td>
				  <td>
					 <select onChange="frm.load.value='true'; frm.do_profile.click();" name="load_profile"><?=$this->profiles?></select>
					 <input type="hidden" name="load" value=""/>
				  </td>
			   </tr>
			   <tr>
				  <td>
					 <?=$this->save_profile_label?>
				  </td>
				  <td>
					 <input type="text" name="save_profile" value="<?=$this->save_profile?>"/>
					 <input class="egwbutton"  type="submit" name="do_profile" value="<?=$this->save_as?>"/>
				  </td>
			   </tr>
			</table>
		 </td>
	  </tr>
   </table>
</form>
<!-- END second_block -->

