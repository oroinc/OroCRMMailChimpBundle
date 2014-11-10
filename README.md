# OroCRMMailChimpBundle

This Bundle provide integration with [MailChimp](http://mailchimp.com) for OroCRM.
It allows to connect existing Marketing List Members with MailChimp service and receive MailChimp email campaign statistics
back to OroCRM.


## Setting up connection

First of all new Integration with type MailChimp must be created. 
To do this go to "System -> Integrations" and click "Create Integration" button.
 
 - *Type* - must be set to MailChimp 
 - *Name* - must be filled with valuable integration name
 - *API Key* - is a MailChimp API Key. API Key may be found at your MailChimp profile page. [About API Keys](http://kb.mailchimp.com/accounts/management/about-api-keys)
 - *Check Connection* button will appear only when API Key is filled. It allows to check connection with MailChimp API with given API Key.
 - *Default Owner* - Select the owner of the integration. All entities imported from the integration will be assigned to the selected user.
 
 
## Connecting Marketing List to MailChimp

After integration is created and Enabled Marketing Lists may be connected to MailChimp. 

Note, only marketing lists with email fields are allowed for connection.

In case when Marketing List is accepted for connection button "Connect To MailChimp" will appear on Marketing List view page.
One marketing list may be connected only with one MailChimp integration. OroCRM Marketing Lists are represented in MailChimp as Static Segments of some List.
MailChimp API disallow Lists creation. To load synchronize members to MailChimp at least on List must be present. [Create a New List](http://kb.mailchimp.com/lists/growth/create-a-new-list)

Clicking on "Connect to MailChimp" button opens connection form:

 - *MailChimp Segment Name* - is a name of MailChimp Static Segment that will be created in MailChimp Subscribers List
 - *MailChimp Integration* - is a MailChimp integration selector
 - *MailChimp Subscribers List* - is a MailChimp Subscribers List in which Static Segment of members will be created
 
After saving connection new Static Segment will be scheduled for creation along with members synchronization job.

Note, Job Queue Daemon required be Running. 

For Marketing Lists that are connected to MailChimp email field presence became required. MailChimp connection settings are added as MailChimp action on Marketing List view page.
Available options are "Connection Settings", "Disconnect" and "Synchronize". Synchronize option is available only for list that are in in Synced status.


## MailChimp Campaign Creation

Marketing List members may be used to send MailChimp campaigns. OroCRM Marketing list is mapped to Static Segment of MailChimp List.
Campaign statistics is collected in OroCRM ONLY when Campaign is sent to Static Segment that connected to OroCRM. 
[More about MailChimp Campaigns](http://kb.mailchimp.com/campaigns)


## Import Synchronization Logic

Import is performed by execution of *oro:cron:integration:sync* cron command.

 - **Lists** - All MailChimp lists are imported with Merge Vars information
 - **Static Segments** - Only segments that are connected with Marketing Lists are synchronized
 - **Campaigns** - Only Sent campaigns which were sent to Static Segment that has connection to OroCRM Marketing List are imported. 
 Within OroCRM for MailChimp Campaign new Email Campaign will be created and keep in sync in further imports.
 
 - **Members** - All members for Lists that are connected to OroCRM Static Segment are imported. *Export API used with Since filter*
 - **Member Activities** - Member activities are loaded for Campaigns that were imported to OroCRM. *Export API used with Since filter*
 Each member activity is mapped to OroCRM Marketing List Item and Email Campaign Statics by email. So more than one Marketing List Item and Email Campaign Statics
 record may be created in case when there are more than one entity with same email is present in Marketing List.
 Activities 'open', click', 'bounce', 'unsub', 'abuse' will increment corresponding counters of Email Campaign Statics. 
 'sent' activity will trigger increment contacted times counter and set last contacted at variable of Marketing List Item.


## Export Logic

Import is performed by execution of *oro:cron:mailchimp:export* cron command.

 During synchronization of Marketing List members to MailChimp next steps are performed. 
 First of all Marketing List member is checked that it is subscribed to MailChimp List, in case whe it is not subscribed it is scheduled for subscription. 
 Then all Marketing List members that are not present in Static Segment will be scheduled for mass add to segment.
 All members that are present in Static Segment but not present in Marketing List are scheduled for removal from Static Segment.
