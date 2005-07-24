/***************************************************************************
                      SyncSessionData.cs  -  description
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
using System.Collections;
using System.Text;
using Microsoft.Win32;
using OutlookSync.Remote;

#endregion

namespace OutlookSync
{
    public class SyncSessionData
    {
        public SyncSessionData()
        {
            properties = new Hashtable();
        }

        public RemoteSessionData GetRemoteSessionData()
        {
            RemoteSessionData rs = new RemoteSessionData();

            rs.userName         = this.UserName;
            rs.userPassword     = this.UserPassword;
            rs.serverUrl        = this.ServerUrl;
            rs.domain           = this.Domain;
            rs.searchDaysBefore = this.SearchDaysBefore;
            rs.searchDaysAfter  = this.SearchDaysAfter;

            return rs;
        }
       
        public void StoreInRegistry(string keyName)
        {
            RegistryKey regKey = Registry.CurrentUser.OpenSubKey(keyName, true);

            if (regKey == null) // key doesn't exist, create
            {
                regKey = Registry.CurrentUser.CreateSubKey(keyName);
            }

            foreach (string valueName in properties.Keys)
            {
                regKey.SetValue(valueName, properties[valueName]);
            }

            // add control value
            regKey.SetValue("IsOutlookSyncSessionKey", true);
        }

        public bool RetrieveFromRegistry(string keyName)
        {
            RegistryKey regKey = Registry.CurrentUser.OpenSubKey(keyName);

            if (regKey == null) return false; // key doesn't exist, fail

            // check for legal registry key
            if (regKey.GetValue("IsOutlookSyncSessionKey") == null) return false;

            String[] valNames = regKey.GetValueNames();
            for (int i = 0; i < valNames.Length; i++)
            {
                properties[valNames[i]] = regKey.GetValue(valNames[i]);
            }

            return true;
        }

        public bool isValid()
        {
            bool valid = true;

            if (LocalDirectory == "")              valid = false;
            if (ServerUrl == "")                valid = false;
            if (UserName == "")                 valid = false;
            if (UserPassword == "")             valid = false;

            return valid;
        }


        // general properties:
        public int AutoSyncIntervall
        {
            get
            {
                if (properties["AutoSyncIntervall"] != null)
                    return int.Parse( (string)properties["AutoSyncIntervall"] );
                else
                    return 10;
            }
            set
            {
                properties["AutoSyncIntervall"] = value.ToString();
            }
        }
        public bool SyncContacts
        {
            get
            {
                if (properties["SyncContacts"] != null)
                    return Boolean.Parse( (string)properties["SyncContacts"]);
                else
                    return true;
            }
            set
            {
                properties["SyncContacts"] = value.ToString();
            }
        }
        public bool SyncAppointments
        {
            get
            {
                if (properties["SyncAppointments"] != null)
                    return Boolean.Parse( (string)properties["SyncAppointments"]);
                else
                    return true;
            }
            set
            {
                properties["SyncAppointments"] = value.ToString();
            }
        }
        public bool SyncTasks
        {
            get
            {
                if (properties["SyncTasks"] != null)
                    return Boolean.Parse( (string)properties["SyncTasks"] );
                else
                    return true;
            }
            set
            {
                properties["SyncTasks"] = value.ToString();
            }
        }
        public string LocalDirectory
        {
            get
            {
                if (properties["LocalDirectory"] != null)
                    return (string)properties["LocalDirectory"];
                else
                    return "";
            }
            set
            {
                string dir = value.TrimEnd(new char[1] { '\\' });
                properties["LocalDirectory"] = dir;
            }
        }
//        public string ContactSncFilePath
//        {
//            get
//            {
//                if (properties["ContactSncFilePath"] != null)
//                    return (string)properties["ContactSncFilePath"];
//                else
//                    return "";
//            }
//            set
//            {
//                properties["ContactSncFilePath"] = value;
//            }
//        }
//        public string AppointmentSncFilePath
//        {
//            get
//            {
//                if (properties["AppointmentSncFilePath"] != null)
//                    return (string)properties["AppointmentSncFilePath"];
//                else
//                    return "";
//            }
//            set
//            {
//                properties["AppointmentSncFilePath"] = value;
//            }
//        }
//        public string TaskSncFilePath
//        {
//            get
//            {
//                if (properties["TaskSncFilePath"] != null)
//                    return (string)properties["TaskSncFilePath"];
//                else
//                    return "";
//            }
//            set
//            {
//                properties["TaskSncFilePath"] = value;
//            }
//        }
//
//        // outlook properties:
//        public string PstFilePath
//        {
//            get
//            {
//                if (properties["PstFilePath"] != null)
//                    return (string)properties["PstFilePath"];
//                else
//                    return "";
//            }
//            set
//            {
//                properties["PstFilePath"] = value;
//            }
//        }

        // remote properties:
        public string UserName
        {
            get
            {
                if (properties["UserName"] != null)
                    return (string)properties["UserName"];
                else
                    return "";
            }
            set
            {
                properties["UserName"] = value;
            }
        }
        public string UserPassword
        {
            get
            {
                if (properties["UserPassword"] != null)
                    return (string)properties["UserPassword"];
                else
                    return "";
            }
            set
            {
                properties["UserPassword"] = value;
            }
        }
        public string ServerUrl
        {
            get
            {
                if (properties["ServerUrl"] != null)
                    return (string)properties["ServerUrl"];
                else
                    return "";
            }
            set
            {
                properties["ServerUrl"] = value;
            }
        }
        public string Domain
        {
            get
            {
                if (properties["Domain"] != null)
                    return (string)properties["Domain"];
                else
                    return "default";
            }
            set
            {
                properties["Domain"] = value;
            }
        }
//        public string Sessionid
//        {
//            get
//            {
//                if (properties["Sessionid"] != null)
//                    return (string)properties["Sessionid"];
//                else
//                    return "";
//            }
//            set
//            {
//                properties["Sessionid"] = value;
//            }
//        }
//        public string Kp3
//        {
//            get
//            {
//                if (properties["Kp3"] != null)
//                    return (string)properties["Kp3"];
//                else
//                    return "";
//            }
//            set
//            {
//                properties["Kp3"] = value;
//            }
//        }
        public double SearchDaysBefore
        {
            get
            {
                if (properties["SearchDaysBefore"] != null)
                    return Double.Parse( (string)properties["SearchDaysBefore"] );
                else
                    return 7.0;
            }
            set
            {
                properties["SearchDaysBefore"] = value.ToString();
            }
        }
        public double SearchDaysAfter
        {
            get
            {
                if (properties["SearchDaysAfter"] != null)
                    return Double.Parse( (string)properties["SearchDaysAfter"]);
                else
                    return 30.0;
            }
            set
            {
                properties["SearchDaysAfter"] = value.ToString();
            }
        }


        private Hashtable properties;
    }
}
