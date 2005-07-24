namespace cUnnectorOutlookAddIn
{
    partial class StatusDialog
    {
        /// <summary>
        /// Required designer variable.
        /// </summary>
        private System.ComponentModel.IContainer components = null;

        /// <summary>
        /// Clean up any resources being used.
        /// </summary>
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
            System.ComponentModel.ComponentResourceManager resources = new System.ComponentModel.ComponentResourceManager(typeof(StatusDialog));
            System.Windows.Forms.DataGridViewCellStyle dataGridViewCellStyle2 = new System.Windows.Forms.DataGridViewCellStyle();
            this.okButton = new System.Windows.Forms.Button();
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
            this.tabControl1 = new System.Windows.Forms.TabControl();
            this.tabPage1 = new System.Windows.Forms.TabPage();
            this.tabPage2 = new System.Windows.Forms.TabPage();
            this.messageLogGridView = new System.Windows.Forms.DataGridView();
            this.dataGridViewImageColumn1 = new System.Windows.Forms.DataGridViewImageColumn();
            this.dataGridViewTextBoxColumn1 = new System.Windows.Forms.DataGridViewTextBoxColumn();
            this.dataGridViewTextBoxColumn2 = new System.Windows.Forms.DataGridViewTextBoxColumn();
            this.clearButton = new System.Windows.Forms.Button();
            this.refreshButton = new System.Windows.Forms.Button();
            this.groupBox7.SuspendLayout();
            ((System.ComponentModel.ISupportInitialize)(this.syncingPictureBox)).BeginInit();
            this.panel1.SuspendLayout();
            this.tabControl1.SuspendLayout();
            this.tabPage1.SuspendLayout();
            this.tabPage2.SuspendLayout();
            ((System.ComponentModel.ISupportInitialize)(this.messageLogGridView)).BeginInit();
            this.SuspendLayout();
// 
// okButton
// 
            this.okButton.DialogResult = System.Windows.Forms.DialogResult.OK;
            this.okButton.Location = new System.Drawing.Point(467, 320);
            this.okButton.Name = "okButton";
            this.okButton.TabIndex = 0;
            this.okButton.Text = "OK";
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
            this.groupBox7.Location = new System.Drawing.Point(18, 18);
            this.groupBox7.Name = "groupBox7";
            this.groupBox7.RightToLeft = System.Windows.Forms.RightToLeft.No;
            this.groupBox7.Size = new System.Drawing.Size(482, 237);
            this.groupBox7.TabIndex = 5;
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
            this.modifiedOutlookItemsLabel.Size = new System.Drawing.Size(12, 14);
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
            this.label24.Size = new System.Drawing.Size(92, 14);
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
            this.deletedOutlookItemsLabel.Size = new System.Drawing.Size(12, 14);
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
            this.label26.Size = new System.Drawing.Size(89, 14);
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
            this.newOutlookItemsLabel.Size = new System.Drawing.Size(12, 14);
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
            this.label28.Size = new System.Drawing.Size(72, 14);
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
            this.modifiedRemoteItemsLabel.Size = new System.Drawing.Size(12, 14);
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
            this.label18.Size = new System.Drawing.Size(93, 14);
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
            this.deletedRemoteItemsLabel.Size = new System.Drawing.Size(12, 14);
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
            this.label20.Size = new System.Drawing.Size(89, 14);
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
            this.newRemoteItemsLabel.Size = new System.Drawing.Size(12, 14);
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
            this.label22.Size = new System.Drawing.Size(73, 14);
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
            this.modReadOnlyItemsLabel.Size = new System.Drawing.Size(12, 14);
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
            this.label16.Size = new System.Drawing.Size(92, 14);
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
            this.conflictingItemsLabel.Size = new System.Drawing.Size(12, 14);
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
            this.label14.Size = new System.Drawing.Size(51, 14);
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
            this.syncedItemsLabel.Size = new System.Drawing.Size(12, 14);
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
            this.label11.Size = new System.Drawing.Size(35, 14);
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
            this.lastSyncedTimeLabel.Size = new System.Drawing.Size(12, 14);
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
            this.label12.Size = new System.Drawing.Size(104, 14);
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
            this.currentOperationLabel.Size = new System.Drawing.Size(32, 14);
            this.currentOperationLabel.TabIndex = 3;
            this.currentOperationLabel.Text = "Done";
// 
// label10
// 
            this.label10.AutoSize = true;
            this.label10.Location = new System.Drawing.Point(13, 215);
            this.label10.Name = "label10";
            this.label10.RightToLeft = System.Windows.Forms.RightToLeft.No;
            this.label10.Size = new System.Drawing.Size(98, 14);
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
// tabControl1
// 
            this.tabControl1.Controls.Add(this.tabPage1);
            this.tabControl1.Controls.Add(this.tabPage2);
            this.tabControl1.Location = new System.Drawing.Point(13, 13);
            this.tabControl1.Name = "tabControl1";
            this.tabControl1.SelectedIndex = 0;
            this.tabControl1.Size = new System.Drawing.Size(529, 300);
            this.tabControl1.TabIndex = 6;
// 
// tabPage1
// 
            this.tabPage1.Controls.Add(this.groupBox7);
            this.tabPage1.Location = new System.Drawing.Point(4, 22);
            this.tabPage1.Name = "tabPage1";
            this.tabPage1.Padding = new System.Windows.Forms.Padding(3);
            this.tabPage1.Size = new System.Drawing.Size(521, 274);
            this.tabPage1.TabIndex = 0;
            this.tabPage1.Text = "Status";
// 
// tabPage2
// 
            this.tabPage2.Controls.Add(this.messageLogGridView);
            this.tabPage2.Location = new System.Drawing.Point(4, 22);
            this.tabPage2.Name = "tabPage2";
            this.tabPage2.Padding = new System.Windows.Forms.Padding(3);
            this.tabPage2.Size = new System.Drawing.Size(521, 274);
            this.tabPage2.TabIndex = 1;
            this.tabPage2.Text = "Message Log";
// 
// messageLogGridView
// 
            this.messageLogGridView.AllowUserToAddRows = false;
            this.messageLogGridView.AllowUserToDeleteRows = false;
            this.messageLogGridView.BackgroundColor = System.Drawing.Color.White;
            this.messageLogGridView.Columns.Add(this.dataGridViewImageColumn1);
            this.messageLogGridView.Columns.Add(this.dataGridViewTextBoxColumn1);
            this.messageLogGridView.Columns.Add(this.dataGridViewTextBoxColumn2);
            this.messageLogGridView.EditMode = System.Windows.Forms.DataGridViewEditMode.EditProgrammatically;
            this.messageLogGridView.GridColor = System.Drawing.SystemColors.ControlLight;
            this.messageLogGridView.Location = new System.Drawing.Point(4, 4);
            this.messageLogGridView.MultiSelect = false;
            this.messageLogGridView.Name = "messageLogGridView";
            this.messageLogGridView.ReadOnly = true;
            this.messageLogGridView.RowHeadersVisible = false;
            //this.messageLogGridView.RowHeadersWidthResizable = false;
            //this.messageLogGridView.RowsResizable = false;
            this.messageLogGridView.SelectionMode = System.Windows.Forms.DataGridViewSelectionMode.FullRowSelect;
            this.messageLogGridView.Size = new System.Drawing.Size(511, 264);
            this.messageLogGridView.TabIndex = 6;
// 
// dataGridViewImageColumn1
// 
            dataGridViewCellStyle1.Alignment = System.Windows.Forms.DataGridViewContentAlignment.MiddleCenter;
            dataGridViewCellStyle1.NullValue = ((object)(resources.GetObject("dataGridViewCellStyle1.NullValue")));
            this.dataGridViewImageColumn1.DefaultCellStyle = dataGridViewCellStyle1;
            this.dataGridViewImageColumn1.HeaderText = "Type";
            this.dataGridViewImageColumn1.Name = "MessageTypeColumn";
            this.dataGridViewImageColumn1.ReadOnly = true;
            this.dataGridViewImageColumn1.Resizable = System.Windows.Forms.DataGridViewTriState.False;
            this.dataGridViewImageColumn1.Width = 50;
// 
// dataGridViewTextBoxColumn1
// 
            this.dataGridViewTextBoxColumn1.DefaultCellStyle = dataGridViewCellStyle2;
            this.dataGridViewTextBoxColumn1.HeaderText = "Time";
            this.dataGridViewTextBoxColumn1.Name = "MessageTimeColumn";
            this.dataGridViewTextBoxColumn1.ReadOnly = true;
            this.dataGridViewTextBoxColumn1.Resizable = System.Windows.Forms.DataGridViewTriState.False;
            this.dataGridViewTextBoxColumn1.Width = 80;
// 
// dataGridViewTextBoxColumn2
// 
            //this.dataGridViewTextBoxColumn2.AutoSizeCriteria = System.Windows.Forms.DataGridViewAutoSizeColumnCriteria.HeaderAndRows;
            this.dataGridViewTextBoxColumn2.DefaultCellStyle = dataGridViewCellStyle2;
            this.dataGridViewTextBoxColumn2.HeaderText = "Message";
            this.dataGridViewTextBoxColumn2.Name = "MessageTextColumn";
            this.dataGridViewTextBoxColumn2.ReadOnly = true;
            this.dataGridViewTextBoxColumn2.Resizable = System.Windows.Forms.DataGridViewTriState.True;
            this.dataGridViewTextBoxColumn2.Width = 70;
// 
// clearButton
// 
            this.clearButton.Location = new System.Drawing.Point(95, 320);
            this.clearButton.Name = "clearButton";
            this.clearButton.TabIndex = 5;
            this.clearButton.Text = "Clear Log";
            this.clearButton.Click += new System.EventHandler(this.clearLogButton_Click);
// 
// refreshButton
// 
            this.refreshButton.Location = new System.Drawing.Point(13, 320);
            this.refreshButton.Name = "refreshButton";
            this.refreshButton.TabIndex = 4;
            this.refreshButton.Text = "Refresh";
            this.refreshButton.Click += new System.EventHandler(this.refreshButton_Click);
// 
// StatusDialog
// 
//            this.AutoScaleBaseSize = new System.Drawing.Size(5, 13);
            this.ClientSize = new System.Drawing.Size(554, 355);
            this.Controls.Add(this.tabControl1);
            this.Controls.Add(this.clearButton);
            this.Controls.Add(this.okButton);
            this.Controls.Add(this.refreshButton);
            this.MaximizeBox = false;
            this.MinimizeBox = false;
            this.Name = "StatusDialog";
            this.StartPosition = System.Windows.Forms.FormStartPosition.CenterParent;
            this.Text = "Synchronization State";
            this.TopMost = true;
            this.groupBox7.ResumeLayout(false);
            this.groupBox7.PerformLayout();
            ((System.ComponentModel.ISupportInitialize)(this.syncingPictureBox)).EndInit();
            this.panel1.ResumeLayout(false);
            this.panel1.PerformLayout();
            this.tabControl1.ResumeLayout(false);
            this.tabPage1.ResumeLayout(false);
            this.tabPage2.ResumeLayout(false);
            ((System.ComponentModel.ISupportInitialize)(this.messageLogGridView)).EndInit();
            this.ResumeLayout(false);

        }

        #endregion

        private System.Windows.Forms.Button okButton;
        private System.Windows.Forms.GroupBox groupBox7;
        private System.Windows.Forms.PictureBox syncingPictureBox;
        private System.Windows.Forms.Panel panel1;
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
        private System.Windows.Forms.Label modReadOnlyItemsLabel;
        private System.Windows.Forms.Label label16;
        private System.Windows.Forms.Label conflictingItemsLabel;
        private System.Windows.Forms.Label label14;
        private System.Windows.Forms.Label syncedItemsLabel;
        private System.Windows.Forms.Label label11;
        private System.Windows.Forms.Label lastSyncedTimeLabel;
        private System.Windows.Forms.Label label12;
        private System.Windows.Forms.Label currentOperationLabel;
        private System.Windows.Forms.Label label10;
        private System.Windows.Forms.ProgressBar syncStatusProgressBar;
        private System.Windows.Forms.TabControl tabControl1;
        private System.Windows.Forms.TabPage tabPage1;
        private System.Windows.Forms.TabPage tabPage2;
        private System.Windows.Forms.DataGridView messageLogGridView;
        private System.Windows.Forms.DataGridViewImageColumn dataGridViewImageColumn1;
        private System.Windows.Forms.DataGridViewTextBoxColumn dataGridViewTextBoxColumn1;
        private System.Windows.Forms.DataGridViewTextBoxColumn dataGridViewTextBoxColumn2;
        private System.Windows.Forms.Button clearButton;
        private System.Windows.Forms.Button refreshButton;
    }
}