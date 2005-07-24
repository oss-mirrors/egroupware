/***************************************************************************
                  SecurityPolicyInstaller.cs  -  description
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
using System.ComponentModel;
using System.Configuration.Install;

using System.Security;
using System.Security.Permissions;
using System.Security.Policy;

using System.Windows.Forms;


namespace cUnnectorOutlookAddIn
{

    // Set 'RunInstaller' attribute to true.
    [RunInstaller(true)]
    public class SecurityPolicyInstaller : Installer
    {
        private const string cUnnectorCodeGroupName = "cUnnectorCodeGroup";

        private const string errorMessage =
            "Error setting security policy.\nAsk your administrator to grant FullTrust to Add-In";


        public SecurityPolicyInstaller()
            : base()
        {
        }

        public override void Install(IDictionary savedState)
        {
            base.Install(savedState);

            String installDirectory = this.Context.Parameters["InstallDir"];

            MessageBox.Show("Granting FullTrust to code in \n" + installDirectory); 

            try
            {
                createPolicyGroup(installDirectory);
            }
            catch (Exception)
            {
                MessageBox.Show(errorMessage);
            }
        }


        public override void Uninstall(IDictionary savedState)
        {
            base.Uninstall(savedState);

            try
            {
                removePolicyGroup();
            }
            catch (Exception)
            {
                //MessageBox.Show(e.Message);
            }
        }

        public override void Rollback(IDictionary savedState)
        {
            base.Rollback(savedState);

            String installDirectory = this.Context.Parameters["InstallDir"];

            try
            {
                createPolicyGroup(installDirectory);
            }
            catch (Exception)
            {
                MessageBox.Show(errorMessage);
            }
        }

        private void createPolicyGroup(string directory)
        {
            String membershipURL = directory + "*";

            PolicyLevel userPolicyLevel = null;

            // find User policy level
            System.Collections.IEnumerator ph = SecurityManager.PolicyHierarchy();
            while (ph.MoveNext())
            {
                PolicyLevel pl = (PolicyLevel)ph.Current;
                if (pl.Label == "User")
                {
                    userPolicyLevel = pl;
                    break;
                }
            }

            if (userPolicyLevel == null) return;

            PermissionSet fullTrustPermission =
                new NamedPermissionSet("FullTrust");

            IMembershipCondition cUnnectorMembership =
                new UrlMembershipCondition(membershipURL);

            PolicyStatement fullTrustPolicy =
                new PolicyStatement(fullTrustPermission);

            CodeGroup cUnnectorCodeGroup =
                new UnionCodeGroup(cUnnectorMembership, fullTrustPolicy);
            cUnnectorCodeGroup.Description =
                "FullTrust security policy for cUnnector Outlook add-in";
            cUnnectorCodeGroup.Name = cUnnectorCodeGroupName;

            userPolicyLevel.RootCodeGroup.AddChild(cUnnectorCodeGroup);


            SecurityManager.SavePolicy();
        }

        private void removePolicyGroup()
        {

            PolicyLevel userPolicyLevel = null;

            System.Collections.IEnumerator ph = SecurityManager.PolicyHierarchy();

            while (ph.MoveNext())
            {
                PolicyLevel pl = (PolicyLevel)ph.Current;
                if (pl.Label == "User")
                {
                    userPolicyLevel = pl;
                    break;
                }
            }

            if (userPolicyLevel == null) return;

            IList children = userPolicyLevel.RootCodeGroup.Children;
            for (int i = 0; i < children.Count; i++)
            {
                CodeGroup currentChild = (CodeGroup)children[i];
                if (currentChild.Name == cUnnectorCodeGroupName)
                {
                    userPolicyLevel.RootCodeGroup.RemoveChild(currentChild);
                    break;
                }
            }

            SecurityManager.SavePolicy();
        }
    }

}