Drupal Commerce Payfast payment module 1.0.1 (Based on:  http://drupal.org/project/commerce_payfast)
=============================================

INTEGRATION:
Requirements:-
Drupal or commerce_kickstart
Commerce

Installation:-
1. Download the module from https://github.com/PayFast/mod-drupalcommerce-7/archive/master.zip and extract the contents into a new folder
2. Move or copy this new folder to your “sites/all/modules” directory
3. Log in as a privileged user on your Drupal7 site
4. Navigate to “modules” via the admin menu and enable the module
5. Navigate to “Payment methods” via the admin menu and:
- click on “PayFast” under the “Enabled payment method rules”,
- then, under “Actions” in the “Elements” block, click on “Enable payment method Payfast”
- The sandbox credentials will be filled in automatically
- Click save to make test transactions with the sandbox (using the account details under “User account:” on https://www.payfast.co.za/c/std/integration-guide#system)
- To make real transactions select “live” and replace the sandbox credentials with your Payfast merchant id and key

Subscriptions:-
1. Log into your PayFast account and enable subscription billing on the settings>integration page
2. Log into the admin dashboard of your Drupal Commerce site and click on 'Store'
3. Navigate to 'Product Types' and add a subscription type
4. Under 'Manage Fields' of the subscription product type add the following fields with respective 'Label', 'Machine Name', 'Field Type' and 'Widget'
- cycles, field_cycles, Integer, Text Field
- frequency, field_frequency, Integer, Text Field
- subscription_type, field_subscription_type, Integer, Text Field
- recurring_amount, field_recurring_amount, Price, Price Textfield
5. Manage the display as required
6. Navigate back 'Products' and add a product of subscription type, and set the subscription fields as follows:
- Set cycles to the number of payments required (set to 0 for infinite)
- Set freqeuncy to 3 for monthly, 4 for quarterly, 5 for biannual, or 6 for annual payments
- Set subscription_type to 1
- Set recurring amount as required
7. Click save
8. NOTE: it is not possible to test subscriptions in sandbox with this integration


******************************************************************************

    Please see the URL below for all information concerning this module:

          https://www.payfast.co.za/shopping-carts/drupal-commerce/

******************************************************************************
