VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "COutlookContacts"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = False
'Put all the local contacts' fullnames from a folder into the listbox
Public Sub List(fldFolder As Outlook.MAPIFolder)
    Dim objItem As Object
    
    If fldFolder.Folders.Count > 0 Then
        For Each objItem In fldFolder.Folders
            List (objItem)
        Next objItem
    End If
    
    For Each objItem In fldFolder.Items
        frmMain.listLocal.AddItem (objItem.FullName)
    Next objItem
End Sub

Public Function Create(ByVal rContact As XMLRPCResponse) As ContactItem
    Dim objContact      As ContactItem
    Dim egwContact      As New XMLRPCStruct
    Dim gnspNameSpace   As NameSpace
    Dim fldContacts     As Outlook.MAPIFolder
    Dim ciFindContact   As ContactItem
    Dim bWriteContact   As Boolean
    Dim objTranslator   As New CContactTranslator
    
    bWriteContact = True    'we assume that we will write a new contact, unless we hear otherwise
    Set gnspNameSpace = GetNamespace("MAPI")
    Set fldContacts = gnspNameSpace.GetDefaultFolder(olFolderContacts)
    Set objContact = fldContacts.Items.Add
    Set egwContact = rContact.params(1).ArrayValue(1).StructValue
    
    'If a contact with the same full name doesn't exist, create the new contact.
    '   Perhaps there should be a more sophisticated method of finding *similar* contacts as well,
    '   but I'll leave that for the future.
    Set ciFindContact = fldContacts.Items.Find("[FullName] = " & egwContact.GetValueByName("fn").StringValue)
    
    'Confirm Overwrite: Only do this if a contact by the same name was found
    If Not (ciFindContact Is Nothing) Then
        'Display a message box to confirm an overwrite.
        Dim x As VbMsgBoxResult
        x = MsgBox("Trying to import " & (Chr(10)) & (egwContact.GetValueByName("fn").StringValue) & (Chr(10)) & _
                    "from the eGroupWare server, but a contact by that name already" & _
                    " exists in the local directory." & (Chr(10)) & (Chr(10)) & _
                    "Press okay to overwrite the local contact, cancel to skip", _
                    vbOKCancel, "Confirm Overwrite of " & (egwContact.GetValueByName("fn").StringValue))

        'If the user chose to overwrite then go ahead and do the writing over
        If x = vbOK Then
            'First delete the current copy to make space for the new one
            ciFindContact.Delete
            'allow writing
            bWriteContact = True
        'If the user chose to skip, exit this function
        ElseIf x = vbCancel Then
            'disallow writing
            bWriteContact = False
            Set Create = Nothing
        End If
    End If

    'If there wasn't an identical contact already, or the user chose to overwrite
    If bWriteContact Then
        objTranslator.eGW2Outlook egwContact, objContact
        
        'Finalize the changes.
        Set Create = objContact
        objContact.Close (olSave)
    End If
End Function

