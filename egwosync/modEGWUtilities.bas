Attribute VB_Name = "modEGWUtilities"
'Provides a simplified way of running commands on the eGW XMLRPC server
Public Function SimpleExec(methodName As String, xmlParms As XMLRPCStruct) As XMLRPCResponse
    Dim linsUtility As New XMLRPCUtility
    Dim bLogin As Boolean
    Dim eGW As New CeGW
    
    eGW.Hostname = frmMain.txtHostname
    eGW.Port = frmMain.txtPort
    eGW.URI = frmMain.txtURI
    eGW.Username = frmMain.txtUsername
    eGW.Password = frmMain.txtPassword
    
    bLogin = eGW.Login
    
    If bLogin Then
        eGW.Reset
        eGW.Exec methodName, xmlParms
        
        'Error handling
        If eGW.response.Status <> XMLRPC_PARAMSRETURNED Then
            Debug.Print "Unexpected response from XML-RPC request " & eGW.response.Status
            If eGW.response.Status = 4 Then
                Debug.Print "XML Parse Error:" & eGW.response.XMLParseError
                Debug.Print eGW.response.XMLResponse
            End If
        ElseIf eGW.response.params.Count <> 1 Then
            Debug.Print "Unexpected response from XML-RPC request " & eGW.response.params.Count & " return parameters, expecting 1"
        ElseIf eGW.response.params(1).ValueType <> XMLRPC_ARRAY Then
            Debug.Print "Unexpected response from XML-RPC request " & linsUtility.GetXMLRPCType(eGW.response.params(1).ValueType) & " returned, expecting an array"
        End If
        
        Set SimpleExec = eGW.response
        eGW.Logout
    End If
End Function

Public Function GetOutlookContacts()
    Dim fldContacts As Outlook.MAPIFolder
    Dim gnspNameSpace As NameSpace
    Set gnspNameSpace = GetNamespace("MAPI")
    Set fldContacts = gnspNameSpace.GetDefaultFolder(olFolderContacts)
    
    GetFolderInfo fldContacts
End Function

Sub GetFolderInfo(fldFolder As Outlook.MAPIFolder)
   ' This procedure prints to the Immediate window information
   ' about items contained in a folder.
   Dim objItem            As Object
   Dim dteCreateDate      As Date
   Dim strSubject         As String
   Dim strItemType        As String
   Dim intCounter         As Integer
   
   On Error Resume Next
   
   If fldFolder.Folders.Count > 0 Then
      For Each objItem In fldFolder.Folders
         Call GetFolderInfo(objItem)
      Next objItem
   End If
   Debug.Print "Folder '" & fldFolder.Name & "' (Contains " _
      & fldFolder.Items.Count & " items):"
   For Each objItem In fldFolder.Items
      intCounter = intCounter + 1
      With objItem
         dteCreateDate = .CreationTime
         strSubject = .Subject
         strItemType = TypeName(objItem)
      End With
      Debug.Print vbTab & "Item #" & intCounter & " - " _
         & strItemType & " - created on " _
         & Format(dteCreateDate, "mmmm dd, yyyy hh:mm am/pm") _
         & vbCrLf & vbTab & vbTab & "Subject: '" _
         & strSubject & "'" & vbCrLf
   Next objItem
End Sub
