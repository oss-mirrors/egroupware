/***************************************************************************
                       OutlookTasks.cs  -  description
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
    public class OutlookTasks : SimpleLoggingBase
    {
        public OutlookTasks(MAPIFolder tasksFolder)
            : base("OutlookTasks")
        {
            this.taskFolder = tasksFolder;
            this.readOnlyTasks = new Hashtable();
            taskFolder.Items.ItemChange += new ItemsEvents_ItemChangeEventHandler(Items_ItemChange);
        }

        void Items_ItemChange(object Item)
        {
            try
            {
                TaskItem olTask = (TaskItem)Item;

                if (olTask.UserProperties[readOnlyProperty] != null)
                {
                    MessageBox.Show("Read-only task modified.\nWill be reset at next synchronizeation.");
                }
            }
            catch { }
        }



        public Hashtable GetTasks()
        {
            outlookTaskItems = new Hashtable();
            Hashtable tasks = new Hashtable();

            foreach (TaskItem outlookTask in taskFolder.Items)
            {
                Task task = new Task(outlookTask);
                outlookTaskItems.Add(task.OutlookId, outlookTask);
                tasks.Add(task.OutlookId, task);
            }

            return tasks;
        }

        public string CreateOutlookTask(Task newTask)
        {
            TaskItem olTask = (TaskItem)taskFolder.Items.Add(OlItemType.olTaskItem);
            updateOutlookTask(olTask, newTask);
            newTask.OutlookId = olTask.EntryID;

            log.LogInfo("New outlook task created. OutlookId: " + newTask.OutlookId);
            return newTask.OutlookId;
        }

        public bool UpdateOutlookTask(Task task)
        {
            TaskItem outlookTask = getTaskByOutlookId(task.OutlookId);

            if (outlookTask != null)
            {
                updateOutlookTask(outlookTask, task);
                return true;
            }

            log.LogError("not found by outlookId: " + task.ToString());

            return false;

        }

        public void DeleteOutlookTask(String outlookId)
        {

            TaskItem olTask = getTaskByOutlookId(outlookId);
            if (olTask != null)
            {
                olTask.Delete();
                log.LogInfo("Deleted outlook task. Id: " + outlookId);
            }
            else
            {
                log.LogWarning("Error deleting outlook task, not found. Id: " + outlookId);
            }
            return;
        }

        private TaskItem getTaskByOutlookId(String outlookId)
        {
            string currentId;
            foreach (TaskItem outlookTask in taskFolder.Items)
            {
                currentId = outlookTask.EntryID;
                if (currentId.Equals(outlookId))
                    return outlookTask;
            }

            return null;
        }

        private void updateOutlookTask(TaskItem outlookTask, Task task)
        {
            outlookTask.StartDate = task.StartDate;
            outlookTask.DueDate = task.DueDate;
            outlookTask.Subject = task.Subject;
            outlookTask.Status = Task.TaskStringToStatus(task.Status);
            outlookTask.Importance = Task.TaskStringToImportance(task.Importance);
            outlookTask.Categories = task.Category;
            outlookTask.ContactNames = task.Contact;

            // if user denies security risc access an exception will be thrown
            try
            {
                outlookTask.Body = task.Body;
            }
            catch
            {
                log.LogWarning("User denied access to Task-Body!");
            }

            if (!task.WriteAccess)
            {
                log.LogWarning("Attempting to create read-only Task!");
                outlookTask.Subject = "[ReadOnly] " + outlookTask.Subject;
                outlookTask.UserProperties.Add(readOnlyProperty, OlUserPropertyType.olYesNo, MISSING.Value, MISSING.Value);
                outlookTask.UserProperties[readOnlyProperty].Value = true;
            }


            outlookTask.Save();

            log.LogInfo("updated: " + task.ToString());

        }


        private Hashtable outlookTaskItems;
        private Hashtable readOnlyTasks;
        private MAPIFolder taskFolder;

        private const string readOnlyProperty = "READONLY";

    }
}