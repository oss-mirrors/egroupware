<fieldset  id="fieldset<?php echo($this->nr);?>" class="slot" class="overflow:hidden;">
<legend>Slot <?php echo($this->nr);?></legend>
<div id="slot<?php echo($this->nr);?>">
   <table>
	  <?foreach($this->multi_items as $item):?>
	  <?php echo($item);?>
	  <?php endforeach?>
	  <tr><td align="right"><input type="button" class="egwbutton" value="Delete" onclick="deleteSlot(<?php echo($this->nr);?>);"></td</tr>
   </table>
</div>
</fieldset>

