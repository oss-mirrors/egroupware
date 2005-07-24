namespace cUnnectorOutlookAddIn
{
    partial class SettingsDialog
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
            this.OK = new System.Windows.Forms.Button();
            this.Cancel = new System.Windows.Forms.Button();
            this.Apply = new System.Windows.Forms.Button();
            this.tabControl = new System.Windows.Forms.TabControl();
            this.generalPage = new System.Windows.Forms.TabPage();
            this.groupBox4 = new System.Windows.Forms.GroupBox();
            this.autoSyncIntervallUpDown = new System.Windows.Forms.NumericUpDown();
            this.label3 = new System.Windows.Forms.Label();
            this.pictureBox4 = new System.Windows.Forms.PictureBox();
            this.groupBox3 = new System.Windows.Forms.GroupBox();
            this.pictureBox6 = new System.Windows.Forms.PictureBox();
            this.label2 = new System.Windows.Forms.Label();
            this.label1 = new System.Windows.Forms.Label();
            this.comingUpDown = new System.Windows.Forms.NumericUpDown();
            this.previousUpDown = new System.Windows.Forms.NumericUpDown();
            this.groupBox2 = new System.Windows.Forms.GroupBox();
            this.localFolderTextBox = new System.Windows.Forms.TextBox();
            this.label6 = new System.Windows.Forms.Label();
            this.pictureBox5 = new System.Windows.Forms.PictureBox();
            this.groupBox1 = new System.Windows.Forms.GroupBox();
            this.syncTasksCheckBox = new System.Windows.Forms.CheckBox();
            this.syncContactsCheckBox = new System.Windows.Forms.CheckBox();
            this.pictureBox3 = new System.Windows.Forms.PictureBox();
            this.pictureBox1 = new System.Windows.Forms.PictureBox();
            this.syncAppointmentsCheckBox = new System.Windows.Forms.CheckBox();
            this.pictureBox2 = new System.Windows.Forms.PictureBox();
            this.eGroupwarePage = new System.Windows.Forms.TabPage();
            this.groupBox6 = new System.Windows.Forms.GroupBox();
            this.pictureBox8 = new System.Windows.Forms.PictureBox();
            this.label14 = new System.Windows.Forms.Label();
            this.label15 = new System.Windows.Forms.Label();
            this.passwordTextBox = new System.Windows.Forms.TextBox();
            this.userTextBox = new System.Windows.Forms.TextBox();
            this.groupBox5 = new System.Windows.Forms.GroupBox();
            this.pictureBox7 = new System.Windows.Forms.PictureBox();
            this.label9 = new System.Windows.Forms.Label();
            this.domainTextBox = new System.Windows.Forms.TextBox();
            this.label12 = new System.Windows.Forms.Label();
            this.urlTextBox = new System.Windows.Forms.TextBox();
            this.label4 = new System.Windows.Forms.Label();
            this.label5 = new System.Windows.Forms.Label();
            this.label7 = new System.Windows.Forms.Label();
            this.label8 = new System.Windows.Forms.Label();
            this.localDirectoryBrowseButton = new System.Windows.Forms.Button();
            this.tabControl.SuspendLayout();
            this.generalPage.SuspendLayout();
            this.groupBox4.SuspendLayout();
            ((System.ComponentModel.ISupportInitialize)(this.autoSyncIntervallUpDown)).BeginInit();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox4)).BeginInit();
            this.groupBox3.SuspendLayout();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox6)).BeginInit();
            ((System.ComponentModel.ISupportInitialize)(this.comingUpDown)).BeginInit();
            ((System.ComponentModel.ISupportInitialize)(this.previousUpDown)).BeginInit();
            this.groupBox2.SuspendLayout();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox5)).BeginInit();
            this.groupBox1.SuspendLayout();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox3)).BeginInit();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox1)).BeginInit();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox2)).BeginInit();
            this.eGroupwarePage.SuspendLayout();
            this.groupBox6.SuspendLayout();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox8)).BeginInit();
            this.groupBox5.SuspendLayout();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox7)).BeginInit();
            this.SuspendLayout();
            // 
            // OK
            // 
            this.OK.DialogResult = System.Windows.Forms.DialogResult.OK;
            this.OK.Location = new System.Drawing.Point(193, 433);
            this.OK.Name = "OK";
            this.OK.Size = new System.Drawing.Size(75, 23);
            this.OK.TabIndex = 0;
            this.OK.Text = "OK";
            this.OK.Click += new System.EventHandler(this.OK_Click);
            // 
            // Cancel
            // 
            this.Cancel.DialogResult = System.Windows.Forms.DialogResult.Cancel;
            this.Cancel.Location = new System.Drawing.Point(275, 433);
            this.Cancel.Name = "Cancel";
            this.Cancel.Size = new System.Drawing.Size(75, 23);
            this.Cancel.TabIndex = 1;
            this.Cancel.Text = "Cancel";
            // 
            // Apply
            // 
            this.Apply.Enabled = false;
            this.Apply.Location = new System.Drawing.Point(357, 433);
            this.Apply.Name = "Apply";
            this.Apply.Size = new System.Drawing.Size(75, 23);
            this.Apply.TabIndex = 2;
            this.Apply.Text = "Apply";
            this.Apply.Click += new System.EventHandler(this.Apply_Click);
            // 
            // tabControl
            // 
            this.tabControl.Controls.Add(this.generalPage);
            this.tabControl.Controls.Add(this.eGroupwarePage);
            this.tabControl.Location = new System.Drawing.Point(13, 13);
            this.tabControl.Multiline = true;
            this.tabControl.Name = "tabControl";
            this.tabControl.SelectedIndex = 0;
            this.tabControl.Size = new System.Drawing.Size(419, 415);
            this.tabControl.TabIndex = 3;
            // 
            // generalPage
            // 
            this.generalPage.Controls.Add(this.groupBox4);
            this.generalPage.Controls.Add(this.groupBox3);
            this.generalPage.Controls.Add(this.groupBox2);
            this.generalPage.Controls.Add(this.groupBox1);
            this.generalPage.Location = new System.Drawing.Point(4, 22);
            this.generalPage.Name = "generalPage";
            this.generalPage.Padding = new System.Windows.Forms.Padding(3);
            this.generalPage.Size = new System.Drawing.Size(411, 389);
            this.generalPage.TabIndex = 0;
            this.generalPage.Text = "General";
            // 
            // groupBox4
            // 
            this.groupBox4.BackColor = System.Drawing.Color.Transparent;
            this.groupBox4.Controls.Add(this.autoSyncIntervallUpDown);
            this.groupBox4.Controls.Add(this.label3);
            this.groupBox4.Controls.Add(this.pictureBox4);
            this.groupBox4.Location = new System.Drawing.Point(4, 296);
            this.groupBox4.Name = "groupBox4";
            this.groupBox4.Size = new System.Drawing.Size(400, 84);
            this.groupBox4.TabIndex = 3;
            this.groupBox4.TabStop = false;
            this.groupBox4.Text = "Auto-Sync Intervall";
            // 
            // autoSyncIntervallUpDown
            // 
            this.autoSyncIntervallUpDown.Location = new System.Drawing.Point(321, 32);
            this.autoSyncIntervallUpDown.Maximum = new decimal(new int[] {
            360,
            0,
            0,
            0});
            this.autoSyncIntervallUpDown.Name = "autoSyncIntervallUpDown";
            this.autoSyncIntervallUpDown.Size = new System.Drawing.Size(70, 20);
            this.autoSyncIntervallUpDown.TabIndex = 9;
            this.autoSyncIntervallUpDown.Value = new decimal(new int[] {
            30,
            0,
            0,
            0});
            this.autoSyncIntervallUpDown.ValueChanged += new System.EventHandler(this.StateChanged);
            // 
            // label3
            // 
            this.label3.AutoSize = true;
            this.label3.Location = new System.Drawing.Point(82, 38);
            this.label3.Name = "label3";
            this.label3.Size = new System.Drawing.Size(220, 13);
            this.label3.TabIndex = 8;
            this.label3.Text = "Minutes between automatic synchronizations: ";
            this.label3.TextAlign = System.Drawing.ContentAlignment.BottomLeft;
            // 
            // pictureBox4
            // 
            this.pictureBox4.AutoSize = true;
            this.pictureBox4.Image = cUnnectorOutlookAddIn.Properties.Resources.time;
            this.pictureBox4.Location = new System.Drawing.Point(7, 20);
            this.pictureBox4.Name = "pictureBox4";
            this.pictureBox4.Size = new System.Drawing.Size(32, 32);
            this.pictureBox4.SizeMode = System.Windows.Forms.PictureBoxSizeMode.AutoSize;
            this.pictureBox4.TabIndex = 7;
            this.pictureBox4.TabStop = false;
            // 
            // groupBox3
            // 
            this.groupBox3.BackColor = System.Drawing.Color.Transparent;
            this.groupBox3.Controls.Add(this.pictureBox6);
            this.groupBox3.Controls.Add(this.label2);
            this.groupBox3.Controls.Add(this.label1);
            this.groupBox3.Controls.Add(this.comingUpDown);
            this.groupBox3.Controls.Add(this.previousUpDown);
            this.groupBox3.Location = new System.Drawing.Point(4, 200);
            this.groupBox3.Margin = new System.Windows.Forms.Padding(3, 0, 3, 3);
            this.groupBox3.Name = "groupBox3";
            this.groupBox3.Size = new System.Drawing.Size(400, 84);
            this.groupBox3.TabIndex = 2;
            this.groupBox3.TabStop = false;
            this.groupBox3.Text = "Date Range";
            // 
            // pictureBox6
            // 
            this.pictureBox6.AutoSize = true;
            this.pictureBox6.Image = cUnnectorOutlookAddIn.Properties.Resources.daterange;
            this.pictureBox6.Location = new System.Drawing.Point(7, 20);
            this.pictureBox6.Name = "pictureBox6";
            this.pictureBox6.Size = new System.Drawing.Size(32, 32);
            this.pictureBox6.SizeMode = System.Windows.Forms.PictureBoxSizeMode.AutoSize;
            this.pictureBox6.TabIndex = 9;
            this.pictureBox6.TabStop = false;
            // 
            // label2
            // 
            this.label2.AutoSize = true;
            this.label2.Location = new System.Drawing.Point(242, 38);
            this.label2.Margin = new System.Windows.Forms.Padding(0, 3, 3, 3);
            this.label2.Name = "label2";
            this.label2.Size = new System.Drawing.Size(69, 13);
            this.label2.TabIndex = 6;
            this.label2.Text = "Coming days: ";
            this.label2.TextAlign = System.Drawing.ContentAlignment.BottomLeft;
            // 
            // label1
            // 
            this.label1.AutoSize = true;
            this.label1.Location = new System.Drawing.Point(82, 38);
            this.label1.Name = "label1";
            this.label1.Size = new System.Drawing.Size(75, 13);
            this.label1.TabIndex = 5;
            this.label1.Text = "Previous days: ";
            this.label1.TextAlign = System.Drawing.ContentAlignment.BottomLeft;
            // 
            // comingUpDown
            // 
            this.comingUpDown.Location = new System.Drawing.Point(321, 32);
            this.comingUpDown.Maximum = new decimal(new int[] {
            365,
            0,
            0,
            0});
            this.comingUpDown.Name = "comingUpDown";
            this.comingUpDown.Size = new System.Drawing.Size(70, 20);
            this.comingUpDown.TabIndex = 4;
            this.comingUpDown.Value = new decimal(new int[] {
            30,
            0,
            0,
            0});
            this.comingUpDown.ValueChanged += new System.EventHandler(this.StateChanged);
            // 
            // previousUpDown
            // 
            this.previousUpDown.Location = new System.Drawing.Point(167, 32);
            this.previousUpDown.Margin = new System.Windows.Forms.Padding(3, 3, 1, 3);
            this.previousUpDown.Maximum = new decimal(new int[] {
            365,
            0,
            0,
            0});
            this.previousUpDown.Name = "previousUpDown";
            this.previousUpDown.Size = new System.Drawing.Size(70, 20);
            this.previousUpDown.TabIndex = 3;
            this.previousUpDown.Value = new decimal(new int[] {
            7,
            0,
            0,
            0});
            this.previousUpDown.ValueChanged += new System.EventHandler(this.StateChanged);
            // 
            // groupBox2
            // 
            this.groupBox2.BackColor = System.Drawing.Color.Transparent;
            this.groupBox2.Controls.Add(this.localDirectoryBrowseButton);
            this.groupBox2.Controls.Add(this.localFolderTextBox);
            this.groupBox2.Controls.Add(this.label6);
            this.groupBox2.Controls.Add(this.pictureBox5);
            this.groupBox2.Location = new System.Drawing.Point(4, 104);
            this.groupBox2.Margin = new System.Windows.Forms.Padding(3, 1, 3, 1);
            this.groupBox2.Name = "groupBox2";
            this.groupBox2.Size = new System.Drawing.Size(400, 84);
            this.groupBox2.TabIndex = 1;
            this.groupBox2.TabStop = false;
            this.groupBox2.Text = "Local Resources";
            // 
            // localFolderTextBox
            // 
            this.localFolderTextBox.AutoSize = false;
            this.localFolderTextBox.Location = new System.Drawing.Point(172, 29);
            this.localFolderTextBox.Margin = new System.Windows.Forms.Padding(3, 3, 3, 1);
            this.localFolderTextBox.Name = "localFolderTextBox";
            this.localFolderTextBox.Size = new System.Drawing.Size(177, 22);
            this.localFolderTextBox.TabIndex = 14;
            this.localFolderTextBox.WordWrap = false;
            this.localFolderTextBox.TextChanged += new System.EventHandler(this.StateChanged);
            // 
            // label6
            // 
            this.label6.AutoSize = true;
            this.label6.BackColor = System.Drawing.Color.Transparent;
            this.label6.Location = new System.Drawing.Point(82, 38);
            this.label6.Name = "label6";
            this.label6.Size = new System.Drawing.Size(64, 13);
            this.label6.TabIndex = 15;
            this.label6.Text = "Local Folder:";
            this.label6.TextAlign = System.Drawing.ContentAlignment.BottomLeft;
            // 
            // pictureBox5
            // 
            this.pictureBox5.AutoSize = true;
            this.pictureBox5.Image = cUnnectorOutlookAddIn.Properties.Resources.folder;
            this.pictureBox5.Location = new System.Drawing.Point(7, 20);
            this.pictureBox5.Name = "pictureBox5";
            this.pictureBox5.Size = new System.Drawing.Size(32, 32);
            this.pictureBox5.SizeMode = System.Windows.Forms.PictureBoxSizeMode.AutoSize;
            this.pictureBox5.TabIndex = 8;
            this.pictureBox5.TabStop = false;
            // 
            // groupBox1
            // 
            this.groupBox1.BackColor = System.Drawing.Color.Transparent;
            this.groupBox1.Controls.Add(this.syncTasksCheckBox);
            this.groupBox1.Controls.Add(this.syncContactsCheckBox);
            this.groupBox1.Controls.Add(this.pictureBox3);
            this.groupBox1.Controls.Add(this.pictureBox1);
            this.groupBox1.Controls.Add(this.syncAppointmentsCheckBox);
            this.groupBox1.Controls.Add(this.pictureBox2);
            this.groupBox1.Location = new System.Drawing.Point(4, 8);
            this.groupBox1.Margin = new System.Windows.Forms.Padding(3, 3, 3, 1);
            this.groupBox1.Name = "groupBox1";
            this.groupBox1.Size = new System.Drawing.Size(400, 84);
            this.groupBox1.TabIndex = 0;
            this.groupBox1.TabStop = false;
            this.groupBox1.Text = "Information to Synchronize";
            // 
            // syncTasksCheckBox
            // 
            this.syncTasksCheckBox.AutoSize = true;
            this.syncTasksCheckBox.Checked = true;
            this.syncTasksCheckBox.CheckState = System.Windows.Forms.CheckState.Checked;
            this.syncTasksCheckBox.Location = new System.Drawing.Point(333, 35);
            this.syncTasksCheckBox.Name = "syncTasksCheckBox";
            this.syncTasksCheckBox.Size = new System.Drawing.Size(51, 17);
            this.syncTasksCheckBox.TabIndex = 3;
            this.syncTasksCheckBox.Text = "Tasks";
            this.syncTasksCheckBox.CheckedChanged += new System.EventHandler(this.StateChanged);
            // 
            // syncContactsCheckBox
            // 
            this.syncContactsCheckBox.AutoSize = true;
            this.syncContactsCheckBox.Checked = true;
            this.syncContactsCheckBox.CheckState = System.Windows.Forms.CheckState.Checked;
            this.syncContactsCheckBox.Location = new System.Drawing.Point(46, 35);
            this.syncContactsCheckBox.Name = "syncContactsCheckBox";
            this.syncContactsCheckBox.Size = new System.Drawing.Size(64, 17);
            this.syncContactsCheckBox.TabIndex = 1;
            this.syncContactsCheckBox.Text = "Contacts";
            this.syncContactsCheckBox.CheckedChanged += new System.EventHandler(this.StateChanged);
            // 
            // pictureBox3
            // 
            this.pictureBox3.AutoSize = true;
            this.pictureBox3.Image = cUnnectorOutlookAddIn.Properties.Resources.tasks;
            this.pictureBox3.Location = new System.Drawing.Point(294, 20);
            this.pictureBox3.Name = "pictureBox3";
            this.pictureBox3.Size = new System.Drawing.Size(32, 32);
            this.pictureBox3.SizeMode = System.Windows.Forms.PictureBoxSizeMode.AutoSize;
            this.pictureBox3.TabIndex = 1;
            this.pictureBox3.TabStop = false;
            // 
            // pictureBox1
            // 
            this.pictureBox1.AutoSize = true;
            this.pictureBox1.Image = cUnnectorOutlookAddIn.Properties.Resources.contacts;
            this.pictureBox1.Location = new System.Drawing.Point(7, 20);
            this.pictureBox1.Name = "pictureBox1";
            this.pictureBox1.Size = new System.Drawing.Size(32, 32);
            this.pictureBox1.SizeMode = System.Windows.Forms.PictureBoxSizeMode.AutoSize;
            this.pictureBox1.TabIndex = 0;
            this.pictureBox1.TabStop = false;
            // 
            // syncAppointmentsCheckBox
            // 
            this.syncAppointmentsCheckBox.AutoSize = true;
            this.syncAppointmentsCheckBox.Checked = true;
            this.syncAppointmentsCheckBox.CheckState = System.Windows.Forms.CheckState.Checked;
            this.syncAppointmentsCheckBox.Location = new System.Drawing.Point(182, 35);
            this.syncAppointmentsCheckBox.Name = "syncAppointmentsCheckBox";
            this.syncAppointmentsCheckBox.Size = new System.Drawing.Size(86, 17);
            this.syncAppointmentsCheckBox.TabIndex = 2;
            this.syncAppointmentsCheckBox.Text = "Appointments";
            this.syncAppointmentsCheckBox.CheckedChanged += new System.EventHandler(this.StateChanged);
            // 
            // pictureBox2
            // 
            this.pictureBox2.AutoSize = true;
            this.pictureBox2.Image = cUnnectorOutlookAddIn.Properties.Resources.appointments;
            this.pictureBox2.Location = new System.Drawing.Point(143, 20);
            this.pictureBox2.Name = "pictureBox2";
            this.pictureBox2.Size = new System.Drawing.Size(32, 32);
            this.pictureBox2.SizeMode = System.Windows.Forms.PictureBoxSizeMode.AutoSize;
            this.pictureBox2.TabIndex = 1;
            this.pictureBox2.TabStop = false;
            // 
            // eGroupwarePage
            // 
            this.eGroupwarePage.Controls.Add(this.groupBox6);
            this.eGroupwarePage.Controls.Add(this.groupBox5);
            this.eGroupwarePage.Location = new System.Drawing.Point(4, 22);
            this.eGroupwarePage.Name = "eGroupwarePage";
            this.eGroupwarePage.Padding = new System.Windows.Forms.Padding(3);
            this.eGroupwarePage.Size = new System.Drawing.Size(411, 389);
            this.eGroupwarePage.TabIndex = 1;
            this.eGroupwarePage.Text = "EGroupware";
            // 
            // groupBox6
            // 
            this.groupBox6.BackColor = System.Drawing.Color.Transparent;
            this.groupBox6.Controls.Add(this.pictureBox8);
            this.groupBox6.Controls.Add(this.label14);
            this.groupBox6.Controls.Add(this.label15);
            this.groupBox6.Controls.Add(this.passwordTextBox);
            this.groupBox6.Controls.Add(this.userTextBox);
            this.groupBox6.Location = new System.Drawing.Point(4, 124);
            this.groupBox6.Margin = new System.Windows.Forms.Padding(3, 3, 3, 2);
            this.groupBox6.Name = "groupBox6";
            this.groupBox6.Size = new System.Drawing.Size(400, 100);
            this.groupBox6.TabIndex = 8;
            this.groupBox6.TabStop = false;
            this.groupBox6.Text = "User Information";
            // 
            // pictureBox8
            // 
            this.pictureBox8.AutoSize = true;
            this.pictureBox8.Image = cUnnectorOutlookAddIn.Properties.Resources.user;
            this.pictureBox8.Location = new System.Drawing.Point(7, 18);
            this.pictureBox8.Name = "pictureBox8";
            this.pictureBox8.Size = new System.Drawing.Size(32, 32);
            this.pictureBox8.SizeMode = System.Windows.Forms.PictureBoxSizeMode.AutoSize;
            this.pictureBox8.TabIndex = 8;
            this.pictureBox8.TabStop = false;
            // 
            // label14
            // 
            this.label14.AutoSize = true;
            this.label14.BackColor = System.Drawing.Color.Transparent;
            this.label14.Location = new System.Drawing.Point(82, 63);
            this.label14.Name = "label14";
            this.label14.Size = new System.Drawing.Size(52, 13);
            this.label14.TabIndex = 5;
            this.label14.Text = "Password:";
            // 
            // label15
            // 
            this.label15.AutoSize = true;
            this.label15.BackColor = System.Drawing.Color.Transparent;
            this.label15.Location = new System.Drawing.Point(82, 36);
            this.label15.Name = "label15";
            this.label15.Size = new System.Drawing.Size(28, 13);
            this.label15.TabIndex = 4;
            this.label15.Text = "User:";
            // 
            // passwordTextBox
            // 
            this.passwordTextBox.Location = new System.Drawing.Point(167, 57);
            this.passwordTextBox.Name = "passwordTextBox";
            this.passwordTextBox.PasswordChar = '●';
            this.passwordTextBox.Size = new System.Drawing.Size(227, 20);
            this.passwordTextBox.TabIndex = 2;
            this.passwordTextBox.UseSystemPasswordChar = true;
            this.passwordTextBox.TextChanged += new System.EventHandler(this.StateChanged);
            // 
            // userTextBox
            // 
            this.userTextBox.AutoSize = false;
            this.userTextBox.Location = new System.Drawing.Point(167, 30);
            this.userTextBox.Multiline = true;
            this.userTextBox.Name = "userTextBox";
            this.userTextBox.Size = new System.Drawing.Size(227, 20);
            this.userTextBox.TabIndex = 1;
            this.userTextBox.TextChanged += new System.EventHandler(this.StateChanged);
            // 
            // groupBox5
            // 
            this.groupBox5.BackColor = System.Drawing.Color.Transparent;
            this.groupBox5.Controls.Add(this.pictureBox7);
            this.groupBox5.Controls.Add(this.label9);
            this.groupBox5.Controls.Add(this.domainTextBox);
            this.groupBox5.Controls.Add(this.label12);
            this.groupBox5.Controls.Add(this.urlTextBox);
            this.groupBox5.Location = new System.Drawing.Point(4, 8);
            this.groupBox5.Margin = new System.Windows.Forms.Padding(3, 3, 3, 2);
            this.groupBox5.Name = "groupBox5";
            this.groupBox5.Size = new System.Drawing.Size(400, 100);
            this.groupBox5.TabIndex = 7;
            this.groupBox5.TabStop = false;
            this.groupBox5.Text = "Remote Server";
            // 
            // pictureBox7
            // 
            this.pictureBox7.AutoSize = true;
            this.pictureBox7.Image = cUnnectorOutlookAddIn.Properties.Resources.remote;
            this.pictureBox7.Location = new System.Drawing.Point(7, 20);
            this.pictureBox7.Name = "pictureBox7";
            this.pictureBox7.Size = new System.Drawing.Size(32, 32);
            this.pictureBox7.SizeMode = System.Windows.Forms.PictureBoxSizeMode.AutoSize;
            this.pictureBox7.TabIndex = 8;
            this.pictureBox7.TabStop = false;
            // 
            // label9
            // 
            this.label9.AutoSize = true;
            this.label9.BackColor = System.Drawing.Color.Transparent;
            this.label9.Location = new System.Drawing.Point(82, 66);
            this.label9.Name = "label9";
            this.label9.Size = new System.Drawing.Size(42, 13);
            this.label9.TabIndex = 7;
            this.label9.Text = "Domain:";
            // 
            // domainTextBox
            // 
            this.domainTextBox.Location = new System.Drawing.Point(167, 60);
            this.domainTextBox.Name = "domainTextBox";
            this.domainTextBox.Size = new System.Drawing.Size(227, 20);
            this.domainTextBox.TabIndex = 6;
            this.domainTextBox.Text = "default";
            this.domainTextBox.TextChanged += new System.EventHandler(this.StateChanged);
            // 
            // label12
            // 
            this.label12.AutoSize = true;
            this.label12.BackColor = System.Drawing.Color.Transparent;
            this.label12.Location = new System.Drawing.Point(82, 38);
            this.label12.Name = "label12";
            this.label12.Size = new System.Drawing.Size(57, 13);
            this.label12.TabIndex = 3;
            this.label12.Text = "xmlrpc.php:";
            // 
            // urlTextBox
            // 
            this.urlTextBox.Location = new System.Drawing.Point(167, 32);
            this.urlTextBox.Margin = new System.Windows.Forms.Padding(3, 1, 3, 1);
            this.urlTextBox.Name = "urlTextBox";
            this.urlTextBox.Size = new System.Drawing.Size(227, 20);
            this.urlTextBox.TabIndex = 0;
            this.urlTextBox.Text = "http://";
            this.urlTextBox.TextChanged += new System.EventHandler(this.StateChanged);
            // 
            // label4
            // 
            this.label4.AutoSize = true;
            this.label4.BackColor = System.Drawing.Color.Transparent;
            this.label4.Location = new System.Drawing.Point(18, 49);
            this.label4.Name = "label4";
            this.label4.Size = new System.Drawing.Size(46, 14);
            this.label4.TabIndex = 7;
            this.label4.Text = "Domain:";
            // 
            // label5
            // 
            this.label5.AutoSize = true;
            this.label5.BackColor = System.Drawing.Color.Transparent;
            this.label5.Location = new System.Drawing.Point(18, 103);
            this.label5.Name = "label5";
            this.label5.Size = new System.Drawing.Size(57, 14);
            this.label5.TabIndex = 5;
            this.label5.Text = "Password:";
            // 
            // label7
            // 
            this.label7.AutoSize = true;
            this.label7.BackColor = System.Drawing.Color.Transparent;
            this.label7.Location = new System.Drawing.Point(18, 76);
            this.label7.Name = "label7";
            this.label7.Size = new System.Drawing.Size(64, 14);
            this.label7.TabIndex = 4;
            this.label7.Text = "User Name:";
            // 
            // label8
            // 
            this.label8.AutoSize = true;
            this.label8.BackColor = System.Drawing.Color.Transparent;
            this.label8.Location = new System.Drawing.Point(18, 22);
            this.label8.Name = "label8";
            this.label8.Size = new System.Drawing.Size(22, 14);
            this.label8.TabIndex = 3;
            this.label8.Text = "Url:";
            // 
            // localDirectoryBrowseButton
            // 
            this.localDirectoryBrowseButton.Location = new System.Drawing.Point(350, 29);
            this.localDirectoryBrowseButton.Name = "localDirectoryBrowseButton";
            this.localDirectoryBrowseButton.Size = new System.Drawing.Size(41, 23);
            this.localDirectoryBrowseButton.TabIndex = 16;
            this.localDirectoryBrowseButton.Text = "Browse";
            this.localDirectoryBrowseButton.Click += new System.EventHandler(this.localDirectoryBrowseButton_Click);
            // 
            // SettingsDialog
            // 
            this.AcceptButton = this.OK;
            this.CancelButton = this.Cancel;
            this.ClientSize = new System.Drawing.Size(444, 468);
            this.Controls.Add(this.tabControl);
            this.Controls.Add(this.Apply);
            this.Controls.Add(this.Cancel);
            this.Controls.Add(this.OK);
            this.FormBorderStyle = System.Windows.Forms.FormBorderStyle.FixedDialog;
            this.MaximizeBox = false;
            this.MinimizeBox = false;
            this.Name = "SettingsDialog";
            this.ShowInTaskbar = false;
            this.SizeGripStyle = System.Windows.Forms.SizeGripStyle.Hide;
            this.StartPosition = System.Windows.Forms.FormStartPosition.CenterParent;
            this.Text = "Settings";
            this.tabControl.ResumeLayout(false);
            this.generalPage.ResumeLayout(false);
            this.groupBox4.ResumeLayout(false);
            this.groupBox4.PerformLayout();
            ((System.ComponentModel.ISupportInitialize)(this.autoSyncIntervallUpDown)).EndInit();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox4)).EndInit();
            this.groupBox3.ResumeLayout(false);
            this.groupBox3.PerformLayout();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox6)).EndInit();
            ((System.ComponentModel.ISupportInitialize)(this.comingUpDown)).EndInit();
            ((System.ComponentModel.ISupportInitialize)(this.previousUpDown)).EndInit();
            this.groupBox2.ResumeLayout(false);
            this.groupBox2.PerformLayout();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox5)).EndInit();
            this.groupBox1.ResumeLayout(false);
            this.groupBox1.PerformLayout();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox3)).EndInit();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox1)).EndInit();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox2)).EndInit();
            this.eGroupwarePage.ResumeLayout(false);
            this.groupBox6.ResumeLayout(false);
            this.groupBox6.PerformLayout();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox8)).EndInit();
            this.groupBox5.ResumeLayout(false);
            this.groupBox5.PerformLayout();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox7)).EndInit();
            this.ResumeLayout(false);

        }

        #endregion

        private System.Windows.Forms.Button OK;
        private System.Windows.Forms.Button Cancel;
        private System.Windows.Forms.Button Apply;
        private System.Windows.Forms.TabControl tabControl;
        private System.Windows.Forms.TabPage generalPage;
        private System.Windows.Forms.TabPage eGroupwarePage;
        private System.Windows.Forms.GroupBox groupBox1;
        private System.Windows.Forms.GroupBox groupBox2;
        private System.Windows.Forms.PictureBox pictureBox1;
        private System.Windows.Forms.GroupBox groupBox3;
        private System.Windows.Forms.PictureBox pictureBox2;
        private System.Windows.Forms.PictureBox pictureBox3;
        private System.Windows.Forms.CheckBox syncContactsCheckBox;
        private System.Windows.Forms.CheckBox syncAppointmentsCheckBox;
        private System.Windows.Forms.CheckBox syncTasksCheckBox;
        private System.Windows.Forms.NumericUpDown previousUpDown;
        private System.Windows.Forms.NumericUpDown comingUpDown;
        private System.Windows.Forms.Label label1;
        private System.Windows.Forms.Label label2;
        private System.Windows.Forms.PictureBox pictureBox4;
        private System.Windows.Forms.PictureBox pictureBox5;
        private System.Windows.Forms.TextBox localFolderTextBox;
        private System.Windows.Forms.Label label6;
        private System.Windows.Forms.GroupBox groupBox4;
        private System.Windows.Forms.NumericUpDown autoSyncIntervallUpDown;
        private System.Windows.Forms.Label label3;
        private System.Windows.Forms.PictureBox pictureBox6;
        private System.Windows.Forms.GroupBox groupBox5;
        private System.Windows.Forms.PictureBox pictureBox7;
        private System.Windows.Forms.Label label9;
        private System.Windows.Forms.TextBox domainTextBox;
        private System.Windows.Forms.Label label12;
        private System.Windows.Forms.TextBox urlTextBox;
        private System.Windows.Forms.Label label4;
        private System.Windows.Forms.Label label5;
        private System.Windows.Forms.Label label7;
        private System.Windows.Forms.Label label8;
        private System.Windows.Forms.GroupBox groupBox6;
        private System.Windows.Forms.PictureBox pictureBox8;
        private System.Windows.Forms.Label label14;
        private System.Windows.Forms.Label label15;
        private System.Windows.Forms.TextBox passwordTextBox;
        private System.Windows.Forms.TextBox userTextBox;
        private System.Windows.Forms.Button localDirectoryBrowseButton;
    }
}