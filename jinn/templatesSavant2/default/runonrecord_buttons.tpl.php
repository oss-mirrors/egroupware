<!--<div style="margin:5px 0px 5px 0px;">-->
   <?php if(is_array($this->runonrecordbuttons_arr)):?>
   <?php foreach($this->runonrecordbuttons_arr as $runonrecordbutton):?>
   <input style="background-color:#abd3ab" type="button" name="<?=($runonrecordbutton['eventlabel']?$runonrecordbutton['eventlabel']:$runonrecordbutton['name'])?>" value="<?=($runonrecordbutton['eventlabel']?$runonrecordbutton['eventlabel']:$runonrecordbutton['name'])?>" onclick="location.href='<?=$runonrecordbutton['runonrecordevent_link']?>'" />
   <?php endforeach?>
   <?php endif?>
   <!--</div>-->
