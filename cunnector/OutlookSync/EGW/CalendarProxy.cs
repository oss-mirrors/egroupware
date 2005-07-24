/***************************************************************************
                       CalendarProxy.cs  -  description
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

    
    public class CalendarProxy : XmlRpcClientProtocol
    {

        public static class AppointmentFields
        {
            public static string access = "access";
            public static string rights = "rights";
            public static string owner = "owner";
            public static string id = "id";
            public static string lastModified = "modtime";

            public static string category = "category";
            public static string title = "title";
            public static string description = "description";
            public static string uid = "uid";
            public static string location = "location";
            public static string reference = "reference";
            public static string start = "start";
            public static string end = "end";
            public static string priority = "priority";
            public static string participants = "participants";
            public static string status = "status";
            public static string alarm = "alarm";
        }

        public static string ParamStartDate = "start";
        public static string ParamEndDate = "end";

        public static string ParamReadId = "id";
        public static string ParamWriteId = "id";

        public static int ParamWriteCreateId = 0;



        public CalendarProxy(RemoteSessionData sessionData)
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

        public CalendarProxy(string url, SessionType session)
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
        [XmlRpcMethod("calendar.bocalendar.search")]
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

        [XmlRpcMethod("calendar.bocalendar.read")]
        public XmlRpcStruct Read(int id)
        {
            return (XmlRpcStruct)Invoke("Read", id);
        }

        
        // Writes or creates an addressbook entry defined by "contact"
        //    'id' => int(id), // id of the entry to update or unset or 0 to create a new entry 
        // Return parameter will be string containing the id for a new contact or boolean 
        // containing update success
        [XmlRpcMethod("calendar.bocalendar.write")]
        public int Write(XmlRpcStruct appointment) 
        {
            return (int)Invoke("Write", appointment);
        }
    
        
        //delete
        //Deletes an addressbook entry:
        //addressbook.boaddressbook.delete(string( 'id' ))
        //
        //methodResponse() 
        [XmlRpcMethod("calendar.bocalendar.delete")]
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
