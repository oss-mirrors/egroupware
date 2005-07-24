/***************************************************************************
                      IRemoteInterface.cs  -  description
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

using System;
using System.Collections;

namespace OutlookSync.Remote
{
    interface IRemoteInterface
    {
        //
        // Classes implementing IRemoteInterface need not implemented 
        // both GetXXXFootprints() and GetXXX(). One is sufficient, the
        // other should return null.
        //


        //
        // System Methods
        //

        bool Connect(RemoteSessionData sessionData);
        bool Disconnect();


        //
        // Appointment Methods
        //

        void CreateAppointments(ICollection newAppointments);
        void DeleteAppointments(ICollection deadAppointments);
        Hashtable GetAppointmentFootprints();
        Hashtable GetAppointments();
        ArrayList GetSelectedAppointments(ICollection footprints);
        void UpdateAppointments(ICollection changedAppointments);


        //
        // Contact Methods
        //

        void CreateContacts(ICollection newContacts);
        void DeleteContacts(ICollection deadContacts);
        Hashtable GetContactFootprints();
        Hashtable GetContacts();
        ArrayList GetSelectedContacts(ICollection footprints);
        void UpdateContacts(ICollection changedContacts);


        //
        // Task Methods
        //

        void CreateTasks(ICollection newTasks);
        void DeleteTasks(ICollection deadTasks);
        ArrayList GetSelectedTasks(ICollection tasks);
        Hashtable GetTaskFootprints();
        Hashtable GetTasks();
        void UpdateTasks(ICollection changedTasks);
    }
}
