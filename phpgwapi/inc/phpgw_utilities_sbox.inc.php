<?PHP
/**
*
* class for creating predefines select boxes
*
* @author		Marc Logemann [loge@mail.com]
* @version	0.9
* 
*/
class sbox {
	
	var $monthnames = array ("", "january", "February", "March", "April", "May", "June", "July",
									"August", "September", "October", "November", "December");

   function hour_formated_text($name, $selected = 0)
   {
      global $phpgw;

      $s = '<select name="' . $name . '">';
      $t_s[$selected] = " selected";
	   for ($i=0; $i<24; $i++) {
	       $s .= '<option value="' . $i . '"' . $t_s[$i] . '>'
  		      . $phpgw->common->formattime($i+1,"00") . '</option>';
  		  $s .= "\n";
		}
		$s .= "</select>";
		return $s;
   }

   function hour_text($name, $selected = 0)
   {
      global $phpgw;

      $s = '<select name="' . $name . '">';
      $t_s[$selected] = " selected";
	   for ($i=1; $i<13; $i++) {
	       $s .= '<option value="' . $i . '"' . $t_s[$i] . '>'
  		      . $i . '</option>';
  		  $s .= "\n";
		}
		$s .= "</select>";
		return $s;
   }

   // I would like to add a increment feature
   function sec_minute_text($name, $selected = 0)
   {
      $s = '<select name="' . $name . '">';
      $t_s[$selected] = " selected";
	   for ($i=0; $i<60; $i++) {
	       $s .= '<option value="' . $i . '"' . $t_s[sprintf("%02d",$i)] . '>' . sprintf("%02d",$i) . '</option>';
  		  $s .= "\n";
		}
		$s .= "</select>";
		return $s;
   }
   
   function ap_text($name,$selected)
   {
      $selected = strtolower($selected);
      $t[$selected] = " selected";
      $s = '<select name="' . $name . '">'
         . ' <option value="am"' . $t["am"] . '>am</option>'
         . ' <option value="pm"' . $t["pm"] . '>pm</option>';
		$s .= "</select>";
		return $s;
   }

   function full_time($hour_name,$hour_selected,$min_name,$min_selected,$sec_name,$sec_selected,$ap_name,$ap_selected)
   {
      // This needs to be changed to support there time format preferences
      $s = $this->hour_text($hour_name,$hour_selected)
         . $this->sec_minute_text($min_name,$min_selected)
         . $this->sec_minute_text($sec_name,$sec_selected)
         . $this->ap_text($ap_name,$ap_selected);
      return $s;
   }

	function getMonthText($name, $selected=0)
	{
		$out = "<select name=\"$name\">\n";
		
		for($i=0;$i<count($this->monthnames);$i++)
		{              
			$out .= "<option value=\"$i\"";
			if($selected==$i) $out .= " SELECTED";
			$out .= ">"; 
			if($this->monthnames[$i]!="") 
				$out .= lang($this->monthnames[$i]);
			else
				$out .= "";
			$out .= "</option>\n";
		}
      $out .= "</select>\n";
      return $out;
   }
   
   function getDays($name, $selected=0)
   {
   	$out = "<select name=\"$name\">\n";
		
		for($i=0;$i<32;$i++)
		{              
			if($i==0) $val = ""; else $val = $i;
			$out .= "<option value=\"$val\"";
			if($selected==$i) $out .= " SELECTED";
			$out .= ">$val</option>\n";
		}
      $out .= "</select>\n";
      return $out;
   }

	function getYears($name, $selected=0)
   {
   	$out = "<select name=\"$name\">\n";
		
		$out .= "<option value=\"\"";
		if($selected == 0 OR $selected == "") $out .= " SELECTED";
		$out .= "></option>\n";
		
		for($i=date("Y");$i<date("Y")+5;$i++)
		{              
			$out .= "<option value=\"$i\"";
			if($selected==$i) $out .= " SELECTED";
			$out .= ">$i</option>\n";
		}
      $out .= "</select>\n";
      return $out;
   }

	function getPercentage($name, $selected=0)
   {
   	$out = "<select name=\"$name\">\n";

		for($i=0;$i<101;$i=$i+10)
		{              
			$out .= "<option value=\"$i\"";
			if($selected==$i) $out .= " SELECTED";
			$out .= ">$i%</option>\n";
		}
      $out .= "</select>\n";
      // echo $out;
      return $out;
   }

	function getPriority($name, $selected=2)
   {
   	$arr = array("", "low", "normal", "high");
   	$out = "<select name=\"$name\">\n";
		
		for($i=1;$i<count($arr);$i++)
		{              
			$out .= "<option value=\"";
			$out .= $i;
			$out .= "\"";
			if($selected==$i) $out .= " SELECTED";
			$out .= ">";
			$out .= lang($arr[$i]);
			$out .= "</option>\n";
		}
      $out .= "</select>\n";
      return $out;
   }

	function getAccessList($name, $selected="private")
   {
   	$arr = array("private" => "Private",
   						"public" => "Global public",
   						"group" => "Group public");

       if (ereg(",", $selected))
 {
          $selected = "group";
       }
   						
   	$out = "<select name=\"$name\">\n";
		
		for(reset($arr);current($arr);next($arr))
		{              
			$out .= '<option value="' . key($arr) . '"';
			if($selected==key($arr)) $out .= " SELECTED";
			$out .= ">" . pos($arr) . "</option>\n";
		}
      $out .= "</select>\n";
      return $out;
   }
   
   function getGroups($groups, $selected="")
   {
      global $phpgw;

  	$out = '<select name="n_groups[]" multiple>';
      while (list($null,$group) = each($groups)) {
         $out .= '<option value="' . $group[0] . '"';
         if (ereg("," . $group[0] . ",", $selected))
 {
            $out .= " SELECTED";
         }
         $out .= ">" . $group[1] . "</option>\n";
      }
      $out .= "</select>\n";

      return $out;
   }
}