/***************************************************************************
                   ThreadedOutlookSyncer.cs  -  description
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
using System.Threading;
using System.Timers;
using System.Windows.Forms;


using OutlookApp = Microsoft.Office.Interop.Outlook.Application;

#endregion

namespace OutlookSync
{
    public class ThreadedOutlookSyncer : OutlookSyncer
    {
        public ThreadedOutlookSyncer(SyncSessionData syncSessionData)
            :base(syncSessionData)
        {
            this.syncThread = new Thread(new ThreadStart(synchronizationThread));
            this.syncTimer = new System.Timers.Timer();
            this.syncTimer.Elapsed += new ElapsedEventHandler(syncTimerTick);
        }

        public ThreadedOutlookSyncer(SyncSessionData syncSessionData, OutlookApp outlookApp)
            :base(syncSessionData, outlookApp)
        {
            this.syncThread = new Thread(new ThreadStart(synchronizationThread));
            this.syncTimer = new System.Timers.Timer();
            this.syncTimer.Elapsed += new ElapsedEventHandler(syncTimerTick);
        }

        // use in most cases (threaded and timed syncing)
        public void StartBackgroundSynchronization(int interval)
        {
            // manual synchronisation (interval == 0)
            if (interval == 0)
            {
                autoSyncing = false;
                log.LogInfo("Background synchronization STARTED");
                startNewSyncThread();
            }
            // automatic synchronisation every intervall minutes
            else
            {
                autoSyncing = true;
                syncTimer.Interval = (interval * 60 * 1000); // interval is in minutes;
                log.LogInfo("Auto synchronization STARTED. Intervall: " + interval + " minutes");
                startNewSyncThread();
            }
        }

        // call to stop auto-synchronization
        public void StopBackgroundSynchronization()
        {
            // deactivate autoSync mode
            autoSyncing = false;

            // stop autoSync timer
            syncTimer.Stop();

            // if syncThread is active, wait till finished
            if (syncThread.IsAlive)
                log.LogWarning("Auto sync will stop after synchronization is completed.");
            else
                log.LogInfo("Auto synchronization STOPPED!");
        }

        public void StopBackgroundSynchronizationAndWait()
        {
            // deactivate autoSync mode
            autoSyncing = false;

            // stop autoSync timer
            syncTimer.Stop();

            // if syncThread is active, wait till finished
            if (syncThread.IsAlive)
            {
                log.LogWarning("Synchronization in progress, waiting...");
                syncThread.Join();
            }

            log.LogInfo("Auto synchronization STOPPED!");
        }

        
        // call to wait for synchronization thread
        public void WaitFinished()
        {
            if (syncThread.IsAlive)
            {
                log.LogWarning("Synchronization in progress, waiting...");
                syncThread.Join();
            }
            log.LogInfo("Synchronizer closed.");
        }

        public bool IsSyncing()
        {
            return syncThread.IsAlive;
        }

        //
        // private utility functions:
        //

        private void startNewSyncThread()
        {
            // currently syncing (wait);
            if (!syncThread.IsAlive)
            {
                syncThread = new Thread(new ThreadStart(synchronizationThread));
                syncThread.Start();
            }
            else
            {
                log.LogWarning("Synchronization in progress...");
                MessageBox.Show("Synchronization in progress...");
                //syncThread.Join();
            }
        }

        private void synchronizationThread()
        {
            Synchronize();

            // if in autoSyncing mode start timer for next sync
            if (autoSyncing)
            {
                syncTimer.Start();
                int min = (int)(syncTimer.Interval / 60 / 1000);
                log.LogInfo("next synchronisation in " + min + " minutes...");
            }
        }

        void syncTimerTick(object sender, EventArgs e)
        {
            // stop timer (will be restarted after sync)
            syncTimer.Stop();

            log.LogInfo("Auto synchronization TICK");

            startNewSyncThread();
        }


        // new Synchronize method creates started and finished events;
        public new void Synchronize()
        {
            if (SyncStarted != null)
                SyncStarted(this, new EventArgs());

            base.Synchronize();

            if (SyncFinished != null)
                SyncFinished(this, new EventArgs());
        }


        public event EventHandler SyncStarted;
        public event EventHandler SyncFinished;

        private System.Timers.Timer syncTimer;
        private System.Threading.Thread syncThread;
        private bool autoSyncing;
    }
}
