/***************************************************************************
                      EGWAppointments.cs  -  description
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

using CookComputing.XmlRpc;
using Utilities.Logging;

using OutlookSync.Items;
using OutlookSync.Remote;

using Proxy = OutlookSync.EGW.CalendarProxy;
using AppointmentFields = OutlookSync.EGW.CalendarProxy.AppointmentFields;

#endregion

namespace OutlookSync.EGW
{
    public class EGWAppointments : SimpleLoggingBase
    {
        public EGWAppointments(RemoteSessionData sessionData) : base("EGWAppointments")
        {
            calendar = new Proxy(sessionData);
        }

        public EGWAppointments(string url, SessionType session) : base("EGWAppointments")
        {
            calendar = new Proxy(url, session);
        }

        public Hashtable GetAppointments(DateTime startDate, DateTime endDate)
        {
            XmlRpcStruct[] searchResult;
            XmlRpcStruct searchParams = new XmlRpcStruct();


            searchParams.Add(Proxy.ParamStartDate, startDate);
            searchParams.Add(Proxy.ParamEndDate, endDate);

            Hashtable hash = new Hashtable();

            try
            {
                searchResult = calendar.Search(searchParams);
            }
            catch (XmlRpcFaultException ex)
            {
                log.LogError("Error searching egw-calendar! FaultCode: " + ex.FaultCode);
                return hash;
            }

            if (searchResult == null)
            {
                log.LogWarning("No appointments found on remote server");
                return hash;
            }

            foreach (XmlRpcStruct var in searchResult)
            {
                Appointment appointment = structToAppointment(var);
                hash.Add(appointment.RemoteId, appointment);
            }

            return hash;
        }

        public Appointment GetAppointment(ItemFootprint footprint)
        {
            return GetAppointment(footprint.RemoteId);
        }


        public Appointment GetAppointment(string id)
        {
            int intId;

            try
            {
                intId = int.Parse(id);
            }
            catch (System.FormatException)
            {
                // id should be integer -> invalid, return null 
                return null;
            }

            // read all fields for contact
            XmlRpcStruct readResult;
            try
            {
                readResult = calendar.Read(intId);
            }
            catch (XmlRpcFaultException ex)
            {
                log.LogWarning("Error reading Appointment. Doesn't exist?. FaultCode: " + ex.FaultCode);

                // id doesn't exist or restricted, return null
                return null;
            }

            Appointment appointment = structToAppointment(readResult);
            return appointment;
        }

        public string CreateAppointment(Appointment appointment)
        {
            XmlRpcStruct newAppointment = appointmentToStruct(appointment);
            newAppointment.Add(Proxy.ParamWriteId, Proxy.ParamWriteCreateId);
            string newId = calendar.Write(newAppointment).ToString();
            appointment.RemoteId = newId;
            return newId;
        }

        public bool UpdateAppointment(Appointment appointment)
        {
            int intId;
            try
            {
                intId = int.Parse(appointment.RemoteId);
            }
            catch (System.FormatException)
            {
                // id should be integer -> invalid, return false 
                return false;
            }

            try
            {
                XmlRpcStruct updatedAppointment = appointmentToStruct(appointment);
                updatedAppointment.Add(Proxy.ParamWriteId, intId);
                calendar.Write(updatedAppointment);
                return true;
            }
            catch (XmlRpcFaultException ex)
            {
                log.LogError("Error updating appointment. Doesn't exist? FaultCode: " + ex.FaultCode);
                return false;
            }
        }


        public void DeleteAppointment(string id)
        {
            int intId;

            try
            {
                intId = int.Parse(id);
            }
            catch (System.FormatException)
            {
                // id should be integer -> invalid, return null 
                log.LogError("Invalid id, should be number: " + id);
                return;
            }

            try
            {
                calendar.Delete(intId);
            }
            catch (XmlRpcFaultException faultException)
            {
                log.LogWarning("Error deleting. Already removed? FaultCode: " + faultException.FaultCode.ToString());
            }
            log.LogInfo("Deleted remote Appointment. Id: " + id);
        }



        private Appointment structToAppointment(XmlRpcStruct appointmentFields)
        {
            Appointment appointment = new Appointment();

            appointment.RemoteId      = appointmentFields[AppointmentFields.id].ToString();
            appointment.LastModRemote = (DateTime)appointmentFields[AppointmentFields.lastModified];

            appointment.Start    = (DateTime)appointmentFields[AppointmentFields.start];
            appointment.End      = (DateTime)appointmentFields[AppointmentFields.end];


            // check if all day event:
            if ((appointment.Start.Date == appointment.End.Date)
                  && (appointment.Start.TimeOfDay == Appointment.AllDayStart)
                  && (appointment.End.TimeOfDay == Appointment.AllDayEnd))
                appointment.AllDayEvent = true;
            else appointment.AllDayEvent = false;

            appointment.Subject  = reformat((string)appointmentFields[AppointmentFields.title]);

            try  // someone elses appointment may return no valid rights value.
            {
                int access = (int)appointmentFields[AppointmentFields.rights];
                if ((access < 0) ||                              // own item
                    (((access & 4) > 0) && ((access & 8) > 0)))  // write & delete 
                    appointment.WriteAccess = true;
                else appointment.WriteAccess = false;
            }
            catch (System.InvalidCastException)
            {
                appointment.WriteAccess = false;
            }



            // these fields will cause problems with microsoft security guidelines if calles from
            // outside a plugin: surround with appropriate try catch statement :

            appointment.Body = reformat((string)appointmentFields[AppointmentFields.description]);
            appointment.Location = reformat((string)appointmentFields[AppointmentFields.location]);

            return appointment;
        }

        private XmlRpcStruct appointmentToStruct(Appointment appointment)
        {
            XmlRpcStruct appointmentStruct = new XmlRpcStruct();

            String temp;

            appointmentStruct.Add(AppointmentFields.start, appointment.Start);
            appointmentStruct.Add(AppointmentFields.end, appointment.End);

            if (appointment.Subject != null) temp = appointment.Subject; else temp = "";
            appointmentStruct.Add(AppointmentFields.title, temp);

            if (appointment.Body != null) temp = appointment.Body; else temp = "";
            appointmentStruct.Add(AppointmentFields.description, temp);

            if (appointment.Location != null) temp = appointment.Location; else temp = "";
            appointmentStruct.Add(AppointmentFields.location, temp);

            return appointmentStruct;
        }

        
        private string reformat(string temp)
        {
            return EGW.HTMLReformater.HtmlToPlain(temp);
        }



        private CalendarProxy calendar;

    }
}

