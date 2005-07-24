/***************************************************************************
                         EGWTasks.cs  -  description
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


using Proxy = OutlookSync.EGW.InfologProxy;
using TaskFields = OutlookSync.EGW.InfologProxy.InfologFields;

#endregion

namespace OutlookSync.EGW
{
    public class EGWTasks : SimpleLoggingBase
    {
        public static readonly DateTime NO_DATE_SET_REMOTE = new DateTime(1970, 01, 01, 01, 00, 00);

        public EGWTasks(RemoteSessionData sessionData) : base("EGWTasks")
        {
            infolog = new Proxy(sessionData);
        }

        public EGWTasks(string url, SessionType session) : base("EGWTasks")
        {
            infolog = new Proxy(url, session);
        }

        public Hashtable GetTasks()
        {
            XmlRpcStruct[] searchResult;
            XmlRpcStruct searchParams = new XmlRpcStruct();

            Hashtable hash = new Hashtable();

            try
            {
                searchResult = infolog.Search(searchParams);
            }
            catch (XmlRpcFaultException ex)
            {
                log.LogError("Error searching egw-infolog! FaultCode: " + ex.FaultCode);
                return hash;
            }

            if (searchResult == null)
            {
                log.LogWarning("No infologs found on remote server");
                return hash;
            }

            foreach (XmlRpcStruct var in searchResult)
            {
                Task task = structToTask(var);
                hash.Add(task.RemoteId, task);
            }

            return hash;
        }

        public Task GetTask(ItemFootprint footprint)
        {
            return GetTask(footprint.RemoteId);
        }



        public Task GetTask(string id)
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

            XmlRpcStruct readResult;
            try
            {
                readResult = infolog.Read(intId);
            }
            catch (XmlRpcFaultException ex)
            {
                log.LogWarning("Error reading Task. Doesn't exist?. FaultCode: " + ex.FaultCode);

                // id doesn't exist or restricted, return null
                return null;
            }

            Task task = structToTask(readResult);
            return task;
        }

        public string CreateTask(Task task)
        {
            XmlRpcStruct newTask = taskToStruct(task);
            newTask.Add(Proxy.ParamWriteId, Proxy.ParamWriteCreateId);

            string newId = (string)infolog.Write(newTask);
            task.RemoteId = newId;
            return newId;
        }

        public bool UpdateTask(Task task)
        {
            int intId;
            try
            {
                intId = int.Parse(task.RemoteId);
            }
            catch (System.FormatException)
            {
                // id should be integer -> invalid, return false 
                return false;
            }

            try
            {
                XmlRpcStruct updatedTask = taskToStruct(task);
                updatedTask.Add(Proxy.ParamWriteId, intId);
                infolog.Write(updatedTask);
                return true;
            }
            catch (XmlRpcFaultException ex)
            {
                log.LogError("Error updating task. Doesn't exist? FaultCode: " + ex.FaultCode);
                return false;
            }
        }


        public void DeleteTask(string id)
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
                infolog.Delete(intId);
            }
            catch (XmlRpcFaultException faultException)
            {
                log.LogWarning("Error deleting. Already removed? FaultCode: " + faultException.FaultCode.ToString());
            }
            catch (XmlRpcIllFormedXmlException)
            {
                log.LogWarning("Error deleting. Ill formed XML exception!?!");
            }

            log.LogInfo("Deleted remote Task. Id: " + id);
        }



        private Task structToTask(XmlRpcStruct taskFields)
        {
            Task task = new Task();

            task.RemoteId = taskFields[TaskFields.id].ToString();
            task.LastModRemote = (DateTime)taskFields[TaskFields.lastModified];

            task.StartDate = (DateTime)taskFields[TaskFields.start];
            if (task.StartDate == NO_DATE_SET_REMOTE) task.StartDate = Task.NO_DATE_SET;

            task.DueDate = (DateTime)taskFields[TaskFields.end];
            if (task.DueDate == NO_DATE_SET_REMOTE) task.DueDate = Task.NO_DATE_SET;

            task.Subject = reformat((string)taskFields[TaskFields.subject]);

            int access = (int)taskFields[TaskFields.rights];
            if ((access < 0) ||                              // own item
                (((access & 4) > 0) && ((access & 8) > 0)))  // write & delete 
                task.WriteAccess = true;
            else task.WriteAccess = false;

            task.Body = reformat((string)taskFields[TaskFields.description]);
            task.Status = reformat((string)taskFields[TaskFields.status]);
            task.Importance = reformat((string)taskFields[TaskFields.priority]);
            task.Contact = reformat((string)taskFields[TaskFields.from]);

            // categories
            try
            {
                XmlRpcStruct categoriesStruct = (XmlRpcStruct)taskFields[TaskFields.category];
                IEnumerator temp = categoriesStruct.Values.GetEnumerator();
                temp.MoveNext();
                task.Category = reformat((string)temp.Current);
            }
            catch (System.InvalidCastException)
            {
                //log.LogWarning("No category set! " + task.RemoteId);
            }

            return task;
        }

        private XmlRpcStruct taskToStruct(Task task)
        {
            XmlRpcStruct taskStruct = new XmlRpcStruct();

            String temp;

            if (task.StartDate == Task.NO_DATE_SET)
                taskStruct.Add(TaskFields.start, NO_DATE_SET_REMOTE);
            else taskStruct.Add(TaskFields.start, task.StartDate);

            if (task.DueDate == Task.NO_DATE_SET)
                taskStruct.Add(TaskFields.end, NO_DATE_SET_REMOTE);
            else taskStruct.Add(TaskFields.end, task.DueDate);

            if (task.Subject != null) temp = task.Subject; else temp = "";
            taskStruct.Add(TaskFields.subject, temp);

            if (task.Body != null) temp = task.Body; else temp = "";
            taskStruct.Add(TaskFields.description, temp);

            if (task.Status != null) temp = task.Status; else temp = "";
            taskStruct.Add(TaskFields.status, temp);

            if (task.Importance != null) temp = task.Importance; else temp = "";
            taskStruct.Add(TaskFields.priority, temp);

            if (task.Contact != null) temp = task.Contact; else temp = "";
            taskStruct.Add(TaskFields.from, temp);

//            if ((task.Category != null) && (task.Category != ""))
//            {
//                XmlRpcStruct catStruct = new XmlRpcStruct();
//                // must supply correct category id, find by calling categories.
//                // problem: no way of creating new categorys.
//                catStruct.Add("3", task.Category);
//                taskStruct.Add(TaskFields.category, catStruct);
//            }
//

            return taskStruct;
        }


        private string reformat(string temp)
        {
            return EGW.HTMLReformater.HtmlToPlain(temp);
        }



        private InfologProxy infolog;

    }
}

