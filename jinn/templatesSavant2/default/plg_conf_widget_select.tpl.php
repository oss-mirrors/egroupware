<tr>
   <td valign="top" class="<?=$rowval?>"><?=$this->cval[label]?></td>
   <td valign="top" class="<?=$rowval?>">
	  <select name="<?=$this->cval[fname]?>">
		 <?php foreach($this->cval[select_arr] as $optkey => $optname):?>
		 <?php unset($selected);
			if($this->set_val==$optkey) $selected='selected="selected"';
		 ?>
		 <option value="<?=$optkey?>" <?=$selected?>><?=$optname?></option>

		 <?php endforeach?>
	  </select>
</td></tr>
