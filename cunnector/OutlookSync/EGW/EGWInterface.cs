/***************************************************************************
                       EGWInterface.cs  -  description
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
using Utilities.Logging;
using OutlookSync.Items;
using OutlookSync.Remote;

#endregion

namespace OutlookSync.EGW
{
    public class EGWInterface : SimpleLoggingBase, IRemoteInterface 
    {
        public EGWInterface()
            :  base("EGWInterface")
        {
            
        }

        
        //
        // System methods:
        //

        public bool Connect(RemoteSessionData sessionData)
        {
            log.LogInfo("Attemting remote connection: " + sessionData.ToString());

            // Setup new certificate policy to enable HTTPS server communication
            
            // Obsolete as of .Net 2.0 Beta2
            //System.Net.ServicePointManager.CertificatePolicy = new Utilities.Networking.AlwaysAcceptCertificatePolicy();

            System.Net.ServicePointManager.ServerCertificateValidationCallback =
                Utilities.Networking.MyRemoteCertificateValidator.AlwaysValidateServerCertificate;

            this.sessionData = sessionData;
            system = new SystemProxy(sessionData.serverUrl);

            LoginType loginParams = new LoginType();
            loginParams.username = sessionData.userName;
            loginParams.password = sessionData.userPassword;
            loginParams.domain = sessionData.domain;

            try
            {
                SessionType sessionParams = system.Login(loginParams);
                sessionData.sessionid = sessionParams.sessionid;
                sessionData.kp3 = sessionParams.kp3;

                log.LogInfo("Connection successful!");
                log.LogInfo("-- Session Id: " + sessionData.sessionid);
                log.LogInfo("-- Session kp3: " + sessionData.kp3);

                egwContacts = new EGWContacts(sessionData);
                egwContacts.SetLogger(log.GetLogger());

                egwAppointments = new EGWAppointments(sessionData);
                egwAppointments.SetLogger(log.GetLogger());

                egwTasks = new EGWTasks(sessionData);
                egwTasks.SetLogger(log.GetLogger());

                return true;
            }
            catch (System.Net.WebException)
            {
                string message = "Connection to remote Server not possible. No internet connection?";
                log.LogError(message);
                throw new Remote.ServerNotFoundException(message);
            }
            catch (CookComputing.XmlRpc.XmlRpcTypeMismatchException)
            {
                string message = "Access denied! Username or Password incorrect?";
                log.LogError(message);
                throw new Remote.InvalidPasswordException(message);
            }

        }

        public bool Disconnect()
        {
            log.LogInfo("Attempting to close connection to " + sessionData.serverUrl);

            SessionType sessionParams= new SessionType();
            sessionParams.sessionid = sessionData.sessionid;
            sessionParams.kp3 = sessionData.kp3;

            LogoutType logoutMessage = system.Logout(sessionParams);

            if (logoutMessage.GOODBYE == SystemProxy.LOGOUT_MESSAGE)
            {
                log.LogInfo("Logout successfull! Message: " + logoutMessage.GOODBYE);
                return true;
            }
            else
            {
                log.LogError("Error logging out! Message: " + logoutMessage.GOODBYE);
                return false;
            }

        }


        //
        // Contacts methods:
        //


        public void CreateContacts(ICollection newContacts)
        {   
            foreach (Contact contact in newContacts)
            {
                String syncId = egwContacts.CreateContact(contact);
                contact.RemoteId = syncId;

                log.LogInfo("Remote contact created. SyncId: " + contact.RemoteId);
            }
        }

        public void UpdateContacts(ICollection changedContacts)
        {
            foreach (Contact contact in changedContacts)
            {
                egwContacts.UpdateContact(contact);
                log.LogInfo("Remote contact updated. SyncId: " + contact.RemoteId);
            }
        }

        public void DeleteContacts(ICollection deadContacts)
        {
            foreach (BaseItem item in deadContacts)
            {
                egwContacts.DeleteContact(item.RemoteId);
            }
        }

        public Hashtable GetContactFootprints()
        {
            return egwContacts.GetFootprints();
        }

        public Hashtable GetContacts()
        {
            // get footprints implemented, use that
            return null;
        }

        public ArrayList GetSelectedContacts(ICollection footprints)
        {
            ArrayList contactList = new ArrayList();

            Contact temp;
            foreach (ItemFootprint footprint in footprints)
            {
                temp = egwContacts.GetContact(footprint);
                contactList.Add(temp);

                log.LogInfo("Downloaded remote contact " + temp.ToString());
            }

            return contactList;
        }


        //
        // Appointments methods
        //

        public void CreateAppointments(ICollection newAppointments)
        {
            foreach (Appointment appointment in newAppointments)
            {
                String syncId = egwAppointments.CreateAppointment(appointment);
                appointment.RemoteId = syncId;

                log.LogInfo("Remote appointment created. SyncId: " + appointment.RemoteId);
            }

        }

        public void UpdateAppointments(ICollection changedAppointments)
        {
            foreach (Appointment appointment in changedAppointments)
            {
                egwAppointments.UpdateAppointment(appointment);
                log.LogInfo("Remote appointment updated. SyncId: " + appointment.RemoteId);
            }

        }

        public void DeleteAppointments(ICollection deadAppointments)
        {
            foreach (BaseItem item in deadAppointments)
            {
                egwAppointments.DeleteAppointment(item.RemoteId);
            }
        }

        public Hashtable GetAppointmentFootprints()
        {
            // not implemented, use GetAppointments()
            return null;
        }


        public Hashtable GetAppointments(DateTime start, DateTime end)
        {
            log.LogInfo("Searching for Appointments between " + start.ToShortDateString() + " and " + end.ToShortDateString());
            
            return egwAppointments.GetAppointments(start, end);
        }

        public Hashtable GetAppointments()
        {   
            DateTime now = System.DateTime.Now.ToUniversalTime();
            DateTime start = now.AddDays(sessionData.searchDaysBefore * -1);
            DateTime end = now.AddDays(sessionData.searchDaysAfter);
            return GetAppointments(start, end);
        }

        public ArrayList GetSelectedAppointments(ICollection footprints)
        {
            ArrayList appointmentList = new ArrayList();

            Appointment temp;
            foreach (ItemFootprint footprint in footprints)
            {
                temp = egwAppointments.GetAppointment(footprint);
                appointmentList.Add(temp);

                log.LogInfo("Downloaded remote appointment " + temp.ToString());
            }

            return appointmentList;
        }


        //
        // Tasks methods:
        //


        public void CreateTasks(ICollection newTasks)
        {
            foreach (Task task in newTasks)
            {
                String syncId = egwTasks.CreateTask(task);
                task.RemoteId = syncId;

                log.LogInfo("Remote task created. SyncId: " + task.RemoteId);
            }

        }

        public void UpdateTasks(ICollection changedTasks)
        {
            foreach (Task task in changedTasks)
            {
                egwTasks.UpdateTask(task);
                log.LogInfo("Remote task updated. SyncId: " + task.RemoteId);
            }

        }

        public void DeleteTasks(ICollection deadTasks)
        {
            foreach (BaseItem item in deadTasks)
            {
                egwTasks.DeleteTask(item.RemoteId);
            }
        }

        public Hashtable GetTaskFootprints()
        {
            // not implemented, use GetTasks()
            return null;
        }

        public Hashtable GetTasks()
        {
            return egwTasks.GetTasks();
        }

        public ArrayList GetSelectedTasks(ICollection tasks)
        {
            ArrayList taskList = new ArrayList();

            Task temp;
            foreach (ItemFootprint footprint in tasks)
            {
                temp = egwTasks.GetTask(footprint);
                taskList.Add(temp);

                log.LogInfo("Downloaded remote task " + temp.ToString());
            }

            return taskList;
        }


        //
        // private fields:
        //

        private RemoteSessionData sessionData;
        private SystemProxy system;
        private EGWContacts egwContacts;
        private EGWAppointments egwAppointments;
        private EGWTasks egwTasks;
    }
}
