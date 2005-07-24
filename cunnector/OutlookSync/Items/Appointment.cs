/***************************************************************************
                        Appointment.cs  -  description
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

using Microsoft.Office.Interop.Outlook;
//using Microsoft.Office.Tools.Outlook;

#endregion

namespace OutlookSync.Items
{
    public class Appointment : BaseItem
    {
        public static readonly DateTime NO_DATE_SET = new DateTime(4501, 1, 1);

        public Appointment()
        {
        }

        public Appointment(AppointmentItem outlookAppointment)
        {
            this.outlookId = outlookAppointment.EntryID;
            this.lastModOutlook = outlookAppointment.LastModificationTime.ToUniversalTime();

            #region Set appointment fields ...

            this.start = outlookAppointment.Start;
            this.end = outlookAppointment.End;
            this.allDayEvent = outlookAppointment.AllDayEvent;
            this.isRecurring = outlookAppointment.IsRecurring;
            this.reminderMinutesBeforeStart = outlookAppointment.ReminderMinutesBeforeStart;
            this.subject = outlookAppointment.Subject;
            this.location = outlookAppointment.Location;

            // user may block access if not called from plugin
            try
            {
                this.body = outlookAppointment.Body;
            }
            catch (System.Exception)
            {
                this.body = "";
            }



            #endregion

        }

        public new string ToString()
        {
            return subject + " (" + remoteId + ")";
        }


        #region Getters, Setters ...

        public DateTime End
        {
            get
            {
                return end;
            }

            set
            {
                end = value;
            }
        }

        public DateTime Start
        {
            get
            {
                return start;
            }

            set
            {
                start = value;
            }
        }

        public bool AllDayEvent
        {
            get
            {
                return allDayEvent;
            }

            set
            {
                allDayEvent = value;
            }
        }

        public string Body
        {
            get
            {
                return body;
            }

            set
            {
                body = value;
            }
        }

        public bool IsRecurring
        {
            get
            {
                return isRecurring;
            }

            set
            {
                isRecurring = value;
            }
        }

        public int ReminderMinutesBeforeStart
        {
            get
            {
                return reminderMinutesBeforeStart;
            }

            set
            {
                reminderMinutesBeforeStart = value;
            }
        }

        public string Subject
        {
            get
            {
                return subject;
            }

            set
            {
                subject = value;
            }
        }
        public string Location
        {
            get
            {
                return location;
            }

            set
            {
                location = value;
            }
        }
        public static TimeSpan AllDayStart
        {
            get
            {
                return allDayStart;
            }
        }
        public static TimeSpan AllDayEnd
        {
            get
            {
                return allDayEnd;
            }
        }


        #endregion  

        private DateTime end;
        private DateTime start;
        private bool allDayEvent;
        private string body;
        private bool isRecurring;
        private int reminderMinutesBeforeStart;
        private string subject;
        private string location;

        private static TimeSpan allDayStart = new TimeSpan(0, 0, 0);

        private static TimeSpan allDayEnd = new TimeSpan(23, 59, 0);


    }
}
