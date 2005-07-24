/***************************************************************************
                           Task.cs  -  description
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
    public class Task : BaseItem
    {
        public static class StatusStrings
        {
            public static readonly string complete = "done";
            public static readonly string inProgress = "ongoing";
        }
        public static class ImportanceStrings
        {
            public static readonly string high = "high";
            public static readonly string normal = "normal";
            public static readonly string low = "low";
        }


        public static readonly DateTime NO_DATE_SET = new DateTime(4501, 1, 1);

        public static string TaskStatusToString(OlTaskStatus status)
        {
            if (status == OlTaskStatus.olTaskComplete)
               return StatusStrings.complete;
            else
               return StatusStrings.inProgress;
        }

        public static OlTaskStatus TaskStringToStatus(String s)
        {
            if (s == StatusStrings.complete)
                return OlTaskStatus.olTaskComplete;
            else
                return OlTaskStatus.olTaskInProgress;
        }


        public static string TaskImportanceToString(OlImportance importance)
        {
            switch (importance)
            {
                case OlImportance.olImportanceHigh:
                    return ImportanceStrings.high;
                case OlImportance.olImportanceLow:
                    return ImportanceStrings.low;
                case OlImportance.olImportanceNormal:
                default:
                    return ImportanceStrings.normal;
            }
        }


        public static OlImportance TaskStringToImportance(String s)
        {
            if (s == ImportanceStrings.high) return OlImportance.olImportanceHigh;
            else if (s == ImportanceStrings.low) return OlImportance.olImportanceLow;
            else return OlImportance.olImportanceNormal;
        }

        public Task()
        {
        }

        public Task(TaskItem outlookTask)
        {
            this.outlookId = outlookTask.EntryID;
            this.lastModOutlook = outlookTask.LastModificationTime.ToUniversalTime();

            #region Set task fields ...

            this.dueDate = outlookTask.DueDate;
            this.startDate = outlookTask.StartDate;
            this.subject = outlookTask.Subject;
            this.status = TaskStatusToString(outlookTask.Status);
            this.importance = TaskImportanceToString(outlookTask.Importance);
            this.category = outlookTask.Categories;
            this.contact = outlookTask.ContactNames;

            // user may block access if not called from plugin
            try
            {
                this.body = outlookTask.Body;
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

        public DateTime DueDate
        {
            get
            {
                return dueDate;
            }

            set
            {
                dueDate = value;
            }
        }

        public DateTime StartDate
        {
            get
            {
                return startDate;
            }

            set
            {
                startDate = value;
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

        public string Status
        {
            get
            {
                return status;
            }

            set
            {
                status = value;
            }
        }
        public string Importance
        {
            get
            {
                return importance;
            }

            set
            {
                importance = value;
            }
        }
        public string Category
        {
            get
            {
                return category;
            }

            set
            {
                category = value;
            }
        }



        #endregion

        private DateTime dueDate;
        private DateTime startDate;
        private string body;
        private string subject;
        private string status;
        private string importance;
        private string category;

        private string contact;
        public string Contact
        {
            get
            {
                return contact;
            }

            set
            {
                contact = value;
            }
        }


    }
}
