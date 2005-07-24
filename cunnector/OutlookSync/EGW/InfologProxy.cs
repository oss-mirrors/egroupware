/***************************************************************************
                       InfologProxy.cs  -  description
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


    public class InfologProxy : XmlRpcClientProtocol
    {

        public static class InfologFields
        {
            public static string access = "access";
            public static string rights = "rights";
            public static string owner = "owner";
            public static string id = "id";
            public static string lastModified = "datemodified";

            public static string type = "type";
            public static string from = "from";
            public static string subject = "subject";
            public static string addr = "addr";
            public static string description = "des";
            public static string responsible = "responsible";

            public static string category = "cat";

            public static string start = "startdate";
            public static string end = "enddate";
            public static string parentId = "id_parent";
            public static string priority = "pri";

            public static string time = "time";

            public static string status = "status";
            public static string confirmed = "confirm";
            public static string modifier = "modifier";
            public static string linkId = "link_id";
        }

        public static string ParamReadId = "id";
        public static string ParamWriteId = "id";

        public static int ParamWriteCreateId = 0;



        public InfologProxy(RemoteSessionData sessionData)
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

        public InfologProxy(string url, SessionType session)
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
        // struct ( 'start' => dateTime, 
        //          'end' => dateTime );
        //
        [XmlRpcMethod("infolog.boinfolog.search")]
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

        [XmlRpcMethod("infolog.boinfolog.read")]
        public XmlRpcStruct Read(int id)
        {
            return (XmlRpcStruct)Invoke("Read", id);
        }


        // Writes or creates an addressbook entry defined by "contact"
        //    'id' => int(id), // id of the entry to update or unset or 0 to create a new entry 
        // Return parameter will be string containing the id for a new contact or boolean 
        // containing update success
        [XmlRpcMethod("infolog.boinfolog.write")]
        public object Write(XmlRpcStruct item)
        {
            return Invoke("Write", item);
        }


        //delete
        //Deletes an addressbook entry:
        //addressbook.boaddressbook.delete(string( 'id' ))
        //
        //methodResponse() 
        [XmlRpcMethod("infolog.boinfolog.delete")]
        public void Delete(int id)
        {
            Invoke("Delete", new Object[] { id });
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
