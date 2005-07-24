/***************************************************************************
                        SyncStatus.cs  -  description
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

namespace OutlookSync
{
    public class SyncStatus
    {
        public SyncStatus()
        {

        }

        public bool IsSyncRunning;
        public bool IsAutoSyncing;
        public bool SyncError;

        public DateTime LastSyncedTime;

        public int AutoSyncInterval;

        public int NumSyncedItems;
        public int NumCorrectedReadOnlyItems;
        public int NumRemovedItemsLocal;
        public int NumRemovedItemsRemote;
        public int NumCreatedItemsLocal;
        public int NumCreatedItemsRemote;
        public int NumModifiedItemsLocal;
        public int NumModifiedItemsRemote;
        public int NumConflicts;

        public int SyncPercentage;
        public string CurrentOperation;

        public void ResetItemCounts()
        {
            NumSyncedItems = 0;
            NumCorrectedReadOnlyItems = 0;
            NumRemovedItemsLocal = 0;
            NumRemovedItemsRemote = 0;
            NumCreatedItemsLocal = 0;
            NumCreatedItemsRemote = 0;
            NumModifiedItemsLocal = 0;
            NumModifiedItemsRemote = 0;
            NumConflicts = 0;
        }

    }
}
