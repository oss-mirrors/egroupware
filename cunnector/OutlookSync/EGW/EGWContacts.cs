/***************************************************************************
                        EGWContacts.cs  -  description
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
using System.Text;

using CookComputing.XmlRpc;
using Utilities.Logging;

using OutlookSync.Items;
using OutlookSync.Remote;



using Proxy = OutlookSync.EGW.AddressbookProxy;
using ContactFields = OutlookSync.EGW.AddressbookProxy.ContactFieldTypes;

#endregion

namespace OutlookSync.EGW
{
    public class EGWContacts : SimpleLoggingBase
    {
        public EGWContacts(RemoteSessionData sessionData) : base("EGWContacts")
        {
            addressbook = new Proxy(sessionData);
        }

        public EGWContacts(string url, SessionType session) : base("EGWContacts")
        {
            addressbook = new Proxy(url, session);
        }

        public Hashtable  GetFootprints()
        {
            XmlRpcStruct[] searchResult;
            XmlRpcStruct searchParams = new XmlRpcStruct();
            searchParams.Add(Proxy.ParamReadFields, addressbookFootprintFields);

            Hashtable hash = new Hashtable();

            try
            {
                searchResult = addressbook.Search(searchParams);
            }
            catch (XmlRpcFaultException ex)
            {
                log.LogError("Error searching egw-addressbook! FaultCode: " + ex.FaultCode);
                return hash;
            }

            if (searchResult == null)
            {
                log.LogWarning("No contacts found on remote server");
                return hash;
            }

            foreach (XmlRpcStruct var in searchResult)
                {
                    String contactId = var[ContactFields.id].ToString();
                    DateTime lastMod = (DateTime)var[ContactFields.lastModified];
                    int access = int.Parse((string)var[ContactFields.rights]);

                    ItemFootprint contact = new ItemFootprint(contactId, lastMod);

                    if ((access < 0) ||                 // own item
                        (((access & 4)>0) && ((access & 8)>0)))  // write & delete 
                        contact.WriteAccess = true;
                    else contact.WriteAccess = false;

                    hash.Add(contactId, contact);
                }

            return hash;
        }


        public Contact GetContact(ItemFootprint footprint)
        {
            Contact contact = GetContact(footprint.RemoteId);
            contact.LastModRemote = footprint.LastModRemote;
            contact.WriteAccess = footprint.WriteAccess;
            return contact;
        }

        public Contact GetContact(string id)
        {
            int intId;

            try
            {
                intId = int.Parse(id);
            }
            catch (System.FormatException)
            {
                // id should be integer -> invalid, return null 
                return null;
            }

            // read all text fields for contact
            XmlRpcStruct[] readTextFieldsResult;
            XmlRpcStruct parameters = new XmlRpcStruct();
            parameters.Add(Proxy.ParamReadId, intId);
            parameters.Add(Proxy.ParamReadFields, addressbookTextFields);
            try
            {
                readTextFieldsResult = addressbook.Read(parameters);
            }
            catch (XmlRpcFaultException ex)
            {
                log.LogWarning("Error reading Contact. Doesn't exist?. FaultCode: " + ex.FaultCode);

                // id doesn't exist or restricted, return null
                return null;
            }

            // read date-time field birthday seperately as not set will lead to exception
            XmlRpcStruct[] readBirthdayResult = null;
            parameters = new XmlRpcStruct();
            parameters.Add(Proxy.ParamReadId, intId);
            parameters.Add(Proxy.ParamReadFields, addressbookBirthdayField);
            try
            {
                readBirthdayResult = addressbook.Read(parameters);
            }
            catch (XmlRpcInvalidXmlRpcException)
            {
                // no birthday set-> readBirthdayResult stays null, nothing to do
            }

            XmlRpcStruct contactTextFields = readTextFieldsResult[0];
            XmlRpcStruct contactBirthdayField = (readBirthdayResult != null) ? readBirthdayResult[0] : null;


            Contact contact = structToContact(contactTextFields, contactBirthdayField);
            contact.RemoteId = id;

            return contact;
          }

        public string CreateContact(Contact contact)
        {
                XmlRpcStruct newContact = contactToStruct(contact);
                newContact.Add(Proxy.ParamWriteId, Proxy.ParamWriteCreateId);
                string newId = (string)addressbook.Write(newContact);
                contact.RemoteId = newId;
                return newId;
        }

        public bool UpdateContact(Contact contact)
        {
            int intId;
            try
            {
                intId = int.Parse(contact.RemoteId);
            }
            catch (System.FormatException)
            {
                // id should be integer -> invalid, return false 
                return false;
            }

            try
            {
                XmlRpcStruct updatedContact = contactToStruct(contact);
                updatedContact.Add(Proxy.ParamWriteId, intId);
                addressbook.Write(updatedContact);
                return true;
            }
            catch (XmlRpcFaultException ex)
            {
                log.LogError("Error updating contact. Doesn't exist? FaultCode: " + ex.FaultCode);
                return false;
            }
        }


        public void DeleteContact(string id)
        {
            int intId;

            try
            {
                intId = int.Parse(id);
            }
            catch (System.FormatException)
            {
                // id should be integer -> invalid, return null 
                log.LogError("Invalid id, should be number: " + id);
                return;
            }

            try
            {
                addressbook.Delete(intId);
            }
            catch (XmlRpcFaultException faultException)
            {
                log.LogWarning("Error deleting. Already removed? FaultCode: " + faultException.FaultCode.ToString());
            } 
            log.LogInfo("Deleted remote Contact. Id: " + id);
        }



        private Contact structToContact(XmlRpcStruct textFields, XmlRpcStruct birthdayField)
        {
            Contact contact = new Contact();

            contact.BusinessAddressCity = reformat( (string)textFields[ContactFields.addressOneLocality] );
            contact.BusinessAddressCountry = reformat( (string)textFields[ContactFields.addressOneCountryname] );
            contact.BusinessAddressPostalCode = reformat( (string)textFields[ContactFields.addressOnePostalcode] );
            contact.BusinessAddressState = reformat( (string)textFields[ContactFields.addressOneRegion] );
            contact.BusinessAddressStreet = reformat( (string)textFields[ContactFields.addressOneStreet] );
            contact.BusinessTelephoneNumber = reformat( (string)textFields[ContactFields.telephoneWork] );
            contact.CarTelephoneNumber = reformat( (string)textFields[ContactFields.telephoneCar] );
            contact.CompanyName = reformat( (string)textFields[ContactFields.organisationName] );
            contact.Department = reformat( (string)textFields[ContactFields.organisationUnit] );
            contact.FirstName = reformat( (string)textFields[ContactFields.givenName] );
            contact.HomeAddressCity = reformat( (string)textFields[ContactFields.addressTwoLocality] );
            contact.HomeAddressCountry = reformat( (string)textFields[ContactFields.addressTwoCountryname] );
            contact.HomeAddressPostalCode = reformat( (string)textFields[ContactFields.addressTwoPostalcode] );
            contact.HomeAddressState = reformat( (string)textFields[ContactFields.addressTwoRegion] );
            contact.HomeAddressStreet = reformat( (string)textFields[ContactFields.addressTwoStreet] );
            contact.HomeTelephoneNumber = reformat( (string)textFields[ContactFields.telephoneHome] );
            contact.LastName = reformat( (string)textFields[ContactFields.familyName] );
            contact.MiddleName = reformat( (string)textFields[ContactFields.middleName] );
            contact.MobileTelephoneNumber = reformat( (string)textFields[ContactFields.telephoneCell] );
            contact.OtherTelephoneNumber = reformat( (string)textFields[ContactFields.telephoneOther] );
            contact.Suffix = reformat( (string)textFields[ContactFields.nameSuffix] );
            contact.Title = reformat( (string)textFields[ContactFields.title] );
            contact.WebPage = reformat( (string)textFields[ContactFields.url] );
            contact.Body = reformat((string)textFields[ContactFields.notes]);

            if (birthdayField != null)
                contact.Birthday = (DateTime)birthdayField[ContactFields.birthday];
            else
                contact.Birthday = Contact.NO_DATE_SET;

            // these fields will cause problems with microsoft security guidelines if calles from
            // outside a plugin: surround with appropriate try catch statement later :
            
            contact.BusinessEmailAddress = (string)textFields[ContactFields.email];
            contact.HomeEmailAddress = (string)textFields[ContactFields.emailHome];

            return contact;
        }

        private string reformat(string temp)
        {
            return EGW.HTMLReformater.HtmlToPlain(temp);
        }

        private XmlRpcStruct contactToStruct(Contact contact)
        {

            XmlRpcStruct contactStruct = new XmlRpcStruct();

            // fullName is not generated automatically. Need to generate and send along 
            // with other fields:
            string fullName = "";
            if (contact.Title != null)           fullName += contact.Title + " ";
            if (contact.FirstName != null)       fullName += contact.FirstName + " ";
            if (contact.MiddleName != null)      fullName += contact.MiddleName + " ";
            if (contact.LastName != null)        fullName += contact.LastName + " ";
            if (contact.Suffix != null)          fullName += contact.Suffix;

            contactStruct.Add(ContactFields.fullName, fullName);

            if ((contact.Birthday != null) && (contact.Birthday != Contact.NO_DATE_SET))
                contactStruct.Add(ContactFields.birthday, contact.Birthday);
            else contactStruct.Add(ContactFields.birthday, "");

            if (contact.Title != null)
                contactStruct.Add(ContactFields.title, contact.Title);
            else contactStruct.Add(ContactFields.title, "");

            if (contact.FirstName != null)
                contactStruct.Add(ContactFields.givenName, contact.FirstName);
            else contactStruct.Add(ContactFields.givenName, "");

            if (contact.MiddleName != null)
                contactStruct.Add(ContactFields.middleName, contact.MiddleName);
            else contactStruct.Add(ContactFields.middleName, "");

            if (contact.LastName != null)
                contactStruct.Add(ContactFields.familyName, contact.LastName);
            else contactStruct.Add(ContactFields.familyName, "");

            if (contact.Suffix != null)
                contactStruct.Add(ContactFields.nameSuffix, contact.Suffix);
            else contactStruct.Add(ContactFields.nameSuffix, "");

            if (contact.BusinessAddressCity != null)
                contactStruct.Add(ContactFields.addressOneLocality, contact.BusinessAddressCity);
            else contactStruct.Add(ContactFields.addressOneLocality, "");

            if (contact.BusinessAddressCountry != null)
                contactStruct.Add(ContactFields.addressOneCountryname, contact.BusinessAddressCountry);
            else contactStruct.Add(ContactFields.addressOneCountryname, "");

            if (contact.BusinessAddressPostalCode != null)
                contactStruct.Add(ContactFields.addressOnePostalcode, contact.BusinessAddressPostalCode);
            else contactStruct.Add(ContactFields.addressOnePostalcode, "");

            if (contact.BusinessAddressState != null)
                contactStruct.Add(ContactFields.addressOneRegion, contact.BusinessAddressState);
            else contactStruct.Add(ContactFields.addressOneRegion, "");

            if (contact.BusinessAddressStreet != null)
                contactStruct.Add(ContactFields.addressOneStreet, contact.BusinessAddressStreet);
            else contactStruct.Add(ContactFields.addressOneStreet, "");

            if (contact.BusinessTelephoneNumber != null)
                contactStruct.Add(ContactFields.telephoneWork, contact.BusinessTelephoneNumber);
            else contactStruct.Add(ContactFields.telephoneWork, "");

            if (contact.CarTelephoneNumber != null)
                contactStruct.Add(ContactFields.telephoneCar, contact.CarTelephoneNumber);
            else contactStruct.Add(ContactFields.telephoneCar, "");

            if (contact.CompanyName != null)
                contactStruct.Add(ContactFields.organisationName, contact.CompanyName);
            else contactStruct.Add(ContactFields.organisationName, "");

            if (contact.Department != null)
                contactStruct.Add(ContactFields.organisationUnit, contact.Department);
            else contactStruct.Add(ContactFields.organisationUnit, "");

            if (contact.HomeAddressCity != null)
                contactStruct.Add(ContactFields.addressTwoLocality, contact.HomeAddressCity);
            else contactStruct.Add(ContactFields.addressTwoLocality, "");

            if (contact.HomeAddressCountry != null)
                contactStruct.Add(ContactFields.addressTwoCountryname, contact.HomeAddressCountry);
            else contactStruct.Add(ContactFields.addressTwoCountryname, "");

            if (contact.HomeAddressPostalCode != null)
                contactStruct.Add(ContactFields.addressTwoPostalcode, contact.HomeAddressPostalCode);
            else contactStruct.Add(ContactFields.addressTwoPostalcode, "");

            if (contact.HomeAddressState != null)
                contactStruct.Add(ContactFields.addressTwoRegion, contact.HomeAddressState);
            else contactStruct.Add(ContactFields.addressTwoRegion, "");

            if (contact.HomeAddressStreet != null)
                contactStruct.Add(ContactFields.addressTwoStreet, contact.HomeAddressStreet);
            else contactStruct.Add(ContactFields.addressTwoStreet, "");

            if (contact.HomeTelephoneNumber != null)
                contactStruct.Add(ContactFields.telephoneHome, contact.HomeTelephoneNumber);
            else contactStruct.Add(ContactFields.telephoneHome, "");

            if (contact.MobileTelephoneNumber != null)
                contactStruct.Add(ContactFields.telephoneCell, contact.MobileTelephoneNumber);
            else contactStruct.Add(ContactFields.telephoneCell, "");

            if (contact.OtherTelephoneNumber != null)
                contactStruct.Add(ContactFields.telephoneOther, contact.OtherTelephoneNumber);
            else contactStruct.Add(ContactFields.telephoneOther, "");

            if (contact.WebPage!= null)
                contactStruct.Add(ContactFields.url, contact.WebPage);
            else contactStruct.Add(ContactFields.url, "");

            if (contact.BusinessEmailAddress != null)
                contactStruct.Add(ContactFields.email, contact.BusinessEmailAddress);
            else contactStruct.Add(ContactFields.email, "");

            if (contact.HomeEmailAddress != null)
                contactStruct.Add(ContactFields.emailHome, contact.HomeEmailAddress);
            else contactStruct.Add(ContactFields.emailHome, "");

            if (contact.Body != null)
                contactStruct.Add(ContactFields.notes, contact.Body);
            else contactStruct.Add(ContactFields.notes, "");

            return contactStruct;
        }

        private string[] addressbookFootprintFields =
            { 
                ContactFields.id,
                ContactFields.lastModified,
                ContactFields.rights
            };

        private string[] addressbookBirthdayField =
            { ContactFields.birthday };

        private string[] addressbookTextFields =
            {
                ContactFields.addressOneLocality,       // businessAddressCity
                ContactFields.addressOneCountryname,    // businessAddressCountry
                ContactFields.addressOnePostalcode,     // businessAddressPostalcode
                ContactFields.addressOneRegion,         // businessAddressState
                ContactFields.addressOneStreet,         // businessAddressStreet
                ContactFields.telephoneWork,            // businessTelephoneNumber
                ContactFields.telephoneCar,             // carTelephoneNumber
                ContactFields.organisationName,         // companyName
                ContactFields.organisationUnit,         // department
                ContactFields.email,                    // businessEmailAddress
                ContactFields.givenName,                // firstName
                ContactFields.addressTwoLocality,       // homeAddressCity
                ContactFields.addressTwoCountryname,    // homeAddressCountry
                ContactFields.addressTwoPostalcode,     // homeAddressPostalcode
                ContactFields.addressTwoRegion,         // homeAddressState
                ContactFields.addressTwoStreet,         // homeAddressStreet
                ContactFields.telephoneHome,            // homeTelephoneNumber
                ContactFields.familyName,               // lastName
                ContactFields.middleName,               // middleName
                ContactFields.telephoneCell,            // mobileTelephoneNumber
                ContactFields.telephoneOther,           // otherTelephoneNumber
                ContactFields.nameSuffix,               // suffix
                ContactFields.title,                    // title
                ContactFields.url,                       // webPage
                ContactFields.notes
         };

        private AddressbookProxy addressbook;

    }
}

