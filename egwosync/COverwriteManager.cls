VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "COverwriteManager"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = False
Private myOriginalContact   As Object
Private myNewContact        As Object

Private Sub Class_Initialize()
    Debug.Print "Overwrite Manager initialized"
    Set myNewContact = Nothing
    Set myOriginalContact = Nothing
    Unload FrmOverwrite
    Unload FrmRename
End Sub

'************************************************************************************************
' This is the workhorse of this class. It takes two contacts that are in conflict, the original
' and the one that needs to be added, and allows the user to choose what should be done with the
' two contacts. The calling function should deal with the Overwrite and Skip contingencies. While
' this should deal with the Rename choice. I know that's messy, maybe I'll fix it.
'************************************************************************************************
Public Function Manage(ByRef thisOriginalContact As Object, ByRef thisNewContact As Object) As Integer
    Dim olC     As New COutlookContacts
    Dim eGWC    As New CeGWContacts
    Dim bTemp   As Boolean
    
    Set OriginalContact = thisOriginalContact
    Set NewContact = thisNewContact
    
    Set FrmOverwrite.Parent = Me
    FrmOverwrite.Show 1
    Manage = FrmOverwrite.Choice
    If Manage = FrmOverwrite.RENAME Then
        Set OriginalContact = UpdateContact(OriginalContact, FrmRename.OriginalContactNames)
        Set NewContact = UpdateContact(NewContact, FrmRename.NewContactNames)
        
        If OriginalContactType = OUTLOOK_CONTACT Then
            OriginalContact.Close (olSave)
        ElseIf OriginalContactType = EGW_CONTACT Then
            bTemp = Master.eGW.Login
            If bTemp Then
                bTemp = Master.eGW.Exec("addressbook.boaddressbook.write", OriginalContact)
                Master.eGW.Logout
                If Not bTemp Then
                    BasUtilities.BugOut 721, "COverwriteManager.Manage", _
                        "Remote execution failed."
                End If
            Else
                BasUtilities.BugOut 721, "COverwriteManager.Manage", _
                    "Login failed."
            End If
        End If
        
        If NewContactType = OUTLOOK_CONTACT Then
            eGWC.Create NewContact
        ElseIf NewContactType = EGW_CONTACT Then
            olC.Create NewContact
        End If
    End If
End Function

Private Function UpdateContact(Contact As Object, Names As Collection) As Object
    If TypeOf Contact Is ContactItem Then
        Dim n As ContactItem
        Set n = Contact.Copy
        Contact.Delete
        n.Title = Names.Item("Prefix")
        n.FirstName = Names.Item("FirstName")
        n.MiddleName = Names.Item("MiddleName")
        n.LastName = Names.Item("LastName")
        n.Suffix = Names.Item("Suffix")
        
        Set UpdateContact = n
    ElseIf TypeOf Contact Is XMLRPCStruct Then
        '[ There has to be a better way to do this. But I obviously haven't found it.
        '[ XMLRPCValues are read only.
        Dim mTemp As XMLRPCMember
        Dim sTemp As New XMLRPCStruct
        For Each mTemp In Contact
            If mTemp.Value.ValueType = XMLRPC_STRING Then
                Select Case mTemp.Name
                    Case "fn"
                        sTemp.AddString mTemp.Name, Names.Item("FullName")
                    Case "label"
                        sTemp.AddString mTemp.Name, Names.Item("FullName")
                    Case "n_prefix"
                        sTemp.AddString mTemp.Name, Names.Item("Prefix")
                    Case "n_given"
                        sTemp.AddString mTemp.Name, Names.Item("FirstName")
                    Case "n_middle"
                        sTemp.AddString mTemp.Name, Names.Item("MiddleName")
                    Case "n_family"
                        sTemp.AddString mTemp.Name, Names.Item("LastName")
                    Case "n_suffix"
                        sTemp.AddString mTemp.Name, Names.Item("Suffix")
                    Case Else
                        sTemp.AddString mTemp.Name, mTemp.Value.StringValue
                End Select
            ElseIf mTemp.Value.ValueType = XMLRPC_STRUCT Then
                sTemp.AddStruct mTemp.Name, mTemp.Value.StructValue
            ElseIf mTemp.Value.ValueType = XMLRPC_ARRAY Then
                sTemp.AddArray mTemp.Name, mTemp.Value.ArrayValue
            ElseIf mTemp.Value.ValueType = XMLRPC_INT_I4 Then
                sTemp.AddInteger mTemp.Name, mTemp.Value.IntegerValue
            ElseIf mTemp.Value.ValueType = XMLRPC_NIL Then
                sTemp.AddNil mTemp.Name
            End If
        Next mTemp
        Set UpdateContact = sTemp
    End If
End Function

'************************************************************************************************
' EGW_CONTACT and OUTLOOK_CONTACT are essentially two constants to clarify the code
'************************************************************************************************
Property Get OUTLOOK_CONTACT() As Boolean
    OUTLOOK_CONTACT = True
End Property

Property Get EGW_CONTACT() As Boolean
    EGW_CONTACT = False
End Property

'************************************************************************************************
' Properties and functions to deal with the Original Contact
'************************************************************************************************
Property Get OriginalContact() As Object
    Set OriginalContact = myOriginalContact
End Property

Private Property Set OriginalContact(oc As Object)
    Set myOriginalContact = oc
End Property

Property Get OriginalContactType() As Boolean
    If TypeOf myOriginalContact Is ContactItem Then
        OriginalContactType = OUTLOOK_CONTACT
    ElseIf TypeOf myOriginalContact Is XMLRPCStruct Then
        OriginalContactType = EGW_CONTACT
    Else
        OriginalContactError
    End If
End Property

Private Sub OriginalContactError()
    On Error Resume Next
    Err.Clear
    Err.Raise vbObjectError + 603, "OverwriteManager", _
                                    "Original Contact type is invalid"
    If Err.Number <> 0 Then
        MsgBox "Error # " & str(Err.Number) & " was generated by " _
                & Err.Source & Chr(13) & Err.Description, vbOKOnly, "Error"
    End If
End Sub

Property Get OriginalContactNames() As Collection
    If OriginalContactType = OUTLOOK_CONTACT Then
        Set OriginalContactNames = OutlookContactNames(OriginalContact)
    ElseIf OriginalContactType = EGW_CONTACT Then
        Set OriginalContactNames = eGWContactNames(OriginalContact)
    Else
        OriginalContactError
    End If
End Property

'************************************************************************************************
' Properties and functions to deal with the New Contact
'************************************************************************************************

Property Get NewContact() As Object
    Set NewContact = myNewContact
End Property

Private Property Set NewContact(oc As Object)
    Set myNewContact = oc
End Property

Property Get NewContactType() As Boolean
    If TypeOf myNewContact Is ContactItem Then
        NewContactType = OUTLOOK_CONTACT
    ElseIf TypeOf myNewContact Is XMLRPCStruct Then
        NewContactType = EGW_CONTACT
    Else
        NewContactError
    End If
End Property

Private Sub NewContactError()
    On Error Resume Next
    Err.Clear
    Err.Raise vbObjectError + 603, "OverwriteManager", _
                                    "New Contact type is invalid"
    If Err.Number <> 0 Then
        MsgBox "Error # " & str(Err.Number) & " was generated by " _
                & Err.Source & Chr(13) & Err.Description, vbOKOnly, "Error"
    End If
End Sub

Property Get NewContactNames() As Collection
    If NewContactType = OUTLOOK_CONTACT Then
        Set NewContactNames = OutlookContactNames(NewContact)
    ElseIf NewContactType = EGW_CONTACT Then
        Set NewContactNames = eGWContactNames(NewContact)
    Else
        NewContactError
    End If
End Property

'************************************************************************************************
' Functions to deal with the eGW and Outlook Contacts directly
'************************************************************************************************

Private Function eGWContactNames(egwContact As XMLRPCStruct) As Collection
    Set eGWContactNames = New Collection
    
    eGWContactNames.Add egwContact.GetValueByName("fn").StringValue, "FullName"
    eGWContactNames.Add egwContact.GetValueByName("n_prefix").StringValue, "Prefix"
    eGWContactNames.Add egwContact.GetValueByName("n_given").StringValue, "FirstName"
    eGWContactNames.Add egwContact.GetValueByName("n_middle").StringValue, "MiddleName"
    eGWContactNames.Add egwContact.GetValueByName("n_family").StringValue, "LastName"
    eGWContactNames.Add egwContact.GetValueByName("n_suffix").StringValue, "Suffix"
End Function

Private Function OutlookContactNames(OutlookContact As ContactItem) As Collection
    Set OutlookContactNames = New Collection
    
    OutlookContactNames.Add OutlookContact.FullName, "FullName"
    OutlookContactNames.Add OutlookContact.Title, "Prefix"
    OutlookContactNames.Add OutlookContact.FirstName, "FirstName"
    OutlookContactNames.Add OutlookContact.MiddleName, "MiddleName"
    OutlookContactNames.Add OutlookContact.LastName, "LastName"
    OutlookContactNames.Add OutlookContact.Suffix, "Suffix"
End Function
