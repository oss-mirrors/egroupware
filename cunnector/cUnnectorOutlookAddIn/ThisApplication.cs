/***************************************************************************
                       ThisApplication.cs  -  description
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
using System.Windows.Forms;
using Microsoft.VisualStudio.Tools.Applications.Runtime;
using Outlook = Microsoft.Office.Interop.Outlook;

using Microsoft.Office.Core;
using OutlookApplication = Microsoft.Office.Interop.Outlook.Application;

using MISSING = System.Reflection.Missing;

using OutlookSync;

using Microsoft.Win32;

namespace cUnnectorOutlookAddIn
{
    public partial class ThisApplication
    {

        #region VSTO Designer generated code

        private void InternalStartup()
        {
            this.Startup += new System.EventHandler(ThisApplication_Startup);
            this.Shutdown += new System.EventHandler(ThisApplication_Shutdown);
        }

        #endregion

        private void ThisApplication_Startup(object sender, System.EventArgs e)
        {
            InitLogger();

            //InitEventHandler();

            InitToolBar();


        }

        private void ThisApplication_Shutdown(object sender, System.EventArgs e)
        {
            if (this.ActiveExplorer() == null) return;

            CommandBars commandBars = this.ActiveExplorer().CommandBars;
            try
            {
                commandBars["OutlookAddInTest"].Delete();
            }
            catch (System.Exception ex)
            {
                MessageBox.Show(ex.Message);
            }
        }

        // custum methods:

        private void InitLogger()
        {
            log = new Utilities.Logging.MemoryLog();

            //            string logFilePath = localDirectoryBox.Text + @"\log.txt";
            //            fileLog = NSpring.Logging.Logger.CreateFileLogger(logFilePath, "{ts} <{ln}> {msg}");
            //            fileLog.IsBufferingEnabled = false;
            //            fileLog.AddChild(log.Logger);
            //            fileLog.Open();
            //            fileLog.Log("Opened Logger");

    
        }


        private void InitEventHandler()
        {
            SyncSessionData sessionData = new SyncSessionData();
            sessionData.RetrieveFromRegistry(registryKey);
            eventHandler = new OutlookSyncer(sessionData, this);
        }

        public void InitToolBar()
        {

            //
            // Synchronization ToolBar
            //

            toolBarAddInTest = null;
            CommandBars commandBars = this.ActiveExplorer().CommandBars;


            try
            {
                // Create a command bar for the add-in
                toolBarAddInTest = commandBars.Add("OutlookAddInTest",
                    Microsoft.Office.Core.MsoBarPosition.msoBarTop,
                    MISSING.Value, true);
            }
            catch (SystemException ex)
            {
                MessageBox.Show(ex.Message);
                return;
            }

            toolBarAddInTest.Visible = true;


            //
            // Synchronize Button
            //

            btnSyncNow = (CommandBarButton)toolBarAddInTest.Controls.Add(1,
                MISSING.Value, MISSING.Value, MISSING.Value, MISSING.Value);

            btnSyncNow.Caption = "Sync Now";
            btnSyncNow.Style = MsoButtonStyle.msoButtonIconAndCaption;
            btnSyncNow.Picture = (stdole.IPictureDisp)
                CommandBarPictureConverter.GetIPictureDispFromPicture(cUnnectorOutlookAddIn.Properties.Resources.sync_btn);

            btnSyncNow.Tag = "Syn Now";
            btnSyncNow.Visible = true;
            btnSyncNow.Click += new _CommandBarButtonEvents_ClickEventHandler(synchronize);


            //
            // AutoSync Button
            //

            btnAutoSync = (CommandBarButton)toolBarAddInTest.Controls.Add(
                MsoControlType.msoControlButton,
                MISSING.Value, MISSING.Value,
                MISSING.Value, MISSING.Value);

            btnAutoSync.Caption = "Start AutoSync";
            btnAutoSync.Style = MsoButtonStyle.msoButtonIconAndCaption;
            btnAutoSync.Picture = (stdole.IPictureDisp)
                CommandBarPictureConverter.GetIPictureDispFromPicture(cUnnectorOutlookAddIn.Properties.Resources.start_btn);

            btnAutoSync.Tag = "AutoSync";
            btnAutoSync.Visible = true;
            btnAutoSync.Click += new _CommandBarButtonEvents_ClickEventHandler(autoSyncButtonClicked);


            //
            // Settings Button
            //

            btnSettings = (CommandBarButton)toolBarAddInTest.Controls.Add(
                MsoControlType.msoControlButton,
                MISSING.Value, MISSING.Value,
                MISSING.Value, MISSING.Value);

            btnSettings.Caption = "Settings";
            btnSettings.Style = MsoButtonStyle.msoButtonIconAndCaption;
            btnSettings.Picture = (stdole.IPictureDisp)
                CommandBarPictureConverter.GetIPictureDispFromPicture(cUnnectorOutlookAddIn.Properties.Resources.settings_btn);

            btnSettings.Tag = "Settings";
            btnSettings.Visible = true;
            btnSettings.Click += new _CommandBarButtonEvents_ClickEventHandler(changeSettings);


            //
            // Status Button
            //

            btnStatus = (CommandBarButton)toolBarAddInTest.Controls.Add(
                MsoControlType.msoControlButton,
                MISSING.Value, MISSING.Value,
                MISSING.Value, MISSING.Value);

            btnStatus.Caption = "SyncStatus";
            btnStatus.Style = MsoButtonStyle.msoButtonIconAndCaption;
            btnStatus.Picture = (stdole.IPictureDisp)
                CommandBarPictureConverter.GetIPictureDispFromPicture(cUnnectorOutlookAddIn.Properties.Resources.status_btn);

            btnStatus.Tag = "SyncStatus";
            btnStatus.Visible = true;
            btnStatus.Click += new _CommandBarButtonEvents_ClickEventHandler(showStatus);
        }


        private void showStatus(CommandBarButton Ctrl, ref bool CancelDefault)
        {
            SyncStatus status =
                    (outlookSyncer == null) ? null : outlookSyncer.Status;

            if (statusDialog == null)
                statusDialog = new StatusDialog(status, log);
            else
                statusDialog.SetStatusAndLog(status, log);

            statusDialog.ShowDialog();
        }

        private void synchronize(CommandBarButton Ctrl, ref bool CancelDefault)
        {
            // if thread is still syncing, do nothing
            if (outlookSyncer != null) if (outlookSyncer.IsSyncing()) return;

            OutlookSync.SyncSessionData sessionData = new OutlookSync.SyncSessionData();
            sessionData.RetrieveFromRegistry(registryKey);

            if (!sessionData.isValid())
            {
                MessageBox.Show("Error, check settings!");
                return;
            }
            outlookSyncer = new ThreadedOutlookSyncer(sessionData, this);

            outlookSyncer.SetLogger(log.Logger);
            outlookSyncer.SyncFinished += new EventHandler(syncFinished);
            btnSyncNow.Enabled = false;
            btnAutoSync.Enabled = false;
            outlookSyncer.StartBackgroundSynchronization(0);

        }

        private void autoSyncButtonClicked(CommandBarButton Ctrl, ref bool CancelDefault)
        {
            if (!isAutoSyncing)
            {
                // if thread is still syncing, do nothing
                if (outlookSyncer != null) if (outlookSyncer.IsSyncing()) return;

                OutlookSync.SyncSessionData sessionData = new OutlookSync.SyncSessionData();
                sessionData.RetrieveFromRegistry(registryKey);
                if (!sessionData.isValid())
                {
                    MessageBox.Show("Error, check settings!");
                    return;
                }

                outlookSyncer = new ThreadedOutlookSyncer(sessionData, this);
                outlookSyncer.SetLogger(log.Logger);

                btnAutoSync.Caption = "Stop AutoSync";
                btnAutoSync.Picture = (stdole.IPictureDisp)
                    CommandBarPictureConverter.GetIPictureDispFromPicture(cUnnectorOutlookAddIn.Properties.Resources.stop_btn);

                btnSyncNow.Enabled = false;

                outlookSyncer.StartBackgroundSynchronization(sessionData.AutoSyncIntervall);

                isAutoSyncing = true;
            }
            else
            {
                if (outlookSyncer != null)
                    outlookSyncer.StopBackgroundSynchronization();

                btnAutoSync.Caption = "Start AutoSync";
                btnAutoSync.Picture = (stdole.IPictureDisp)
                    CommandBarPictureConverter.GetIPictureDispFromPicture(cUnnectorOutlookAddIn.Properties.Resources.start_btn);

                if (outlookSyncer.IsSyncing())
                {
                    outlookSyncer.SyncFinished += new EventHandler(syncFinished);
                    btnSyncNow.Enabled = false;
                    btnAutoSync.Enabled = false;
                }
                else btnSyncNow.Enabled = true;

                isAutoSyncing = false;
            }
        }

        private void changeSettings(CommandBarButton Ctrl, ref bool CancelDefault)
        {
            OutlookSync.SyncSessionData sessionData =
                new OutlookSync.SyncSessionData();

            sessionData.RetrieveFromRegistry(registryKey);

            SettingsDialog dialog = new SettingsDialog(sessionData);
            dialog.ShowDialog();
            sessionData = dialog.getSessionData();

            sessionData.StoreInRegistry(registryKey);

            //InitEventHandler();
        }


        private void syncFinished(Object sender, EventArgs e)
        {
            btnSyncNow.Enabled = true;
            btnAutoSync.Enabled = true;
        }


        private const string registryKey = @"Software\credativ\cUnnector";

        private CommandBarButton btnSyncNow;
        private CommandBarButton btnSettings;
        private CommandBarButton btnStatus;
        private CommandBarButton btnAutoSync;
        private CommandBar toolBarAddInTest;


        private Utilities.Logging.MemoryLog log;
        //private NSpring.Logging.Logger fileLog;

        private StatusDialog statusDialog;

        private ThreadedOutlookSyncer outlookSyncer;
        private OutlookSyncer eventHandler;
        private bool isAutoSyncing = false;

    }
}