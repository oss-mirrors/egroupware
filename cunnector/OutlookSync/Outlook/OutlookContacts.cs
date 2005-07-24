/***************************************************************************
                      OutlookContacts.cs  -  description
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
using System.Collections;
using System.Collections.Generic;
using System.Text;
using System.Windows.Forms;


using Microsoft.Office.Interop.Outlook;
//using Microsoft.Office.Tools.Outlook;

using Utilities.Logging;

using OutlookSync.Items;


using MISSING = System.Reflection.Missing;

#endregion

namespace OutlookSync.Outlook
{
    public class OutlookContacts : SimpleLoggingBase
    {
        public OutlookContacts(MAPIFolder contactFolder)
            : base("OutlookContacts")
        {
            this.contactFolder = contactFolder;
            this.readOnlyContacts = new Hashtable();

            contactFolder.Items.ItemChange += new ItemsEvents_ItemChangeEventHandler(Items_ItemChange);
        }

        void Items_ItemChange(object Item)
        {
            try
            {
                ContactItem olContact = (ContactItem)Item;

                if (olContact.UserProperties[readOnlyProperty] != null)
                {
                    MessageBox.Show("Read-only contact modified.\nWill be reset at next synchronizeation.");
                }
            }
            catch { }
        }

        /// <summary>
        ///  Returns all contacts contained in Folder
        /// </summary>
        /// <returns>Hashtable of Contact objects</returns>
        public Hashtable GetContacts()
        {
            outlookContactItems = new Hashtable();
            Hashtable contacts = new Hashtable();

            foreach (ContactItem outlookContact in contactFolder.Items)
            {
                Contact contact = new Contact(outlookContact);
                outlookContactItems.Add(contact.OutlookId, outlookContact);
                contacts.Add(contact.OutlookId, contact);
            }

            return contacts;
        }


        /// <summary>
        ///  Creates a new contact in Outlook
        /// </summary>
        /// <param name="contact">Contact object corresponding to new Outlook contact</param>
        public string CreateOutlookContact(Contact newContact)
        {
            ContactItem olContact = (ContactItem)contactFolder.Items.Add(OlItemType.olContactItem);
            updateOutlookContact(olContact, newContact);
            newContact.OutlookId = olContact.EntryID;

            log.LogInfo("New outlook contact created. OutlookId: " + newContact.OutlookId);
            return newContact.OutlookId;
        }


        /// <summary>
        ///  Updates an outlook contact with information contained in contact object
        /// </summary>
        /// <param name="contact">Contact object must have set SyncId field</param>
        public bool UpdateOutlookContact(Contact contact)
        {
            ContactItem outlookContact = getContactByOutlookId(contact.OutlookId);

            if (outlookContact != null)
            {
                updateOutlookContact(outlookContact, contact);
                return true;
            }

            log.LogError("not found by outlookId: " + contact.ToString());

            return false;

        }

        public void DeleteOutlookContact(String outlookId)
        {

            ContactItem olContact = getContactByOutlookId(outlookId);
            if (olContact != null)
            {
                olContact.Delete();
                log.LogInfo("Deleted outlook contact. Id: " + outlookId);
            }
            else
            {
                log.LogWarning("Error deleting outlook contact, not found. Id: " + outlookId);
            }
            return;
        }


        //
        // Check this as template for user properties:
        //
        private string getSyncId(ContactItem outlookContact)
        {
            UserProperty syncProperty = outlookContact.UserProperties["GroupwareSyncId"];
            if (syncProperty != null) return syncProperty.Value.ToString();
            else return "";
        }

        private void setSyncId(ContactItem outlookContact, string syncId)
        {
            UserProperty syncIdProperty = outlookContact.UserProperties["GroupwareSyncId"];
            if (syncIdProperty == null)
            {
                syncIdProperty = outlookContact.UserProperties.Add("GroupwareSyncId", OlUserPropertyType.olText,
                    MISSING.Value, MISSING.Value);
            }
            syncIdProperty.Value = syncId;
            outlookContact.Save();
        }

        private ContactItem getContactByOutlookId(String outlookId)
        {
            string currentId;
            foreach (ContactItem outlookContact in contactFolder.Items)
            {
                currentId = outlookContact.EntryID;
                if (currentId.Equals(outlookId))
                    return outlookContact;
            }

            return null;
        }


        private void updateOutlookContact(ContactItem outlookContact, Contact contact)
        {
            //            outlookContact.LastModificationTime = contact.LastModified;

            #region Set contact fields...
            outlookContact.BusinessAddressCity = contact.BusinessAddressCity;
            outlookContact.BusinessAddressCountry = contact.BusinessAddressCountry;
            outlookContact.BusinessAddressPostalCode = contact.BusinessAddressPostalCode;
            outlookContact.BusinessAddressStreet = contact.BusinessAddressStreet;
            outlookContact.BusinessTelephoneNumber = contact.BusinessTelephoneNumber;
            outlookContact.CarTelephoneNumber = contact.CarTelephoneNumber;
            outlookContact.CompanyName = contact.CompanyName;
            outlookContact.Department = contact.Department;
            outlookContact.FirstName = contact.FirstName;
            outlookContact.HomeAddressCity = contact.HomeAddressCity;
            outlookContact.HomeAddressCountry = contact.HomeAddressCountry;
            outlookContact.HomeAddressPostalCode = contact.HomeAddressPostalCode;
            outlookContact.HomeAddressState = contact.HomeAddressState;
            outlookContact.HomeAddressStreet = contact.HomeAddressStreet;
            outlookContact.HomeTelephoneNumber = contact.HomeTelephoneNumber;
            outlookContact.LastName = contact.LastName;
            outlookContact.MiddleName = contact.MiddleName;
            outlookContact.MobileTelephoneNumber = contact.MobileTelephoneNumber;
            outlookContact.OtherTelephoneNumber = contact.OtherTelephoneNumber;
            outlookContact.Suffix = contact.Suffix;
            outlookContact.Title = contact.Title;
            outlookContact.WebPage = contact.WebPage;
            outlookContact.Birthday = contact.Birthday;
            // if user denies security risc access an exception will be thrown
            try
            {
                outlookContact.Email1Address = contact.BusinessEmailAddress;
                outlookContact.Email2Address = contact.HomeEmailAddress;
                outlookContact.Body = contact.Body;
            }
            catch
            {
                log.LogWarning("User denied access to email-fields!");
            }


            #endregion

            if (!contact.WriteAccess)
            {
               log.LogWarning("Attempting to create read-only Contact!");
               
               outlookContact.FileAs = "[ReadOnly] " + outlookContact.FileAs;

               outlookContact.UserProperties.Add(readOnlyProperty, OlUserPropertyType.olYesNo, MISSING.Value, MISSING.Value);
               outlookContact.UserProperties[readOnlyProperty].Value = true;
           }

            outlookContact.Save();

            log.LogInfo("updated: " + contact.ToString());
        }

        private Hashtable outlookContactItems;
        private Hashtable readOnlyContacts;
        private MAPIFolder contactFolder;

        private const string readOnlyProperty = "READONLY";


void  Items_ItemRemove()
{
 	throw new NotImplementedException();
}
}
}
