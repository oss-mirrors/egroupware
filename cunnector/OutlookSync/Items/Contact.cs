/***************************************************************************
                         Contact.cs  -  description
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

using Microsoft.Office.Interop.Outlook;
//using Microsoft.Office.Tools.Outlook;

#endregion

namespace OutlookSync.Items
{
    public class Contact : BaseItem
    {
        public static readonly DateTime NO_DATE_SET = new DateTime(4501, 1, 1);

        public Contact()
        {
        }

        public Contact(ContactItem outlookContact)
        {
            this.outlookId = outlookContact.EntryID;
//            this.lastModified = outlookContact.LastModificationTime.ToUniversalTime();
            this.lastModOutlook = outlookContact.LastModificationTime.ToUniversalTime();

            #region Set contact fields ...
            this.birthday = outlookContact.Birthday;
            this.businessAddressCity = outlookContact.BusinessAddressCity;
            this.businessAddressCountry = outlookContact.BusinessAddressCountry;
            this.businessAddressPostalCode = outlookContact.BusinessAddressPostalCode;
            this.businessAddressStreet = outlookContact.BusinessAddressStreet;
            this.businessTelephoneNumber = outlookContact.BusinessTelephoneNumber;
            this.carTelephoneNumber = outlookContact.CarTelephoneNumber;
            this.companyName = outlookContact.CompanyName;
            this.department = outlookContact.Department;
            this.firstName = outlookContact.FirstName;
            this.homeAddressCity = outlookContact.HomeAddressCity;
            this.homeAddressCountry = outlookContact.HomeAddressCountry;
            this.homeAddressPostalCode = outlookContact.HomeAddressPostalCode;
            this.homeAddressState = outlookContact.HomeAddressState;
            this.homeAddressStreet = outlookContact.HomeAddressStreet;
            this.homeTelephoneNumber = outlookContact.HomeTelephoneNumber;
            this.lastName = outlookContact.LastName;
            this.middleName = outlookContact.MiddleName;
            this.mobileTelephoneNumber = outlookContact.MobileTelephoneNumber;
            this.otherTelephoneNumber = outlookContact.OtherTelephoneNumber;
            this.suffix = outlookContact.Suffix;
            this.title = outlookContact.Title;
            this.webPage = outlookContact.WebPage;

            // user may block access if not called from plugin
            try
            {
                this.businessEmailAddress = outlookContact.Email1Address;
                this.homeEmailAddress = outlookContact.Email2Address;
                this.body = outlookContact.Body;

            }
            catch (System.Exception)
            {
               this.businessEmailAddress = "";
               this.homeEmailAddress = "";
               this.body = "";
           }
            #endregion

        }

        public new string ToString()
        {
            return firstName + " " + lastName + " (" + remoteId + ")";
        }


        #region Getters, Setters ...


        public DateTime Birthday
        {
            get
            {
                return birthday;
            }

            set
            {
                birthday = value;
            }
        }
        public string BusinessAddressCity
        {
            get
            {
                return businessAddressCity;
            }

            set
            {
                businessAddressCity = value;
            }
        }
        public string BusinessAddressCountry
        {
            get
            {
                return businessAddressCountry;
            }

            set
            {
                businessAddressCountry = value;
            }
        }
        public string BusinessAddressPostalCode
        {
            get
            {
                return businessAddressPostalCode;
            }

            set
            {
                businessAddressPostalCode = value;
            }
        }
        public string BusinessAddressState
        {
            get
            {
                return businessAddressState;
            }

            set
            {
                businessAddressState = value;
            }
        }
        public string BusinessAddressStreet
        {
            get
            {
                return businessAddressStreet;
            }

            set
            {
                businessAddressStreet = value;
            }
        }
        public string BusinessTelephoneNumber
        {
            get
            {
                return businessTelephoneNumber;
            }

            set
            {
                businessTelephoneNumber = value;
            }
        }
        public string CarTelephoneNumber
        {
            get
            {
                return carTelephoneNumber;
            }

            set
            {
                carTelephoneNumber = value;
            }
        }
        public string CompanyName
        {
            get
            {
                return companyName;
            }

            set
            {
                companyName = value;
            }
        }
        public string Department
        {
            get
            {
                return department;
            }

            set
            {
                department = value;
            }
        }
        public string BusinessEmailAddress
        {
            get
            {
                return businessEmailAddress;
            }

            set
            {
                businessEmailAddress = value;
            }
        }
        public string HomeEmailAddress
        {
            get
            {
                return homeEmailAddress;
            }

            set
            {
                homeEmailAddress = value;
            }
        }
        public string FirstName
        {
            get
            {
                return firstName;
            }

            set
            {
                firstName = value;
            }
        }
        public string HomeAddressCity
        {
            get
            {
                return homeAddressCity;
            }

            set
            {
                homeAddressCity = value;
            }
        }
        public string HomeAddressCountry
        {
            get
            {
                return homeAddressCountry;
            }

            set
            {
                homeAddressCountry = value;
            }
        }
        public string HomeAddressPostalCode
        {
            get
            {
                return homeAddressPostalCode;
            }

            set
            {
                homeAddressPostalCode = value;
            }
        }
        public string HomeAddressState
        {
            get
            {
                return homeAddressState;
            }

            set
            {
                homeAddressState = value;
            }
        }
        public string HomeAddressStreet
        {
            get
            {
                return homeAddressStreet;
            }

            set
            {
                homeAddressStreet = value;
            }
        }
        public string HomeTelephoneNumber
        {
            get
            {
                return homeTelephoneNumber;
            }

            set
            {
                homeTelephoneNumber = value;
            }
        }
        public string LastName
        {
            get
            {
                return lastName;
            }

            set
            {
                lastName = value;
            }
        }
        public string MiddleName
        {
            get
            {
                return middleName;
            }

            set
            {
                middleName = value;
            }
        }
        public string MobileTelephoneNumber
        {
            get
            {
                return mobileTelephoneNumber;
            }

            set
            {
                mobileTelephoneNumber = value;
            }
        }
        public string OtherTelephoneNumber
        {
            get
            {
                return otherTelephoneNumber;
            }

            set
            {
                otherTelephoneNumber = value;
            }
        }
        public string Suffix
        {
            get
            {
                return suffix;
            }

            set
            {
                suffix = value;
            }
        }
        public string Title
        {
            get
            {
                return title;
            }

            set
            {
                title = value;
            }
        }
        public string WebPage
        {
            get
            {
                return webPage;
            }

            set
            {
                webPage = value;
            }
        }



        #endregion


        private DateTime birthday;
        private string businessAddressCity;
        private string businessAddressCountry;
        private string businessAddressPostalCode;
        private string businessAddressState;
        private string businessAddressStreet;
        private string businessTelephoneNumber;
        private string carTelephoneNumber;
        private string companyName;
        private string department;
        private string businessEmailAddress;
        private string homeEmailAddress;
        private string firstName;
        private string homeAddressCity;
        private string homeAddressCountry;
        private string homeAddressPostalCode;
        private string homeAddressState;
        private string homeAddressStreet;
        private string homeTelephoneNumber;
        private string lastName;
        private string middleName;
        private string mobileTelephoneNumber;
        private string otherTelephoneNumber;
        private string suffix;
        private string title;
        private string webPage;
        private string body;
        public string Body
        {
            get
            {
                return body;
            }

            set
            {
                body = value;
            }
        }


    }
}
