/***************************************************************************
                       ItemFootprint.cs  -  description
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
    public class ItemFootprint
    {
        public ItemFootprint()
        {
            this.remoteId = "";
            this.writeAccess = true;
        }


        public ItemFootprint(string id, DateTime lastMod)
        {
            this.remoteId = id;
            this.lastModRemote = lastMod;
            this.writeAccess = true;
        }

        public ItemFootprint(string id, DateTime lastMod, bool writeAccess)
        {
            this.remoteId = id;
            this.lastModRemote = lastMod;
            this.writeAccess = writeAccess;
        }

        public string RemoteId
        {
            get
            {
                return remoteId;
            }

            set
            {
                remoteId = value;
            }
        }
        public DateTime LastModRemote
        {
            get
            {
                return lastModRemote;
            }

            set
            {
                lastModRemote = value;
            }
        }
        public bool WriteAccess
        {
            get
            {
                return writeAccess;
            }

            set
            {
                writeAccess = value;
            }
        }


        protected DateTime lastModRemote;
        protected string remoteId;
        protected bool writeAccess;
    }
}
