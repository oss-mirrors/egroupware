VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "CeGWContacts"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = False
'#################################################################################################
'# CeGWContacts.cls
'# Functions specific to dealing with eGroupWare Contacts in relation to Outlook Contact Items
'#
'# Please visit egroupware.org for more information
'# This software is distributed under the GPL and is provided as-is. I assume no responsibility
'# for its usage or losses of any kind that may ensue thereof or otherwise. Feedback is nice:
'# heisters[at]0x09.com
'#################################################################################################

Public CursoryInfo As New Collection

'***********************************************************************************************
' Create a contact on the eGroupWare server given a corresponding outlook contact and an array
' that holds cursory information on all the eGW contacts.
'***********************************************************************************************
Public Function Create(ByVal ciContact As ContactItem) As XMLRPCResponse
    Dim xmlParms        As New XMLRPCStruct
    Dim xmlArray        As New XMLRPCArray
    Dim xmlResponse     As New XMLRPCResponse
    Dim varTemp         As XMLRPCValue
    Dim dblDuplicateID  As Double
    Dim DONT_WRITE      As Integer
    Dim objTranslator   As New CContactTranslator

    'A macro to indicate that now writing to the server should be done.
    DONT_WRITE = -1
    
    'initialize dblDuplicateID so we can later tell if there was a duplicate contact found
    dblDuplicateID = 0
    
    'Look for the local contact in the downloaded list of remote contacts
    If CursoryInfo.Count > 0 Then
        For Each varTemp In CursoryInfo
            'If a contact that was downloaded from the server has the same fullname
            If ciContact.FullName = varTemp.StructValue.GetValueByName("fn").StringValue Then
                'record the contact's id number
                dblDuplicateID = Val(varTemp.StructValue.GetValueByName("id").StringValue)
                'Grab the full information on the original contact
                Set xmlResponse = GetFullInfoFromServer(varTemp.StructValue.GetValueByName("fn").StringValue)
                'and exit the loop
                Exit For
            End If
        Next varTemp
    End If
    
    'Do something if a duplicate was found
    If dblDuplicateID <> 0 Then
        Dim OMResponse As Integer
        Dim OM As New COverwriteManager
        OMResponse = OM.Manage(xmlResponse.Params(1).ArrayValue(1).StructValue, ciContact)
        
        If OMResponse = FrmOverwrite.OVERWRITE Then
            'No need to do anything, dblDuplicateID holds the ID to overwrite
        ElseIf OMResponse = FrmOverwrite.SKIP Then
            Set Create = Nothing
            'Setting dblDuplicate ID to DONT_WRITE means that neither a new contact should be
            'created nor an old one overwritten
            dblDuplicateID = DONT_WRITE
        ElseIf OMResponse = FrmOverwrite.RENAME Then
            'Be sure it won't write a new one, OM will take care of the rest
            dblDuplicateID = DONT_WRITE
        End If
        
        'Reset xmlResponse
        Set xmlResponse = New XMLRPCResponse
    End If

    If dblDuplicateID <> DONT_WRITE Then
        '[ > Create a new entry
        '[ If there wasn't a duplicate found, dblDuplicateID will be 0, which indicates to eGW
        '[ that it should create a new contact. If a duplicate was found, it will be overwritten.
        objTranslator.Outlook2eGW ciContact, xmlParms
        xmlParms.AddString "id", dblDuplicateID

        Set xmlResponse = SimpleExec("addressbook.boaddressbook.write", xmlParms)

        '[ > If the write was succesful, get and return the cursory information on the contact
        '[ The server returns a boolean if an entry was overwritten, or an integer if a new entry
        '[ was made.
        If xmlResponse.Params(1).ValueType = XMLRPC_INT_I4 Or _
           xmlResponse.Params(1).ValueType = XMLRPC_BOOLEAN Then
            Set xmlParms = New XMLRPCStruct
            If xmlResponse.Params(1).ValueType = XMLRPC_INT_I4 Then
                xmlParms.AddInteger "id", xmlResponse.Params(1).IntegerValue
            ElseIf xmlResponse.Params(1).ValueType = XMLRPC_BOOLEAN And _
                xmlResponse.Params(1).BooleanValue Then
                    xmlParms.AddInteger "id", dblDuplicateID
            End If
            xmlArray.AddString "fn"
            xmlParms.AddArray "fields", xmlArray
            Set Create = SimpleExec("addressbook.boaddressbook.read", xmlParms)
        End If
    End If
End Function

'***********************************************************************************************
' Retrieve all the fields of an eGW contact from the server given a string that is the full name
' of the contact and an array that holds cursory information on all the eGW contacts
'***********************************************************************************************
Public Function GetFullInfoFromServer(strSelectedName As String) As XMLRPCResponse
    Debug.Print "Getting full information on " & strSelectedName & " from the server"
    Dim xmlParms    As New XMLRPCStruct
    Dim varTemp     As XMLRPCValue
    'Now we need to go back and get the full information on the selected contacts.
    '   arrResponses holds the id of each contact which we can use in a
    '   boaddressbook.read call, we just need to get the id out of arrResponses.
    '   I can't think of any other way to do this than to do a loop search. It
    '   seems inefficient though.
    For Each varTemp In CursoryInfo
    
        'For every selected item from the listbox, cycle through the responses from the
        '   server. If a fullname property is the same as the selected item...
        If (strSelectedName = varTemp.StructValue.GetValueByName("fn").StringValue) Then

            'Get the full information on this contact from the server. The ID needs to be
            '   converted to an Integer.
            xmlParms.AddInteger "id", Val(varTemp.StructValue.GetValueByName("id").StringValue)
            Set GetFullInfoFromServer = BasUtilities.SimpleExec("addressbook.boaddressbook.read", xmlParms)
            'Break out of the loop since we've found our man.
            Exit For
            
        End If
    Next varTemp
End Function
