Attribute VB_Name = "eGWOSyncMod"

Sub eGWSynchronize()

    'load previous settings
    If GetSetting(AppName:="eGWOSync", Section:="Settings", Key:="Hostname") <> "" Then
        frmMain.txtHostname.Text = GetSetting(AppName:="eGWOSync", _
            Section:="Settings", Key:="Hostname")
        frmMain.txtPort.Text = GetSetting(AppName:="eGWOSync", _
            Section:="Settings", Key:="Port")
        frmMain.txtURI.Text = GetSetting(AppName:="eGWOSync", _
            Section:="Settings", Key:="URI")
        frmMain.txtUsername.Text = GetSetting(AppName:="eGWOSync", _
            Section:="Settings", Key:="Username")
        frmMain.txtPassword.Text = GetSetting(AppName:="eGWOSync", _
            Section:="Settings", Key:="Password")
    End If
    
    'open the GUI
    frmMain.Show

End Sub
