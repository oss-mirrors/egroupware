namespace cUnnectorStandaloneApplication
{
    partial class ControlForm
    {
        /// <summary>
        /// Required designer variable.
        /// </summary>
        private System.ComponentModel.IContainer components = null;

        /// <summary>
        /// Clean up any resources being used.
        /// </summary>
        /// <param name="disposing">true if managed resources should be disposed; otherwise, false.</param>
        protected override void Dispose(bool disposing)
        {
            if (disposing && (components != null))
            {
                components.Dispose();
            }
            base.Dispose(disposing);
        }

        #region Windows Form Designer generated code

        /// <summary>
        /// Required method for Designer support - do not modify
        /// the contents of this method with the code editor.
        /// </summary>
        private void InitializeComponent()
        {
            System.Windows.Forms.DataGridViewCellStyle dataGridViewCellStyle1 = new System.Windows.Forms.DataGridViewCellStyle();
            this.tabControl1 = new System.Windows.Forms.TabControl();
            this.mainPage = new System.Windows.Forms.TabPage();
            this.groupBox9 = new System.Windows.Forms.GroupBox();
            this.manualSynchronizeButton = new System.Windows.Forms.Button();
            this.groupBox8 = new System.Windows.Forms.GroupBox();
            this.stopSynchronizingButton = new System.Windows.Forms.Button();
            this.startSynchronizingButton = new System.Windows.Forms.Button();
            this.groupBox7 = new System.Windows.Forms.GroupBox();
            this.syncingPictureBox = new System.Windows.Forms.PictureBox();
            this.panel1 = new System.Windows.Forms.Panel();
            this.modifiedOutlookItemsLabel = new System.Windows.Forms.Label();
            this.label24 = new System.Windows.Forms.Label();
            this.deletedOutlookItemsLabel = new System.Windows.Forms.Label();
            this.label26 = new System.Windows.Forms.Label();
            this.newOutlookItemsLabel = new System.Windows.Forms.Label();
            this.label28 = new System.Windows.Forms.Label();
            this.modifiedRemoteItemsLabel = new System.Windows.Forms.Label();
            this.label18 = new System.Windows.Forms.Label();
            this.deletedRemoteItemsLabel = new System.Windows.Forms.Label();
            this.label20 = new System.Windows.Forms.Label();
            this.newRemoteItemsLabel = new System.Windows.Forms.Label();
            this.label22 = new System.Windows.Forms.Label();
            this.modReadOnlyItemsLabel = new System.Windows.Forms.Label();
            this.label16 = new System.Windows.Forms.Label();
            this.conflictingItemsLabel = new System.Windows.Forms.Label();
            this.label14 = new System.Windows.Forms.Label();
            this.syncedItemsLabel = new System.Windows.Forms.Label();
            this.label11 = new System.Windows.Forms.Label();
            this.lastSyncedTimeLabel = new System.Windows.Forms.Label();
            this.label12 = new System.Windows.Forms.Label();
            this.currentOperationLabel = new System.Windows.Forms.Label();
            this.label10 = new System.Windows.Forms.Label();
            this.syncStatusProgressBar = new System.Windows.Forms.ProgressBar();
            this.settingsPage = new System.Windows.Forms.TabPage();
            this.groupBox1 = new System.Windows.Forms.GroupBox();
            this.tasksCheckBox = new System.Windows.Forms.CheckBox();
            this.appointmentsCheckBox = new System.Windows.Forms.CheckBox();
            this.contactsCheckBox = new System.Windows.Forms.CheckBox();
            this.applySettingsButton = new System.Windows.Forms.Button();
            this.cancelSettingsButton = new System.Windows.Forms.Button();
            this.groupBox4 = new System.Windows.Forms.GroupBox();
            this.label8 = new System.Windows.Forms.Label();
            this.syncIntervallUpDown = new System.Windows.Forms.NumericUpDown();
            this.groupBox5 = new System.Windows.Forms.GroupBox();
            this.localDirectoryBox = new System.Windows.Forms.TextBox();
            this.label6 = new System.Windows.Forms.Label();
            this.groupBox2 = new System.Windows.Forms.GroupBox();
            this.label1 = new System.Windows.Forms.Label();
            this.domainBox = new System.Windows.Forms.TextBox();
            this.label4 = new System.Windows.Forms.Label();
            this.label3 = new System.Windows.Forms.Label();
            this.label2 = new System.Windows.Forms.Label();
            this.passwordBox = new System.Windows.Forms.TextBox();
            this.userBox = new System.Windows.Forms.TextBox();
            this.urlBox = new System.Windows.Forms.TextBox();
            this.logPage = new System.Windows.Forms.TabPage();
            this.groupBox3 = new System.Windows.Forms.GroupBox();
            this.messageLogGridView = new System.Windows.Forms.DataGridView();
            this.clearButton = new System.Windows.Forms.Button();
            this.refreshButton = new System.Windows.Forms.Button();
            this.expertPage = new System.Windows.Forms.TabPage();
            this.resetsGroupBox = new System.Windows.Forms.GroupBox();
            this.resetSyncStateButton = new System.Windows.Forms.Button();
            this.expertLockCheckBox = new System.Windows.Forms.CheckBox();
            this.expertControlsGroupBox = new System.Windows.Forms.GroupBox();
            this.disconnectButton = new System.Windows.Forms.Button();
            this.connectButton = new System.Windows.Forms.Button();
            this.saveButton = new System.Windows.Forms.Button();
            this.loadButton = new System.Windows.Forms.Button();
            this.syncButton = new System.Windows.Forms.Button();
            this.infoPage = new System.Windows.Forms.TabPage();
            this.aboutRichTextBox = new System.Windows.Forms.RichTextBox();
            this.dataGridViewImageColumn1 = new System.Windows.Forms.DataGridViewImageColumn();
            this.dataGridViewTextBoxColumn1 = new System.Windows.Forms.DataGridViewTextBoxColumn();
            this.dataGridViewTextBoxColumn2 = new System.Windows.Forms.DataGridViewTextBoxColumn();
            this.tabControl1.SuspendLayout();
            this.mainPage.SuspendLayout();
            this.groupBox9.SuspendLayout();
            this.groupBox8.SuspendLayout();
            this.groupBox7.SuspendLayout();
            ((System.ComponentModel.ISupportInitialize)(this.syncingPictureBox)).BeginInit();
            this.panel1.SuspendLayout();
            this.settingsPage.SuspendLayout();
            this.groupBox1.SuspendLayout();
            this.groupBox4.SuspendLayout();
            ((System.ComponentModel.ISupportInitialize)(this.syncIntervallUpDown)).BeginInit();
            this.groupBox5.SuspendLayout();
            this.groupBox2.SuspendLayout();
            this.logPage.SuspendLayout();
            this.groupBox3.SuspendLayout();
            ((System.ComponentModel.ISupportInitialize)(this.messageLogGridView)).BeginInit();
            this.expertPage.SuspendLayout();
            this.resetsGroupBox.SuspendLayout();
            this.expertControlsGroupBox.SuspendLayout();
            this.infoPage.SuspendLayout();
            this.SuspendLayout();
            // 
            // tabControl1
            // 
            this.tabControl1.Controls.Add(this.mainPage);
            this.tabControl1.Controls.Add(this.settingsPage);
            this.tabControl1.Controls.Add(this.logPage);
            this.tabControl1.Controls.Add(this.expertPage);
            this.tabControl1.Controls.Add(this.infoPage);
            this.tabControl1.Location = new System.Drawing.Point(13, 13);
            this.tabControl1.Name = "tabControl1";
            this.tabControl1.SelectedIndex = 0;
            this.tabControl1.Size = new System.Drawing.Size(507, 421);
            this.tabControl1.TabIndex = 0;
            // 
            // mainPage
            // 
            this.mainPage.Controls.Add(this.groupBox9);
            this.mainPage.Controls.Add(this.groupBox8);
            this.mainPage.Controls.Add(this.groupBox7);
            this.mainPage.Location = new System.Drawing.Point(4, 22);
            this.mainPage.Name = "mainPage";
            this.mainPage.Padding = new System.Windows.Forms.Padding(3);
            this.mainPage.RightToLeft = System.Windows.Forms.RightToLeft.Yes;
            this.mainPage.Size = new System.Drawing.Size(499, 395);
            this.mainPage.TabIndex = 0;
            this.mainPage.Text = "Main";
            // 
            // groupBox9
            // 
            this.groupBox9.BackColor = System.Drawing.Color.Transparent;
            this.groupBox9.Controls.Add(this.manualSynchronizeButton);
            this.groupBox9.Location = new System.Drawing.Point(9, 10);
            this.groupBox9.Name = "groupBox9";
            this.groupBox9.RightToLeft = System.Windows.Forms.RightToLeft.No;
            this.groupBox9.Size = new System.Drawing.Size(237, 131);
            this.groupBox9.TabIndex = 6;
            this.groupBox9.TabStop = false;
            this.groupBox9.Text = "Manual";
            // 
            // manualSynchronizeButton
            // 
            this.manualSynchronizeButton.Location = new System.Drawing.Point(46, 33);
            this.manualSynchronizeButton.Name = "manualSynchronizeButton";
            this.manualSynchronizeButton.Size = new System.Drawing.Size(140, 85);
            this.manualSynchronizeButton.TabIndex = 4;
            this.manualSynchronizeButton.Text = "Synchronize Now";
            this.manualSynchronizeButton.TextImageRelation = System.Windows.Forms.TextImageRelation.ImageAboveText;
            this.manualSynchronizeButton.Click += new System.EventHandler(this.manualSynchronizeButton_Click);
            // 
            // groupBox8
            // 
            this.groupBox8.BackColor = System.Drawing.Color.Transparent;
            this.groupBox8.Controls.Add(this.stopSynchronizingButton);
            this.groupBox8.Controls.Add(this.startSynchronizingButton);
            this.groupBox8.Location = new System.Drawing.Point(256, 10);
            this.groupBox8.Name = "groupBox8";
            this.groupBox8.RightToLeft = System.Windows.Forms.RightToLeft.No;
            this.groupBox8.Size = new System.Drawing.Size(234, 131);
            this.groupBox8.TabIndex = 5;
            this.groupBox8.TabStop = false;
            this.groupBox8.Text = "Automatic";
            // 
            // stopSynchronizingButton
            // 
            this.stopSynchronizingButton.Font = new System.Drawing.Font("Microsoft Sans Serif", 8.25F);
            this.stopSynchronizingButton.Location = new System.Drawing.Point(48, 78);
            this.stopSynchronizingButton.Margin = new System.Windows.Forms.Padding(3, 2, 3, 3);
            this.stopSynchronizingButton.Name = "stopSynchronizingButton";
            this.stopSynchronizingButton.Size = new System.Drawing.Size(140, 40);
            this.stopSynchronizingButton.TabIndex = 3;
            this.stopSynchronizingButton.Text = "Stop Synchronizing";
            this.stopSynchronizingButton.TextImageRelation = System.Windows.Forms.TextImageRelation.ImageBeforeText;
            this.stopSynchronizingButton.Click += new System.EventHandler(this.stopSynchronizingButton_Click);
            // 
            // startSynchronizingButton
            // 
            this.startSynchronizingButton.Font = new System.Drawing.Font("Microsoft Sans Serif", 8.25F);
            this.startSynchronizingButton.Location = new System.Drawing.Point(48, 33);
            this.startSynchronizingButton.Margin = new System.Windows.Forms.Padding(3, 1, 3, 3);
            this.startSynchronizingButton.Name = "startSynchronizingButton";
            this.startSynchronizingButton.Size = new System.Drawing.Size(140, 40);
            this.startSynchronizingButton.TabIndex = 2;
            this.startSynchronizingButton.Text = "Start Synchronizing";
            this.startSynchronizingButton.TextImageRelation = System.Windows.Forms.TextImageRelation.ImageBeforeText;
            this.startSynchronizingButton.Click += new System.EventHandler(this.startSynchronizingButton_Click);
            // 
            // groupBox7
            // 
            this.groupBox7.BackColor = System.Drawing.Color.Transparent;
            this.groupBox7.Controls.Add(this.syncingPictureBox);
            this.groupBox7.Controls.Add(this.panel1);
            this.groupBox7.Controls.Add(this.lastSyncedTimeLabel);
            this.groupBox7.Controls.Add(this.label12);
            this.groupBox7.Controls.Add(this.currentOperationLabel);
            this.groupBox7.Controls.Add(this.label10);
            this.groupBox7.Controls.Add(this.syncStatusProgressBar);
            this.groupBox7.Location = new System.Drawing.Point(9, 145);
            this.groupBox7.Name = "groupBox7";
            this.groupBox7.RightToLeft = System.Windows.Forms.RightToLeft.No;
            this.groupBox7.Size = new System.Drawing.Size(482, 237);
            this.groupBox7.TabIndex = 4;
            this.groupBox7.TabStop = false;
            this.groupBox7.Text = "Status";
            // 
            // syncingPictureBox
            // 
            this.syncingPictureBox.Enabled = false;
            this.syncingPictureBox.Location = new System.Drawing.Point(56, 60);
            this.syncingPictureBox.Name = "syncingPictureBox";
            this.syncingPictureBox.Size = new System.Drawing.Size(57, 50);
            this.syncingPictureBox.TabIndex = 5;
            this.syncingPictureBox.TabStop = false;
            // 
            // panel1
            // 
            this.panel1.BackColor = System.Drawing.Color.Transparent;
            this.panel1.Controls.Add(this.modifiedOutlookItemsLabel);
            this.panel1.Controls.Add(this.label24);
            this.panel1.Controls.Add(this.deletedOutlookItemsLabel);
            this.panel1.Controls.Add(this.label26);
            this.panel1.Controls.Add(this.newOutlookItemsLabel);
            this.panel1.Controls.Add(this.label28);
            this.panel1.Controls.Add(this.modifiedRemoteItemsLabel);
            this.panel1.Controls.Add(this.label18);
            this.panel1.Controls.Add(this.deletedRemoteItemsLabel);
            this.panel1.Controls.Add(this.label20);
            this.panel1.Controls.Add(this.newRemoteItemsLabel);
            this.panel1.Controls.Add(this.label22);
            this.panel1.Controls.Add(this.modReadOnlyItemsLabel);
            this.panel1.Controls.Add(this.label16);
            this.panel1.Controls.Add(this.conflictingItemsLabel);
            this.panel1.Controls.Add(this.label14);
            this.panel1.Controls.Add(this.syncedItemsLabel);
            this.panel1.Controls.Add(this.label11);
            this.panel1.ForeColor = System.Drawing.SystemColors.ActiveCaption;
            this.panel1.Location = new System.Drawing.Point(171, 51);
            this.panel1.Name = "panel1";
            this.panel1.Size = new System.Drawing.Size(293, 115);
            this.panel1.TabIndex = 26;
            // 
            // modifiedOutlookItemsLabel
            // 
            this.modifiedOutlookItemsLabel.AutoSize = true;
            this.modifiedOutlookItemsLabel.Font = new System.Drawing.Font("Microsoft Sans Serif", 8.25F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.modifiedOutlookItemsLabel.Location = new System.Drawing.Point(258, 94);
            this.modifiedOutlookItemsLabel.Margin = new System.Windows.Forms.Padding(1, 0, 3, 3);
            this.modifiedOutlookItemsLabel.Name = "modifiedOutlookItemsLabel";
            this.modifiedOutlookItemsLabel.RightToLeft = System.Windows.Forms.RightToLeft.No;
            this.modifiedOutlookItemsLabel.Size = new System.Drawing.Size(9, 13);
            this.modifiedOutlookItemsLabel.TabIndex = 25;
            this.modifiedOutlookItemsLabel.Text = "--";
            // 
            // label24
            // 
            this.label24.AutoSize = true;
            this.label24.Location = new System.Drawing.Point(155, 94);
            this.label24.Margin = new System.Windows.Forms.Padding(3, 0, 2, 3);
            this.label24.Name = "label24";
            this.label24.RightToLeft = System.Windows.Forms.RightToLeft.No;
            this.label24.Size = new System.Drawing.Size(86, 13);
            this.label24.TabIndex = 24;
            this.label24.Text = "Outlook Modified:";
            // 
            // deletedOutlookItemsLabel
            // 
            this.deletedOutlookItemsLabel.AutoSize = true;
            this.deletedOutlookItemsLabel.Font = new System.Drawing.Font("Microsoft Sans Serif", 8.25F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.deletedOutlookItemsLabel.Location = new System.Drawing.Point(258, 79);
            this.deletedOutlookItemsLabel.Margin = new System.Windows.Forms.Padding(1, 2, 3, 1);
            this.deletedOutlookItemsLabel.Name = "deletedOutlookItemsLabel";
            this.deletedOutlookItemsLabel.RightToLeft = System.Windows.Forms.RightToLeft.No;
            this.deletedOutlookItemsLabel.Size = new System.Drawing.Size(9, 13);
            this.deletedOutlookItemsLabel.TabIndex = 23;
            this.deletedOutlookItemsLabel.Text = "--";
            // 
            // label26
            // 
            this.label26.AutoSize = true;
            this.label26.Location = new System.Drawing.Point(155, 78);
            this.label26.Margin = new System.Windows.Forms.Padding(3, 1, 2, 1);
            this.label26.Name = "label26";
            this.label26.RightToLeft = System.Windows.Forms.RightToLeft.No;
            this.label26.Size = new System.Drawing.Size(83, 13);
            this.label26.TabIndex = 22;
            this.label26.Text = "Outlook Deleted:";
            // 
            // newOutlookItemsLabel
            // 
            this.newOutlookItemsLabel.AutoSize = true;
            this.newOutlookItemsLabel.Font = new System.Drawing.Font("Microsoft Sans Serif", 8.25F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.newOutlookItemsLabel.Location = new System.Drawing.Point(258, 61);
            this.newOutlookItemsLabel.Margin = new System.Windows.Forms.Padding(1, 1, 3, 2);
            this.newOutlookItemsLabel.Name = "newOutlookItemsLabel";
            this.newOutlookItemsLabel.RightToLeft = System.Windows.Forms.RightToLeft.No;
            this.newOutlookItemsLabel.Size = new System.Drawing.Size(9, 13);
            this.newOutlookItemsLabel.TabIndex = 21;
            this.newOutlookItemsLabel.Text = "--";
            // 
            // label28
            // 
            this.label28.AutoSize = true;
            this.label28.Location = new System.Drawing.Point(155, 61);
            this.label28.Margin = new System.Windows.Forms.Padding(3, 1, 2, 2);
            this.label28.Name = "label28";
            this.label28.RightToLeft = System.Windows.Forms.RightToLeft.No;
            this.label28.Size = new System.Drawing.Size(68, 13);
            this.label28.TabIndex = 20;
            this.label28.Text = "Outlook New:";
            // 
            // modifiedRemoteItemsLabel
            // 
            this.modifiedRemoteItemsLabel.AutoSize = true;
            this.modifiedRemoteItemsLabel.Font = new System.Drawing.Font("Microsoft Sans Serif", 8.25F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.modifiedRemoteItemsLabel.Location = new System.Drawing.Point(258, 34);
            this.modifiedRemoteItemsLabel.Margin = new System.Windows.Forms.Padding(1, 0, 3, 3);
            this.modifiedRemoteItemsLabel.Name = "modifiedRemoteItemsLabel";
            this.modifiedRemoteItemsLabel.RightToLeft = System.Windows.Forms.RightToLeft.No;
            this.modifiedRemoteItemsLabel.Size = new System.Drawing.Size(9, 13);
            this.modifiedRemoteItemsLabel.TabIndex = 19;
            this.modifiedRemoteItemsLabel.Text = "--";
            // 
            // label18
            // 
            this.label18.AutoSize = true;
            this.label18.Location = new System.Drawing.Point(155, 34);
            this.label18.Margin = new System.Windows.Forms.Padding(3, 0, 2, 3);
            this.label18.Name = "label18";
            this.label18.RightToLeft = System.Windows.Forms.RightToLeft.No;
            this.label18.Size = new System.Drawing.Size(86, 13);
            this.label18.TabIndex = 18;
            this.label18.Text = "Remote Modified:";
            // 
            // deletedRemoteItemsLabel
            // 
            this.deletedRemoteItemsLabel.AutoSize = true;
            this.deletedRemoteItemsLabel.Font = new System.Drawing.Font("Microsoft Sans Serif", 8.25F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.deletedRemoteItemsLabel.Location = new System.Drawing.Point(258, 18);
            this.deletedRemoteItemsLabel.Margin = new System.Windows.Forms.Padding(1, 0, 3, 1);
            this.deletedRemoteItemsLabel.Name = "deletedRemoteItemsLabel";
            this.deletedRemoteItemsLabel.RightToLeft = System.Windows.Forms.RightToLeft.No;
            this.deletedRemoteItemsLabel.Size = new System.Drawing.Size(9, 13);
            this.deletedRemoteItemsLabel.TabIndex = 17;
            this.deletedRemoteItemsLabel.Text = "--";
            // 
            // label20
            // 
            this.label20.AutoSize = true;
            this.label20.Location = new System.Drawing.Point(155, 18);
            this.label20.Margin = new System.Windows.Forms.Padding(3, 0, 2, 1);
            this.label20.Name = "label20";
            this.label20.RightToLeft = System.Windows.Forms.RightToLeft.No;
            this.label20.Size = new System.Drawing.Size(83, 13);
            this.label20.TabIndex = 16;
            this.label20.Text = "Remote Deleted:";
            // 
            // newRemoteItemsLabel
            // 
            this.newRemoteItemsLabel.AutoSize = true;
            this.newRemoteItemsLabel.Font = new System.Drawing.Font("Microsoft Sans Serif", 8.25F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.newRemoteItemsLabel.Location = new System.Drawing.Point(258, 3);
            this.newRemoteItemsLabel.Margin = new System.Windows.Forms.Padding(1, 1, 3, 1);
            this.newRemoteItemsLabel.Name = "newRemoteItemsLabel";
            this.newRemoteItemsLabel.RightToLeft = System.Windows.Forms.RightToLeft.No;
            this.newRemoteItemsLabel.Size = new System.Drawing.Size(9, 13);
            this.newRemoteItemsLabel.TabIndex = 15;
            this.newRemoteItemsLabel.Text = "--";
            // 
            // label22
            // 
            this.label22.AutoSize = true;
            this.label22.Location = new System.Drawing.Point(155, 3);
            this.label22.Margin = new System.Windows.Forms.Padding(3, 1, 2, 1);
            this.label22.Name = "label22";
            this.label22.RightToLeft = System.Windows.Forms.RightToLeft.No;
            this.label22.Size = new System.Drawing.Size(68, 13);
            this.label22.TabIndex = 14;
            this.label22.Text = "Remote New:";
            // 
            // modReadOnlyItemsLabel
            // 
            this.modReadOnlyItemsLabel.AutoSize = true;
            this.modReadOnlyItemsLabel.Font = new System.Drawing.Font("Microsoft Sans Serif", 8.25F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.modReadOnlyItemsLabel.Location = new System.Drawing.Point(117, 34);
            this.modReadOnlyItemsLabel.Margin = new System.Windows.Forms.Padding(1, 0, 3, 3);
            this.modReadOnlyItemsLabel.Name = "modReadOnlyItemsLabel";
            this.modReadOnlyItemsLabel.RightToLeft = System.Windows.Forms.RightToLeft.No;
            this.modReadOnlyItemsLabel.Size = new System.Drawing.Size(9, 13);
            this.modReadOnlyItemsLabel.TabIndex = 13;
            this.modReadOnlyItemsLabel.Text = "--";
            // 
            // label16
            // 
            this.label16.AutoSize = true;
            this.label16.Location = new System.Drawing.Point(9, 34);
            this.label16.Margin = new System.Windows.Forms.Padding(3, 0, 2, 3);
            this.label16.Name = "label16";
            this.label16.RightToLeft = System.Windows.Forms.RightToLeft.No;
            this.label16.Size = new System.Drawing.Size(85, 13);
            this.label16.TabIndex = 12;
            this.label16.Text = "Read-Only Mods:";
            // 
            // conflictingItemsLabel
            // 
            this.conflictingItemsLabel.AutoSize = true;
            this.conflictingItemsLabel.Font = new System.Drawing.Font("Microsoft Sans Serif", 8.25F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.conflictingItemsLabel.Location = new System.Drawing.Point(117, 18);
            this.conflictingItemsLabel.Margin = new System.Windows.Forms.Padding(1, 0, 3, 1);
            this.conflictingItemsLabel.Name = "conflictingItemsLabel";
            this.conflictingItemsLabel.RightToLeft = System.Windows.Forms.RightToLeft.No;
            this.conflictingItemsLabel.Size = new System.Drawing.Size(9, 13);
            this.conflictingItemsLabel.TabIndex = 11;
            this.conflictingItemsLabel.Text = "--";
            // 
            // label14
            // 
            this.label14.AutoSize = true;
            this.label14.Location = new System.Drawing.Point(9, 18);
            this.label14.Margin = new System.Windows.Forms.Padding(3, 0, 2, 1);
            this.label14.Name = "label14";
            this.label14.RightToLeft = System.Windows.Forms.RightToLeft.No;
            this.label14.Size = new System.Drawing.Size(46, 13);
            this.label14.TabIndex = 10;
            this.label14.Text = "Conflicts:";
            // 
            // syncedItemsLabel
            // 
            this.syncedItemsLabel.AutoSize = true;
            this.syncedItemsLabel.Font = new System.Drawing.Font("Microsoft Sans Serif", 8.25F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.syncedItemsLabel.Location = new System.Drawing.Point(117, 3);
            this.syncedItemsLabel.Margin = new System.Windows.Forms.Padding(1, 1, 3, 1);
            this.syncedItemsLabel.Name = "syncedItemsLabel";
            this.syncedItemsLabel.RightToLeft = System.Windows.Forms.RightToLeft.No;
            this.syncedItemsLabel.Size = new System.Drawing.Size(9, 13);
            this.syncedItemsLabel.TabIndex = 9;
            this.syncedItemsLabel.Text = "--";
            // 
            // label11
            // 
            this.label11.AutoSize = true;
            this.label11.Location = new System.Drawing.Point(9, 3);
            this.label11.Margin = new System.Windows.Forms.Padding(3, 1, 2, 1);
            this.label11.Name = "label11";
            this.label11.RightToLeft = System.Windows.Forms.RightToLeft.No;
            this.label11.Size = new System.Drawing.Size(31, 13);
            this.label11.TabIndex = 8;
            this.label11.Text = "Items:";
            // 
            // lastSyncedTimeLabel
            // 
            this.lastSyncedTimeLabel.AutoSize = true;
            this.lastSyncedTimeLabel.Font = new System.Drawing.Font("Microsoft Sans Serif", 8.25F, System.Drawing.FontStyle.Bold, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.lastSyncedTimeLabel.ForeColor = System.Drawing.SystemColors.ActiveCaption;
            this.lastSyncedTimeLabel.Location = new System.Drawing.Point(287, 22);
            this.lastSyncedTimeLabel.Margin = new System.Windows.Forms.Padding(1, 3, 3, 2);
            this.lastSyncedTimeLabel.Name = "lastSyncedTimeLabel";
            this.lastSyncedTimeLabel.RightToLeft = System.Windows.Forms.RightToLeft.No;
            this.lastSyncedTimeLabel.Size = new System.Drawing.Size(11, 13);
            this.lastSyncedTimeLabel.TabIndex = 7;
            this.lastSyncedTimeLabel.Text = "--";
            // 
            // label12
            // 
            this.label12.AutoSize = true;
            this.label12.Font = new System.Drawing.Font("Microsoft Sans Serif", 8.25F, System.Drawing.FontStyle.Bold, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.label12.ForeColor = System.Drawing.SystemColors.ActiveCaption;
            this.label12.Location = new System.Drawing.Point(179, 22);
            this.label12.Margin = new System.Windows.Forms.Padding(3, 3, 2, 2);
            this.label12.Name = "label12";
            this.label12.RightToLeft = System.Windows.Forms.RightToLeft.No;
            this.label12.Size = new System.Drawing.Size(111, 13);
            this.label12.TabIndex = 6;
            this.label12.Text = "Last Synchronized:";
            // 
            // currentOperationLabel
            // 
            this.currentOperationLabel.AutoSize = true;
            this.currentOperationLabel.Font = new System.Drawing.Font("Microsoft Sans Serif", 8.25F, System.Drawing.FontStyle.Bold, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.currentOperationLabel.Location = new System.Drawing.Point(118, 214);
            this.currentOperationLabel.Name = "currentOperationLabel";
            this.currentOperationLabel.RightToLeft = System.Windows.Forms.RightToLeft.No;
            this.currentOperationLabel.Size = new System.Drawing.Size(33, 13);
            this.currentOperationLabel.TabIndex = 3;
            this.currentOperationLabel.Text = "Done";
            // 
            // label10
            // 
            this.label10.AutoSize = true;
            this.label10.Location = new System.Drawing.Point(13, 215);
            this.label10.Name = "label10";
            this.label10.RightToLeft = System.Windows.Forms.RightToLeft.No;
            this.label10.Size = new System.Drawing.Size(89, 13);
            this.label10.TabIndex = 2;
            this.label10.Text = "Current Operation:";
            // 
            // syncStatusProgressBar
            // 
            this.syncStatusProgressBar.Location = new System.Drawing.Point(13, 177);
            this.syncStatusProgressBar.Name = "syncStatusProgressBar";
            this.syncStatusProgressBar.Size = new System.Drawing.Size(451, 30);
            this.syncStatusProgressBar.TabIndex = 1;
            this.syncStatusProgressBar.Value = 100;
            // 
            // settingsPage
            // 
            this.settingsPage.Controls.Add(this.groupBox1);
            this.settingsPage.Controls.Add(this.applySettingsButton);
            this.settingsPage.Controls.Add(this.cancelSettingsButton);
            this.settingsPage.Controls.Add(this.groupBox4);
            this.settingsPage.Controls.Add(this.groupBox5);
            this.settingsPage.Controls.Add(this.groupBox2);
            this.settingsPage.Location = new System.Drawing.Point(4, 22);
            this.settingsPage.Name = "settingsPage";
            this.settingsPage.Padding = new System.Windows.Forms.Padding(3);
            this.settingsPage.Size = new System.Drawing.Size(499, 395);
            this.settingsPage.TabIndex = 2;
            this.settingsPage.Text = "Settings";
            // 
            // groupBox1
            // 
            this.groupBox1.BackColor = System.Drawing.Color.Transparent;
            this.groupBox1.Controls.Add(this.tasksCheckBox);
            this.groupBox1.Controls.Add(this.appointmentsCheckBox);
            this.groupBox1.Controls.Add(this.contactsCheckBox);
            this.groupBox1.Location = new System.Drawing.Point(7, 10);
            this.groupBox1.Name = "groupBox1";
            this.groupBox1.Size = new System.Drawing.Size(481, 49);
            this.groupBox1.TabIndex = 17;
            this.groupBox1.TabStop = false;
            this.groupBox1.Text = "To Synchronize:";
            // 
            // tasksCheckBox
            // 
            this.tasksCheckBox.AutoSize = true;
            this.tasksCheckBox.Checked = true;
            this.tasksCheckBox.CheckState = System.Windows.Forms.CheckState.Checked;
            this.tasksCheckBox.Location = new System.Drawing.Point(399, 20);
            this.tasksCheckBox.Name = "tasksCheckBox";
            this.tasksCheckBox.Size = new System.Drawing.Size(51, 17);
            this.tasksCheckBox.TabIndex = 2;
            this.tasksCheckBox.Text = "Tasks";
            // 
            // appointmentsCheckBox
            // 
            this.appointmentsCheckBox.AutoSize = true;
            this.appointmentsCheckBox.Checked = true;
            this.appointmentsCheckBox.CheckState = System.Windows.Forms.CheckState.Checked;
            this.appointmentsCheckBox.Location = new System.Drawing.Point(289, 20);
            this.appointmentsCheckBox.Name = "appointmentsCheckBox";
            this.appointmentsCheckBox.Size = new System.Drawing.Size(86, 17);
            this.appointmentsCheckBox.TabIndex = 1;
            this.appointmentsCheckBox.Text = "Appointments";
            // 
            // contactsCheckBox
            // 
            this.contactsCheckBox.AutoSize = true;
            this.contactsCheckBox.Checked = true;
            this.contactsCheckBox.CheckState = System.Windows.Forms.CheckState.Checked;
            this.contactsCheckBox.Location = new System.Drawing.Point(181, 20);
            this.contactsCheckBox.Name = "contactsCheckBox";
            this.contactsCheckBox.Size = new System.Drawing.Size(64, 17);
            this.contactsCheckBox.TabIndex = 0;
            this.contactsCheckBox.Text = "Contacts";
            // 
            // applySettingsButton
            // 
            this.applySettingsButton.Location = new System.Drawing.Point(415, 363);
            this.applySettingsButton.Margin = new System.Windows.Forms.Padding(3, 1, 3, 3);
            this.applySettingsButton.Name = "applySettingsButton";
            this.applySettingsButton.Size = new System.Drawing.Size(75, 23);
            this.applySettingsButton.TabIndex = 16;
            this.applySettingsButton.Text = "Apply";
            this.applySettingsButton.Click += new System.EventHandler(this.applySettingsButton_Click);
            // 
            // cancelSettingsButton
            // 
            this.cancelSettingsButton.Location = new System.Drawing.Point(333, 363);
            this.cancelSettingsButton.Margin = new System.Windows.Forms.Padding(3, 1, 3, 3);
            this.cancelSettingsButton.Name = "cancelSettingsButton";
            this.cancelSettingsButton.Size = new System.Drawing.Size(75, 23);
            this.cancelSettingsButton.TabIndex = 15;
            this.cancelSettingsButton.Text = "Cancel";
            this.cancelSettingsButton.Click += new System.EventHandler(this.cancelSettingsButton_Click);
            // 
            // groupBox4
            // 
            this.groupBox4.BackColor = System.Drawing.Color.Transparent;
            this.groupBox4.Controls.Add(this.label8);
            this.groupBox4.Controls.Add(this.syncIntervallUpDown);
            this.groupBox4.Location = new System.Drawing.Point(10, 308);
            this.groupBox4.Margin = new System.Windows.Forms.Padding(3, 0, 3, 1);
            this.groupBox4.Name = "groupBox4";
            this.groupBox4.Size = new System.Drawing.Size(480, 53);
            this.groupBox4.TabIndex = 14;
            this.groupBox4.TabStop = false;
            this.groupBox4.Text = "Auto-Synchronization Settings:";
            // 
            // label8
            // 
            this.label8.AutoSize = true;
            this.label8.Location = new System.Drawing.Point(16, 23);
            this.label8.Name = "label8";
            this.label8.Size = new System.Drawing.Size(87, 13);
            this.label8.TabIndex = 3;
            this.label8.Text = "Interval (Minutes):";
            // 
            // syncIntervallUpDown
            // 
            this.syncIntervallUpDown.Location = new System.Drawing.Point(178, 23);
            this.syncIntervallUpDown.Maximum = new decimal(new int[] {
            60,
            0,
            0,
            0});
            this.syncIntervallUpDown.Minimum = new decimal(new int[] {
            1,
            0,
            0,
            0});
            this.syncIntervallUpDown.Name = "syncIntervallUpDown";
            this.syncIntervallUpDown.Size = new System.Drawing.Size(55, 20);
            this.syncIntervallUpDown.TabIndex = 2;
            this.syncIntervallUpDown.Value = new decimal(new int[] {
            2,
            0,
            0,
            0});
            // 
            // groupBox5
            // 
            this.groupBox5.BackColor = System.Drawing.Color.Transparent;
            this.groupBox5.Controls.Add(this.localDirectoryBox);
            this.groupBox5.Controls.Add(this.label6);
            this.groupBox5.Location = new System.Drawing.Point(9, 201);
            this.groupBox5.Margin = new System.Windows.Forms.Padding(3, 1, 3, 3);
            this.groupBox5.Name = "groupBox5";
            this.groupBox5.Size = new System.Drawing.Size(480, 103);
            this.groupBox5.TabIndex = 8;
            this.groupBox5.TabStop = false;
            this.groupBox5.Text = "Local Settings:";
            // 
            // localDirectoryBox
            // 
            this.localDirectoryBox.Location = new System.Drawing.Point(179, 34);
            this.localDirectoryBox.Margin = new System.Windows.Forms.Padding(3, 3, 3, 1);
            this.localDirectoryBox.Name = "localDirectoryBox";
            this.localDirectoryBox.Size = new System.Drawing.Size(270, 20);
            this.localDirectoryBox.TabIndex = 8;
            // 
            // label6
            // 
            this.label6.AutoSize = true;
            this.label6.BackColor = System.Drawing.Color.Transparent;
            this.label6.Location = new System.Drawing.Point(18, 34);
            this.label6.Name = "label6";
            this.label6.Size = new System.Drawing.Size(82, 13);
            this.label6.TabIndex = 9;
            this.label6.Text = "Cache Directory:";
            // 
            // groupBox2
            // 
            this.groupBox2.BackColor = System.Drawing.Color.Transparent;
            this.groupBox2.Controls.Add(this.label1);
            this.groupBox2.Controls.Add(this.domainBox);
            this.groupBox2.Controls.Add(this.label4);
            this.groupBox2.Controls.Add(this.label3);
            this.groupBox2.Controls.Add(this.label2);
            this.groupBox2.Controls.Add(this.passwordBox);
            this.groupBox2.Controls.Add(this.userBox);
            this.groupBox2.Controls.Add(this.urlBox);
            this.groupBox2.Location = new System.Drawing.Point(8, 63);
            this.groupBox2.Margin = new System.Windows.Forms.Padding(3, 3, 3, 2);
            this.groupBox2.Name = "groupBox2";
            this.groupBox2.Size = new System.Drawing.Size(480, 136);
            this.groupBox2.TabIndex = 6;
            this.groupBox2.TabStop = false;
            this.groupBox2.Text = "Server Settings:";
            // 
            // label1
            // 
            this.label1.AutoSize = true;
            this.label1.BackColor = System.Drawing.Color.Transparent;
            this.label1.Location = new System.Drawing.Point(18, 49);
            this.label1.Name = "label1";
            this.label1.Size = new System.Drawing.Size(42, 13);
            this.label1.TabIndex = 7;
            this.label1.Text = "Domain:";
            // 
            // domainBox
            // 
            this.domainBox.Location = new System.Drawing.Point(179, 49);
            this.domainBox.Name = "domainBox";
            this.domainBox.Size = new System.Drawing.Size(270, 20);
            this.domainBox.TabIndex = 6;
            // 
            // label4
            // 
            this.label4.AutoSize = true;
            this.label4.BackColor = System.Drawing.Color.Transparent;
            this.label4.Location = new System.Drawing.Point(18, 103);
            this.label4.Name = "label4";
            this.label4.Size = new System.Drawing.Size(52, 13);
            this.label4.TabIndex = 5;
            this.label4.Text = "Password:";
            // 
            // label3
            // 
            this.label3.AutoSize = true;
            this.label3.BackColor = System.Drawing.Color.Transparent;
            this.label3.Location = new System.Drawing.Point(18, 76);
            this.label3.Name = "label3";
            this.label3.Size = new System.Drawing.Size(59, 13);
            this.label3.TabIndex = 4;
            this.label3.Text = "User Name:";
            // 
            // label2
            // 
            this.label2.AutoSize = true;
            this.label2.BackColor = System.Drawing.Color.Transparent;
            this.label2.Location = new System.Drawing.Point(18, 22);
            this.label2.Name = "label2";
            this.label2.Size = new System.Drawing.Size(19, 13);
            this.label2.TabIndex = 3;
            this.label2.Text = "Url:";
            // 
            // passwordBox
            // 
            this.passwordBox.Location = new System.Drawing.Point(179, 103);
            this.passwordBox.Name = "passwordBox";
            this.passwordBox.Size = new System.Drawing.Size(270, 20);
            this.passwordBox.TabIndex = 2;
            // 
            // userBox
            // 
            this.userBox.Location = new System.Drawing.Point(179, 76);
            this.userBox.Name = "userBox";
            this.userBox.Size = new System.Drawing.Size(270, 20);
            this.userBox.TabIndex = 1;
            // 
            // urlBox
            // 
            this.urlBox.Location = new System.Drawing.Point(179, 22);
            this.urlBox.Name = "urlBox";
            this.urlBox.Size = new System.Drawing.Size(270, 20);
            this.urlBox.TabIndex = 0;
            // 
            // logPage
            // 
            this.logPage.Controls.Add(this.groupBox3);
            this.logPage.Location = new System.Drawing.Point(4, 22);
            this.logPage.Name = "logPage";
            this.logPage.Padding = new System.Windows.Forms.Padding(3);
            this.logPage.Size = new System.Drawing.Size(499, 395);
            this.logPage.TabIndex = 1;
            this.logPage.Text = "Message Log";
            // 
            // groupBox3
            // 
            this.groupBox3.BackColor = System.Drawing.Color.Transparent;
            this.groupBox3.Controls.Add(this.messageLogGridView);
            this.groupBox3.Controls.Add(this.clearButton);
            this.groupBox3.Controls.Add(this.refreshButton);
            this.groupBox3.Dock = System.Windows.Forms.DockStyle.Fill;
            this.groupBox3.Location = new System.Drawing.Point(3, 3);
            this.groupBox3.Name = "groupBox3";
            this.groupBox3.Size = new System.Drawing.Size(493, 389);
            this.groupBox3.TabIndex = 3;
            this.groupBox3.TabStop = false;
            this.groupBox3.Text = "Message Log:";
            // 
            // messageLogGridView
            // 
            this.messageLogGridView.AllowUserToAddRows = false;
            this.messageLogGridView.AllowUserToDeleteRows = false;
            this.messageLogGridView.AllowUserToOrderColumns = true;
            this.messageLogGridView.AutoSizeColumnsMode = System.Windows.Forms.DataGridViewAutoSizeColumnsMode.Fill;
            this.messageLogGridView.BackgroundColor = System.Drawing.Color.White;
            this.messageLogGridView.Columns.Add(this.dataGridViewImageColumn1);
            this.messageLogGridView.Columns.Add(this.dataGridViewTextBoxColumn1);
            this.messageLogGridView.Columns.Add(this.dataGridViewTextBoxColumn2);
            this.messageLogGridView.EditMode = System.Windows.Forms.DataGridViewEditMode.EditProgrammatically;
            this.messageLogGridView.GridColor = System.Drawing.SystemColors.ControlLight;
            this.messageLogGridView.Location = new System.Drawing.Point(19, 21);
            this.messageLogGridView.MultiSelect = false;
            this.messageLogGridView.Name = "messageLogGridView";
            this.messageLogGridView.ReadOnly = true;
            this.messageLogGridView.RowHeadersVisible = false;
            this.messageLogGridView.RowHeadersWidthSizeMode = System.Windows.Forms.DataGridViewRowHeadersWidthSizeMode.AutoSizeToDisplayedHeaders;
            this.messageLogGridView.SelectionMode = System.Windows.Forms.DataGridViewSelectionMode.FullRowSelect;
            this.messageLogGridView.Size = new System.Drawing.Size(468, 329);
            this.messageLogGridView.TabIndex = 3;
            // 
            // clearButton
            // 
            this.clearButton.Location = new System.Drawing.Point(323, 356);
            this.clearButton.Name = "clearButton";
            this.clearButton.Size = new System.Drawing.Size(75, 23);
            this.clearButton.TabIndex = 2;
            this.clearButton.Text = "Clear";
            this.clearButton.Click += new System.EventHandler(this.clearLogButton_Click);
            // 
            // refreshButton
            // 
            this.refreshButton.Location = new System.Drawing.Point(405, 356);
            this.refreshButton.Name = "refreshButton";
            this.refreshButton.Size = new System.Drawing.Size(75, 23);
            this.refreshButton.TabIndex = 1;
            this.refreshButton.Text = "Refresh";
            this.refreshButton.Click += new System.EventHandler(this.refreshLogButton_Click);
            // 
            // expertPage
            // 
            this.expertPage.Controls.Add(this.resetsGroupBox);
            this.expertPage.Controls.Add(this.expertLockCheckBox);
            this.expertPage.Controls.Add(this.expertControlsGroupBox);
            this.expertPage.Location = new System.Drawing.Point(4, 22);
            this.expertPage.Name = "expertPage";
            this.expertPage.Padding = new System.Windows.Forms.Padding(3);
            this.expertPage.Size = new System.Drawing.Size(499, 395);
            this.expertPage.TabIndex = 3;
            this.expertPage.Text = "Expert Controls";
            // 
            // resetsGroupBox
            // 
            this.resetsGroupBox.BackColor = System.Drawing.Color.Transparent;
            this.resetsGroupBox.Controls.Add(this.resetSyncStateButton);
            this.resetsGroupBox.Enabled = false;
            this.resetsGroupBox.Location = new System.Drawing.Point(214, 47);
            this.resetsGroupBox.Name = "resetsGroupBox";
            this.resetsGroupBox.Size = new System.Drawing.Size(131, 339);
            this.resetsGroupBox.TabIndex = 3;
            this.resetsGroupBox.TabStop = false;
            this.resetsGroupBox.Text = "Resets";
            // 
            // resetSyncStateButton
            // 
            this.resetSyncStateButton.Location = new System.Drawing.Point(7, 51);
            this.resetSyncStateButton.Name = "resetSyncStateButton";
            this.resetSyncStateButton.Size = new System.Drawing.Size(118, 53);
            this.resetSyncStateButton.TabIndex = 3;
            this.resetSyncStateButton.Text = "Reset Synchronization State";
            this.resetSyncStateButton.Click += new System.EventHandler(this.resetSyncStateButton_Click);
            // 
            // expertLockCheckBox
            // 
            this.expertLockCheckBox.AutoSize = true;
            this.expertLockCheckBox.BackColor = System.Drawing.Color.Transparent;
            this.expertLockCheckBox.Checked = true;
            this.expertLockCheckBox.CheckState = System.Windows.Forms.CheckState.Checked;
            this.expertLockCheckBox.Location = new System.Drawing.Point(352, 19);
            this.expertLockCheckBox.Name = "expertLockCheckBox";
            this.expertLockCheckBox.Size = new System.Drawing.Size(87, 17);
            this.expertLockCheckBox.TabIndex = 2;
            this.expertLockCheckBox.Text = "Lock Controls";
            this.expertLockCheckBox.UseVisualStyleBackColor = false;
            this.expertLockCheckBox.CheckedChanged += new System.EventHandler(this.expertLockCheckBox_CheckedChanged);
            // 
            // expertControlsGroupBox
            // 
            this.expertControlsGroupBox.BackColor = System.Drawing.Color.Transparent;
            this.expertControlsGroupBox.Controls.Add(this.disconnectButton);
            this.expertControlsGroupBox.Controls.Add(this.connectButton);
            this.expertControlsGroupBox.Controls.Add(this.saveButton);
            this.expertControlsGroupBox.Controls.Add(this.loadButton);
            this.expertControlsGroupBox.Controls.Add(this.syncButton);
            this.expertControlsGroupBox.Enabled = false;
            this.expertControlsGroupBox.Location = new System.Drawing.Point(352, 47);
            this.expertControlsGroupBox.Name = "expertControlsGroupBox";
            this.expertControlsGroupBox.Size = new System.Drawing.Size(131, 339);
            this.expertControlsGroupBox.TabIndex = 1;
            this.expertControlsGroupBox.TabStop = false;
            this.expertControlsGroupBox.Text = "Sync-Steps";
            // 
            // disconnectButton
            // 
            this.disconnectButton.Location = new System.Drawing.Point(7, 171);
            this.disconnectButton.Name = "disconnectButton";
            this.disconnectButton.Size = new System.Drawing.Size(113, 23);
            this.disconnectButton.TabIndex = 4;
            this.disconnectButton.Text = "Disconnect";
            this.disconnectButton.Click += new System.EventHandler(this.disconnectButton_Click);
            // 
            // connectButton
            // 
            this.connectButton.Location = new System.Drawing.Point(7, 51);
            this.connectButton.Name = "connectButton";
            this.connectButton.Size = new System.Drawing.Size(113, 23);
            this.connectButton.TabIndex = 3;
            this.connectButton.Text = "Connect";
            this.connectButton.Click += new System.EventHandler(this.connectButton_Click);
            // 
            // saveButton
            // 
            this.saveButton.Location = new System.Drawing.Point(7, 141);
            this.saveButton.Name = "saveButton";
            this.saveButton.Size = new System.Drawing.Size(113, 23);
            this.saveButton.TabIndex = 2;
            this.saveButton.Text = "Save";
            this.saveButton.Click += new System.EventHandler(this.saveButton_Click);
            // 
            // loadButton
            // 
            this.loadButton.Location = new System.Drawing.Point(7, 81);
            this.loadButton.Name = "loadButton";
            this.loadButton.Size = new System.Drawing.Size(113, 23);
            this.loadButton.TabIndex = 1;
            this.loadButton.Text = "Load";
            this.loadButton.Click += new System.EventHandler(this.loadButton_Click);
            // 
            // syncButton
            // 
            this.syncButton.Location = new System.Drawing.Point(7, 111);
            this.syncButton.Name = "syncButton";
            this.syncButton.Size = new System.Drawing.Size(113, 23);
            this.syncButton.TabIndex = 0;
            this.syncButton.Text = "Synchronise";
            this.syncButton.Click += new System.EventHandler(this.syncButton_Click);
            // 
            // infoPage
            // 
            this.infoPage.Controls.Add(this.aboutRichTextBox);
            this.infoPage.Location = new System.Drawing.Point(4, 22);
            this.infoPage.Name = "infoPage";
            this.infoPage.Padding = new System.Windows.Forms.Padding(3);
            this.infoPage.Size = new System.Drawing.Size(499, 395);
            this.infoPage.TabIndex = 4;
            this.infoPage.Text = "About";
            // 
            // aboutRichTextBox
            // 
            this.aboutRichTextBox.Location = new System.Drawing.Point(10, 14);
            this.aboutRichTextBox.Name = "aboutRichTextBox";
            this.aboutRichTextBox.ReadOnly = true;
            this.aboutRichTextBox.Size = new System.Drawing.Size(478, 368);
            this.aboutRichTextBox.TabIndex = 0;
            this.aboutRichTextBox.Text = "";
            // 
            // dataGridViewImageColumn1
            // 
            this.dataGridViewImageColumn1.AutoSizeMode = System.Windows.Forms.DataGridViewAutoSizeColumnMode.None;
            dataGridViewCellStyle1.Alignment = System.Windows.Forms.DataGridViewContentAlignment.MiddleCenter;
            dataGridViewCellStyle1.FormatProvider = new System.Globalization.CultureInfo("de-DE");
            this.dataGridViewImageColumn1.DefaultCellStyle = dataGridViewCellStyle1;
            this.dataGridViewImageColumn1.FillWeight = 1F;
            this.dataGridViewImageColumn1.HeaderText = "Type";
            this.dataGridViewImageColumn1.Name = "MessageTypeColumn";
            this.dataGridViewImageColumn1.ReadOnly = true;
            this.dataGridViewImageColumn1.Resizable = System.Windows.Forms.DataGridViewTriState.True;
            this.dataGridViewImageColumn1.Width = 155;
            // 
            // dataGridViewTextBoxColumn1
            // 
            this.dataGridViewTextBoxColumn1.AutoSizeMode = System.Windows.Forms.DataGridViewAutoSizeColumnMode.None;
            this.dataGridViewTextBoxColumn1.FillWeight = 1F;
            this.dataGridViewTextBoxColumn1.HeaderText = "Time";
            this.dataGridViewTextBoxColumn1.Name = "MessageTimeColumn";
            this.dataGridViewTextBoxColumn1.ReadOnly = true;
            this.dataGridViewTextBoxColumn1.Resizable = System.Windows.Forms.DataGridViewTriState.True;
            this.dataGridViewTextBoxColumn1.Width = 155;
            // 
            // dataGridViewTextBoxColumn2
            // 
            this.dataGridViewTextBoxColumn2.AutoSizeMode = System.Windows.Forms.DataGridViewAutoSizeColumnMode.Fill;
            this.dataGridViewTextBoxColumn2.HeaderText = "Message";
            this.dataGridViewTextBoxColumn2.Name = "MessageTextColumn";
            this.dataGridViewTextBoxColumn2.ReadOnly = true;
            this.dataGridViewTextBoxColumn2.Resizable = System.Windows.Forms.DataGridViewTriState.True;
            // 
            // ControlForm
            // 
            this.ClientSize = new System.Drawing.Size(532, 446);
            this.Controls.Add(this.tabControl1);
            this.MaximizeBox = false;
            this.MaximumSize = new System.Drawing.Size(540, 480);
            this.MinimumSize = new System.Drawing.Size(540, 480);
            this.Name = "ControlForm";
            this.SizeGripStyle = System.Windows.Forms.SizeGripStyle.Hide;
            this.Text = "Outlook-EGW Synchronizer (Preview)";
            this.FormClosing += new System.Windows.Forms.FormClosingEventHandler(this.ControleForm_FormClosing);
            this.tabControl1.ResumeLayout(false);
            this.mainPage.ResumeLayout(false);
            this.groupBox9.ResumeLayout(false);
            this.groupBox8.ResumeLayout(false);
            this.groupBox7.ResumeLayout(false);
            this.groupBox7.PerformLayout();
            ((System.ComponentModel.ISupportInitialize)(this.syncingPictureBox)).EndInit();
            this.panel1.ResumeLayout(false);
            this.panel1.PerformLayout();
            this.settingsPage.ResumeLayout(false);
            this.groupBox1.ResumeLayout(false);
            this.groupBox1.PerformLayout();
            this.groupBox4.ResumeLayout(false);
            this.groupBox4.PerformLayout();
            ((System.ComponentModel.ISupportInitialize)(this.syncIntervallUpDown)).EndInit();
            this.groupBox5.ResumeLayout(false);
            this.groupBox5.PerformLayout();
            this.groupBox2.ResumeLayout(false);
            this.groupBox2.PerformLayout();
            this.logPage.ResumeLayout(false);
            this.groupBox3.ResumeLayout(false);
            ((System.ComponentModel.ISupportInitialize)(this.messageLogGridView)).EndInit();
            this.expertPage.ResumeLayout(false);
            this.expertPage.PerformLayout();
            this.resetsGroupBox.ResumeLayout(false);
            this.expertControlsGroupBox.ResumeLayout(false);
            this.infoPage.ResumeLayout(false);
            this.ResumeLayout(false);

        }

        #endregion

        private System.Windows.Forms.TabControl tabControl1;
        private System.Windows.Forms.TabPage mainPage;
        private System.Windows.Forms.TabPage logPage;
        private System.Windows.Forms.Button refreshButton;
        private System.Windows.Forms.Button clearButton;
        private System.Windows.Forms.TabPage settingsPage;
        private System.Windows.Forms.TextBox urlBox;
        private System.Windows.Forms.TextBox userBox;
        private System.Windows.Forms.TextBox passwordBox;
        private System.Windows.Forms.Label label2;
        private System.Windows.Forms.Label label3;
        private System.Windows.Forms.Label label4;
        private System.Windows.Forms.GroupBox groupBox2;
        private System.Windows.Forms.GroupBox groupBox3;
        private System.Windows.Forms.Label label1;
        private System.Windows.Forms.TextBox domainBox;
        private System.Windows.Forms.GroupBox groupBox5;
        private System.Windows.Forms.GroupBox expertControlsGroupBox;
        private System.Windows.Forms.Button disconnectButton;
        private System.Windows.Forms.Button connectButton;
        private System.Windows.Forms.Button saveButton;
        private System.Windows.Forms.Button loadButton;
        private System.Windows.Forms.Button syncButton;
        private System.Windows.Forms.Label label6;
        private System.Windows.Forms.TextBox localDirectoryBox;
        private System.Windows.Forms.GroupBox groupBox4;
        private System.Windows.Forms.NumericUpDown syncIntervallUpDown;
        private System.Windows.Forms.Label label8;
        private System.Windows.Forms.TabPage expertPage;
        private System.Windows.Forms.Button startSynchronizingButton;
        private System.Windows.Forms.GroupBox groupBox7;
        private System.Windows.Forms.Button stopSynchronizingButton;
        private System.Windows.Forms.Button manualSynchronizeButton;
        private System.Windows.Forms.GroupBox groupBox8;
        private System.Windows.Forms.GroupBox groupBox9;
        private System.Windows.Forms.TabPage infoPage;
        private System.Windows.Forms.Button applySettingsButton;
        private System.Windows.Forms.Button cancelSettingsButton;
        private System.Windows.Forms.RichTextBox aboutRichTextBox;
        private System.Windows.Forms.Label currentOperationLabel;
        private System.Windows.Forms.Label label10;
        private System.Windows.Forms.ProgressBar syncStatusProgressBar;
        private System.Windows.Forms.Label lastSyncedTimeLabel;
        private System.Windows.Forms.Label label12;
        private System.Windows.Forms.CheckBox expertLockCheckBox;
        private System.Windows.Forms.GroupBox resetsGroupBox;
        private System.Windows.Forms.Button resetSyncStateButton;
        private System.Windows.Forms.Label modReadOnlyItemsLabel;
        private System.Windows.Forms.Label label16;
        private System.Windows.Forms.Label conflictingItemsLabel;
        private System.Windows.Forms.Label label14;
        private System.Windows.Forms.Label syncedItemsLabel;
        private System.Windows.Forms.Label label11;
        private System.Windows.Forms.Label modifiedOutlookItemsLabel;
        private System.Windows.Forms.Label label24;
        private System.Windows.Forms.Label deletedOutlookItemsLabel;
        private System.Windows.Forms.Label label26;
        private System.Windows.Forms.Label newOutlookItemsLabel;
        private System.Windows.Forms.Label label28;
        private System.Windows.Forms.Label modifiedRemoteItemsLabel;
        private System.Windows.Forms.Label label18;
        private System.Windows.Forms.Label deletedRemoteItemsLabel;
        private System.Windows.Forms.Label label20;
        private System.Windows.Forms.Label newRemoteItemsLabel;
        private System.Windows.Forms.Label label22;
        private System.Windows.Forms.Panel panel1;
        private System.Windows.Forms.PictureBox syncingPictureBox;
        private System.Windows.Forms.DataGridView messageLogGridView;
        private System.Windows.Forms.GroupBox groupBox1;
        private System.Windows.Forms.CheckBox tasksCheckBox;
        private System.Windows.Forms.CheckBox appointmentsCheckBox;
        private System.Windows.Forms.CheckBox contactsCheckBox;
        private System.Windows.Forms.DataGridViewImageColumn dataGridViewImageColumn1;
        private System.Windows.Forms.DataGridViewTextBoxColumn dataGridViewTextBoxColumn1;
        private System.Windows.Forms.DataGridViewTextBoxColumn dataGridViewTextBoxColumn2;
    }
}