# Magento2 Admin Trax Carrier
This is a module that allows managing the trax relationship with the payments methods to consume the IWS methods related to the orders
## Manually Installation

Magento2 module installation is very easy, please follow the steps for installation-

=> Download and unzip the respective extension zip and create Pasarela(vendor) and Conciliacion(module) name folder inside your magento/app/code/ directory and then move all module's files into magento root directory Magento2/app/code/Pasarela/Conciliacion/ folder.
    

## Run following command via terminal from magento root directory 
  
     $ bin/magento setup:upgrade
     $ bin/magento setup:di:compile
     $ bin/magento setup:static-content:deploy

=> Flush the cache and reindex all.

now module is properly installed