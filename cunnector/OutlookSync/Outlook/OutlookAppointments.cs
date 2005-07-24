/***************************************************************************
                     OutlookAppointments.cs  -  description
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
using System.Collections.Generic;
using System.Text;
using System.Windows.Forms;

using Microsoft.Office.Interop.Outlook;
//using Microsoft.Office.Tools.Outlook;

using Utilities.Logging;

using OutlookSync.Items;


using MISSING = System.Reflection.Missing;

#endregion

namespace OutlookSync.Outlook
{
    public class OutlookAppointments : SimpleLoggingBase
    {
        public OutlookAppointments(MAPIFolder appointmentFolder)
            : base("OutlookAppointments")
        {
            this.appointmentFolder = appointmentFolder;
            this.readOnlyAppointments = new Hashtable();
            appointmentFolder.Items.ItemChange += new ItemsEvents_ItemChangeEventHandler(Items_ItemChange);
        }

        void Items_ItemChange(object Item)
        {
            try
            {
                AppointmentItem olAppointment = (AppointmentItem)Item;
                if (olAppointment.UserProperties[readOnlyProperty] != null)
                {
                    MessageBox.Show("Read-only appointment modified.\nWill be reset at next synchronizeation.");
                }
            }
            catch { }
        }

        public Hashtable GetAppointments()
        {
            outlookAppointmentItems = new Hashtable();
            Hashtable appointments = new Hashtable();

            foreach (AppointmentItem outlookAppointment in appointmentFolder.Items)
            {
                Appointment appointment = new Appointment(outlookAppointment);
                outlookAppointmentItems.Add(appointment.OutlookId, outlookAppointment);
                appointments.Add(appointment.OutlookId, appointment);
            }

            return appointments;
        }


        public string CreateOutlookAppointment(Appointment newAppointment)
        {
            AppointmentItem olAppointment = (AppointmentItem)appointmentFolder.Items.Add(OlItemType.olAppointmentItem);
            updateOutlookAppointment(olAppointment, newAppointment);
            newAppointment.OutlookId = olAppointment.EntryID;

            log.LogInfo("New outlook appointment created. OutlookId: " + newAppointment.OutlookId);
            return newAppointment.OutlookId;
        }

        public bool UpdateOutlookAppointment(Appointment appointment)
        {
            AppointmentItem outlookAppointment = getAppointmentByOutlookId(appointment.OutlookId);

            if (outlookAppointment != null)
            {
                updateOutlookAppointment(outlookAppointment, appointment);
                return true;
            }

            log.LogError("not found by outlookId: " + appointment.ToString());

            return false;

        }

        public void DeleteOutlookAppointment(String outlookId)
        {

            AppointmentItem olAppointment = getAppointmentByOutlookId(outlookId);
            if (olAppointment != null)
            {
                olAppointment.Delete();
                log.LogInfo("Deleted outlook appointment. Id: " + outlookId);
            }
            else
            {
                log.LogWarning("Error deleting outlook appointment, not found. Id: " + outlookId);
            }
            return;
        }


        private AppointmentItem getAppointmentByOutlookId(String outlookId)
        {
            string currentId;
            foreach (AppointmentItem outlookAppointment in appointmentFolder.Items)
            {
                currentId = outlookAppointment.EntryID;
                if (currentId.Equals(outlookId))
                    return outlookAppointment;
            }

            return null;
        }


        private void updateOutlookAppointment(AppointmentItem outlookAppointment, Appointment appointment)
        {
            outlookAppointment.Start    = appointment.Start;
            outlookAppointment.End      = appointment.End;
            outlookAppointment.Subject  = appointment.Subject;
            outlookAppointment.AllDayEvent = appointment.AllDayEvent;
            outlookAppointment.Location = appointment.Location;

            // if user denies security risc access an exception will be thrown
            try
            {
                outlookAppointment.Body = appointment.Body;
            }
            catch
            {
                log.LogWarning("User denied access to Appointment-Body!");
            }

            if (!appointment.WriteAccess)
            {
                log.LogWarning("Attempting to create read-only Appointment!");
                outlookAppointment.Subject = "[ReadOnly] " + outlookAppointment.Subject;
                outlookAppointment.UserProperties.Add(readOnlyProperty, OlUserPropertyType.olYesNo, MISSING.Value, MISSING.Value);
                outlookAppointment.UserProperties[readOnlyProperty].Value = true;
            }


            outlookAppointment.Save();

            log.LogInfo("updated: " + appointment.ToString());

        }


        private Hashtable outlookAppointmentItems;
        private Hashtable readOnlyAppointments;
        private MAPIFolder appointmentFolder;

        private const string readOnlyProperty = "READONLY";


    }
}