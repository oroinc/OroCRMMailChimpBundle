# OroMailChimpBundle

OroMailChimpBundle provides [integration](https://github.com/oroinc/platform/tree/master/src/Oro/Bundle/IntegrationBundle) with [MailChimp](https://mailchimp.com/) marketing automation platform for Oro applications.

The bundle enables users to create and configure the integration, schedule or manually start synchronization between Oro application marketing lists and MailChimp subscribers list, and import data of MailChimp email campaigns into the Oro application.

## Setting Up the Connection

First of all, a new Integration with type "MailChimp" must be created.

Go to the "System -> Integrations" and click "Create Integration" button.
 
 - *Type*: must be set to MailChimp 
 - *Name*: must be filled with meaningful integration name
 - *API Key*: is a MailChimp API Key from your MailChimp profile page. [About API Keys](http://kb.mailchimp.com/accounts/management/about-api-keys)
 - *Check Connection*: appear only after the API Key has been filled. Click the button to check connection to the MailChimp API with the given API Key.
 - *Default Owner*: Select the owner of the integration. All entities imported from the integration will be assigned to the selected user.
 
 
## Connecting Marketing List to MailChimp

After integration is created and Enabled Marketing Lists may be connected to MailChimp. 

> Only Marketing Lists with Email fields can be connected.

If the Marketing list is suitable for the connection, "Connect To MailChimp" button will appear on Marketing List view page.
One Marketing List may be connected only to one MailChimp integration. OroCRM Marketing Lists are represented in MailChimp as Static Segments of some List.

Lists creation is not allowed by the MailChimp API, so at least one List must be present to load the members to MailChimp. [Create a New List](http://kb.mailchimp.com/lists/growth/create-a-new-list)

When "Connect to MailChimp" button is clicked, the following form will emerge:

 - *MailChimp Segment Name*: is a name of MailChimp Static Segment that will be created in MailChimp Subscribers List
 - *MailChimp Integration*: is a MailChimp integration selector
 - *MailChimp Subscribers List*: is a MailChimp Subscribers List where Static Segment of members will be created
 
After the connection has been saved, a new Static Segment will be scheduled for creation along with the members synchronization job.

>Job Queue Daemon has to be running.

Marketing Lists connected to MailChimp must contain the Email field. Connection settings of the lists are added as a MailChimp action on their view pages.
Available options are "Connection Settings", "Disconnect" and "Synchronize". Synchronize option is available only for lists in the Synced status.


## MailChimp Campaign Creation

Marketing List members may be used to send MailChimp campaigns. OroCRM Marketing list is mapped to Static Segment of MailChimp List.
Campaign statistics are collected in OroCRM ONLY when Campaign is sent to a Static Segment connected to OroCRM. 
[More about MailChimp Campaigns](http://kb.mailchimp.com/campaigns)


## Import Synchronization Logic

Import is performed with *oro:cron:integration:sync* cron command.

 - **Lists**: All MailChimp lists are imported with Merge Vars information
 - **Static Segments**: Only segment connected with Marketing Lists are synchronized
 - **Campaigns**: Only Sent campaigns sent to a Static Segment that has connection to OroCRM Marketing List are imported.

A new Email Campaign will be created in OroCRM for a MailChimp Campaign and synchronized during the following imports.
 
 - **Members**: All members for the Lists connected to OroCRM Static Segment are imported. *Export API used with Since filter*
 - **Member Activities**: Member activities are loaded for Campaigns that were imported to OroCRM. *Export API used with Since filter*

Each member activity is mapped to OroCRM Marketing List Item and Email Campaign Statics by Email. So more than one Marketing List Item and Email Campaign Statics
record may be create if there are several entities with the same Email in the Marketing List.
Activities 'open', click', 'bounce', 'unsub', 'abuse' increment corresponding counters of Email Campaign Statics. 
The 'sent' activity will increment 'contacted times' counter and set 'last contacted at' variable of Marketing List Item.


## Export Logic

Export is performed with *oro:cron:mailchimp:export* cron command.

The following steps are performed in the course of the Marketing List members synchronization with MailChimp:
First, all the Marketing List members are checked for subscription to the MailChimp List. Members not subscribed are scheduled for subscription. 
After that, all the Marketing List members absent in the Static Segment are scheduled for a mass add to the segment.
All the members present in the Static Segment but absent in the Marketing List are scheduled for removal from the Static Segment.

During segment export we have 4 steps

 - **handle_add_state**: Will add new member to MailChimp
 - **handle_remove_state**: Will remove members from Segment at MailChimp
 - **handle_unsubscribe_state**: Will unsubscribe members from List at MailChimp
 - **handle_delete_state**: Will delete members from List at MailChimp

## Extended Merge Vars

Extended Merge Vars is a functionality to add MailChimp Merge Vars. 
Merge Vars creates from the definition of the Segment of the MarketingList. 
Merge Var is equal to column in the Segment definition. Then Merge Vars could be used to personalize MailChimp email templates.
During export next steps executes:

 - creates Extended Merge Vars from the Segment definition
 - creates MailChimp Merge Vars and export its values to every MailChimp subscriber in the list

Predefined cart item Merge Vars adds if Segment built on Shopping Cart entity. For now it limits to 3 cart items.

## Known Issues

Email Campaign Statistics and MailChimp Statistics may differ. Email Campaign Statistics is calculated based on 
Export API data which at the moment contains only clicks and opens. 
MailChimp Statistics contains summary statistics for MailChimp Email Campaign.
