[![Code Climate](https://codeclimate.com/github/DemacMedia/Magento-Multi-Location-Inventory.png)](https://codeclimate.com/github/DemacMedia/Magento-Multi-Location-Inventory)

#Multi Location Inventory v1.2.0
##Description
Allows the creation of multiple inventory locations in Magento along with assigning those inventory locations to store views.

##Installation
1. Create Locations
2. Run Multi Location Inventory Indexer
3. Create inventory for products.
4. Run all indexers.

##Bugs and Limitations
- Limited to selling 9,999 of a specific virtual product in a single transaction.
- Decimal quantities are untested and will experience issues.
- Setting Manage Stock has no effect.
- Bundled, Downloadable and Gift Card products are not supported yet.
- Virtual Products and Configurable Products allow you to set inventory levels even though they are never used.

##Troubleshooting
Verify that you aren't running any other extensions that may conflict with Multi Location Inventory.

Some common extensions that conflict include:
- OrganicInternet_SimpleConfigurableProducts

Please contribute other extensions you find that are not compatible either by sending a pull request updating this README or opening a GitHub issue.
