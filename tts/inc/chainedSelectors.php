<?php
    /*
    ** Class: chainedSelectors
    ** Description: This class allows you to create two selectors.  Selections
    ** made in the first selector cause the second selector to be updated.
    ** PHP is used to dynamically create the necessary JavaScript.
    */

    //These constants make the code a bit more readable.  They should be
    //used in in the creation of the input data arrays, too.
    define("CS_FORM", 0);
    define("CS_FIRST_SELECTOR", 1);
    define("CS_SECOND_SELECTOR", 2);
    define("CS_THIRD_SELECTOR", 3);

    define("CS_SOURCE_ID", 0);
    define("CS_SOURCE_LABEL", 1);
    define("CS_TARGET_ID", 2);
    define("CS_TARGET_LABEL", 3);
    define("CS_TARGET2_ID", 4);
    define("CS_TARGET2_LABEL", 5);

    class chainedSelectors
    {
        /*
        ** Properties
        */

        //Array of names for the form and the two selectors.
        //Should take the form of array("myForm", "Selector1", "Selector2")
        var $names;

        //Array of data used to fill the two selectors
        var $data;

        //Unique set of choices for the first selector, generated on init
        var $uniqueChoices;
        var $uniqueChoices2;
        var $uniqueTargetChoices;
        var $uniqueTarget2Choices;

        //Calculated counts

        var $maxTargetChoices;
        var $maxTarget2Choices;
        var $longestTargetChoice;
        var $longestTarget2Choice;


        /*
        ** Methods
        */

        //constructor
        function chainedSelectors($names, $data)
        {
            /*
            **copy parameters into properties
            */
            $this->names = $names;
            $this->data = $data;



            $temp_source_id = "";
            $temp_target_id = "";

            /*
            ** traverse data, create uniqueChoices, get limits
            */
            foreach($data as $row)
            {

				if (($row[CS_SOURCE_ID]) != $temp_source_id)
                {
                  $maxPerChoice[($row[CS_SOURCE_ID])]++;
                }
                else
                {
                 	if (($row[CS_TARGET_ID]) != $temp_target_id)
                    {
                     	$maxPerChoice[($row[CS_SOURCE_ID])]++;
                    }
                    else
                    {

                    }
                }

            	$temp_source_id = ($row[CS_SOURCE_ID]);
                $temp_target_id = ($row[CS_TARGET_ID]);

                //create list of unique choices for first selector
                $this->uniqueChoices[($row[CS_SOURCE_ID])] = $row[CS_SOURCE_LABEL];

                $this->uniqueChoices2Label[($row[CS_SOURCE_ID]).($row[CS_TARGET_ID])] = $row[CS_TARGET_LABEL];
                $this->uniqueChoices2Id[($row[CS_SOURCE_ID]).($row[CS_TARGET_ID])] = $row[CS_TARGET_ID];

                $maxPerChoice2[($row[CS_SOURCE_ID]).($row[CS_TARGET_ID])]++;

                //create list of unique choices for second selector
                $this->uniqueTargetChoices[($row[CS_TARGET_ID])] = $row[CS_TARGET_LABEL];

                //create list of unique choices for second selector
                $this->uniqueTarget2Choices[($row[CS_TARGET2_ID])] = $row[CS_TARGET2_LABEL];

                //find the maximum choices for target selector
                //$maxPerChoice[($row[CS_SOURCE_ID])]++;
                //$maxPerChoice[($row[CS_SOURCE_ID])]++;

                //////$maxPerChoice2[($row[CS_TARGET_ID])]++;

                //find longest value for target selector
                if(strlen($row[CS_TARGET_LABEL]) > $this->longestTargetChoice)
                {
                    $this->longestTargetChoice=strlen($row[CS_TARGET_LABEL]);
                }
                //find longest value for target 2 selector
                if(strlen($row[CS_TARGET2_LABEL]) > $this->longestTarget2Choice)
                {
                    $this->longestTarget2Choice=strlen($row[CS_TARGET2_LABEL]);
                }

            }

            foreach($data as $row)
            {
            }

            foreach($this->uniqueTargetChoices as $row)
            {
             $i++;
            }

            $this->maxTargetChoices = max($maxPerChoice);
            $this->maxTarget2Choices = max($maxPerChoice2);
        }

        //prints the JavaScript function to update second selector
        function printUpdateFunction()
        {
            /*
            ** Create some variables to make the code
            ** more readable.
            */
            $sourceSelector = "document." . $this->names[CS_FORM] . "." .
                $this->names[CS_FIRST_SELECTOR];
            $targetSelector = "document." . $this->names[CS_FORM] . "." .
                $this->names[CS_SECOND_SELECTOR];
            $target2Selector = "document." . $this->names[CS_FORM] . "." .
                $this->names[CS_THIRD_SELECTOR];

            /*
            ** Start the function
            */
            print("function update" .$this->names[CS_SECOND_SELECTOR] . "()\n");

            print("{\n");

            /*
            ** Add code to clear out next selector
            */
            print("\t//clear " . $this->names[CS_SECOND_SELECTOR] . "\n");
            print("\tfor(index=0; index < $this->maxTargetChoices; index++)\n");
            print("\t{\n");
            print("\t\t" . $targetSelector . ".options[index].text = '';\n");
            print("\t\t" . $targetSelector . ".options[index].value = '';\n");
            print("\t}\n\n");
            print("\t" . $targetSelector . ".options[0].selected = true;\n\n");

            /*
            ** Add code to find which was selected
            */
            print("whichSelected = " . $sourceSelector . ".selectedIndex;\n");

            /*
            ** Add giant "if" tree that puts values into target selector
            ** based on which selection was made in source selector
            */

            //loop over each value of this selector
            foreach($this->uniqueChoices as $sourceValue=>$sourceLabel)
            {
                print("\tif(" . $sourceSelector .
                    ".options[whichSelected].value == " .
                    "'$sourceValue')\n");
                print("\t{\n");

                $count=0;
                $optionValueOld = "";


                foreach($this->data as $row)
                {
                    if($row[0] == $sourceValue)
                    {
                        $optionValue = $row[CS_TARGET_ID];
                        $optionLabel = $row[CS_TARGET_LABEL];

                        if ($optionValue == $optionValueOld)
                        {}
                        else
                        {
	                        print("\t\t" . $targetSelector .
                            ".options[$count].value = '$optionValue';\n");
                            print("\t\t" . $targetSelector .
                            ".options[$count].text = '$optionLabel';\n\n");
                            $count++;
                        }
                        $optionValueOld = $optionValue;

                    }
                }

                print("\t}\n\n");
            }

            print("\treturn true;\n");
            print("}\n\n");





             /*
            ** Start the function
            */
            print("function update" .$this->names[CS_THIRD_SELECTOR] . "()\n");

            print("{\n");

            /*
            ** Add code to clear out next selector
            */

            print("\t//clear " . $this->names[CS_THIRD_SELECTOR] . "\n");
            print("\tfor(index=0; index < $this->maxTarget2Choices; index++)\n");
            print("\t{\n");
            print("\t\t" . $target2Selector . ".options[index].text = '';\n");
            print("\t\t" . $target2Selector . ".options[index].value = '';\n");
            print("\t}\n\n");
            print("\t" . $target2Selector . ".options[0].selected = true;\n\n");

            /*
            ** Add code to find which was selected
            */
            print("whichSelected2 = " . $targetSelector . ".selectedIndex;\n");

            /*
            ** Add giant "if" tree that puts values into target selector
            ** based on which selection was made in source selector
            */

            //loop over each value of this selector
            foreach($this->uniqueTargetChoices as $targetValue=>$targetLabel)
            {
                print("\tif(" . $targetSelector .
                    ".options[whichSelected2].value == " .
                    "'$targetValue')\n");
                print("\t{\n");

                $count=0;
                foreach($this->data as $row)
                {
                    if($row[2] == $targetValue)
                    {
                        $optionValue2 = $row[CS_TARGET2_ID];
                        $optionLabel2 = $row[CS_TARGET2_LABEL];

                        print("\t\t" . $target2Selector .
                            ".options[$count].value = '$optionValue2';\n");
                        print("\t\t" . $target2Selector .
                            ".options[$count].text = '$optionLabel2';\n\n");

                        $count++;
                    }
                }

                print("\t}\n\n");
            }

            print("\treturn true;\n");
            print("}\n\n");

        }


        function printUpdateFunction2()
        {
            /*
            ** Create some variables to make the code
            ** more readable.
            */
            $sourceSelector = "document." . $this->names[CS_FORM] . "." .
                $this->names[CS_FIRST_SELECTOR];
            $targetSelector = "document." . $this->names[CS_FORM] . "." .
                $this->names[CS_SECOND_SELECTOR];
            $target2Selector = "document." . $this->names[CS_FORM] . "." .
                $this->names[CS_THIRD_SELECTOR];

            /*
            ** Start the function
            */
            $ret="function update" .$this->names[CS_SECOND_SELECTOR] . "()\n";

            $ret=$ret."{\n";

            /*
            ** Add code to clear out next selector
            */
            $ret=$ret."\t//clear " . $this->names[CS_SECOND_SELECTOR] . "\n";
            $ret=$ret."\tfor(index=0; index < $this->maxTargetChoices; index++)\n";
            $ret=$ret."\t{\n";
            $ret=$ret."\t\t" . $targetSelector . ".options[index].text = '';\n";
            $ret=$ret."\t\t" . $targetSelector . ".options[index].value = '';\n";
            $ret=$ret."\t}\n\n";
            $ret=$ret."\t" . $targetSelector . ".options[0].selected = true;\n\n";

            /*
            ** Add code to find which was selected
            */
            $ret=$ret."\t" ."whichSelected = " . $sourceSelector . ".selectedIndex;\n";
            $ret=$ret."\t\n";

            /*
            ** Add giant "if" tree that puts values into target selector
            ** based on which selection was made in source selector
            */

            //loop over each value of this selector
            foreach($this->uniqueChoices as $sourceValue=>$sourceLabel)
            {
                $ret=$ret."\tif(" . $sourceSelector .
                    ".options[whichSelected].value == " .
                    "'$sourceValue')\n";
                $ret=$ret."\t{\n";

                $count=0;
                $optionValueOld = "";


                foreach($this->data as $row)
                {
                    if($row[0] == $sourceValue)
                    {
                        $optionValue = $row[CS_TARGET_ID];
                        $optionLabel = $row[CS_TARGET_LABEL];

                        if ($optionValue == $optionValueOld)
                        {}
                        else
                        {
                            $ret=$ret."\t\t" . $targetSelector .
                            ".options[$count].value = '$optionValue';\n";
                            $ret=$ret."\t\t" . $targetSelector .
                            ".options[$count].text = '$optionLabel';\n\n";
                            $count++;
                        }
                        $optionValueOld = $optionValue;

                    }
                }

                $ret=$ret."\t}\n\n";
            }

            $ret=$ret."\treturn true;\n";
            $ret=$ret."}\n\n";





             /*
            ** Start the function
            */
            $ret=$ret."function update" .$this->names[CS_THIRD_SELECTOR] . "()\n";

            $ret=$ret."{\n";

            /*
            ** Add code to clear out next selector
            */

            $ret=$ret."\t//clear " . $this->names[CS_THIRD_SELECTOR] . "\n";
            $ret=$ret."\tfor(index=0; index < $this->maxTarget2Choices; index++)\n";
            $ret=$ret."\t{\n";
            $ret=$ret."\t\t" . $target2Selector . ".options[index].text = '';\n";
            $ret=$ret."\t\t" . $target2Selector . ".options[index].value = '';\n";
            $ret=$ret."\t}\n\n";
            $ret=$ret."\t" . $target2Selector . ".options[0].selected = true;\n\n";

            /*
            ** Add code to find which was selected
            */
            $ret=$ret."\t" ."whichSelected2 = " . $targetSelector . ".selectedIndex;\n";
            $ret=$ret."\t\n";

            /*
            ** Add giant "if" tree that puts values into target selector
            ** based on which selection was made in source selector
            */

            //loop over each value of this selector
            //$this->uniqueChoices2Label[($row[CS_SOURCE_ID]).($row[CS_TARGET_ID])] = $row[CS_TARGET_LABEL];
            //$this->uniqueChoices2Id[($row[CS_SOURCE_ID]).($row[CS_TARGET_ID])] = $row[CS_TARGET_ID];


            foreach($this->uniqueTargetChoices as $targetValue=>$targetLabel)
            {

                $ret=$ret."\tif(" . $targetSelector .
                    ".options[whichSelected2].value == " .
                    "'$targetValue')\n";
                $ret=$ret."\t{\n";

                $count=0;

                foreach($this->data as $row)
                {
                  if ($count < $this->maxTarget2Choices)
                  {

                    if($row[2] == $targetValue)
                    {
                        $optionValue2 = $row[CS_TARGET2_ID];
                        $optionLabel2 = $row[CS_TARGET2_LABEL];

                        $ret=$ret."\t\t" . $target2Selector .
                            ".options[$count].value = '$optionValue2';\n";
                        $ret=$ret."\t\t" . $target2Selector .
                            ".options[$count].text = '$optionLabel2';\n\n";

                        $count++;
                    }

                  }

                }

                $ret=$ret."\t}\n\n";
            }

            $ret=$ret."\treturn true;\n";
            $ret=$ret."}\n\n";

            return $ret;
        }

        //print the thre selectors
        function printSelectors()
        {
            /*
            **create prefilled first selector
            */
            $selected=TRUE;
            print("<select name=\"" . $this->names[CS_FIRST_SELECTOR] . "\" " .
                "onChange=\"update".$this->names[CS_SECOND_SELECTOR]."(); update".$this->names[CS_THIRD_SELECTOR]."();\">\n");
            foreach($this->uniqueChoices as $key=>$value)
            {
                print("\t<option value=\"$key\"");
                if($selected)
                {
                    //print(" selected=\"selected\"");
                    print(" selected");
                    $selected=FALSE;
                }
                print(">$value</option>\n");
            }
            print("</select>\n");

            /*
            **create empty target selector
            */
            $dummyData = str_repeat("X", $this->longestTargetChoice);

            print("<select name=\"".$this->names[CS_SECOND_SELECTOR] . "\" " .
                "onChange=\"update".$this->names[CS_THIRD_SELECTOR]."();\">\n");
            for($i=0; $i < $this->maxTargetChoices; $i++)
            {
                print("\t<option value=\"\">$dummyData</option>\n");
            }
            print("</select>\n");

             /*
            **create empty target 2 selector
            */
            $dummyData2 = str_repeat("X", $this->longestTarget2Choice);

            print("<select name=\"".$this->names[CS_THIRD_SELECTOR]."\">\n");
            for($i=0; $i < $this->maxTarget2Choices; $i++)
            {
                print("\t<option value=\"\">$dummyData2</option>\n");
            }
            print("</select>\n");
        }

        function printSelector1($def_value = "a", $disabled = "")
        {
            /*
            **create prefilled first selector
            */
            if ($def_value == "a" OR $def_value =="")
            {
                $need_check_def_value = FALSE;
             	$selected=TRUE;
            }
            else
            {
                $need_check_def_value = TRUE;
                $selected=FALSE;
            }
            $ret="<select name=\"" . $this->names[CS_FIRST_SELECTOR] . "\" $disabled " .
                "onChange=\"update".$this->names[CS_SECOND_SELECTOR]."(); update".$this->names[CS_THIRD_SELECTOR]."();\">\n";
            foreach($this->uniqueChoices as $key=>$value)
            {
                $ret=$ret."\t<option value=\"$key\"";
                if ($def_value <> "a")
                {
                    if ($def_value == $key)
                    {
                     	$ret=$ret." selected";
                        $need_check_def_value = FALSE;
                    }
                }
                if($selected)
                {
                    //$ret=$ret." selected=\"selected\"";
                    $ret=$ret." selected";
                    $selected=FALSE;
                }

                $ret=$ret.">$value</option>\n";
            }
            if ($need_check_def_value)
            {
               $ret=$ret."\t<option value=\"$def_value\" selected";
               $ret=$ret.">$def_value</option>\n";
            }
            $ret=$ret."</select>\n";

            return $ret;
        }

      
        function printSelector2($value="a", $name="a", $disabled = "")
        {
             /*
            **create empty target 3 selector
            */
            $selected = TRUE;

            $dummyData = str_repeat("X", $this->longestTargetChoice);

            $ret="<select name=\"".$this->names[CS_SECOND_SELECTOR] . "\" $disabled " .
            "onChange=\"update".$this->names[CS_THIRD_SELECTOR]."();\">\n";
            for($i=0; $i < $this->maxTargetChoices; $i++)
            {
                 if ($selected)
                {
                   if ($value <> "a" OR $value <> "")
                  {
                    if ($name == "a" OR $name == "")
                    {
                         $name = $value;
                    }
                    if ($selected)
                    {
                         $ret=$ret."\t<option value=\"$value\">$name</option>\n";
                    }
                  }
                }
                else
                {
                    $ret=$ret."\t<option value=\"\">$dummyData</option>\n";
                }
                $selected = FALSE;
            }
            $ret=$ret."</select>\n";

            return $ret;

        }

        function printSelector3($value="a", $name="a", $disabled = "")
        {
             /*
            **create empty target 3 selector
            */
            $selected = TRUE;

            $dummyData2 = str_repeat("X", $this->longestTarget2Choice);

            $ret="<select name=\"".$this->names[CS_THIRD_SELECTOR]."\" $disabled>\n";
            for($i=0; $i < $this->maxTarget2Choices; $i++)
            {
             	if ($selected)
                {
             	  if ($value <> "a" OR $value <> "")
                  {
                    if ($name == "a" OR $name == "")
                    {
                     	$name = $value;
                    }
                    if ($selected)
                    {
                     	$ret=$ret."\t<option value=\"$value\">$name</option>\n";
                    }
                  }
                }
                else
                {
                    $ret=$ret."\t<option value=\"\">$dummyData2</option>\n";
                }
                $selected = FALSE;
            }
            $ret=$ret."</select>\n";

            return $ret;

        }

        //prints a call to the update function
        function initialize()
        {
            print("update" .$this->names[CS_SECOND_SELECTOR] . "();\n");
             print("update" .$this->names[CS_THIRD_SELECTOR] . "();\n");

        }
         //prints a call to the update function
        function initialize2()
        {
            $ret="\n";
            $ret=$ret."\t" ."update" .$this->names[CS_SECOND_SELECTOR] . "();\n";
            $ret=$ret."\t" ."update" .$this->names[CS_THIRD_SELECTOR] . "();\n";
            return $ret;
        }


    }
?>