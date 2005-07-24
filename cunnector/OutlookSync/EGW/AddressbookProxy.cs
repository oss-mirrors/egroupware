/***************************************************************************
                      AddressbookProxy.cs  -  description
                             -------------------
    copyright            : (C) 2005 by credativ GmbH, Germany
    email                : cunnector-dev@credativ.org
 ***************************************************************************/

/***************************************************************************
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

#region Using directives

using System;
using System.Collections.Generic;
using System.Text;
using CookComputing.XmlRpc;

using OutlookSync.Remote;

#endregion

namespace OutlookSync.EGW
{
    public class AddressbookProxy : XmlRpcClientProtocol
    {
        public static class ContactFieldTypes
        {
            public static string access = "access";
            public static string rights = "rights";
            public static string owner = "owner";

            public static string id = "id";
            public static string lastModified = "last_mod";
            public static string fullName = "fn";
            public static string sound = "sound";
            public static string organisationName = "org_name";
            public static string organisationUnit = "org_unit";
            public static string title = "title";
            public static string namePrefix = "n_prefix";
            public static string givenName = "n_given";
            public static string middleName = "n_middle";
            public static string familyName = "n_family";
            public static string nameSuffix = "n_suffix";
            public static string label = "label";
            public static string addressOneStreet = "adr_one_street";
            public static string addressOneLocality = "adr_one_locality";
            public static string addressOneRegion = "adr_one_region";
            public static string addressOnePostalcode = "adr_one_postalcode";
            public static string addressOneCountryname = "adr_one_countryname";
            public static string addressOneType = "adr_one_type";
            public static string addressTwoStreet = "adr_two_street";
            public static string addressTwoLocality = "adr_two_locality";
            public static string addressTwoRegion = "adr_two_region";
            public static string addressTwoPostalcode = "adr_two_postalcode";
            public static string addressTwoCountryname = "adr_two_countryname";
            public static string addressTwoType = "adr_two_type";
            public static string timeZone = "tz";
            public static string geo = "geo";
            public static string telephoneWork = "tel_work";
            public static string telephoneHome = "tel_home";
            public static string telephoneVoice = "tel_voice";
            public static string telephoneMessage = "tel_msg";
            public static string telephoneFax = "tel_fax";
            public static string telephonePager = "tel_pager";
            public static string telephoneCell = "tel_cell";
            public static string telephoneBBS = "tel_bbs";
            public static string telephoneModem = "tel_modem";
            public static string telephoneISDN = "tel_isdn";
            public static string telephoneCar = "tel_car";
            public static string telephoneVideo = "tel_video";
            public static string telephonePrefered = "tel_prefer";
            public static string email = "email";
            public static string emailType = "email_type";
            public static string emailHome = "email_home";
            public static string emailHomeType = "email_home_type";
            public static string addressLine2 = "adress2";
            public static string addressLine3 = "adress3";
            public static string telephoneOther = "ophone";
            public static string birthday = "bday";
            public static string url = "url";
            public static string publicKey = "pubkey";
            public static string notes = "note";
        }

        public static string ParamReadFields = "fields";
        public static string ParamReadId = "id";
        public static string ParamWriteId = "id";
        public static int ParamWriteCreateId = 0;


        public AddressbookProxy(RemoteSessionData sessionData)
        {
            this.KeepAlive = false;

            SessionType temp = new SessionType();
            temp.kp3 = sessionData.kp3;
            temp.sessionid = sessionData.sessionid;

            this.session = temp;
            this.Url = sessionData.serverUrl;

            // must pass session id through header
            // format: "Authorization: Basic [$session + ":" + $kp3](coded Base64)"
            string sessionstring = session.sessionid + ":" + session.kp3;
            string sessionBase64 = base64Encode(sessionstring);
            this.Headers.Add("Authorization", "Basic " + sessionBase64);

            // require xml-rpc conform date format, not default for egw-server
            this.Headers.Add("isoDate", "simple");
        }

        public AddressbookProxy(string url, SessionType session)
        {
            this.Url = url;
            this.session = session;

            // must pass session id through header
            // format: "Authorization: Basic [$session + ":" + $kp3](coded Base64)"
            string sessionstring = session.sessionid + ":" + session.kp3;
            string sessionBase64 = base64Encode(sessionstring);
            this.Headers.Add("Authorization", "Basic " + sessionBase64);
        }


        
        //        search
        //Searches or lists addressbook entries you have rights to view:
        //addressbook.boaddressbook.search(struct(
        //
        //    'start' => int(0), // starting number of the returned entries 
        //    'limit' => int(20), // the number of entries to return or 0 or not set for all 
        //    'fields' => array('n_familiy','n_given'), // the fields to return, unset for all fields 
        //    'query' => 'pattern', // a pattern entries have to match, leave it empty or unset for all entries 
        //    'filter' => {'none'|'yours'|'private'}, // use one of the predefined filters 
        //    'order' => 'column-name', // column to order the result 
        //    'sort' => {'ASC'|'DESC'}, 
        //    'include_users' => {'all','calendar','groupmates'}, // unset or empty for regular entries only 
        //
        //))
        //methodResponse(array(struct(
        //'fieldname' => data,
        //...
        //), ...)) 
        [XmlRpcMethod("addressbook.boaddressbook.search")]
        public XmlRpcStruct[] Search(XmlRpcStruct parameters)
        {
            Object returnValue = Invoke("Search", parameters);

            try
            {
                return (XmlRpcStruct[])returnValue;
            }
            // this will happen when there is no contact to return
            catch (System.InvalidCastException)
            {
                return null;
            } 
        }

        [XmlRpcMethod("addressbook.boaddressbook.read")]
        public XmlRpcStruct[] Read(XmlRpcStruct contact)
        {
            return (XmlRpcStruct[])Invoke("Read", contact);
        }

        
        // Writes or creates an addressbook entry defined by "contact"
        //    'id' => int(id), // id of the entry to update or unset or 0 to create a new entry 
        //    'fieldname' => data, // field will be empty if left blanc
        // Return parameter will be string containing the id for a new contact or boolean 
        // containing update success
        [XmlRpcMethod("addressbook.boaddressbook.write")]
        public object Write(XmlRpcStruct contact) 
        {
            return Invoke("Write", contact);
        }
    
        
        //delete
        //Deletes an addressbook entry:
        //addressbook.boaddressbook.delete(string( 'id' ))
        //
        //methodResponse() 
        [XmlRpcMethod("addressbook.boaddressbook.delete")]
        public void Delete(int id)
        {
            Invoke("Delete", new Object[] { id.ToString() });
        }

        private string base64Encode(string data)
        {
            try
            {
                byte[] encData_byte = new byte[data.Length];
                encData_byte = System.Text.Encoding.UTF8.GetBytes(data);
                string encodedData = Convert.ToBase64String(encData_byte);
                return encodedData;
            }
            catch (Exception e)
            {
                throw new Exception("Error in base64Encode" + e.Message);
            }
        }

        private SessionType session;
    }
}
