Attribute VB_Name = "eGWOSync"
Sub eGWSynchronize()
    'load previous settings
    If GetSetting(AppName:="eGWOSync", Section:="Settings", Key:="Hostname") <> "" Then
        eGWForm.txtHostname.Text = GetSetting(AppName:="eGWOSync", _
            Section:="Settings", Key:="Hostname")
        eGWForm.txtPort.Text = GetSetting(AppName:="eGWOSync", _
            Section:="Settings", Key:="Port")
        eGWForm.txtURI.Text = GetSetting(AppName:="eGWOSync", _
            Section:="Settings", Key:="URI")
        eGWForm.txtUsername.Text = GetSetting(AppName:="eGWOSync", _
            Section:="Settings", Key:="Username")
        eGWForm.txtPassword.Text = GetSetting(AppName:="eGWOSync", _
            Section:="Settings", Key:="Password")
    End If
    'open the GUI
    eGWForm.Show
End Sub
Public Function PushContacts() As Boolean
    Dim OCon As COutlookContacts
    Dim eGW As CeGW
    Dim lR As Long
    Dim xmlParms As XMLRPCStruct
    Dim xmlArray As XMLRPCArray
    
    Set OCon = New COutlookContacts
    Set eGW = New CeGW
    Set xmlParms = New XMLRPCStruct
    Set xmlArray = New XMLRPCArray
    
    OCon.Connect
    OCon.OpenFolder "Contacts"
    For lR = 1 To OCon.RecordCount
        'Find the contact
        xmlParms.AddString "start", "1"
        xmlArray.AddString "n_family"
        xmlParms.AddArray "fields", xmlArray
        xmlParms.AddString "query", OCon.Field(LastName)
        eGW.Exec "addressbook.boaddressbook.search", xmlParms
        If eGW.Response.Status <> XMLRPC_PARAMSRETURNED Then
            Debug.Print "Unexpected response from XML-RPC request " & eGW.Response.Status
        ElseIf eGW.Response.Params.Count <> 1 Then
            Debug.Print "Unexpected response from XML-RPC request " & eGW.Response.Params.Count & " return parameters, expecting 1"
        ElseIf eGW.Response.Params(1).ValueType <> XMLRPC_STRUCT Then
            Debug.Print "Unexpected response from XML-RPC request " & linsUtility.GetXMLRPCType(eGW.Response.Params(1).ValueType) & " returned, expecting a Struct"
        End If
        For Each linsMember In eGW.Response.Params(1).StructValue
            Debug.Print linsMember.Name
        Next linsMember
    Next lR
End Function
