/***************************************************************************
                     RemoteSessionData.cs  -  description
                             -------------------
    copyright            : (C) 2005 by credativ GmbH, Germany
    email                : cunnector-dev@credativ.org
 ***************************************************************************/

/***************************************************************************
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

#region Using directives

using System;
using System.Collections.Generic;
using System.Text;

#endregion

namespace OutlookSync.Remote
{
    public class RemoteSessionData
    {
        public RemoteSessionData()
        {
            searchDaysBefore = 7;
            searchDaysAfter  = 30;
        }

        public new string ToString()
        {
            return "( " + userName +
                   ":" + userPassword +
                   "@" + serverUrl +
                   ")";
        }

        public string userName;
        public string userPassword;

        public string serverUrl;
        public string domain;

        public string sessionid;
        public string kp3;


        public double searchDaysBefore;
        public double searchDaysAfter;
    }
}
