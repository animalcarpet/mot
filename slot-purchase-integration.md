# Slot Purchase Module
This file contains instructions and steps required to integration Slot Purchase Modules into the MOT Application. Slot Purchasing is made uo 
of 2 modules stored in separate repositories

* Backend Module : Integrates with the MOT Backend API and communicates with the CPMS API to process payments etc
* Frontend Module: Integrates with the MOT Web Frontend Module comprising of the UIs and controllers used in the slot purchase journey. 
This module uses the MOT Rest Client to communicate with the MOT Backend

## MOT Backend API Integration
Follow this steps to integrate the slot purchase backend module to the `mot-api` module
* Add the slot purchase related modules to application.config.php. 

        CpmsClient
        SlotPurchaseApi
        
* Add slot-purchase backend module to composer.json
        
        "require": {
            "slot-purchase/backend-api":"0.1.x-dev"
        }
        
* Add the repositories where the module resides


            "repositories": [
                {
                    "url": "git@gitlab.clb.npm:cpms/cpms-forms.git",
                    "type": "git"
                },
                {
                    "url": "git@gitlab.clb.npm:cpms/cpms-client.git",
                    "type": "git"
                },
                {
                    "type": "git",
                    "url": "git@gitlab.clb.npm:slot-purchase/backend-api.git"
                }
            ]
* Run `composer.phar update` to load dependencies
* Add `CpmsClient` and `SlotPurchaseApi` to `application.config.php` to enable the modules
`slot-purchase-config.global.php` and provide the require configurations.
* Run SQL scripts in the scripts directory (integrate with MOT Db setup script
* Modify DvsaEntities/Entity/TestSlotTransaction.php add attribute `salesReference` with getters and setters

## MOT Web Frontend Integration
Follow this steps to integrate the slot purchase UI and user journeys to the `mot-web-frontend` module
* Add the slot purchase related modules to application.config.php. Note that the order in which the module are added is critical. The modules should be loaded before the 
MOT Organisation module:

       CpmsClient
       CpmsForm
       SlotPurchase
       
* Add slot-purchase frontend module to composer.json
        
        "require": {
            "slot-purchase/web-frontend": "0.1.x-dev",
        }
        
* Add the repositories where the module resides


            "repositories": [
                {
                    "url": "git@gitlab.clb.npm:cpms/cpms-forms.git",
                    "type": "git"
                },
                {
                    "url": "git@gitlab.clb.npm:cpms/cpms-client.git",
                    "type": "git"
                },
                {
                    "type": "git",
                    "url": "git@gitlab.clb.npm:slot-purchase/web-frontend.git"
                }
            ]
* Run `composer.phar update` to load dependencies
* Add `CpmsForm`, `CpmsClient` and `SlotPurchase` to `application.config.php` to enable the modules
`slot-purchase.global.php`.
* Add links to buy slot

                $this->url(
                   \SlotPurchase\Controller\Journey\AbstractJourneyController::ROUTE_NAME_START,
                   ['organisationId' => $organisationId]
               );
               

## Development Environment
* If running in your local development environment ensure that the host file in the MOT VM can resolve hostname e.g. payment-service.in
* After running the MOT scripts to setup the VM and database, apply the SQL scripts in slot-purchasee/backend-api/scripts. When integrated,
these scripts would be integrated in the main MOT setup scripts