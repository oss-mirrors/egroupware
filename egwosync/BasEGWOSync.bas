Attribute VB_Name = "BasEGWOSync"
'#################################################################################################
'# BasUtilities.bas
'# Various multipurpose functions for the egwosync module of eGroupWare
'#
'# Please visit egroupware.org for more information
'# This software is distributed under the GPL and is provided as-is. I assume no responsibility
'# for its usage or losses of any kind that may ensue thereof or otherwise. Feedback is nice:
'# heisters[at]0x09.com
'#################################################################################################

'***********************************************************************************************
' This is the bad boy that gets the ball rolling.
'***********************************************************************************************
Sub eGWSynchronize()
    'load previous settings
    frmMain.LoadSettings
    'open the GUI
    frmMain.Show
End Sub
