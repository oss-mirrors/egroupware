/***************************************************************************
                       ControlForm.cs  -  description
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

using System;
using System.Collections;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Text;
using System.Windows.Forms;


using System.Configuration;

using OutlookSync;
using Utilities.Logging;

namespace cUnnectorStandaloneApplication
{
    public partial class ControlForm : Form
    {
        private ThreadedOutlookSyncer syncer;
        private MemoryLog log;
        private NSpring.Logging.Logger fileLog;
        private int updateCount;

        private Timer timer;

        public ControlForm()
        {
            InitializeComponent();

            setupDefaultValues();
            setupLogger();
            setupTimer();
            setupOutlookSyncer();

            setupAboutText();
        }


        private void setupAboutText()
        {
            try
            {
                aboutRichTextBox.LoadFile("Resources/about.rtf");
            }
            catch (System.Exception)
            {
            }

        }

        private void setupLogger()
        {
            string logFilePath = localDirectoryBox.Text + @"\log.txt";

            log = new MemoryLog();
            fileLog = NSpring.Logging.Logger.CreateFileLogger(logFilePath, "{ts} <{ln}> {msg}");
            fileLog.IsBufferingEnabled = false;
            fileLog.AddChild(log.Logger);
            fileLog.Open();
            fileLog.Log("Opened Logger");


        }

        private void setupTimer()
        {
            timer = new Timer();
            timer.Interval = 500;
            timer.Tick += new EventHandler(timer_Tick);
            timer.Start();
        }

        private void setupDefaultValues()
        {
            urlBox.Text =
                System.Configuration.ConfigurationManager.AppSettings["ServerURL"];
            domainBox.Text =
                System.Configuration.ConfigurationManager.AppSettings["Domain"];
            userBox.Text =
                System.Configuration.ConfigurationManager.AppSettings["Username"];
            passwordBox.Text = 
                System.Configuration.ConfigurationManager.AppSettings["Password"];
            localDirectoryBox.Text =
                System.Configuration.ConfigurationManager.AppSettings["localDir"];
        }


        private void setupOutlookSyncer()
        {
            SyncSessionData syncSessionData = new SyncSessionData();
            syncSessionData.ServerUrl = urlBox.Text;
            syncSessionData.Domain = domainBox.Text;
            syncSessionData.UserName = userBox.Text;
            syncSessionData.UserPassword = passwordBox.Text;

            syncSessionData.LocalDirectory = localDirectoryBox.Text;

            syncSessionData.SyncContacts = contactsCheckBox.Checked;
            syncSessionData.SyncAppointments = appointmentsCheckBox.Checked;
            syncSessionData.SyncTasks = tasksCheckBox.Checked;

            syncSessionData.SearchDaysBefore = 31;
            syncSessionData.SearchDaysAfter = 31;


            syncer = new ThreadedOutlookSyncer(syncSessionData);
    
            syncer.SetLogger(fileLog);
        }

        private void ControleForm_FormClosing(object sender, FormClosingEventArgs e)
        {
            if (syncer != null) syncer.WaitFinished();
            if (fileLog != null) fileLog.Close();
        }

        void timer_Tick(object sender, EventArgs e)
        {
            refreshLog();
            refreshStatus();
        }

        private void refreshLog()
        {
            ArrayList newMessages = log.GetMessages();
            foreach (string message in newMessages)
            {
                Object[] objectArray = new Object[4];

                // find message level leading: <LEVEL>
                string level = message.Substring(1,message.IndexOf(">")-1);
                if (level == "Info")
                    objectArray[0] = Properties.Resources.info_small;
                else if (level == "Warning")
                    objectArray[0] = Properties.Resources.warning_small;
                else if (level == "Exception")
                    objectArray[0] = Properties.Resources.error_small;
                else 
                    objectArray[0] = null;
                // remove message level and following space
                string temp = message.Substring(message.IndexOf(">") + 2);

                // time, eight digits hh:mm:ss
                objectArray[1] = temp.Substring(0, 8);

                // remove time and " - "
                temp = temp.Substring(11);

                objectArray[2] = temp;

                messageLogGridView.Rows.Insert(0,objectArray);
            }
        }

        private void refreshStatus()
        {
            if (syncer == null) return; 
            if (syncer.Status == null) return;

            // sync-status picture:
            if (syncer.Status.SyncError)
            {
                // error
                syncingPictureBox.Image = Properties.Resources.error;
                }

            else if (syncer.Status.IsSyncRunning)
            {
                // syncing (blinking through alternating images)
                if (++updateCount % 2 == 0) syncingPictureBox.Image = Properties.Resources.syncRunning;
                else syncingPictureBox.Image = Properties.Resources.syncRunning2;
            }
            else // sync done
                syncingPictureBox.Image = Properties.Resources.syncDone;


            // sync progress bar
            syncStatusProgressBar.Value = syncer.Status.SyncPercentage;
            currentOperationLabel.Text = syncer.Status.CurrentOperation;

            // last known sync time
            if (syncer.Status.LastSyncedTime.Year < 1000)
                lastSyncedTimeLabel.Text = "--";
            else
                lastSyncedTimeLabel.Text = syncer.Status.LastSyncedTime.ToString();

            // global sync stats
            syncedItemsLabel.Text = syncer.Status.NumSyncedItems.ToString();
            conflictingItemsLabel.Text = syncer.Status.NumConflicts.ToString();
            modReadOnlyItemsLabel.Text = syncer.Status.NumCorrectedReadOnlyItems.ToString();

            // remote sync stats
            newRemoteItemsLabel.Text = syncer.Status.NumCreatedItemsRemote.ToString();
            deletedRemoteItemsLabel.Text = syncer.Status.NumRemovedItemsRemote.ToString();
            modifiedRemoteItemsLabel.Text = syncer.Status.NumModifiedItemsRemote.ToString();

            // outlook sync stats
            newOutlookItemsLabel.Text = syncer.Status.NumModifiedItemsLocal.ToString();
            deletedOutlookItemsLabel.Text = syncer.Status.NumRemovedItemsLocal.ToString();
            modifiedOutlookItemsLabel.Text = syncer.Status.NumModifiedItemsLocal.ToString();

        }



        private void clearLogButton_Click(object sender, EventArgs e)
        {
            messageLogGridView.Rows.Clear();
        }

        private void refreshLogButton_Click(object sender, EventArgs e)
        {
            refreshLog();
        }

        private void startSynchronizingButton_Click(object sender, EventArgs e)
        {
            startSynchronizingButton.Enabled = false;
            manualSynchronizeButton.Enabled = false;

            int syncIntervall = (int)syncIntervallUpDown.Value;
            syncer.StartBackgroundSynchronization(syncIntervall);

            refreshStatus();
        }

        private void stopSynchronizingButton_Click(object sender, EventArgs e)
        {
            syncer.StopBackgroundSynchronizationAndWait();
            startSynchronizingButton.Enabled = true;
            manualSynchronizeButton.Enabled = true;
        }

        private void manualSynchronizeButton_Click(object sender, EventArgs e)
        {
            syncer.StartBackgroundSynchronization(0);
            refreshStatus();
        }

        private void applySettingsButton_Click(object sender, EventArgs e)
        {
            setupOutlookSyncer();
        }

        private void cancelSettingsButton_Click(object sender, EventArgs e)
        {
            setupDefaultValues();
        }



        //
        // Deprecated Buttons, will be removed!
        //

        private void syncButton_Click(object sender, EventArgs e)
        {
            //syncer.SynchronizeAllAnsynchronous();
            MessageBox.Show("Deactivated function.");
        }
        private void loadButton_Click(object sender, EventArgs e)
        {
            syncer.LoadSyncState();
        }
        private void saveButton_Click(object sender, EventArgs e)
        {
            syncer.SaveSyncState();
        }
        private void connectButton_Click(object sender, EventArgs e)
        {
            setupOutlookSyncer();
            syncer.ConnectRemote();
        }
        private void disconnectButton_Click(object sender, EventArgs e)
        {
            syncer.DisconnectRemote();
        }

        private void expertLockCheckBox_CheckedChanged(object sender, EventArgs e)
        {
            expertControlsGroupBox.Enabled = !expertLockCheckBox.Checked;
            resetsGroupBox.Enabled = !expertLockCheckBox.Checked;
        }

        private void resetSyncStateButton_Click(object sender, EventArgs e)
        {
            syncer.resetSyncedFiles();
        }

   
    }
}