VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "CeGWContacts"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = False
Public Function Create(ByVal ciContact As ContactItem, aResponses() As XMLRPCValue) As XMLRPCResponse
    Dim xmlParms        As New XMLRPCStruct
    Dim xmlArray        As New XMLRPCArray
    Dim xmlResponse     As New XMLRPCResponse
    Dim varTemp         As Variant
    Dim dblDuplicateID  As Double
    Dim DONT_WRITE      As Integer
    Dim objTranslator   As New CContactTranslator
    
    'A macro to indicate that now writing to the server should be done.
    DONT_WRITE = -1
    
    'initialize dblDuplicateID so we can later tell if there was a duplicate contact found
    dblDuplicateID = 0
    
    'Look for the local contact in the downloaded list of remote contacts
    For Each varTemp In aResponses
        'If a contact that was downloaded from the server has the same fullname
        If ciContact.FullName = varTemp.StructValue.GetValueByName("fn").StringValue Then
                'record the contact's id number
                dblDuplicateID = Val(varTemp.StructValue.GetValueByName("id").StringValue)
                'and exit the loop
                Exit For
        End If
    Next varTemp

    If dblDuplicateID <> 0 Then
        Dim x As VbMsgBoxResult
        x = MsgBox("Trying to export " & (Chr(10)) & (ciContact.FullName) & (Chr(10)) & _
                        "to the eGroupWare server, but a contact by that name already" & _
                        " exists in the remote directory." & (Chr(10)) & (Chr(10)) & _
                        "Press okay to overwrite the remote contact, cancel to skip", _
                        vbOKCancel, "Confirm Overwrite of " & (ciContact.FullName))
        If x = vbOK Then
            'No need to do anything, dblDuplicateID holds the ID to overwrite.
        ElseIf x = vbCancel Then
            Set Create = Nothing
            'Setting dblDuplicate ID to DONT_WRITE means that neither a new contact should be
            '   created nor an old one overwritten
            dblDuplicateID = DONT_WRITE
        End If
    End If
    
    If Not (dblDuplicateID = DONT_WRITE) Then
        'Create a new entry
        '   If there wasn't a duplicate found, dblDuplicateID will be 0, which indicates to eGW
        '   that it should create a new contact. If a duplicate was found, it will be overwritten.
        objTranslator.Outlook2eGW ciContact, xmlParms
        xmlParms.AddString "id", dblDuplicateID

        Set xmlResponse = SimpleExec("addressbook.boaddressbook.write", xmlParms)

        'If the write was succesfull, return the basic information on the contact
        '   The server returns a boolean if an entry was overwritten, or an integer if a new entry
        '   was made.
        If xmlResponse.params(1).ValueType = XMLRPC_INT_I4 Then
            Set xmlParms = New XMLRPCStruct
            xmlParms.AddInteger "id", xmlResponse.params(1).IntegerValue
            xmlArray.AddString "fn"
            xmlParms.AddArray "fields", xmlArray
            Set Create = SimpleExec("addressbook.boaddressbook.read", xmlParms)
        ElseIf xmlResponse.params(1).BooleanValue Then
            Set xmlParms = New XMLRPCStruct
            xmlParms.AddInteger "id", dblDuplicateID
            xmlArray.AddString "fn"
            xmlParms.AddArray "fields", xmlArray
            Set Create = SimpleExec("addressbook.boaddressbook.read", xmlParms)
        End If
        
    End If
End Function

Public Function GetFullInfoFromServer(strSelectedName As String, aResponses() As XMLRPCValue) As XMLRPCResponse
    Dim xmlParms    As New XMLRPCStruct
    Dim varTemp     As Variant
    'Now we need to go back and get the full information on the selected contacts.
    '   aResponses holds the id of each contact which we can use in a
    '   boaddressbook.read call, we just need to get the id out of aResponses.
    '   I can't think of any other way to do this than to do a loop search. It
    '   seems inefficient though.
    For Each varTemp In aResponses
    
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
