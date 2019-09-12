## FOR SUPPORT PLEASE CONTACT THE FOLLOWING
support@mage365.zendesk.com

## Introduction
This official Zendesk module developed by wagento.

## Version
We are adding a new version management to make module installation available through composer, so this is the table for reference: 

Magento Version | Composer prefix 
----            | ---- 
2.0.x           | 200.x.x 
2.1.x           | 210.x.x
2.2.x           | 220.x.x

So if you are in magento 2.0.x to install by composer just execute: `composer require wagento/zendesk:200.0.1`

BUT in file `etc/module.xml` version will be the same for all composer version, use `setup_version` as global version reference.

## Support
If you are facing any issue with module installation and configuration please send an email to support@mage365.zendesk.com

## Changelog
Based in `setup_version`
v1.0.3
- Added support for M2.3
- Fixed template issue when enabling Open Ticket on recent order list
- Added sync customer by cron
- Added callback url information for zendesk API

v1.0.2
- Fixed Customer grid from `Zendesk > Tickets > Create Ticket > Select Customer`.
- Fixed Order grid from `Zendesk > Tickets > Create Ticket > Select Order`.
- Fixed Customer can't see comment box even if "Customer Can Comment In Ticket" is enabled in `System > Zendesk > Zendesk Support > Ticket Configuration > Frontend Options > Customer Can Comment In Ticket`.
- Fixed Zendesk can't open OAuth poup when backend has custom url.
- Improved Order Ticket Field always will be created on zendesk platform and synced to magento store.
- Improved Config Cache clean strategy and clean execution when config is saved to DB.


v1.0.1
- Fixed Authorization problem when user clicks Authorize button in admin
- Improved `App Installation` algorithm, now is smarter and will always update installation infromation.
- Fixed 'Customer Sync' ajax call.
- Fixed Customer Session validation for Ticket Form when magento's cache is enabled.
- Included code improvement from pull request
    - Removed unused dependecies
    - Changed sql select query to load order by customer email 
      

v1.0.0
- Initial module integration for Zendesk 
