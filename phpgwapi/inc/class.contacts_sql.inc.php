<?php
  /**************************************************************************\
  * phpGroupWare API - Accounts manager for SQL                              *
  * This file written by Joseph Engo <jengo@phpgroupware.org>                *
  * View and manipulate contact records using SQL                            *
  * Copyright (C) 2001 Joseph Engo                                           *
  * -------------------------------------------------------------------------*
  * This library is part of the phpGroupWare API                             *
  * http://www.phpgroupware.org/api                                          * 
  * ------------------------------------------------------------------------ *
  * This library is free software; you can redistribute it and/or modify it  *
  * under the terms of the GNU Lesser General Public License as published by *
  * the Free Software Foundation; either version 2.1 of the License,         *
  * or any later version.                                                    *
  * This library is distributed in the hope that it will be useful, but      *
  * WITHOUT ANY WARRANTY; without even the implied warranty of               *
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     *
  * See the GNU Lesser General Public License for more details.              *
  * You should have received a copy of the GNU Lesser General Public License *
  * along with this library; if not, write to the Free Software Foundation,  *
  * Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            *
  \**************************************************************************/

  /* $Id$ */

  /*
     contacts_extra (
       contact_id          int,
       contact_owner       int,
       contact_name        varchar(255),
       contact_value       varchar(255)
     );
  */
  
  /* ldap is a copy of sql for now */
  
  class contacts_
  {
     var $db;
     var $account_id;
     var $stock_contact_fields;         // This is an array of all the fields in the contacts table
     var $total_records;                // This will contain numrows for data retrieved

     function contacts_()
     {
        global $phpgw, $phpgw_info;

       $this->db = $phpgw->db;

        $this->account_id = $phpgw_info["user"]["account_id"];
	// rework the following to be a simple sed style creation
        $this->stock_contact_fields = array("FN"              => "FN",        //'firstname lastname'
                                            "SOUND"           => "SOUND",
                                            "ORG.Name"        => "ORG.Name",  //company
                                            "ORG.Unit"        => "ORG.Unit",  //division
                                            "TITLE"           => "TITLE",
                                            "N.Given"         => "N_Given",   //firstname
                                            "N.Family"        => "N_Family",  //lastname
                                            "N.Middle"        => "N_Middle",
                                            "N.Prefix"        => "N_Prefix",
                                            "N.Suffix"        => "N_Suffix",
                                            "LABEL"           => "LABEL",
                                            "ADR_Street"      => "ADR_Street",
                                            "ADR.Locality"    => "ADR_Locality",   //city
                                            "ADR.Region"      => "ADR_Region",     //state
                                            "ADR.PostalCode"  => "ADR_PostalCode", //zip
                                            "ADR.CountryName" => "ADR_CountryName",
                                            "ADR_Work"        => "ADR_Work",
                                            "ADR_Home"        => "ADR_Home",
                                            "ADR_Parcel"      => "ADR_Parcel",
                                            "ADR_Postal"      => "ADR_Postal",
                                            "TZ"              => "TZ",
                                            "GEO"             => "GEO",
                                            "A.TEL"           => "A_TEL",
					    "A.TEL.Work"      => "A_TEL_Work",
                                            "A.TEL.Home"      => "A_TEL_Home",
                                            "A.TEL.Voice"     => "A_TEL_Voice",
                                            "A.TEL.Msg"       => "A_TEL_Msg",
                                            "A.TEL.Fax"       => "A_TEL_Fax",
                                            "A.TEL.Prefer"    => "A_TEL_Prefer",
                                            "B.TEL"           => "B_TEL",
					    "B.TEL.Work"      => "B_TEL_Work",
                                            "B.TEL.Home"      => "B_TEL_Home",
                                            "B.TEL.Voice"     => "B_TEL_Voice",
                                            "B.TEL.Msg"       => "B_TEL_Msg",
                                            "B.TEL.Fax"       => "B_TEL_Fax",
                                            "B.TEL.Prefer"    => "B_TEL_Prefer",
                                            "C.TEL"           => "C_TEL",
					    "C.TEL.Work"      => "C_TEL_Work",
                                            "C.TEL.Home"      => "C_TEL_Home",
                                            "C.TEL.Voice"     => "C_TEL_Voice",
                                            "C.TEL.Msg"       => "C_TEL_Msg",
                                            "C.TEL.Fax"       => "C_TEL_Fax",
                                            "C.TEL.Prefer"    => "C_TEL_Prefer",
                                            "D.EMAIL"         => "D_EMAIL",
					    "D.EMAILTYPE"     => "D_EMAILTYPE",   //'INTERNET','CompuServe',etc...
                                            "D.EMAIL.Work"    => "D_EMAIL_Work",  //yn
                                            "D.EMAIL.Home"    => "D_EMAIL_Home",  //yn
                                            );
     }

     function read_single_entry($id,$fields)
     {
        list($ab_fields,$ab_fieldnames,$extra_fields) = $this->split_ab_and_extras($fields);
        if (count($ab_fieldnames)) {
           $t_fields = "," . implode(",",$ab_fieldnames);
           if ($t_fields == ",") {
              unset($t_fields);
           }
        }

        $this->db2 = $this->db;
 
        $this->db->query("select id,lid,owner $t_fields from contacts WHERE id='$id'");
        $this->db->next_record();
       
        $return_fields[0]["id"]     = $this->db->f("id");
	$return_fields[0]["lid"]    = $this->db->f("lid");
        $return_fields[0]["owner"]  = $this->db->f("owner");
        if (gettype($ab_fieldnames) == "array") {
          while (list($f_name) = each($ab_fieldnames)) {
            $return_fields[0][$f_name] = $this->db->f($f_name);
          }
        }
        $this->db2->query("select contact_name,contact_value from contacts_extra where contact_id='"
                           . $this->db->f("id") . "'",__LINE__,__FILE__);
        while ($this->db2->next_record()) {
          // If its not in the list to be returned, don't return it.
          // This is still quicker then 5(+) separate queries
          if ($extra_fields[$this->db2->f("contact_name")]) {
            $return_fields[0][$this->db2->f("contact_name")] = $this->db2->f("contact_value");
          }
        }

        return $return_fields;
     }

     function read($start,$offset,$fields,$query="",$sort="",$order="")
     {
        global $phpgw,$phpgw_info;

        if (!$sort)   { $sort   = "ASC";  }

        if ($order) {
           $ordermethod = "order by $order $sort ";
        }  else {
           $ordermethod = "order by N_Family,N_Given,D_EMAIL $sort";
        }

        list($ab_fields,$ab_fieldnames,$extra_fields) = $this->split_ab_and_extras($fields);
        if (count($ab_fieldnames)) {
           $t_fields = "," . implode(",",$ab_fieldnames);
           if ($t_fields == ",") {
              unset($t_fields);
           }
        }

        $this->db3 = $this->db2 = $this->db; // Create new result objects before our queries

        if ($query) {
            $this->db3->query("SELECT * from contacts WHERE (N_Family like '"
                     . "%$query%' OR N_Given like '%$query%' OR D_EMAIL like '%$query%' OR "
                     . "ADR_Street like '%$query%' OR ADR_Locality like '%$query%' OR ADR_Region "
                     . "like '%$query%' OR ADR_PostalCode like '%$query%' OR ORG_Unit like "
                     . "'%$query%' OR ORG_Name like '%$query%') " . $ordermethod,__LINE__,__FILE__);
            $this->total_records = $this->db3->num_rows();

            $this->db->query("SELECT * from contacts WHERE (N_Family like '"
                     . "%$query%' OR N_Given like '%$query%' OR D_EMAIL like '%$query%' OR "
                     . "ADR_Street like '%$query%' OR ADR_Locality like '%$query%' OR ADR_Region "
                     . "like '%$query%' OR ADR_PostalCode like '%$query%' OR ORG_Unit like "
                     . "'%$query%' OR ORG_Name like '%$query%') " . $ordermethod . " "
		     . $this->db->limit($start,$offset),__LINE__,__FILE__);
        }  else  {
           $this->db3->query("select id,lid,owner $t_fields from contacts "
                       . $filtermethod,__LINE__,__FILE__);
           $this->total_records = $this->db3->num_rows();
	
           $this->db->query("select id,lid,owner $t_fields from contacts "
                       . $filtermethod . " " . $ordermethod . " " . $this->db->limit($start,$offset),__LINE__,__FILE__);
        }

        $i=0;
        while ($this->db->next_record()) {
           $return_fields[$i]["id"]     = $this->db->f("id");
	   $return_fields[$i]["lid"]    = $this->db->f("lid");
           $return_fields[$i]["owner"]  = $this->db->f("owner");
           if (gettype($ab_fieldnames) == "array") {
              while (list($f_name) = each($ab_fieldnames)) {
                 $return_fields[$i][$f_name] = $this->db->f($f_name);
              }
              reset($ab_fieldnames);
           }

           $this->db2->query("select contact_name,contact_value from contacts_extra where contact_id='"
                           . $this->db->f("id") . "'",__LINE__,__FILE__);
           while ($this->db2->next_record()) {
              // If its not in the list to be returned, don't return it.
              // This is still quicker then 5(+) separate queries
              if ($extra_fields[$this->db2->f("contact_name")]) {
                 $return_fields[$i][$this->db2->f("contact_name")] = $this->db2->f("contact_value");
              }
           }
           $i++;
        }
        
        return $return_fields;
     }

     function add($owner,$fields)
     {
        list($ab_fields,$ab_fieldnames,$extra_fields) = $this->split_ab_and_extras($fields);

        //$this->db->lock(array("contacts"));
        $this->db->query("insert into contacts (owner,"
                       . implode(",",$this->stock_contact_fields)
                       . ") values ('$owner','"
                       . implode("','",$this->loop_addslashes($ab_fields)) . "')",__LINE__,__FILE__);

        $this->db->query("select max(id) from contacts",__LINE__,__FILE__);
        $this->db->next_record();
        $id = $this->db->f(0);
        //$this->db->unlock();

        if (count($extra_fields)) {
           while (list($name,$value) = each($extra_fields)) {
              $this->db->query("insert into contacts_extra values ('$id','" . $this->account_id . "','"
                             . addslashes($name) . "','" . addslashes($value) . "')",__LINE__,__FILE__);
           }
        }
     }

     function field_exists($id,$field_name)
     {
        $this->db->query("select count(*) from contacts_extra where contact_id='$id' and contact_name='"
                       . addslashes($field_name) . "'",__LINE__,__FILE__);
        $this->db->next_record();
        return $this->db->f(0);
     }

     function add_single_extra_field($id,$owner,$field_name,$field_value)
     {
        $this->db->query("insert into contacts_extra values ($id,'$owner','" . addslashes($field_name)
                       . "','" . addslashes($field_value) . "')",__LINE__,__FILE__);
     }

     function delete_single_extra_field($id,$field_name)
     {
        $this->db->query("delete from contacts_extra where contact_id='$id' and contact_name='"
                       . addslashes($field_name) . "'",__LINE__,__FILE__);
     }

     function update($id,$owner,$fields)
     {
        // First make sure that id number exists
        $this->db->query("select count(*) from contacts where id='$id'",__LINE__,__FILE__);
        $this->db->next_record();
        if (! $this->db->f(0)) {
           return False;
        }

        list($ab_fields,$ab_fieldnames,$extra_fields) = $this->split_ab_and_extras($fields);
        if (count($ab_fields)) {
           while (list($ab_fieldname) = each($ab_fieldnames)) {
              $ta[] = $ab_fieldname . "='" . addslashes($ab_fields[$ab_fieldname]) . "'";
           }
           $fields_s = "," . implode(",",$ta);
           if ($field_s == ",") {
              unset($field_s);
           }
           $this->db->query("update contacts set owner='$owner', $fields_s where "
                          . "id='$id'",__LINE__,__FILE__);
        }

        while (list($x_name,$x_value) = each($extra_fields)) {
           if ($this->field_exists($id,$x_name)) {
              if (! $x_value) {
                 $this->delete_single_extra_field($id,$x_name);
              } else {
                 $this->db->query("update contacts_extra set contact_value='" . addslashes($x_value)
                                . "',contact_owner='$owner' where contact_name='" . addslashes($x_name)
                                . "' and contact_id='$id'",__LINE__,__FILE__);
              }
           } else {
              $this->add_single_extra_field($id,$owner,$x_name,$x_value);
           }
        }
     }

     // This is where the real work of delete() is done
     function delete_($id)
     {
        $this->db->query("delete from contacts where owner='" . $this->account_id . "' and "
                       . "id='$id'",__LINE__,__FILE__);
        $this->db->query("delete from contacts_extra where contact_id='$id' and contact_owner='"
                       . $this->account_id . "'",__LINE__,__FILE__);
     }

  }

?>
