[![Code Climate](https://codeclimate.com/github/DemacMedia/Magento-Multi-Location-Inventory.png)](https://codeclimate.com/github/DemacMedia/Magento-Multi-Location-Inventory)

#Multi Location Inventory v1.2.3
##Description
Allows the creation of multiple inventory locations in Magento along with assigning those inventory locations to store views.

##Installation
1. Create Locations
2. Run Multi Location Inventory Indexer
3. Create inventory for products.
4. Run all indexers.

##Bugs and Limitations
- Decimal quantities don't work. Don't try it. Things will break.
- Bundled and Gift Card products are not supported yet.

##Troubleshooting
Verify that you aren't running any other extensions that may conflict with Multi Location Inventory.

Some common extensions that conflict include:
- OrganicInternet_SimpleConfigurableProducts
- Unirgy uRapidFlow
- Any other stock import / export utilities that weren’t specifically built for MultiLocationInventory.

Please contribute other extensions you find that are not compatible either by sending a pull request updating this README or opening a GitHub issue.
