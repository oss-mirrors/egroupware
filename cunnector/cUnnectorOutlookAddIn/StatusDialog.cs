/***************************************************************************
                       StatusDialog.cs  -  description
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
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Text;
using System.Windows.Forms;

using Utilities.Logging;
using OutlookSync;

#endregion

namespace cUnnectorOutlookAddIn
{
    partial class StatusDialog : Form
    {

        private int updateCount;
        private Timer timer;

        private SyncStatus status;

        private MemoryLog log;


        public StatusDialog(SyncStatus status, MemoryLog log)
        {
            InitializeComponent();

            this.status = status;
            this.log = log;
        
            refreshStatus();
            refreshLog();

            setupTimer();
        }

        public void SetStatusAndLog(SyncStatus status, MemoryLog log)
        {
            this.status = status;
            this.log = log;
        }

        private void setupTimer()
        {
            timer = new Timer();
            timer.Interval = 500;
            timer.Tick += new EventHandler(timer_Tick);
            timer.Start();
        }

        void timer_Tick(object sender, EventArgs e)
        {
            refreshLog();
            refreshStatus();
        }

        
        //
        // status
        //

        private void refreshStatus()
        {
            if (status == null) return;

            // sync-status picture:
            if (status.SyncError)
            {
                // error
                syncingPictureBox.Image = Properties.Resources.error;
            }

            else if (status.IsSyncRunning)
            {
                // syncing (blinking through alternating images)
                if (++updateCount % 2 == 0) syncingPictureBox.Image = Properties.Resources.syncRunning;
                else syncingPictureBox.Image = Properties.Resources.syncRunning2;
            }
            else // sync done
                syncingPictureBox.Image = Properties.Resources.syncDone;


            // sync progress bar
            syncStatusProgressBar.Value = status.SyncPercentage;
            currentOperationLabel.Text = status.CurrentOperation;

            // last known sync time
            if (status.LastSyncedTime.Year < 1000)
                lastSyncedTimeLabel.Text = "--";
            else
                lastSyncedTimeLabel.Text = status.LastSyncedTime.ToString();

            // global sync stats
            syncedItemsLabel.Text = status.NumSyncedItems.ToString();
            conflictingItemsLabel.Text = status.NumConflicts.ToString();
            modReadOnlyItemsLabel.Text = status.NumCorrectedReadOnlyItems.ToString();

            // remote sync stats
            newRemoteItemsLabel.Text = status.NumCreatedItemsRemote.ToString();
            deletedRemoteItemsLabel.Text = status.NumRemovedItemsRemote.ToString();
            modifiedRemoteItemsLabel.Text = status.NumModifiedItemsRemote.ToString();

            // outlook sync stats
            newOutlookItemsLabel.Text = status.NumModifiedItemsLocal.ToString();
            deletedOutlookItemsLabel.Text = status.NumRemovedItemsLocal.ToString();
            modifiedOutlookItemsLabel.Text = status.NumModifiedItemsLocal.ToString();
        }


        //
        // log
        //

        private void refreshLog()
        {
            if (log == null) return;

            ArrayList newMessages = log.GetMessages();
            foreach (string message in newMessages)
            {
                Object[] objectArray = new Object[4];

                // find message level leading: <LEVEL>
                string level = message.Substring(1, message.IndexOf(">") - 1);
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

                messageLogGridView.Rows.Insert(0, objectArray);
            }
        }

        private void clearLogButton_Click(object sender, EventArgs e)
        {
            messageLogGridView.Rows.Clear();
        }

        private void refreshButton_Click(object sender, EventArgs e)
        {
            refreshLog();
            refreshStatus();
        }

    }
}