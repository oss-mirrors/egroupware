/***************************************************************************
                     OutlookInterface.cs  -  description
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

using Microsoft.Office.Interop.Outlook;
//using Microsoft.Office.Tools.Outlook;

using Utilities.Logging;

using OutlookApp = Microsoft.Office.Interop.Outlook.Application;
using OutlookSync.Items;

#endregion

namespace OutlookSync.Outlook
{
    public class OutlookInterface : SimpleLoggingBase
    {
        private const string PST_FILE_NAME = "sync_cache.pst";
        private const string FOLDER_NAME = "Groupware";


        public OutlookInterface(SyncSessionData sessionData)
            :   base("OutlookInterface")
        {
            this.outlookApp = new Microsoft.Office.Interop.Outlook.Application();
            this.outlookNS = outlookApp.GetNamespace("MAPI");

            this.pstPath = sessionData.LocalDirectory + "\\" + PST_FILE_NAME;
            this.groupwareFolderName = FOLDER_NAME + " (" +  sessionData.UserName + ")";

            initNames();
            createCache();

            outlookContacts = new OutlookContacts(contactFolder);
            outlookAppointments = new OutlookAppointments(appointmentFolder);
            outlookTasks = new OutlookTasks(taskFolder);
        }

        public OutlookInterface(SyncSessionData sessionData, Microsoft.Office.Interop.Outlook.Application outlookApp) 
            :  base("OutlookInterface")
        {
            this.outlookApp = outlookApp;
            outlookNS = outlookApp.GetNamespace("MAPI");

            this.pstPath = sessionData.LocalDirectory + "\\" + PST_FILE_NAME;
            this.groupwareFolderName = FOLDER_NAME + " (" + sessionData.UserName + ")";

            initNames();
            createCache();

            outlookContacts = new OutlookContacts(contactFolder);
            outlookAppointments = new OutlookAppointments(appointmentFolder);
            outlookTasks = new OutlookTasks(taskFolder);
        }

        public new void SetLogger(NSpring.Logging.Logger logger)
        {
            outlookContacts.SetLogger(logger);
            outlookAppointments.SetLogger(logger);
            outlookTasks.SetLogger(logger);
            base.SetLogger(logger);
        }


        //
        // contact methods
        //

        public void UpdateContacts(ICollection contacts)
        {
    
            foreach (Contact contact in contacts)
            {
                outlookContacts.UpdateOutlookContact(contact);
            }
        }

        public void CreateContacts(ICollection contacts)
        {
            foreach (Contact contact in contacts)
            {
                outlookContacts.CreateOutlookContact(contact);
                log.LogInfo("Outlook contact created. OutlookId: " + contact.OutlookId);

            }
        }

        public Hashtable GetContacts()
        {
            return outlookContacts.GetContacts();
        }

        public void DeleteContacts(ICollection baseItems)
        {
            foreach (BaseItem item in baseItems)
            {
                outlookContacts.DeleteOutlookContact(item.OutlookId);
            }
        }


        //
        // appointment methods
        //

        public void UpdateAppointments(ICollection appointments)
        {

            foreach (Appointment appointment in appointments)
            {
                outlookAppointments.UpdateOutlookAppointment(appointment);
            }
        }

        public void CreateAppointments(ICollection appointments)
        {
            foreach (Appointment appointment in appointments)
            {
                outlookAppointments.CreateOutlookAppointment(appointment);
                log.LogInfo("Outlook appointment created. OutlookId: " + appointment.OutlookId);

            }
        }

        public Hashtable GetAppointments()
        {
            return outlookAppointments.GetAppointments();
        }

        public void DeleteAppointments(ICollection baseItems)
        {
            foreach (BaseItem item in baseItems)
            {
                outlookAppointments.DeleteOutlookAppointment(item.OutlookId);
            }
        }


        
        //
        // tasks methods
        //

        public void UpdateTasks(ICollection tasks)
        {
            foreach (Task task in tasks)
            {
                outlookTasks.UpdateOutlookTask(task);
            }
        }
    
        public void CreateTasks(ICollection tasks)
        {
            foreach (Task task in tasks)
            {
                outlookTasks.CreateOutlookTask(task);
                log.LogInfo("Outlook task created. OutlookId: " + task.OutlookId);
            }
        }

        public Hashtable GetTasks()
        {
            return outlookTasks.GetTasks();
        }

        public void DeleteTasks(ICollection baseItems)
        {
            foreach (BaseItem item in baseItems)
            {
                outlookTasks.DeleteOutlookTask(item.OutlookId);
            }
        }



        //
        // private methods:
        //

        private void initNames()
        {
            // get names of default folders to avoid language conflicts.
            contactFolderName = outlookNS.GetDefaultFolder(OlDefaultFolders.olFolderContacts).Name;
            taskFolderName = outlookNS.GetDefaultFolder(OlDefaultFolders.olFolderTasks).Name;
            appointmentFolderName = outlookNS.GetDefaultFolder(OlDefaultFolders.olFolderCalendar).Name;
        }

        private void createCache()
        {
            if (System.IO.File.Exists(pstPath))
            {
                outlookNS.AddStore(pstPath);
                groupwareFolder = (MAPIFolder)outlookNS.Folders.GetLast();
            }
            else
            {
                outlookNS.AddStore(pstPath);
                groupwareFolder = (MAPIFolder)outlookNS.Folders.GetLast();
                groupwareFolder.Name = groupwareFolderName;

                groupwareFolder.Folders.Add(contactFolderName, OlDefaultFolders.olFolderContacts);
                groupwareFolder.Folders.Add(appointmentFolderName, OlDefaultFolders.olFolderCalendar);
                groupwareFolder.Folders.Add(taskFolderName, OlDefaultFolders.olFolderTasks);
            }
            contactFolder = groupwareFolder.Folders[contactFolderName];
            appointmentFolder = groupwareFolder.Folders[appointmentFolderName];
            taskFolder = groupwareFolder.Folders[taskFolderName];

        }

        private void removeCache()
        {
            outlookNS.RemoveStore(groupwareFolder);
        }


        private OutlookContacts     outlookContacts;
        private OutlookAppointments outlookAppointments;
        private OutlookTasks        outlookTasks;

        private string pstPath;
        private string groupwareFolderName;

        private OutlookApp outlookApp;
        private NameSpace outlookNS;
        private MAPIFolder groupwareFolder;
        private MAPIFolder contactFolder;
        private MAPIFolder appointmentFolder;
        private MAPIFolder taskFolder;

        private String contactFolderName;
        private String appointmentFolderName;
        private String taskFolderName;


  }
}
