<?php
$installer = $this;

$installer->startSetup();


$sql = '
DROP PROCEDURE IF EXISTS `DEMAC_MLI_REINDEX_SET`;
CREATE PROCEDURE `DEMAC_MLI_REINDEX_SET` (reindex_entity_ids TEXT)
BEGIN
  UPDATE demac_multilocationinventory_stock_status_index dest, 
       (SELECT stock.product_id                                      AS 
               product_id, 
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1, Sum( 
               IF(stock.is_in_stock = 1, stock.qty, 0)))             AS qty, 
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1, 
               IF(Sum(stock.is_in_stock) > 0, 1, 0))                 AS 
               is_in_stock, 
               IF(Sum(stock.backorders) > 0, 1, 0)                   AS 
               backorders, 
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 0, 1) AS 
               manage_stock, 
               stores.store_id                                       AS store_id 
        FROM   demac_multilocationinventory_stock AS stock 
               JOIN demac_multilocationinventory_location AS location 
                 ON stock.location_id = location.id 
               JOIN catalog_product_entity AS product_entity 
                 ON stock.product_id = product_entity.entity_id 
               JOIN demac_multilocationinventory_stores AS stores 
                 ON stock.location_id = stores.location_id 
        WHERE  location.status = 1 
               AND product_entity.type_id IN ("simple", "giftcard")
               AND FIND_IN_SET(product_entity.entity_id, reindex_entity_ids)
        GROUP  BY Concat(stores.store_id, "_", stock.product_id) 
        UNION 
        SELECT stock.product_id                                      AS 
               product_id, 
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1, Sum( 
               IF(stock.is_in_stock = 1, stock.qty, 0)))             AS qty, 
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1, 
               IF(Sum(stock.is_in_stock) > 0, 1, 0))                 AS 
               is_in_stock, 
               IF(Sum(stock.backorders) > 0, 1, 0)                   AS 
               backorders, 
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 0, 1) AS 
               manage_stock, 
               0                                                     AS store_id 
        FROM   demac_multilocationinventory_stock AS stock 
               JOIN demac_multilocationinventory_location AS location 
                 ON stock.location_id = location.id 
               JOIN catalog_product_entity AS product_entity 
                 ON stock.product_id = product_entity.entity_id 
        WHERE  location.status = 1 
               AND product_entity.type_id IN ("simple", "giftcard")
               AND FIND_IN_SET(product_entity.entity_id, reindex_entity_ids)
        GROUP  BY stock.product_id 
        UNION 
        SELECT product_entity.entity_id 
               AS product_id, 
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1, IF( 
               Sum(IF(stock.is_in_stock = 
                      1, stock.qty, 0)) 
               AND 
               Sum(stock.is_in_stock) > 0, 1, 0))                    AS qty, 
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1, IF( 
               Sum(IF(stock.is_in_stock = 
                      1, stock.qty, 0)) 
               AND 
               Sum(stock.is_in_stock) > 0, 1, 0))                    AS 
               is_in_stock, 
               IF(Sum(stock.backorders) > 0, 1, 0)                   AS 
               backorders, 
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 0, 1) AS 
               manage_stock,
               stores.store_id                                       AS store_id
        FROM   catalog_product_entity AS product_entity 
               JOIN catalog_product_super_link AS link 
                 ON product_entity.entity_id = link.parent_id 
               JOIN demac_multilocationinventory_stock AS stock 
                 ON link.product_id = stock.product_id 
               JOIN demac_multilocationinventory_stores AS stores 
                 ON stock.location_id = stores.location_id 
               JOIN demac_multilocationinventory_location AS location 
                 ON stock.location_id = location.id 
        WHERE  location.status = 1 
               AND product_entity.type_id = "configurable"
               AND FIND_IN_SET(product_entity.entity_id, reindex_entity_ids)
        GROUP  BY Concat(stores.store_id, "_", product_entity.entity_id) 
        UNION 
        SELECT product_entity.entity_id 
               AS product_id,
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1, IF( 
               Sum(IF(stock.is_in_stock = 
                      1, stock.qty, 0)) 
               AND 
               Sum(stock.is_in_stock) > 0, 1, 0))                    AS qty, 
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1, IF( 
               Sum(IF(stock.is_in_stock = 
                      1, stock.qty, 0)) 
               AND 
               Sum(stock.is_in_stock) > 0, 1, 0))                    AS 
               is_in_stock, 
               IF(Sum(stock.backorders) > 0, 1, 0)                   AS 
               backorders, 
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 0, 1) AS 
               manage_stock,
               0                                                     AS store_id
        FROM   catalog_product_entity AS product_entity 
               JOIN catalog_product_super_link AS link 
                 ON product_entity.entity_id = link.parent_id 
               JOIN demac_multilocationinventory_stock AS stock 
                 ON link.product_id = stock.product_id 
               JOIN demac_multilocationinventory_location AS location 
                 ON stock.location_id = location.id 
        WHERE  location.status = 1 
               AND product_entity.type_id = "configurable"
               AND FIND_IN_SET(product_entity.entity_id, reindex_entity_ids)
        GROUP  BY product_entity.entity_id 
        UNION 
        SELECT product_entity.entity_id                              AS 
               product_id, 
               IF(Sum(IF(stock.is_in_stock = 1, stock.qty, 0)) 
                  AND Sum(stock.is_in_stock) > 0, 1, 0)              AS qty, 
               IF(Sum(IF(stock.is_in_stock = 1, stock.qty, 0)) 
                  AND Sum(stock.is_in_stock) > 0, 1, 0)              AS 
               is_in_stock, 
               IF(Sum(stock.backorders) > 0, 1, 0)                   AS 
               backorders, 
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 0, 1) AS 
               manage_stock, 
               stores.store_id                                       AS store_id 
        FROM   catalog_product_entity AS product_entity 
               JOIN catalog_product_link AS link 
                 ON product_entity.entity_id = link.product_id 
               JOIN demac_multilocationinventory_stock AS stock 
                 ON link.linked_product_id = stock.product_id 
               JOIN demac_multilocationinventory_location AS location 
                 ON stock.location_id = location.id 
               JOIN demac_multilocationinventory_stores AS stores 
                 ON stock.location_id = stores.location_id 
        WHERE  location.status = 1 
               AND product_entity.type_id = "grouped"
               AND FIND_IN_SET(product_entity.entity_id, reindex_entity_ids)
        GROUP  BY Concat(stores.store_id, "_", product_entity.entity_id) 
        UNION 
        SELECT product_entity.entity_id                              AS 
               product_id, 
               IF(Sum(IF(stock.is_in_stock = 1, stock.qty, 0)) 
                  AND Sum(stock.is_in_stock) > 0, 1, 0)              AS qty, 
               IF(Sum(IF(stock.is_in_stock = 1, stock.qty, 0)) 
                  AND Sum(stock.is_in_stock) > 0, 1, 0)              AS 
               is_in_stock, 
               IF(Sum(stock.backorders) > 0, 1, 0)                   AS 
               backorders, 
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 0, 1) AS 
               manage_stock, 
               0                                                     AS store_id 
        FROM   catalog_product_entity AS product_entity 
               JOIN catalog_product_link AS link 
                 ON product_entity.entity_id = link.product_id 
               JOIN demac_multilocationinventory_stock AS stock 
                 ON link.linked_product_id = stock.product_id 
               JOIN demac_multilocationinventory_location AS location 
                 ON stock.location_id = location.id 
        WHERE  location.status = 1 
               AND product_entity.type_id = "grouped"
               AND FIND_IN_SET(product_entity.entity_id, reindex_entity_ids)
        GROUP  BY product_entity.entity_id 
        UNION 
        SELECT stock.product_id                                      AS 
               product_id, 
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1, Sum( 
               IF(stock.is_in_stock = 1, stock.qty, 0)))             AS qty, 
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1, 
               IF(Sum(stock.is_in_stock) > 0, 1, 0))                 AS 
               is_in_stock, 
               IF(Sum(stock.backorders) > 0, 1, 0)                   AS 
               backorders, 
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 0, 1) AS 
               manage_stock, 
               stores.store_id                                       AS store_id 
        FROM   demac_multilocationinventory_stock AS stock 
               JOIN demac_multilocationinventory_location AS location 
                 ON stock.location_id = location.id 
               JOIN catalog_product_entity AS product_entity 
                 ON stock.product_id = product_entity.entity_id 
               JOIN demac_multilocationinventory_stores AS stores 
                 ON stock.location_id = stores.location_id 
        WHERE  location.status = 1 
               AND product_entity.type_id = "virtual"
               AND FIND_IN_SET(product_entity.entity_id, reindex_entity_ids) 
        GROUP  BY Concat(stores.store_id, "_", stock.product_id) 
        UNION 
        SELECT stock.product_id                                      AS 
               product_id, 
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1, Sum( 
               IF(stock.is_in_stock = 1, stock.qty, 0)))             AS qty, 
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1, 
               IF(Sum(stock.is_in_stock) > 0, 1, 0))                 AS 
               is_in_stock, 
               IF(Sum(stock.backorders) > 0, 1, 0)                   AS 
               backorders, 
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 0, 1) AS 
               manage_stock, 
               0                                                     AS store_id 
        FROM   demac_multilocationinventory_stock AS stock 
               JOIN demac_multilocationinventory_location AS location 
                 ON stock.location_id = location.id 
               JOIN catalog_product_entity AS product_entity 
                 ON stock.product_id = product_entity.entity_id 
        WHERE  location.status = 1 
               AND product_entity.type_id = "virtual"
               AND FIND_IN_SET(product_entity.entity_id, reindex_entity_ids)
        GROUP  BY stock.product_id 
        UNION 
        SELECT stock.product_id                                      AS 
               product_id, 
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1, Sum( 
               IF(stock.is_in_stock = 1, stock.qty, 0)))             AS qty, 
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1, 
               IF(Sum(stock.is_in_stock) > 0, 1, 0))                 AS 
               is_in_stock, 
               IF(Sum(stock.backorders) > 0, 1, 0)                   AS 
               backorders, 
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 0, 1) AS 
               manage_stock, 
               stores.store_id                                       AS store_id 
        FROM   demac_multilocationinventory_stock AS stock 
               JOIN demac_multilocationinventory_location AS location 
                 ON stock.location_id = location.id 
               JOIN catalog_product_entity AS product_entity 
                 ON stock.product_id = product_entity.entity_id 
               JOIN demac_multilocationinventory_stores AS stores 
                 ON stock.location_id = stores.location_id 
        WHERE  location.status = 1 
               AND product_entity.type_id = "downloadable"
               AND FIND_IN_SET(product_entity.entity_id, reindex_entity_ids)
        GROUP  BY Concat(stores.store_id, "_", stock.product_id) 
        UNION 
        SELECT stock.product_id                                      AS 
               product_id, 
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1, Sum( 
               IF(stock.is_in_stock = 1, stock.qty, 0)))             AS qty, 
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1, 
               IF(Sum(stock.is_in_stock) > 0, 1, 0))                 AS 
               is_in_stock, 
               IF(Sum(stock.backorders) > 0, 1, 0)                   AS 
               backorders, 
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 0, 1) AS 
               manage_stock, 
               0                                                     AS store_id 
        FROM   demac_multilocationinventory_stock AS stock 
               JOIN demac_multilocationinventory_location AS location 
                 ON stock.location_id = location.id 
               JOIN catalog_product_entity AS product_entity 
                 ON stock.product_id = product_entity.entity_id 
        WHERE  location.status = 1 
               AND product_entity.type_id = "downloadable"
               AND FIND_IN_SET(product_entity.entity_id, reindex_entity_ids)
        GROUP  BY stock.product_id) src 
SET    dest.qty = src.qty, 
       dest.is_in_stock = src.is_in_stock, 
       dest.backorders = src.backorders, 
       dest.manage_stock = src.manage_stock 
WHERE  dest.store_id = src.store_id 
       AND dest.product_id = src.product_id;

-- BUNDLE SECTION
-- check if all fields are optional
CREATE temporary TABLE IF NOT EXISTS
demac_multilocationinventory_bundled_indexer_tmp AS
  (SELECT catalog_product_entity.entity_id,
          Sum(catalog_product_bundle_option.required) AS required_count
   FROM   catalog_product_entity
          JOIN catalog_product_bundle_option
            ON catalog_product_bundle_option.parent_id =
               catalog_product_entity.entity_id
   WHERE  type_id = "bundle"
   AND FIND_IN_SET(catalog_product_entity.entity_id, reindex_entity_ids)
   GROUP  BY catalog_product_entity.entity_id);

-- ALL OPTIONAL: get all child products, if sum of is_in_stock > 0 then set is_in_stock = 1 and qty = some really big number
-- GLOBAL:
UPDATE
    demac_multilocationinventory_stock_status_index dest,
   (SELECT catalog_product_bundle_selection.parent_product_id AS product_id,
           IF(Sum(is_in_stock) > 0, 1, 0)                     AS qty,
           IF(Sum(is_in_stock) > 0, 1, 0)                     AS is_in_stock
    FROM   catalog_product_bundle_selection
           JOIN demac_multilocationinventory_bundled_indexer_tmp
             ON demac_multilocationinventory_bundled_indexer_tmp.entity_id =
                catalog_product_bundle_selection.parent_product_id
           JOIN demac_multilocationinventory_stock
             ON demac_multilocationinventory_stock.product_id =
                catalog_product_bundle_selection.product_id
    WHERE  required_count = 0
    AND
        FIND_IN_SET(catalog_product_bundle_selection.parent_product_id, reindex_entity_ids)
    GROUP  BY catalog_product_bundle_selection.parent_product_id) src
SET
    dest.qty = src.qty,
    dest.is_in_stock = src.is_in_stock,
    dest.backorders = 0,
    dest.manage_stock = 1
WHERE
    dest.store_id = 0
AND
    dest.product_id = src.product_id;

-- STORE SPECIFIC:
UPDATE demac_multilocationinventory_stock_status_index dest,
    (SELECT demac_multilocationinventory_stores.store_id                        AS store_id,
           catalog_product_bundle_selection.parent_product_id                   AS product_id,
           IF(Sum(demac_multilocationinventory_stock.is_in_stock) > 0, 1, 0)    AS qty,
           IF(Sum(demac_multilocationinventory_stock.is_in_stock) > 0, 1, 0)    AS is_in_stock
    FROM   catalog_product_bundle_selection
        JOIN demac_multilocationinventory_bundled_indexer_tmp
            ON demac_multilocationinventory_bundled_indexer_tmp.entity_id = catalog_product_bundle_selection.parent_product_id
        JOIN demac_multilocationinventory_stock
            ON demac_multilocationinventory_stock.product_id = catalog_product_bundle_selection.product_id
        JOIN demac_multilocationinventory_stores
            ON demac_multilocationinventory_stock.location_id = demac_multilocationinventory_stores.location_id
    WHERE
        required_count = 0
    AND
        FIND_IN_SET(catalog_product_bundle_selection.parent_product_id, reindex_entity_ids)
    GROUP BY
        Concat(catalog_product_bundle_selection.parent_product_id, "_",demac_multilocationinventory_stores.store_id)
    ) src
SET
    dest.qty = src.qty,
    dest.is_in_stock = src.is_in_stock,
    dest.backorders = 0,
    dest.manage_stock = 1
WHERE
    dest.store_id = src.store_id
AND
    dest.product_id = src.product_id;

-- SOME REQUIRED: Verify that each required option has at least one child item in stock (sum is_in_stock grouped by option, if all are greater than 1)
-- GLOBAL:
UPDATE
    demac_multilocationinventory_stock_status_index dest,
    (SELECT src.product_id AS product_id,
        IF(Concat(",", Group_concat(src.is_in_stock), ",") LIKE "%0%", 0, 1) AS is_in_stock,
        IF(Concat(",", Group_concat(src.is_in_stock), ",") LIKE "%0%", 0, 1) AS qty
    FROM
        (SELECT catalog_product_bundle_selection.option_id AS option_id,
            catalog_product_bundle_selection.parent_product_id AS product_id,
            IF(Sum(demac_multilocationinventory_stock.is_in_stock) > 0, 1, 0) AS is_in_stock
        FROM
            catalog_product_bundle_selection
        JOIN demac_multilocationinventory_bundled_indexer_tmp
            ON demac_multilocationinventory_bundled_indexer_tmp.entity_id = catalog_product_bundle_selection.parent_product_id
        JOIN catalog_product_bundle_option
            ON catalog_product_bundle_option.option_id = catalog_product_bundle_selection.option_id
        JOIN demac_multilocationinventory_stock
            ON demac_multilocationinventory_stock.product_id = catalog_product_bundle_selection.product_id
        WHERE
            required_count > 0
        AND
            catalog_product_bundle_option.required = 1
        AND
            FIND_IN_SET(catalog_product_bundle_selection.parent_product_id, reindex_entity_ids)
        GROUP BY
            Concat(catalog_product_bundle_selection.parent_product_id, "_", catalog_product_bundle_selection.option_id)
        ) src
    GROUP BY
        Concat(src.product_id)) src2
SET
    dest.qty = src2.qty,
    dest.is_in_stock = src2.is_in_stock,
    dest.backorders = 0,
    dest.manage_stock = 1
WHERE
    dest.store_id = 0
AND
    dest.product_id = src2.product_id;

-- STORE SPECIFIC:
UPDATE demac_multilocationinventory_stock_status_index dest,
    (SELECT src.store_id,
        src.product_id,
        IF(Concat(",", Group_concat(src.is_in_stock), ",") LIKE "%0%", 0, 1) AS is_in_stock,
        IF(Concat(",", Group_concat(src.is_in_stock), ",") LIKE "%0%", 0, 1) AS qty
    FROM
        (SELECT demac_multilocationinventory_stores.store_id AS store_id,
            catalog_product_bundle_selection.option_id AS option_id,
            catalog_product_bundle_selection.parent_product_id AS product_id,
            IF(Sum(demac_multilocationinventory_stock.is_in_stock) > 0, 1, 0) AS is_in_stock
        FROM
            catalog_product_bundle_selection
        JOIN demac_multilocationinventory_bundled_indexer_tmp
            ON demac_multilocationinventory_bundled_indexer_tmp.entity_id = catalog_product_bundle_selection.parent_product_id
        JOIN catalog_product_bundle_option
            ON catalog_product_bundle_option.option_id = catalog_product_bundle_selection.option_id
        JOIN demac_multilocationinventory_stock
            ON demac_multilocationinventory_stock.product_id = catalog_product_bundle_selection.product_id
        JOIN demac_multilocationinventory_stores
            ON demac_multilocationinventory_stock.location_id = demac_multilocationinventory_stores.location_id
        WHERE
            required_count > 0
        AND catalog_product_bundle_option.required = 1
        AND
            FIND_IN_SET(catalog_product_bundle_selection.parent_product_id, reindex_entity_ids)
        GROUP BY
            Concat(catalog_product_bundle_selection.parent_product_id, "_", demac_multilocationinventory_stores.store_id, "_", catalog_product_bundle_selection.option_id)
        ) src
    GROUP  BY Concat(src.product_id, "_", src.store_id)
    ) src2
SET
    dest.qty = src2.qty,
    dest.is_in_stock = src2.is_in_stock,
    dest.backorders = 0,
    dest.manage_stock = 1
WHERE
    dest.store_id = src2.store_id
AND
    dest.product_id = src2.product_id;

-- CLEANUP:
DROP TABLE IF EXISTS demac_multilocationinventory_bundled_indexer_tmp;
END;
';


$sql2 = '
DROP PROCEDURE IF EXISTS `DEMAC_MLI_REINDEX_ALL`;
CREATE PROCEDURE `DEMAC_MLI_REINDEX_ALL` ()
BEGIN
  UPDATE demac_multilocationinventory_stock_status_index dest,
       (SELECT stock.product_id                                      AS
               product_id,
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1, Sum(
               IF(stock.is_in_stock = 1, stock.qty, 0)))             AS qty,
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1,
               IF(Sum(stock.is_in_stock) > 0, 1, 0))                 AS
               is_in_stock,
               IF(Sum(stock.backorders) > 0, 1, 0)                   AS
               backorders,
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 0, 1) AS
               manage_stock,
               stores.store_id                                       AS store_id
        FROM   demac_multilocationinventory_stock AS stock
               JOIN demac_multilocationinventory_location AS location
                 ON stock.location_id = location.id
               JOIN catalog_product_entity AS product_entity
                 ON stock.product_id = product_entity.entity_id
               JOIN demac_multilocationinventory_stores AS stores
                 ON stock.location_id = stores.location_id
        WHERE  location.status = 1
               AND product_entity.type_id IN ("simple", "giftcard")
        GROUP  BY Concat(stores.store_id, "_", stock.product_id)
        UNION
        SELECT stock.product_id                                      AS
               product_id,
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1, Sum(
               IF(stock.is_in_stock = 1, stock.qty, 0)))             AS qty,
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1,
               IF(Sum(stock.is_in_stock) > 0, 1, 0))                 AS
               is_in_stock,
               IF(Sum(stock.backorders) > 0, 1, 0)                   AS
               backorders,
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 0, 1) AS
               manage_stock,
               0                                                     AS store_id
        FROM   demac_multilocationinventory_stock AS stock
               JOIN demac_multilocationinventory_location AS location
                 ON stock.location_id = location.id
               JOIN catalog_product_entity AS product_entity
                 ON stock.product_id = product_entity.entity_id
        WHERE  location.status = 1
               AND product_entity.type_id IN ("simple", "giftcard")
        GROUP  BY stock.product_id
        UNION
        SELECT product_entity.entity_id
               AS product_id,
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1, IF(
               Sum(IF(stock.is_in_stock =
                      1, stock.qty, 0))
               AND
               Sum(stock.is_in_stock) > 0, 1, 0))                    AS qty,
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1, IF(
               Sum(IF(stock.is_in_stock =
                      1, stock.qty, 0))
               AND
               Sum(stock.is_in_stock) > 0, 1, 0))                    AS
               is_in_stock,
               IF(Sum(stock.backorders) > 0, 1, 0)                   AS
               backorders,
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 0, 1) AS
               manage_stock,
               stores.store_id                                       AS store_id
        FROM   catalog_product_entity AS product_entity
               JOIN catalog_product_super_link AS link
                 ON product_entity.entity_id = link.parent_id
               JOIN demac_multilocationinventory_stock AS stock
                 ON link.product_id = stock.product_id
               JOIN demac_multilocationinventory_stores AS stores
                 ON stock.location_id = stores.location_id
               JOIN demac_multilocationinventory_location AS location
                 ON stock.location_id = location.id
        WHERE  location.status = 1
               AND product_entity.type_id = "configurable"
        GROUP  BY Concat(stores.store_id, "_", product_entity.entity_id)
        UNION
        SELECT product_entity.entity_id
               AS product_id,
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1, IF(
               Sum(IF(stock.is_in_stock =
                      1, stock.qty, 0))
               AND
               Sum(stock.is_in_stock) > 0, 1, 0))                    AS qty,
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1, IF(
               Sum(IF(stock.is_in_stock =
                      1, stock.qty, 0))
               AND
               Sum(stock.is_in_stock) > 0, 1, 0))                    AS
               is_in_stock,
               IF(Sum(stock.backorders) > 0, 1, 0)                   AS
               backorders,
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 0, 1) AS
               manage_stock,
               0                                                     AS store_id
        FROM   catalog_product_entity AS product_entity
               JOIN catalog_product_super_link AS link
                 ON product_entity.entity_id = link.parent_id
               JOIN demac_multilocationinventory_stock AS stock
                 ON link.product_id = stock.product_id
               JOIN demac_multilocationinventory_location AS location
                 ON stock.location_id = location.id
        WHERE  location.status = 1
               AND product_entity.type_id = "configurable"
        GROUP  BY product_entity.entity_id
        UNION
        SELECT product_entity.entity_id                              AS
               product_id,
               IF(Sum(IF(stock.is_in_stock = 1, stock.qty, 0))
                  AND Sum(stock.is_in_stock) > 0, 1, 0)              AS qty,
               IF(Sum(IF(stock.is_in_stock = 1, stock.qty, 0))
                  AND Sum(stock.is_in_stock) > 0, 1, 0)              AS
               is_in_stock,
               IF(Sum(stock.backorders) > 0, 1, 0)                   AS
               backorders,
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 0, 1) AS
               manage_stock,
               stores.store_id                                       AS store_id
        FROM   catalog_product_entity AS product_entity
               JOIN catalog_product_link AS link
                 ON product_entity.entity_id = link.product_id
               JOIN demac_multilocationinventory_stock AS stock
                 ON link.linked_product_id = stock.product_id
               JOIN demac_multilocationinventory_location AS location
                 ON stock.location_id = location.id
               JOIN demac_multilocationinventory_stores AS stores
                 ON stock.location_id = stores.location_id
        WHERE  location.status = 1
               AND product_entity.type_id = "grouped"
        GROUP  BY Concat(stores.store_id, "_", product_entity.entity_id)
        UNION
        SELECT product_entity.entity_id                              AS
               product_id,
               IF(Sum(IF(stock.is_in_stock = 1, stock.qty, 0))
                  AND Sum(stock.is_in_stock) > 0, 1, 0)              AS qty,
               IF(Sum(IF(stock.is_in_stock = 1, stock.qty, 0))
                  AND Sum(stock.is_in_stock) > 0, 1, 0)              AS
               is_in_stock,
               IF(Sum(stock.backorders) > 0, 1, 0)                   AS
               backorders,
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 0, 1) AS
               manage_stock,
               0                                                     AS store_id
        FROM   catalog_product_entity AS product_entity
               JOIN catalog_product_link AS link
                 ON product_entity.entity_id = link.product_id
               JOIN demac_multilocationinventory_stock AS stock
                 ON link.linked_product_id = stock.product_id
               JOIN demac_multilocationinventory_location AS location
                 ON stock.location_id = location.id
        WHERE  location.status = 1
               AND product_entity.type_id = "grouped"
        GROUP  BY product_entity.entity_id
        UNION
        SELECT stock.product_id                                      AS
               product_id,
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1, Sum(
               IF(stock.is_in_stock = 1, stock.qty, 0)))             AS qty,
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1,
               IF(Sum(stock.is_in_stock) > 0, 1, 0))                 AS
               is_in_stock,
               IF(Sum(stock.backorders) > 0, 1, 0)                   AS
               backorders,
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 0, 1) AS
               manage_stock,
               stores.store_id                                       AS store_id
        FROM   demac_multilocationinventory_stock AS stock
               JOIN demac_multilocationinventory_location AS location
                 ON stock.location_id = location.id
               JOIN catalog_product_entity AS product_entity
                 ON stock.product_id = product_entity.entity_id
               JOIN demac_multilocationinventory_stores AS stores
                 ON stock.location_id = stores.location_id
        WHERE  location.status = 1
               AND product_entity.type_id = "virtual"
        GROUP  BY Concat(stores.store_id, "_", stock.product_id)
        UNION
        SELECT stock.product_id                                      AS
               product_id,
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1, Sum(
               IF(stock.is_in_stock = 1, stock.qty, 0)))             AS qty,
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1,
               IF(Sum(stock.is_in_stock) > 0, 1, 0))                 AS
               is_in_stock,
               IF(Sum(stock.backorders) > 0, 1, 0)                   AS
               backorders,
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 0, 1) AS
               manage_stock,
               0                                                     AS store_id
        FROM   demac_multilocationinventory_stock AS stock
               JOIN demac_multilocationinventory_location AS location
                 ON stock.location_id = location.id
               JOIN catalog_product_entity AS product_entity
                 ON stock.product_id = product_entity.entity_id
        WHERE  location.status = 1
               AND product_entity.type_id = "virtual"
        GROUP  BY stock.product_id
        UNION
        SELECT stock.product_id                                      AS
               product_id,
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1, Sum(
               IF(stock.is_in_stock = 1, stock.qty, 0)))             AS qty,
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1,
               IF(Sum(stock.is_in_stock) > 0, 1, 0))                 AS
               is_in_stock,
               IF(Sum(stock.backorders) > 0, 1, 0)                   AS
               backorders,
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 0, 1) AS
               manage_stock,
               stores.store_id                                       AS store_id
        FROM   demac_multilocationinventory_stock AS stock
               JOIN demac_multilocationinventory_location AS location
                 ON stock.location_id = location.id
               JOIN catalog_product_entity AS product_entity
                 ON stock.product_id = product_entity.entity_id
               JOIN demac_multilocationinventory_stores AS stores
                 ON stock.location_id = stores.location_id
        WHERE  location.status = 1
               AND product_entity.type_id = "downloadable"
        GROUP  BY Concat(stores.store_id, "_", stock.product_id)
        UNION
        SELECT stock.product_id                                      AS
               product_id,
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1, Sum(
               IF(stock.is_in_stock = 1, stock.qty, 0)))             AS qty,
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 1,
               IF(Sum(stock.is_in_stock) > 0, 1, 0))                 AS
               is_in_stock,
               IF(Sum(stock.backorders) > 0, 1, 0)                   AS
               backorders,
               IF(Group_concat(stock.manage_stock) LIKE "%0%", 0, 1) AS
               manage_stock,
               0                                                     AS store_id
        FROM   demac_multilocationinventory_stock AS stock
               JOIN demac_multilocationinventory_location AS location
                 ON stock.location_id = location.id
               JOIN catalog_product_entity AS product_entity
                 ON stock.product_id = product_entity.entity_id
        WHERE  location.status = 1
               AND product_entity.type_id = "downloadable"
        GROUP  BY stock.product_id) src
SET    dest.qty = src.qty,
       dest.is_in_stock = src.is_in_stock,
       dest.backorders = src.backorders,
       dest.manage_stock = src.manage_stock
WHERE  dest.store_id = src.store_id
       AND dest.product_id = src.product_id;

-- BUNDLE SECTION
-- check if all fields are optional
CREATE temporary TABLE IF NOT EXISTS
demac_multilocationinventory_bundled_indexer_tmp AS
  (SELECT catalog_product_entity.entity_id,
          Sum(catalog_product_bundle_option.required) AS required_count
   FROM   catalog_product_entity
          JOIN catalog_product_bundle_option
            ON catalog_product_bundle_option.parent_id =
               catalog_product_entity.entity_id
   WHERE  type_id = "bundle"
   GROUP  BY catalog_product_entity.entity_id);

-- ALL OPTIONAL: get all child products, if sum of is_in_stock > 0 then set is_in_stock = 1 and qty = some really big number
-- GLOBAL:
UPDATE
    demac_multilocationinventory_stock_status_index dest,
   (SELECT catalog_product_bundle_selection.parent_product_id AS product_id,
           IF(Sum(is_in_stock) > 0, 1, 0)                     AS qty,
           IF(Sum(is_in_stock) > 0, 1, 0)                     AS is_in_stock
    FROM   catalog_product_bundle_selection
           JOIN demac_multilocationinventory_bundled_indexer_tmp
             ON demac_multilocationinventory_bundled_indexer_tmp.entity_id =
                catalog_product_bundle_selection.parent_product_id
           JOIN demac_multilocationinventory_stock
             ON demac_multilocationinventory_stock.product_id =
                catalog_product_bundle_selection.product_id
    WHERE  required_count = 0
    GROUP  BY catalog_product_bundle_selection.parent_product_id) src
SET
    dest.qty = src.qty,
    dest.is_in_stock = src.is_in_stock,
    dest.backorders = 0,
    dest.manage_stock = 1
WHERE
    dest.store_id = 0
AND
    dest.product_id = src.product_id;

-- STORE SPECIFIC:
UPDATE demac_multilocationinventory_stock_status_index dest,
    (SELECT demac_multilocationinventory_stores.store_id                        AS store_id,
           catalog_product_bundle_selection.parent_product_id                   AS product_id,
           IF(Sum(demac_multilocationinventory_stock.is_in_stock) > 0, 1, 0)    AS qty,
           IF(Sum(demac_multilocationinventory_stock.is_in_stock) > 0, 1, 0)    AS is_in_stock
    FROM   catalog_product_bundle_selection
        JOIN demac_multilocationinventory_bundled_indexer_tmp
            ON demac_multilocationinventory_bundled_indexer_tmp.entity_id = catalog_product_bundle_selection.parent_product_id
        JOIN demac_multilocationinventory_stock
            ON demac_multilocationinventory_stock.product_id = catalog_product_bundle_selection.product_id
        JOIN demac_multilocationinventory_stores
            ON demac_multilocationinventory_stock.location_id = demac_multilocationinventory_stores.location_id
    WHERE
        required_count = 0
    GROUP BY
        Concat(catalog_product_bundle_selection.parent_product_id, "_",demac_multilocationinventory_stores.store_id)
    ) src
SET
    dest.qty = src.qty,
    dest.is_in_stock = src.is_in_stock,
    dest.backorders = 0,
    dest.manage_stock = 1
WHERE
    dest.store_id = src.store_id
AND
    dest.product_id = src.product_id;

-- SOME REQUIRED: Verify that each required option has at least one child item in stock (sum is_in_stock grouped by option, if all are greater than 1)
-- GLOBAL:
UPDATE
    demac_multilocationinventory_stock_status_index dest,
    (SELECT src.product_id AS product_id,
        IF(Concat(",", Group_concat(src.is_in_stock), ",") LIKE "%0%", 0, 1) AS is_in_stock,
        IF(Concat(",", Group_concat(src.is_in_stock), ",") LIKE "%0%", 0, 1) AS qty
    FROM
        (SELECT catalog_product_bundle_selection.option_id AS option_id,
            catalog_product_bundle_selection.parent_product_id AS product_id,
            IF(Sum(demac_multilocationinventory_stock.is_in_stock) > 0, 1, 0) AS is_in_stock
        FROM
            catalog_product_bundle_selection
        JOIN demac_multilocationinventory_bundled_indexer_tmp
            ON demac_multilocationinventory_bundled_indexer_tmp.entity_id = catalog_product_bundle_selection.parent_product_id
        JOIN catalog_product_bundle_option
            ON catalog_product_bundle_option.option_id = catalog_product_bundle_selection.option_id
        JOIN demac_multilocationinventory_stock
            ON demac_multilocationinventory_stock.product_id = catalog_product_bundle_selection.product_id
        WHERE
            required_count > 0
        AND
            catalog_product_bundle_option.required = 1
        GROUP BY
            Concat(catalog_product_bundle_selection.parent_product_id, "_", catalog_product_bundle_selection.option_id)
        ) src
    GROUP BY
        Concat(src.product_id)) src2
SET
    dest.qty = src2.qty,
    dest.is_in_stock = src2.is_in_stock,
    dest.backorders = 0,
    dest.manage_stock = 1
WHERE
    dest.store_id = 0
AND
    dest.product_id = src2.product_id;

-- STORE SPECIFIC:
UPDATE demac_multilocationinventory_stock_status_index dest,
    (SELECT src.store_id,
        src.product_id,
        IF(Concat(",", Group_concat(src.is_in_stock), ",") LIKE "%0%", 0, 1) AS is_in_stock,
        IF(Concat(",", Group_concat(src.is_in_stock), ",") LIKE "%0%", 0, 1) AS qty
    FROM
        (SELECT demac_multilocationinventory_stores.store_id AS store_id,
            catalog_product_bundle_selection.option_id AS option_id,
            catalog_product_bundle_selection.parent_product_id AS product_id,
            IF(Sum(demac_multilocationinventory_stock.is_in_stock) > 0, 1, 0) AS is_in_stock
        FROM
            catalog_product_bundle_selection
        JOIN demac_multilocationinventory_bundled_indexer_tmp
            ON demac_multilocationinventory_bundled_indexer_tmp.entity_id = catalog_product_bundle_selection.parent_product_id
        JOIN catalog_product_bundle_option
            ON catalog_product_bundle_option.option_id = catalog_product_bundle_selection.option_id
        JOIN demac_multilocationinventory_stock
            ON demac_multilocationinventory_stock.product_id = catalog_product_bundle_selection.product_id
        JOIN demac_multilocationinventory_stores
            ON demac_multilocationinventory_stock.location_id = demac_multilocationinventory_stores.location_id
        WHERE
            required_count > 0
        AND catalog_product_bundle_option.required = 1
        GROUP BY
            Concat(catalog_product_bundle_selection.parent_product_id, "_", demac_multilocationinventory_stores.store_id, "_", catalog_product_bundle_selection.option_id)
        ) src
    GROUP  BY Concat(src.product_id, "_", src.store_id)
    ) src2
SET
    dest.qty = src2.qty,
    dest.is_in_stock = src2.is_in_stock,
    dest.backorders = 0,
    dest.manage_stock = 1
WHERE
    dest.store_id = src2.store_id
AND
    dest.product_id = src2.product_id;

-- CLEANUP:
DROP TABLE IF EXISTS demac_multilocationinventory_bundled_indexer_tmp;
END;
';

$write = Mage::getSingleton('core/resource')->getConnection('core_write');
$write->exec($sql);
$write->exec($sql2);


$installer->endSetup();
