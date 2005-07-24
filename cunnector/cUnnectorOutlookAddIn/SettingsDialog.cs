/***************************************************************************
                     SettingsDialog.cs  -  description
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
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Text;
using System.Windows.Forms;

using OutlookSync;

#endregion

namespace cUnnectorOutlookAddIn
{
    public partial class SettingsDialog : Form
    {
        private SyncSessionData sessionData;

        public void applySessionData()
        {
            if (sessionData == null) return;

            syncContactsCheckBox.Checked = sessionData.SyncContacts;
            syncAppointmentsCheckBox.Checked = sessionData.SyncAppointments;
            syncTasksCheckBox.Checked = sessionData.SyncTasks;

            localFolderTextBox.Text = sessionData.LocalDirectory;

            previousUpDown.Value = (int)sessionData.SearchDaysBefore;
            comingUpDown.Value = (int)sessionData.SearchDaysAfter;

            autoSyncIntervallUpDown.Value = sessionData.AutoSyncIntervall;

            urlTextBox.Text = sessionData.ServerUrl;
            domainTextBox.Text = sessionData.Domain;

            userTextBox.Text = sessionData.UserName;
            passwordTextBox.Text = sessionData.UserPassword;
        }

        void retrieveSessionData()
        {
            sessionData = new SyncSessionData();

            sessionData.SyncContacts = syncContactsCheckBox.Checked;
            sessionData.SyncAppointments = syncAppointmentsCheckBox.Checked;
            sessionData.SyncTasks = syncTasksCheckBox.Checked;

            sessionData.LocalDirectory = localFolderTextBox.Text;

            sessionData.SearchDaysBefore = (double)previousUpDown.Value;
            sessionData.SearchDaysAfter = (double)comingUpDown.Value;

            sessionData.AutoSyncIntervall = (int)autoSyncIntervallUpDown.Value;

            sessionData.ServerUrl = urlTextBox.Text;
            sessionData.Domain = domainTextBox.Text;

            sessionData.UserName = userTextBox.Text;
            sessionData.UserPassword = passwordTextBox.Text;
        }

        public SettingsDialog(SyncSessionData sessionData)
        {
            InitializeComponent();
            this.sessionData = sessionData;
            applySessionData();
            Apply.Enabled = false;
        }


        public SyncSessionData getSessionData()
        {
            return sessionData;
        }

        private void StateChanged(object sender, EventArgs e)
        {
            Apply.Enabled = true;
        }


        private void Apply_Click(object sender, EventArgs e)
        {
            retrieveSessionData();
            Apply.Enabled = false;
        }

        private void OK_Click(object sender, EventArgs e)
        {
            retrieveSessionData();
            // close form, result accept (implicit)
        }

        private void localDirectoryBrowseButton_Click(object sender, EventArgs e)
        {
            FolderBrowserDialog folderDialog = new FolderBrowserDialog();
            //folderDialog.RootFolder = Environment.SpecialFolder.Personal;

            DialogResult result = folderDialog.ShowDialog();
            if (result == DialogResult.OK)
            {
                 localFolderTextBox.Text = folderDialog.SelectedPath;
            }
        }



        


    }
}