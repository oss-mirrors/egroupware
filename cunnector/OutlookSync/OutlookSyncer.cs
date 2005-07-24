/***************************************************************************
                       OutlookSyncer.cs  -  description
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
using System.Windows.Forms;


using Utilities.Logging;

using OutlookSync.EGW;
using OutlookSync.Outlook;
using OutlookSync.Items;
using OutlookSync.Remote;

using OutlookApp = Microsoft.Office.Interop.Outlook.Application;

#endregion

namespace OutlookSync
{
    public class OutlookSyncer : SimpleLoggingBase
    {
        private const string contactSncFileName = "sync_contacts.snc";
        private const string appointmentSncFileName = "sync_appointments.snc";
        private const string taskSncFileName = "sync_tasks.snc";

        public OutlookSyncer(SyncSessionData syncSessionData) 
            : base("OutlookSyncer")
        {
            this.sessionData = syncSessionData;
            initialize();

            this.outlookInterface = new OutlookInterface(syncSessionData);
            this.remoteInterface = new EGWInterface();
        }

        public OutlookSyncer(SyncSessionData syncSessionData, OutlookApp outlookApp) 
            : base("OutlookSyncer")
        {
            sessionData = syncSessionData;
            initialize();

            outlookInterface = new OutlookInterface(syncSessionData, outlookApp);
            remoteInterface = new EGWInterface();
        }

        public new void SetLogger(NSpring.Logging.Logger logger)
        {
            outlookInterface.SetLogger(logger);

            try  // remote interface implements logging ?
            {
                ((SimpleLoggingBase)remoteInterface).SetLogger(logger);
            }
            catch (System.InvalidCastException) 
            {
                log.LogWarning("RemoteInterface does not implement logging!");
            } ;

            base.SetLogger(logger);
        }


        public void Synchronize()
        {
            try // Check for errors during connection to server
            {
                ConnectRemote();
                LoadSyncState();
                synchronizeAll();
                //SaveSyncState(); // now done after each sync block
                DisconnectRemote();

                log.LogInfo("Synchronization DONE!");
            }
            catch (Remote.ServerNotFoundException)
            {
                status.SyncError = true;
                log.LogError("Server not found, Synchronisation stopped!");
                MessageBox.Show("Connection to server couldn't be established.\nNo internet connection?", "Warning!");
            }
            catch (Remote.InvalidPasswordException)
            {
                status.SyncError = true;
                log.LogError("Username or Password incorrect, connection terminated");
                MessageBox.Show("Server denied connection\nUsername or Password incorrect?", "Warning!");
            }
        }


        // returns update on synchronization status. only really usefull with threaded syncing
        public SyncStatus Status
        {
            get
            {
                return status;
            }
        }



        //
        // private utility functions:
        //

        private void initialize()
        {
            contactSncFilePath      = sessionData.LocalDirectory + "\\" + contactSncFileName;
            appointmentSncFilePath  = sessionData.LocalDirectory + "\\" + appointmentSncFileName;
            taskSncFilePath         = sessionData.LocalDirectory + "\\" + taskSncFileName;


            this.outlookSyncedItems = new Hashtable[3];
            this.remoteSyncedItems = new Hashtable[3];

            for (int i = 0; i < 3; i++)
            {
                this.outlookSyncedItems[i] = new Hashtable();
                this.remoteSyncedItems[i] = new Hashtable();
            }

            this.status = new SyncStatus();
        }




        //
        // Most work done here!
        //
        private void synchronizeAll()
        {
            log.LogInfo("--STARTING SYNCHRONIZATION--");

            status.ResetItemCounts();
            status.SyncPercentage = 0;
            status.SyncError = false;


            status.IsSyncRunning = true;

            if (sessionData.SyncContacts)
                synchronizeContacts();  

            if (sessionData.SyncAppointments)
                synchronizeAppointments();

            if (sessionData.SyncTasks)
                synchronizeTasks();

            // update status fields

            status.NumSyncedItems = outlookSyncedItems[0].Count + outlookSyncedItems[1].Count + outlookSyncedItems[2].Count;
            status.SyncPercentage = 100;
            status.IsSyncRunning = false;
            status.CurrentOperation = "Done";
            status.LastSyncedTime = DateTime.Now;

            log.LogInfo("--SYNCHRONIZATION DONE--");

        }

        private void synchronizeContacts()
        {
            //
            // read only contacts:
            //
            status.CurrentOperation = "Identifying modified read-only contacts";

            // get list of outlook contacts
            Hashtable outlookContactHashtable = outlookInterface.GetContacts();
            log.LogInfo(outlookContactHashtable.Count + " contacts retrieved from Outlook");

          
            // deal with modified read-only contacts
            ArrayList modReadOnlyContacts = filterModifiedReadOnlyItems(outlookContactHashtable, ItemType.Contacts);
            outlookInterface.DeleteContacts(modReadOnlyContacts);

            status.NumCorrectedReadOnlyItems += modReadOnlyContacts.Count;
            log.LogInfo(modReadOnlyContacts.Count + " read only contacts were modified and will be replaced");

            //
            // outlook side:
            //
            status.CurrentOperation = "Checking for new contacts in outlook";

            // get list of outlook contacts
            outlookContactHashtable = outlookInterface.GetContacts();
            log.LogInfo(outlookContactHashtable.Count + " contacts retrieved from outlook");

            // identify new contacts
            ArrayList newOutlookContactList = filterNewOutlookItems(outlookContactHashtable, ItemType.Contacts);
            log.LogInfo(newOutlookContactList.Count + " contacts created since last synchronised (Outlook)");

            //create new remote contacts
            remoteInterface.CreateContacts(newOutlookContactList);
            status.NumCreatedItemsRemote += newOutlookContactList.Count;
            log.LogInfo(newOutlookContactList.Count + " contacts created (Remote)");

            // update synchronized items list (add)
            addSynchronizedItems(newOutlookContactList, ItemType.Contacts);
            log.LogInfo(newOutlookContactList.Count + " contacts added to synchronized items");

            // check for missing contacts
            ArrayList deadRemoteContactList = filterDeletedOutlookItems(outlookContactHashtable, ItemType.Contacts);
            log.LogInfo(deadRemoteContactList.Count + " contacts deleted since last synchronised (Outlook)");

            // delete remote contacts
            remoteInterface.DeleteContacts(deadRemoteContactList);
            status.NumRemovedItemsRemote += deadRemoteContactList.Count;
            log.LogInfo(deadRemoteContactList.Count + " contacts deleted (Remote)");

            // update synchronized items list (remove)
            removeSynchronizedItems(deadRemoteContactList, ItemType.Contacts);
            log.LogInfo(deadRemoteContactList.Count + " contacts removed from synchronized items");



            //
            // remote side:
            //
            status.CurrentOperation = "Checking for new contacts on remote server";
            status.SyncPercentage = 30;

            // getFootprints
            ArrayList newRemoteContactList;

            Hashtable remoteContactFootprintHashtable = remoteInterface.GetContactFootprints();
            log.LogInfo(remoteContactFootprintHashtable.Count + " contact footprints retrieved from remote server");

            status.SyncPercentage = 40;

            // check for unknown syncIds
            ArrayList newContactFootprintList = filterNewRemoteItems(remoteContactFootprintHashtable, ItemType.Contacts);
            log.LogInfo(newContactFootprintList.Count + " contacts created since last synchronised (Remote)");

            // download unknown contacts
            newRemoteContactList = remoteInterface.GetSelectedContacts(newContactFootprintList);
            log.LogInfo(newRemoteContactList.Count + " contacts downloaded from remote server");

            status.SyncPercentage = 65;

            // createNewOutlookContacts
            outlookInterface.CreateContacts(newRemoteContactList);
            status.NumCreatedItemsLocal += (newRemoteContactList.Count - status.NumCorrectedReadOnlyItems);
            log.LogInfo(newRemoteContactList.Count + " contacts created (Outlook)");

            // update synchronized items (add)
            addSynchronizedItems(newRemoteContactList, ItemType.Contacts);
            log.LogInfo(newRemoteContactList.Count + " contacts added to synchronized items");


            // check for missing contacts
            ArrayList deadOutlookContactList = filterDeletedRemoteItems(remoteContactFootprintHashtable, ItemType.Contacts);
            log.LogInfo(deadOutlookContactList.Count + " contacts deleted since last synchronised (Remote)");

            // remove deleted contacts (outlook)
            outlookInterface.DeleteContacts(deadOutlookContactList);
            status.NumRemovedItemsLocal += deadOutlookContactList.Count;
            log.LogInfo(deadOutlookContactList.Count + " contacts deleted (Outlook)");

            // update synchronized items list (remove)
            removeSynchronizedItems(deadOutlookContactList, ItemType.Contacts);
            log.LogInfo(deadOutlookContactList.Count + " contacts removed from synchronized items");

            status.SyncPercentage = 60;

            //
            // modified
            //
            status.CurrentOperation = "Checking modified outlook items";


            // remove dead files from modified check
            foreach (BaseItem item in deadRemoteContactList) remoteContactFootprintHashtable.Remove(item.RemoteId);
            foreach (BaseItem item in deadOutlookContactList) outlookContactHashtable.Remove(item.OutlookId);

            // remove new files from modified check
            foreach (BaseItem item in newRemoteContactList) remoteContactFootprintHashtable.Remove(item.RemoteId);
            foreach (BaseItem item in newOutlookContactList) remoteContactFootprintHashtable.Remove(item.RemoteId);
            foreach (BaseItem item in newOutlookContactList) outlookContactHashtable.Remove(item.OutlookId);


            // filter modified outlook contacts
            Hashtable modifiedOutlookContactsHashtable
                = filterModifiedOutlookItems(outlookContactHashtable, ItemType.Contacts);
            log.LogInfo(modifiedOutlookContactsHashtable.Count + " modified outlook contacts found");

            // filter modified remote contact footprints
            Hashtable modifiedRemoteContactFootprintsHashtable
                = filterModifiedRemoteFootprints(remoteContactFootprintHashtable, ItemType.Contacts);
            log.LogInfo(modifiedRemoteContactFootprintsHashtable.Count + " modified remote contacts found");

            // remove conflicts
            int conflicts = removeConflicts(modifiedRemoteContactFootprintsHashtable,
                modifiedOutlookContactsHashtable, ConflictStrategy.PreferRemote, ItemType.Contacts);
            status.NumConflicts += conflicts;
            log.LogInfo(conflicts + " conflicts removed!");

            status.SyncPercentage = 70;

            // download changed remote contacts (from server)
            status.CurrentOperation = "Checking modified remote items";

            ICollection readContacts = modifiedRemoteContactFootprintsHashtable.Values;
            ArrayList modifiedRemoteContactsList = remoteInterface.GetSelectedContacts(readContacts);
            log.LogInfo(modifiedRemoteContactsList.Count + " contacts download from server");


            // update changed outlook contacts (on server)
            getRemoteIds(modifiedOutlookContactsHashtable.Values, ItemType.Contacts);
            remoteInterface.UpdateContacts(modifiedOutlookContactsHashtable.Values);
            status.NumModifiedItemsRemote += modifiedOutlookContactsHashtable.Count;
            log.LogInfo(modifiedOutlookContactsHashtable.Count + " contacts updated on remote server");


            status.SyncPercentage = 90;

            // update changed remote contacts (on outlook)
            getOutlookIds(modifiedRemoteContactsList, ItemType.Contacts);
            outlookInterface.UpdateContacts(modifiedRemoteContactsList);
            status.NumModifiedItemsLocal += modifiedRemoteContactsList.Count;
            log.LogInfo(modifiedRemoteContactsList.Count + " contacts updated in outlook");


            //
            //  finish up
            // 


            // update last modification dates
            Hashtable remoteFootprintsSynced = remoteInterface.GetContactFootprints();
            refreshLastModRemoteTimes(remoteFootprintsSynced.Values, ItemType.Contacts);
            log.LogInfo(remoteFootprintsSynced.Count + " remote contact modification times updated");
            Hashtable outlookContactsSynced = outlookInterface.GetContacts();
            refreshLastModOutlookTimes(outlookContactsSynced.Values, ItemType.Contacts);
            log.LogInfo(outlookContactsSynced.Count + " outlook contact modification times updated");


            //
            //  Save Synchronized Contacts
            // 
            
            saveSynchronizedItems(contactSncFilePath, ItemType.Contacts);
            log.LogInfo(remoteSyncedItems[(int)ItemType.Contacts].Count + " previously synced contacts saved");

        }

        private void synchronizeAppointments()
        {
            //
            // read only appointments:
            //
            status.CurrentOperation = "Identifying modified read-only appointments";

            // get list of outlook appointments
            Hashtable outlookAppointmentHashtable = outlookInterface.GetAppointments();
            log.LogInfo(outlookAppointmentHashtable.Count + " appointments retrieved from Outlook");


            // deal with modified read-only appointments
            ArrayList modReadOnlyAppointments = filterModifiedReadOnlyItems(outlookAppointmentHashtable, ItemType.Appointments);
            outlookInterface.DeleteAppointments(modReadOnlyAppointments);

            status.NumCorrectedReadOnlyItems += modReadOnlyAppointments.Count;
            log.LogInfo(modReadOnlyAppointments.Count + " read only appointments were modified and will be replaced");


            //
            // outlook side:
            //
            status.CurrentOperation = "Checking for new appointments in outlook";

            // get list of outlook appointments
            outlookAppointmentHashtable = outlookInterface.GetAppointments();
            log.LogInfo(outlookAppointmentHashtable.Count + " appointments retrieved from Outlook");

            // identify new appointments
            ArrayList newOutlookAppointmentList = filterNewOutlookItems(outlookAppointmentHashtable, ItemType.Appointments);
            log.LogInfo(newOutlookAppointmentList.Count + " appointments created since last synchronised (Outlook)");

            //create new remote appointments
            remoteInterface.CreateAppointments(newOutlookAppointmentList);
            status.NumCreatedItemsRemote += newOutlookAppointmentList.Count;
            log.LogInfo(newOutlookAppointmentList.Count + " appointments created (Remote)");

            // update synchronized items list (add)
            addSynchronizedItems(newOutlookAppointmentList, ItemType.Appointments);
            log.LogInfo(newOutlookAppointmentList.Count + " appointments added to synchronized items");

            // check for missing appointments
            ArrayList deadRemoteAppointmentList = filterDeletedOutlookItems(outlookAppointmentHashtable, ItemType.Appointments);
            log.LogInfo(deadRemoteAppointmentList.Count + " appointments deleted since last synchronised (Outlook)");

            // delete remote appointments
            remoteInterface.DeleteAppointments(deadRemoteAppointmentList);
            status.NumRemovedItemsRemote += deadRemoteAppointmentList.Count;
            log.LogInfo(deadRemoteAppointmentList.Count + " appointments deleted (Remote)");

            // update synchronized items list (remove)
            removeSynchronizedItems(deadRemoteAppointmentList, ItemType.Appointments);
            log.LogInfo(deadRemoteAppointmentList.Count + " appointments removed from synchronized items");



            //
            // remote side:
            //
            status.CurrentOperation = "Checking for new appointments on remote server";
            status.SyncPercentage = 30;

            // getAppointments
            Hashtable remoteAppointmentHashtable = remoteInterface.GetAppointments();
            log.LogInfo(remoteAppointmentHashtable.Count + " appointments retrieved from remote server");

            status.SyncPercentage = 40;


            // check for unknown appointments
            ArrayList newRemoteAppointmentList = filterNewRemoteItems(remoteAppointmentHashtable, ItemType.Appointments);
            log.LogInfo(newRemoteAppointmentList.Count + " appointemnts created since last synchronised (Remote)");

            status.SyncPercentage = 65;

            // createNewOutlookAppointments
            outlookInterface.CreateAppointments(newRemoteAppointmentList);

            status.NumCreatedItemsLocal += (newRemoteAppointmentList.Count - status.NumCorrectedReadOnlyItems);

            log.LogInfo(newRemoteAppointmentList.Count + " appointments created (Outlook)");

            // update synchronized items (add)
            addSynchronizedItems(newRemoteAppointmentList, ItemType.Appointments);
            log.LogInfo(newRemoteAppointmentList.Count + " appointments added to synchronized items");


            // check for missing appointments
            ArrayList deadOutlookAppointmentList = filterDeletedRemoteItems(remoteAppointmentHashtable, ItemType.Appointments);
            log.LogInfo(deadOutlookAppointmentList.Count + " appointments deleted since last synchronised (Remote)");

            // remove deleted appointments (outlook)
            outlookInterface.DeleteAppointments(deadOutlookAppointmentList);
            status.NumRemovedItemsLocal += deadOutlookAppointmentList.Count;
            log.LogInfo(deadOutlookAppointmentList.Count + " appointments deleted (Outlook)");

            // update synchronized items list (remove)
            removeSynchronizedItems(deadOutlookAppointmentList, ItemType.Appointments);
            log.LogInfo(deadOutlookAppointmentList.Count + " appointments removed from synchronized items");

            status.SyncPercentage = 60;

            //
            // modified
            //
            status.CurrentOperation = "Checking modified outlook appointments";


            // remove dead files from modified check
            foreach (BaseItem item in deadRemoteAppointmentList) remoteAppointmentHashtable.Remove(item.RemoteId);
            foreach (BaseItem item in deadOutlookAppointmentList) outlookAppointmentHashtable.Remove(item.OutlookId);

            // remove new files from modified check
            foreach (BaseItem item in newRemoteAppointmentList) remoteAppointmentHashtable.Remove(item.RemoteId);
            foreach (BaseItem item in newOutlookAppointmentList) remoteAppointmentHashtable.Remove(item.RemoteId);
            foreach (BaseItem item in newOutlookAppointmentList) outlookAppointmentHashtable.Remove(item.OutlookId);


            // filter modified outlook appoitments
            Hashtable modifiedOutlookAppointmentsHashtable
                = filterModifiedOutlookItems(outlookAppointmentHashtable, ItemType.Appointments);
            log.LogInfo(modifiedOutlookAppointmentsHashtable.Count + " modified outlook appointments found");

            // filter modified remote appointments
            Hashtable modifiedRemoteAppointmentHashtable
                = filterModifiedRemoteFootprints(remoteAppointmentHashtable, ItemType.Appointments);
            log.LogInfo(modifiedRemoteAppointmentHashtable.Count + " modified remote appointments found");

            // remove conflicts
            int conflicts = removeConflicts(modifiedRemoteAppointmentHashtable,
                modifiedOutlookAppointmentsHashtable, ConflictStrategy.PreferRemote, ItemType.Appointments);

            status.NumConflicts += conflicts;
            log.LogInfo(conflicts + " conflicts removed!");

            status.SyncPercentage = 70;

            // download changed remote appointments (from server)
            status.CurrentOperation = "Checking modified remote appointments";

            ICollection modifiedRemoteAppointmentList = modifiedRemoteAppointmentHashtable.Values;

            // update changed outlook appointments (on server)
            getRemoteIds(modifiedOutlookAppointmentsHashtable.Values, ItemType.Appointments);
            remoteInterface.UpdateAppointments(modifiedOutlookAppointmentsHashtable.Values);

            status.NumModifiedItemsRemote += modifiedOutlookAppointmentsHashtable.Count;
            log.LogInfo(modifiedOutlookAppointmentsHashtable.Count + " appointments updated on remote server");


            status.SyncPercentage = 90;

            // update changed remote appointments (on outlook)
            getOutlookIds(modifiedRemoteAppointmentList, ItemType.Appointments);
            outlookInterface.UpdateAppointments(modifiedRemoteAppointmentList);

            status.NumModifiedItemsLocal += modifiedRemoteAppointmentList.Count;
            log.LogInfo(modifiedRemoteAppointmentList.Count + " appointments updated in outlook");


            //
            //  finish up
            // 


            // update last modification dates
            Hashtable remoteAppointmentsSynced = remoteInterface.GetAppointments();
            refreshLastModRemoteTimes(remoteAppointmentsSynced.Values, ItemType.Appointments);
            log.LogInfo(remoteAppointmentsSynced.Count + " remote appointment modification times updated");
            Hashtable outlookAppointmentsSynced = outlookInterface.GetAppointments();
            refreshLastModOutlookTimes(outlookAppointmentsSynced.Values, ItemType.Appointments);
            log.LogInfo(outlookAppointmentsSynced.Count + " outlook appointment modification times updated");


            //
            //  Save Synchronized Appointments
            // 

            saveSynchronizedItems(appointmentSncFilePath, ItemType.Appointments);
            log.LogInfo(remoteSyncedItems[(int)ItemType.Appointments].Count + " previously synced appointments saved");


        }

        private void synchronizeTasks()
        {
            //
            // read only tasks:
            //
            status.CurrentOperation = "Identifying modified Read-Only Tasks";

            // get list of outlook tasks
            Hashtable outlookTaskHashtable = outlookInterface.GetTasks();
            log.LogInfo(outlookTaskHashtable.Count + " tasks retrieved from Outlook");

            ArrayList modReadOnlyTasks = filterModifiedReadOnlyItems(outlookTaskHashtable, ItemType.Tasks);
            outlookInterface.DeleteAppointments(modReadOnlyTasks);

            status.NumCorrectedReadOnlyItems += modReadOnlyTasks.Count;
            log.LogInfo(modReadOnlyTasks.Count + " read only tasks were modified and will be replaced");

            //
            // outlook side:
            //
            status.CurrentOperation = "Checking for new tasks in Outlook";

            // get list of outlook tasks
            outlookTaskHashtable = outlookInterface.GetTasks();
            log.LogInfo(outlookTaskHashtable.Count + " tasks retrieved from Outlook");

            // identify new tasks
            ArrayList newOutlookTaskList = filterNewOutlookItems(outlookTaskHashtable, ItemType.Tasks);
            log.LogInfo(newOutlookTaskList.Count + " tasks created since last synchronised (Outlook)");

            //create new remote tasks
            remoteInterface.CreateTasks(newOutlookTaskList);
            status.NumCreatedItemsRemote += newOutlookTaskList.Count;
            log.LogInfo(newOutlookTaskList.Count + " tasks created (Remote)");

            // update synchronized items list (add)
            addSynchronizedItems(newOutlookTaskList, ItemType.Tasks);
            log.LogInfo(newOutlookTaskList.Count + " tasks added to synchronized items");

            // check for missing tasks
            ArrayList deadRemoteTaskList = filterDeletedOutlookItems(outlookTaskHashtable, ItemType.Tasks);
            log.LogInfo(deadRemoteTaskList.Count + " tasks deleted since last synchronised (Outlook)");

            // delete remote tasks
            remoteInterface.DeleteTasks(deadRemoteTaskList);
            status.NumRemovedItemsRemote += deadRemoteTaskList.Count;
            log.LogInfo(deadRemoteTaskList.Count + " tasks deleted (Remote)");

            // update synchronized items list (remove)
            removeSynchronizedItems(deadRemoteTaskList, ItemType.Tasks);
            log.LogInfo(deadRemoteTaskList.Count + " tasks removed from synchronized items");



            //
            // remote side:
            //
            status.CurrentOperation = "Checking for New Tasks on Remote Server";
            status.SyncPercentage = 30;

            // getTasks
            Hashtable remoteTaskHashtable = remoteInterface.GetTasks();
            log.LogInfo(remoteTaskHashtable.Count + " tasks retrieved from remote server");

            status.SyncPercentage = 40;

            
            // check for unknown appointments
            ArrayList newRemoteTaskList = filterNewRemoteItems(remoteTaskHashtable, ItemType.Tasks);
            log.LogInfo(newRemoteTaskList.Count + " tasks created since last synchronised (Remote)");

            status.SyncPercentage = 65;

            // createNewOutlookTasks
            outlookInterface.CreateTasks(newRemoteTaskList);

            status.NumCreatedItemsLocal += (newRemoteTaskList.Count - status.NumCorrectedReadOnlyItems);

            log.LogInfo(newRemoteTaskList.Count + " tasks created (Outlook)");

            // update synchronized items (add)
            addSynchronizedItems(newRemoteTaskList, ItemType.Tasks);
            log.LogInfo(newRemoteTaskList.Count + " tasks added to synchronized items");


            // check for missing tasks
            ArrayList deadOutlookTaskList = filterDeletedRemoteItems(remoteTaskHashtable, ItemType.Tasks);
            log.LogInfo(deadOutlookTaskList.Count + " tasks deleted since last synchronised (Remote)");

            // remove deleted tasks (outlook)
            outlookInterface.DeleteTasks(deadOutlookTaskList);
            status.NumRemovedItemsLocal += deadOutlookTaskList.Count;
            log.LogInfo(deadOutlookTaskList.Count + " tasks deleted (Outlook)");

            // update synchronized items list (remove)
            removeSynchronizedItems(deadOutlookTaskList, ItemType.Tasks);
            log.LogInfo(deadOutlookTaskList.Count + " tasks removed from synchronized items");

            status.SyncPercentage = 60;

            //
            // modified
            //
            status.CurrentOperation = "Checking Modified Outlook Tasks";


            // remove dead files from modified check
            foreach (BaseItem item in deadRemoteTaskList) remoteTaskHashtable.Remove(item.RemoteId);
            foreach (BaseItem item in deadOutlookTaskList) outlookTaskHashtable.Remove(item.OutlookId);

            // remove new files from modified check
            foreach (BaseItem item in newRemoteTaskList) remoteTaskHashtable.Remove(item.RemoteId);
            foreach (BaseItem item in newOutlookTaskList) remoteTaskHashtable.Remove(item.RemoteId);
            foreach (BaseItem item in newOutlookTaskList) outlookTaskHashtable.Remove(item.OutlookId);


            // filter modified outlook tasks
            Hashtable modifiedOutlookTasksHashtable
                = filterModifiedOutlookItems(outlookTaskHashtable, ItemType.Tasks);
            log.LogInfo(modifiedOutlookTasksHashtable.Count + " modified outlook tasks found");

            // filter modified remote tasks
            Hashtable modifiedRemoteTasksHashtable
                = filterModifiedRemoteFootprints(remoteTaskHashtable, ItemType.Tasks);
            log.LogInfo(modifiedRemoteTasksHashtable.Count + " modified remote tasks found");

            // remove conflicts
            int conflicts = removeConflicts(modifiedRemoteTasksHashtable,
                modifiedOutlookTasksHashtable, ConflictStrategy.PreferRemote, ItemType.Tasks);

            status.NumConflicts += conflicts;
            log.LogInfo(conflicts + " conflicts removed!");

            status.SyncPercentage = 70;

            // download changed remote tasks (from server)
            status.CurrentOperation = "Checking Modified Remote Tasks";

            ICollection modifiedRemoteTaskList = modifiedRemoteTasksHashtable.Values;

            // update changed outlook tasks (on server)
            getRemoteIds(modifiedOutlookTasksHashtable.Values, ItemType.Tasks);
            remoteInterface.UpdateTasks(modifiedOutlookTasksHashtable.Values);

            status.NumModifiedItemsRemote += modifiedOutlookTasksHashtable.Count;
            log.LogInfo(modifiedOutlookTasksHashtable.Count + " tasks updated on remote server");


            status.SyncPercentage = 90;

            // update changed remote tasks (on outlook)
            getOutlookIds(modifiedRemoteTaskList, ItemType.Tasks);
            outlookInterface.UpdateTasks(modifiedRemoteTaskList);

            status.NumModifiedItemsLocal += modifiedRemoteTaskList.Count;
            log.LogInfo(modifiedRemoteTaskList.Count + " tasks updated in outlook");


            //
            //  finish up
            // 


            // update last modification dates
            Hashtable remoteTasksSynced = remoteInterface.GetTasks();
            refreshLastModRemoteTimes(remoteTasksSynced.Values, ItemType.Tasks);
            log.LogInfo(remoteTasksSynced.Count + " remote task modification times updated");
            Hashtable outlookTasksSynced = outlookInterface.GetTasks();
            refreshLastModOutlookTimes(outlookTasksSynced.Values, ItemType.Tasks);
            log.LogInfo(outlookTasksSynced.Count + " outlook task modification times updated");


            //
            //  Save Synchronized Contacts
            // 

            saveSynchronizedItems(taskSncFilePath, ItemType.Tasks);
            log.LogInfo(remoteSyncedItems[(int)ItemType.Tasks].Count + " previously synced tasks saved");


        }



        //
        // deprecated functions, do not use:
        // (will be private soon ...)
        //

        public void resetSyncedFiles()
        {
            string message = "Really Reset the Synchronization State?";
            string caption = "Warning!";
            MessageBoxButtons buttons = MessageBoxButtons.YesNo;
            DialogResult result = MessageBox.Show(message, caption, buttons);
            if (result == DialogResult.Yes)
            {
                if (System.IO.File.Exists(contactSncFilePath))
                {
                    System.IO.File.Delete(contactSncFilePath);
                    log.LogWarning("ContactSNC File deleted");
                }

                if (System.IO.File.Exists(appointmentSncFilePath))
                {
                    System.IO.File.Delete(appointmentSncFilePath);
                    log.LogWarning("Appointment SNC File deleted");
                }

                if (System.IO.File.Exists(taskSncFilePath))
                {
                    System.IO.File.Delete(taskSncFilePath);
                    log.LogWarning("Task SNC File deleted");
                }

                Hashtable contacts = outlookInterface.GetContacts();
                outlookInterface.DeleteContacts(contacts.Values);
                log.LogWarning("Deleted " + contacts.Count + " contacts in Outlook");

                Hashtable appointments = outlookInterface.GetAppointments();
                outlookInterface.DeleteAppointments(appointments.Values);
                log.LogWarning("Deleted " + appointments.Count + " appointments in Outlook");

                Hashtable tasks = outlookInterface.GetTasks();
                outlookInterface.DeleteTasks(tasks.Values);
                log.LogWarning("Deleted " + tasks.Count + " tasks in Outlook");

                LoadSyncState();
                log.LogWarning("Synchronization State has been reset!");
            }
        }

//        public void SynchronizeAllAnsynchronous()
//        {
//            Thread synchronizeThread;
//            synchronizeThread = new Thread(new ThreadStart(synchronizeAll));
//            synchronizeThread.Start();
//        }

        public void ConnectRemote()
        {
            remoteInterface.Connect(sessionData.GetRemoteSessionData());
        }


        public void DisconnectRemote()
        {
            remoteInterface.Disconnect();
        }

        public void LoadSyncState()
        {
            loadSynchronizedItems(contactSncFilePath, ItemType.Contacts);
            log.LogInfo(remoteSyncedItems[(int)ItemType.Contacts].Count + " previously synced contacts loaded");

            loadSynchronizedItems(appointmentSncFilePath, ItemType.Appointments);
            log.LogInfo(remoteSyncedItems[(int)ItemType.Appointments].Count + " previously synced appointments loaded");

            loadSynchronizedItems(taskSncFilePath, ItemType.Tasks);
            log.LogInfo(remoteSyncedItems[(int)ItemType.Tasks].Count + " previously synced tasks loaded");
        }

        public void SaveSyncState()
        {
            saveSynchronizedItems(contactSncFilePath, ItemType.Contacts);
            log.LogInfo(remoteSyncedItems[(int)ItemType.Contacts].Count + " previously synced contacts saved");

            saveSynchronizedItems(appointmentSncFilePath, ItemType.Appointments);
            log.LogInfo(remoteSyncedItems[(int)ItemType.Appointments].Count + " previously synced appointments saved");

            saveSynchronizedItems(taskSncFilePath, ItemType.Tasks);
            log.LogInfo(remoteSyncedItems[(int)ItemType.Tasks].Count + " previously synced tasks saved");
        }



        //
        // private utility functions: 
        //
        

        private ArrayList filterModifiedReadOnlyItems(Hashtable outlookItems, ItemType itemType)
        {
            Hashtable synchronizedItemsOutlook = outlookSyncedItems[(int)itemType];
            Hashtable synchronizedItemsRemote = remoteSyncedItems[(int)itemType];


            ArrayList removeFromSyncedItems = new ArrayList();
            ArrayList deleteOutlookItems = new ArrayList();

            int count = 0;

            foreach (BaseItem item in synchronizedItemsOutlook.Values)
                if (!item.WriteAccess)
                {
                    if (outlookItems.ContainsKey(item.OutlookId))
                    {
                        BaseItem outlookItem = (BaseItem)outlookItems[item.OutlookId];
                        if (outlookItem.LastModOutlook > item.LastModOutlook)
                        {
                            // read-only item modified. remove from outlook and read only list.
                            removeFromSyncedItems.Add(item);
                            deleteOutlookItems.Add(outlookItem);
                            count++;
                            log.LogWarning("Read only item has been modified, deleting! Remote ID: " + item.RemoteId);
                        }
                    }
                    else
                    {
                        // read-only item deleted. Only need to delete from synchronized items
                        removeFromSyncedItems.Add(item);
                        count++;
                        log.LogWarning("Read only item has been deleted, will be replaced! Remote ID: " + item.RemoteId);
                    }
                }

            // remove marked items
            removeSynchronizedItems(removeFromSyncedItems, itemType);

            return deleteOutlookItems;
        }

        private void addSynchronizedItems(ICollection items, ItemType itemType)
        {
            Hashtable synchronizedItemsOutlook = outlookSyncedItems[(int)itemType];
            Hashtable synchronizedItemsRemote = remoteSyncedItems[(int)itemType];


            foreach (BaseItem item in items)
            {
                synchronizedItemsRemote.Add(item.RemoteId, item);
                synchronizedItemsOutlook.Add(item.OutlookId, item);
            }
        }

        private void removeSynchronizedItems(ICollection items, ItemType itemType)
        {
            Hashtable synchronizedItemsOutlook = outlookSyncedItems[(int)itemType];
            Hashtable synchronizedItemsRemote = remoteSyncedItems[(int)itemType];


            foreach (BaseItem item in items)
            {
                synchronizedItemsRemote.Remove(item.RemoteId);
                synchronizedItemsOutlook.Remove(item.OutlookId);
            }
        }

        private ArrayList filterNewOutlookItems(Hashtable outlookItems, ItemType itemType)
        {
            Hashtable synchronizedItemsOutlook = outlookSyncedItems[(int)itemType];
            Hashtable synchronizedItemsRemote = remoteSyncedItems[(int)itemType];


            ArrayList unknownItems = new ArrayList();
            foreach (BaseItem item in outlookItems.Values)
            {
                if (!synchronizedItemsOutlook.ContainsKey(item.OutlookId))
                    unknownItems.Add(item);
            }
            return unknownItems;
        }

        private ArrayList filterDeletedOutlookItems(Hashtable outlookItems, ItemType itemType)
        {
            Hashtable synchronizedItemsOutlook = outlookSyncedItems[(int)itemType];
            Hashtable synchronizedItemsRemote = remoteSyncedItems[(int)itemType];


            ArrayList deletedItems = new ArrayList();
            foreach (string outlookId in synchronizedItemsOutlook.Keys)
            {
                if (!outlookItems.ContainsKey(outlookId))
                    deletedItems.Add(synchronizedItemsOutlook[outlookId]);
                
            }
            return deletedItems;
        }

        private ArrayList filterNewRemoteItems(Hashtable remoteFootprints, ItemType itemType)
        {
            Hashtable synchronizedItemsOutlook = outlookSyncedItems[(int)itemType];
            Hashtable synchronizedItemsRemote = remoteSyncedItems[(int)itemType];


            ArrayList unknownItems = new ArrayList();
            foreach (string remoteId in remoteFootprints.Keys)
            {
                if (!synchronizedItemsRemote.ContainsKey(remoteId))
                    unknownItems.Add(remoteFootprints[remoteId]);
            }
            return unknownItems;
        }

        private ArrayList filterDeletedRemoteItems(Hashtable remoteFootprints, ItemType itemType)
        {
            Hashtable synchronizedItemsOutlook = outlookSyncedItems[(int)itemType];
            Hashtable synchronizedItemsRemote = remoteSyncedItems[(int)itemType];

            ArrayList deletedItems = new ArrayList();
            foreach (string remoteId in synchronizedItemsRemote.Keys)
            {
                if (!remoteFootprints.ContainsKey(remoteId))
                    deletedItems.Add(synchronizedItemsRemote[remoteId]);

            }
            return deletedItems;
        }

        private Hashtable filterModifiedRemoteFootprints(Hashtable remoteItemFootprints, ItemType itemType)
        {
            Hashtable synchronizedItemsOutlook = outlookSyncedItems[(int)itemType];
            Hashtable synchronizedItemsRemote = remoteSyncedItems[(int)itemType];


            Hashtable modifiedFootprints = new Hashtable();
            foreach (ItemFootprint footprint in remoteItemFootprints.Values)
            {
                BaseItem syncedContact = (BaseItem)synchronizedItemsRemote[footprint.RemoteId];
                //log.LogInfo("Remote contact modified, date: " + footprint.LastModRemote + " synced: " + syncedContact.LastModRemote);
                if (footprint.LastModRemote > syncedContact.LastModRemote)
                {
                    modifiedFootprints.Add(footprint.RemoteId, footprint);
                }
            }
            return modifiedFootprints;
        }

        private Hashtable filterModifiedOutlookItems(Hashtable outlookItems, ItemType itemType)
        {
            Hashtable synchronizedItemsOutlook = outlookSyncedItems[(int)itemType];
            Hashtable synchronizedItemsRemote = remoteSyncedItems[(int)itemType];


            Hashtable modifiedContacts = new Hashtable();
            foreach (BaseItem item in outlookItems.Values)
            {
                BaseItem syncedContact = (BaseItem)synchronizedItemsOutlook[item.OutlookId];
                //log.LogInfo("Outlook contact modified, date: " + item.LastModOutlook + " synced: " + syncedContact.LastModOutlook);
                if (item.LastModOutlook > syncedContact.LastModOutlook)
                {
                    modifiedContacts.Add(item.OutlookId, item);
                }

            }
            return modifiedContacts;
        }

        private int removeConflicts(Hashtable remoteItems, Hashtable localItems, ConflictStrategy strategy, ItemType itemType)
        {
            Hashtable synchronizedItemsOutlook = outlookSyncedItems[(int)itemType];
            Hashtable synchronizedItemsRemote = remoteSyncedItems[(int)itemType];


            int conflicts = 0;
            if (strategy == ConflictStrategy.PreferLatest)
            {
                //ToDo !
            }
            else if (strategy == ConflictStrategy.PreferLocal)
            {
                foreach (string key in localItems.Keys)
                {
                    string remoteKey = ((BaseItem)synchronizedItemsOutlook[key]).RemoteId;
                    if (remoteItems.ContainsKey(remoteKey))
                    {
                        log.LogWarning("Conflict found: " + remoteItems[remoteKey].ToString());
                        remoteItems.Remove(remoteKey);
                        conflicts++;
                    }
                }
            }
            else // strategy PreferRemote is default!
            {
                foreach (string key in remoteItems.Keys)
                {
                    string localKey = ((BaseItem)synchronizedItemsRemote[key]).OutlookId;
                    if (localItems.ContainsKey(localKey))
                    {
                        log.LogWarning("Conflict found: " + localItems[localKey].ToString());
                        localItems.Remove(localKey);
                        conflicts++;
                    }
                }
            }
            return conflicts;
        }

        private void refreshLastModRemoteTimes(ICollection footprints, ItemType itemType)
        {
            Hashtable synchronizedItemsOutlook = outlookSyncedItems[(int)itemType];
            Hashtable synchronizedItemsRemote = remoteSyncedItems[(int)itemType];

            foreach (ItemFootprint footprint in footprints)
            {
                // Add a second to avoid modification on millisecond differences
                footprint.LastModRemote = footprint.LastModRemote.AddSeconds(1);
                if (synchronizedItemsRemote.ContainsKey(footprint.RemoteId))
                   ((BaseItem)synchronizedItemsRemote[footprint.RemoteId]).LastModRemote = footprint.LastModRemote;
            }
        }

        private void refreshLastModOutlookTimes(ICollection items, ItemType itemType)
        {
            Hashtable synchronizedItemsOutlook = outlookSyncedItems[(int)itemType];
            Hashtable synchronizedItemsRemote = remoteSyncedItems[(int)itemType];

            foreach (BaseItem item in items)
            {
                // Add a second to avoid modification on millisecond differences
                item.LastModOutlook = item.LastModOutlook.AddSeconds(1);
                if (synchronizedItemsOutlook.ContainsKey(item.OutlookId))
                    ((BaseItem)synchronizedItemsOutlook[item.OutlookId]).LastModOutlook = item.LastModOutlook;
            }
        }

        private void getOutlookIds(ICollection items, ItemType itemType)
        {
            Hashtable synchronizedItemsOutlook = outlookSyncedItems[(int)itemType];
            Hashtable synchronizedItemsRemote = remoteSyncedItems[(int)itemType];

            foreach (BaseItem item in items)
            {
                item.OutlookId = ((BaseItem)synchronizedItemsRemote[item.RemoteId]).OutlookId;
            }
        }

        private void getRemoteIds(ICollection items, ItemType itemType)
        {
            Hashtable synchronizedItemsOutlook = outlookSyncedItems[(int)itemType];
            Hashtable synchronizedItemsRemote = remoteSyncedItems[(int)itemType];

            foreach (BaseItem item in items)
            {
                item.RemoteId = ((BaseItem)synchronizedItemsOutlook[item.OutlookId]).RemoteId;
            }
        }

        private void loadSynchronizedItems(string fileName, ItemType itemType)
        {
            outlookSyncedItems[(int)itemType] = new Hashtable();
            remoteSyncedItems[(int)itemType] = new Hashtable();

            if (!System.IO.File.Exists(fileName)) return;

            System.IO.StreamReader reader = new System.IO.StreamReader(fileName);

            try
            {
                string readstring;
                while ((readstring = reader.ReadLine()) != null)
                {
                    string[] strings = readstring.Split(',');
                    bool writeAccess = Boolean.Parse(strings[2]);
                    DateTime lastModRemote = DateTime.Parse(strings[3]);
                    DateTime lastModOutlook = DateTime.Parse(strings[4]);
                    BaseItem item = new BaseItem(strings[0], strings[1], lastModRemote, lastModOutlook, writeAccess);
                    remoteSyncedItems[(int)itemType].Add(item.RemoteId, item);
                    outlookSyncedItems[(int)itemType].Add(item.OutlookId, item);
                }

            }
            catch (System.Exception)
            {
                log.LogError("Correspondence file corrupt, not read!");
            }

            reader.Close();
        }

        private void saveSynchronizedItems(string fileName, ItemType itemType)
        {
            Hashtable synchronizedItemsOutlook = outlookSyncedItems[(int)itemType];
            Hashtable synchronizedItemsRemote = remoteSyncedItems[(int)itemType];

            System.IO.StreamWriter writer = new System.IO.StreamWriter(fileName);
            
            foreach (BaseItem item in synchronizedItemsRemote.Values)
	        {
                writer.WriteLine(item.RemoteId + "," + item.OutlookId + "," + item.WriteAccess + "," +
                       item.LastModRemote + "," + item.LastModOutlook);
	        }
            writer.Close();
        }

        
        //
        // private fields:
        //

        
        private enum ConflictStrategy
        {
            PreferRemote    = 0,
            PreferLocal     = 1,
            PreferLatest    = 2,
        }

        private enum ItemType
        {
            Contacts     = 0,
            Appointments = 1,
            Tasks        = 2
        }

        private string contactSncFilePath;
        private string appointmentSncFilePath;
        private string taskSncFilePath;


        private SyncSessionData sessionData;

        private SyncStatus status;

        private Hashtable[] remoteSyncedItems;
        private Hashtable[] outlookSyncedItems;

        private OutlookInterface outlookInterface;
        private IRemoteInterface remoteInterface;

    }
}
