Attribute VB_Name = "modEGWUtilities"
'Provides a simplified way of running commands on the eGW XMLRPC server
Public Function SimpleExec(methodName As String, xmlParms As XMLRPCStruct) As XMLRPCResponse
    Dim linsUtility As New XMLRPCUtility
    Dim bLogin As Boolean
    Dim eGW As New CeGW
    
    'grab the login information from the form GUI
    eGW.Hostname = frmMain.txtHostname
    eGW.Port = frmMain.txtPort
    eGW.URI = frmMain.txtURI
    eGW.Username = frmMain.txtUsername
    eGW.Password = frmMain.txtPassword
    
    'login and put the result in a variable for testing
    bLogin = eGW.Login
    
    'If we logged in successfully...
    If bLogin Then
        eGW.Reset
        eGW.Exec methodName, xmlParms
        
        'Error handling
        If eGW.Response.Status <> XMLRPC_PARAMSRETURNED Then
            Debug.Print "Unexpected response from XML-RPC request " & eGW.Response.Status
            If eGW.Response.Status = 4 Then
                Debug.Print "XML Parse Error: " & eGW.Response.XMLParseError
                'Debug.Print eGW.Response.XMLResponse
            End If
        ElseIf eGW.Response.params.Count <> 1 Then
            Debug.Print "Unexpected response from XML-RPC request " & eGW.Response.params.Count & " return parameters, expecting 1"
        ElseIf eGW.Response.params(1).ValueType <> XMLRPC_ARRAY Then
            Debug.Print "Unexpected response from XML-RPC request " & linsUtility.GetXMLRPCType(eGW.Response.params(1).ValueType) & " returned, expecting an array"
            PrintXMLRPCValue eGW.Response.params(1)
        End If
        
        'return the response from the XMLRPC server
        Set SimpleExec = eGW.Response
        'it's always polite to close the door when you leave
        eGW.Logout
    Else
        Debug.Print "Login Failed"
    End If
End Function

Public Function GetOutlookContacts(fldFolder As Outlook.MAPIFolder)
    Dim objItem         As Object
    
    If fldFolder.Folders.Count > 0 Then
        For Each objItem In fldFolder.Folders
            GetOutlookContacts (objItem)
        Next objItem
    End If
    
    For Each objItem In fldFolder.Items
        frmMain.listLocal.AddItem (objItem.FullName)
    Next objItem
End Function

Private Sub GetFolderInfo(fldFolder As Outlook.MAPIFolder)
   ' This procedure prints to the Immediate window information
   ' about items contained in a folder.
   'robbed from msdn.com
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

Public Sub PrintXMLRPCValue(vItem As XMLRPCValue)
    'linsValue holds each element of an array for recursive calling
    Dim linsValue As XMLRPCValue
    'linsMember holds each member of a structure for recursive calling
    Dim linsMember As XMLRPCMember
    
    'Define a printing process for each XMLRPC data type
    Select Case vItem.ValueType
        
        'Arrays
        Case XMLRPC_ARRAY
            Debug.Print "--Array Start: "
            For Each linsValue In vItem.ArrayValue
                PrintXMLRPCValue linsValue
            Next
            Debug.Print "--Array End"
        
        'Base 64
        Case XMLRPC_BASE64
            Debug.Print "Base 64: " & vItem.Base64Value
        
        'Booleans
        Case XMLRPC_BOOLEAN
            Debug.Print "Boolean: " & vItem.BooleanValue
        
        'Dates and times
        Case XMLRPC_DATETIME
            Debug.Print "Date/time: " & vItem.DateTimeValue
        
        'Double numbers
        Case XMLRPC_DOUBLE
            Debug.Print "Double: " & vItem.DoubleValue
            'Debug.Print vItem.DoubleValue
        
        'Integers
        Case XMLRPC_INT_I4
            Debug.Print "XMLRPC Integer: " & vItem.IntegerValue
        
        'Nil values
        Case XMLRPC_NIL
            Debug.Print "Nil value"
        
        'Strings
        Case XMLRPC_STRING
            Debug.Print "String: " & vItem.StringValue
        
        'Structures
        Case XMLRPC_STRUCT
            Debug.Print "--Structure Start: "
            For Each linsMember In vItem.StructValue
                PrintXMLRPCValue linsMember.Value
            Next
            Debug.Print "--Structure End"
        
        'If the value type is unrecognized
        Case Else
            Debug.Print "Attempt to print XMLRPCValue failed: ValueType not recognized."
        End Select
End Sub
