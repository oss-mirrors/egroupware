VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "COutlookContacts"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = False
'#################################################################################################
'# COutlookContacts.cls
'# Functions specific to dealing with Outlook Contact Items in relation to eGroupWare Contacts
'#
'# Please visit egroupware.org for more information
'# This software is distributed under the GPL and is provided as-is. I assume no responsibility
'# for its usage or losses of any kind that may ensue thereof or otherwise. Feedback is nice:
'# heisters[at]0x09.com
'#################################################################################################

'***********************************************************************************************
' Put all the local contacts' fullnames from a folder into the listbox
'***********************************************************************************************
Public Sub List(fldFolder As Outlook.MAPIFolder)
    Dim objItem As Object
    
    If fldFolder.Folders.Count > 0 Then
        For Each objItem In fldFolder.Folders
            List (objItem)
        Next objItem
    End If
    
    For Each objItem In fldFolder.Items
        FrmMain.listLocal.AddItem (objItem.FullName)
    Next objItem
End Sub

'***********************************************************************************************
' Create an Outlook contact given an eGroupWare Response that holds the full information on an
' eGW contact
'***********************************************************************************************
Public Function Create(ByVal egwContact As XMLRPCStruct) As ContactItem
    Dim objContact      As ContactItem
    Dim gnspNamespace   As NameSpace
    Dim fldContacts     As Outlook.MAPIFolder
    Dim ciFindContact   As ContactItem
    Dim bWriteContact   As Boolean
    Dim objTranslator   As New CContactTranslator
    
    bWriteContact = True    'we assume that we will write a new contact, unless we hear otherwise
    Set gnspNamespace = GetNamespace("MAPI")
    Set fldContacts = gnspNamespace.GetDefaultFolder(olFolderContacts)
    Set objContact = fldContacts.Items.Add
    
    'If a contact with the same full name doesn't exist, create the new contact.
    Set ciFindContact = fldContacts.Items.Find("[FullName] = " & egwContact.GetValueByName("fn").StringValue)
    
    'Confirm Overwrite: Only do this if a contact by the same name was found
    If Not (ciFindContact Is Nothing) Then
        'Manage overwriting
        Dim OMResponse As Integer
        Dim OM As New COverwriteManager
        
        OMResponse = OM.Manage(ciFindContact, egwContact)
        If OMResponse = FrmOverwrite.OVERWRITE Then
            ciFindContact.Delete
        ElseIf OMResponse = FrmOverwrite.SKIP Then
            bWriteContact = False
        ElseIf OMResponse = FrmOverwrite.RENAME Then
            bWriteContact = False 'OM will take care of the rest.
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
