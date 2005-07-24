/***************************************************************************
                         BaseItem.cs  -  description
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

namespace OutlookSync.Items
{
    public class BaseItem : ItemFootprint
    {
        public BaseItem()
        {
            this.remoteId = "";
            this.outlookId = "";
            this.writeAccess = true;
        }

        public BaseItem(string remoteId, string outlookId, DateTime lastModRemote, DateTime lastModOutlook)
        {
            this.remoteId = remoteId;
            this.outlookId = outlookId;
            this.lastModRemote = lastModRemote;
            this.LastModOutlook = lastModOutlook;
            this.writeAccess = true;
        }

        public BaseItem(string remoteId, string outlookId, DateTime lastModRemote, DateTime lastModOutlook, bool writeAccess)
        {
            this.remoteId = remoteId;
            this.outlookId = outlookId;
            this.lastModRemote = lastModRemote;
            this.lastModOutlook = lastModOutlook;
            this.writeAccess = writeAccess;
        }

        public string OutlookId
        {
            get
            {
                return outlookId;
            }

            set
            {
                outlookId = value;
            }
        }
        public DateTime LastModOutlook
        {
            get
            {
                return lastModOutlook;
            }

            set
            {
                lastModOutlook = value;
            }
        }

        protected string outlookId;
        protected DateTime lastModOutlook;


    }
}
