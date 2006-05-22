<!-- many_to_many -->
<table style="padding:0px; margin:0; cell-border-spacing:0px; border:solid 0px #cccccc" >
   <tr>
	  <td valign="top" style="padding:0;margin:0;width:190px;">
		 <?=$this->sel1_all_from?><br/>
	  </td>

	  <td valign="top" style="padding:0;margin:0;width:40px;">&nbsp;</td>

	  <td valign="top" style="padding:0;margin:0;"><?=lang('Linked entries')?><br/>
	  </td>
   </tr>
   <tr>
	  <td valign="top" style="padding:0;margin:0;">
		 <select onDblClick="<?=$this->on_dbl_click1?>" style="width:190px;" multiple size="5" name="<?=$this->sel1_name?>">
			<?=$this->sel1_options?>
		 </select>
	  </td>

	  <td valign="middle" style="vertical-align:middle;padding:0;margin:0;width:40px;">
		 <input onClick="<?=$this->on_dbl_click1?>" class="egwbutton"  style="width:40px;margin:3px 10px 3px 10px;" type="button" value=" &gt;&gt; " name="add">
		 <input onClick="<?=$this->on_dbl_click2?>" class="egwbutton"  style="width:40px;margin:3px 10px 3px 10px;" type="button" value=" &lt;&lt; " name="remove">
	  </td>

	  <td valign="top" style="padding:0;margin:0;width:190px;">
		 <select onDblClick="<?=$this->on_dbl_click2?>" style="width:190px;color:red" multiple size="5" name="<?= $this->sel2_name?>">
			<?=$this->sel2_options?>
		 </select>
	  </td>
   </tr>
</table>
<input type="hidden" name="<?=$this->m2m_rel_string_name?>" value="<?=$this->m2m_rel_string_val?>">
<input type="hidden" name="<?=$this->m2m_opt_string_name?>">
<!-- many_to_many -->

