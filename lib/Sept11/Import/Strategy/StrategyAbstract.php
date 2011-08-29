<?php
require_once 'Sept11/Import/Strategy/StrategyInterface.php';

abstract class Sept11_Import_Strategy_StrategyAbstract 
    implements Sept11_Import_Strategy_StrategyInterface
{
    protected $_dbSept11;
    protected $_dbOmeka;
    protected $_collectionSept11;
    
    private $_privateStatuses = array(Sept11_Import::STATUS_REVIEW, 
                                      Sept11_Import::STATUS_REJECTED, 
                                      Sept11_Import::STATUS_PRIVATE);
    private $_privateConsents = array(Sept11_Import::CONSENT_NO, 
                                      Sept11_Import::CONSENT_CONDITIONAL);
    private $_privatePostings = array('no');
    
    private $_sources = array(
        Sept11_Import::SOURCE_BORN_DIGITAL => 'born-digital', 
        Sept11_Import::SOURCE_DIGITAL_IMAGE => 'digital image', 
        Sept11_Import::SOURCE_SCANNED_IMAGE => 'scanned image', 
        Sept11_Import::SOURCE_DIGITALLY_RECORDED => 'digitally recorded', 
        Sept11_Import::SOURCE_TRANSCRIPTION => 'transcription', 
        Sept11_Import::SOURCE_UNKNOWN => 'unknown', 
    );
    
    private $_mediaTypes = array(
        Sept11_Import::MEDIA_TYPE_AUDIO => 'audio', 
        Sept11_Import::MEDIA_TYPE_DOCUMENT => 'document', 
        Sept11_Import::MEDIA_TYPE_STILL_IMAGE => 'still image', 
        Sept11_Import::MEDIA_TYPE_MOVING_IMAGE => 'moving image', 
        Sept11_Import::MEDIA_TYPE_EMAIL => 'email', 
        Sept11_Import::MEDIA_TYPE_WEBPAGE => 'webpage', 
        Sept11_Import::MEDIA_TYPE_STORY => 'story', 
        Sept11_Import::MEDIA_TYPE_ARTICLE => 'article', 
        Sept11_Import::MEDIA_TYPE_MIXED_MEDIA => 'mixed media', 
        Sept11_Import::MEDIA_TYPE_DATA => 'data', 
        Sept11_Import::MEDIA_TYPE_INTERVIEW => 'interview', 
        Sept11_Import::MEDIA_TYPE_UNKNOWN => 'unknown', 
    );
    
    private $_statuses = array(
        Sept11_Import::STATUS_REVIEW => 'review', 
        Sept11_Import::STATUS_APPROVED => 'approved', 
        Sept11_Import::STATUS_REJECTED => 'rejected', 
        Sept11_Import::STATUS_FEATURED => 'featured', 
        Sept11_Import::STATUS_PRIVATE => 'private', 
    );
    
    private $_consents = array(
        Sept11_Import::CONSENT_FULL => 'full',
        Sept11_Import::CONSENT_NO => 'no', 
        Sept11_Import::CONSENT_IMPLIED => 'implied', 
        Sept11_Import::CONSENT_CONDITIONAL => 'conditional', 
        Sept11_Import::CONSENT_UNKNOWN => 'unknown', 
    );
    
    /**
     * Construct this import object.
     */
    public function __construct()
    {
        // Cache databases.
        $this->_dbSept11 = Sept11_Import::getDbSept11();
        $this->_dbOmeka = Sept11_Import::getDbOmeka();
        
        // Set the Sept11 collection to import.
        $this->_collectionSept11 = $this->_fetchCollectionSept11($this->getCollectionIdSept11());
    }
    
    /**
     * Delete this collection from Omeka.
     * 
     * Override this method in child class.
     */
    public function delete()
    {}
    
    /**
     * Import or resume import on this collection.
     * 
     * Override this method in child class.
     */
    public function import()
    {}
    
    /**
     * Get the Sept11 collection ID.
     * 
     * Implement this method in child class.
     */
    abstract public function getCollectionIdSept11();
    
    /**
     * Get the item type metadata.
     * 
     * Implement this method in child class.
     * 
     * @return array
     */
    abstract protected function _getItemTypeMetadata();
    
    /**
     * Get the item type element metadata.
     * 
     * Implement this method in child class. Item type elements must be uniquely 
     * named since they may have semantically unique descriptions. To accomplish 
     * this, use the item type name as the element name prefix. For example: 
     * "Item Type Name: Element Name"
     * 
     * @return array
     */
    abstract protected function _getItemTypeElementMetadata();
    
    /**
     * Insert the item type this collection will use.
     * 
     * @return int
     */
    protected function _insertItemType()
    {
        $metadata = $this->_getItemTypeMetadata();
        $elementMetadata = $this->_getItemTypeElementMetadata();
        
        // A shared item type may already exist, so don't insert it.
        $itemType = $this->_dbOmeka->getTable('ItemType')->findByName($metadata['name']);
        if ($itemType) {
            return $itemType->id;
        }
        
        $itemType = insert_item_type($metadata, $elementMetadata);
        return $itemType->id;
    }
    
    /**
     * Insert the collection and log the collection/collection mapping.
     * 
     * Transparent interface to insert_collection(). 
     * 
     * @param array $collectionMetadataOmeka
     * @return int
     */
    protected function _insertCollection()
    {
        // A corresponding Omeka collection may already exist.
        $sql = '
        SELECT `collection_id_omeka` 
        FROM `sept11_import_collections_log` 
        WHERE `collection_id_sept11` = ? 
        LIMIT 1';
        $collectionIdOmeka = $this->_dbOmeka->fetchOne($sql, $this->_collectionSept11['COLLECTION_ID']);
        
        // Return the Omeka collection ID if it exists.
        if ($collectionIdOmeka) {
            return $collectionIdOmeka;
        }
        
        // Set collection metadata.
        $collectionMetadataOmeka['name'] = $this->_collectionSept11['COLLECTION_TITLE'];
        $collectionMetadataOmeka['description'] = $this->_collectionSept11['COLLECTION_DESC'];
        $collectionMetadataOmeka['public'] = $this->_collectionIsPublic($this->_collectionSept11);
        
        $collectionOmeka = insert_collection($collectionMetadataOmeka);
        $collectionIdOmeka = $collectionOmeka->id;
        $this->_logCollection($collectionIdOmeka);
        
        // Release the collection object to avoid memory leak.
        release_object($collectionOmeka);
        return $collectionIdOmeka;
    }
    
    /**
     * Insert the item and log the object/item mapping.
     * 
     * Transparent interface to insert_item(). 
     * 
     * @param array $metadata
     * @param array $elementTexts
     * @param int $collectionOmekaId
     * @param array $object
     * @return int
     */
    protected function _insertItem($collectionOmekaId, 
                                   array $object, 
                                   array $metadata = array(), 
                                   array $elementTexts = array(), 
                                   array $fileMetadata = array())
    {
        // Set item metadata (overwrite if already set).
        $metadata['collection_id'] = $collectionOmekaId;
        $metadata['public'] = $this->_itemIsPublic($object);
        
        // Set Dublin Core element texts if not already set. Setting html to 
        // true will remove new lines.
        if (!isset($elementTexts['Dublin Core']['Title'])) {
            $elementTexts['Dublin Core']['Title'] = array(array('text' => $object['OBJECT_TITLE'], 'html' => false));
        }
        if (!isset($elementTexts['Dublin Core']['Description'])) {
            $elementTexts['Dublin Core']['Description'] = array(array('text' => $object['OBJECT_DESC'], 'html' => false));
        }
        
        // Set item element texts.
        $elementTexts[Sept11_Import::SEPT11_ITEM_ELEMENT_SET] = array(
            'Status' => array(array('text' => $this->_statuses[$object['STATUS_ID']], 'html' => false)), 
            'Consent' => array(array('text' => $this->_consents[$object['CONSENT_ID']], 'html' => false)), 
            'Posting' => array(array('text' => $object['OBJECT_POSTING'], 'html' => false)), 
            'Copyright' => array(array('text' => $object['OBJECT_COPYRIGHT'], 'html' => false)), 
            'Source' => array(array('text' => $this->_sources[$object['SOURCE_ID']], 'html' => false)), 
            'Media Type' => array(array('text' => $this->_mediaTypes[$object['OBJECT_MEDIA_TYPE_ID']], 'html' => false)), 
            'Original Name' => array(array('text' => $object['OBJECT_ORIG_NAME'], 'html' => false)), 
            'Created by Author' => array(array('text' => $object['OBJECT_AUTHOR_CREATE'], 'html' => false)), 
            'Described by Author' => array(array('text' => $object['OBJECT_AUTHOR_DESCRIBE'], 'html' => false)), 
            'Date Entered' => array(array('text' => $object['OBJECT_DATE_ENTERED'], 'html' => false)), 
            'IP Address' => array(array('text' => $object['OBJECT_IP_SOURCE'], 'html' => false)), 
            'Annotation' => array(array('text' => $object['OBJECT_ANNOTATION'], 'html' => false)), 
            'Notes' => array(array('text' => $object['OBJECT_NOTES'], 'html' => false)), 
        );
        
        try {
            $item = insert_item($metadata, $elementTexts, $fileMetadata);
        
        // Catch various exceptions, log the object ID, collection ID, and 
        // information about the error, and return to the import strategy. No 
        // item ID was returned, so it is useless to log the item. These errors 
        // indicate that the item was not inserted or not completely inserted. 
        // Clean up incomplete items by matching unique fields in the sept11 
        // OBJECTS table to the corresponding items in Omeka, if any.
        } catch (Omeka_File_Ingest_Exception $e) {
            $this->_logError($e, $object['OBJECT_ID'], $collectionOmekaId);
            return;
        } catch (Omeka_File_Ingest_InvalidException $e) {
            $this->_logError($e, $object['OBJECT_ID'], $collectionOmekaId);
            return;
        } catch (Omeka_File_Derivative_Exception $e) {
            $this->_logError($e, $object['OBJECT_ID'], $collectionOmekaId);
            return;
        } catch (Omeka_Storage_Exception $e) {
            $this->_logError($e, $object['OBJECT_ID'], $collectionOmekaId);
            return;
        }
        
        // Record the item contributor.
        $sql = '
        SELECT `contributor_id_omeka` 
        FROM `sept11_import_contributors_log` 
        WHERE `contributor_id_sept11` = ?';
        $contributorId = $this->_dbOmeka->fetchOne($sql, $object['CONTRIBUTOR_ID']);
        $this->_dbOmeka->insert('contributors_items', array(
            'contributor_id' => $contributorId, 
            'item_id' => $item->id, 
        ));
        
        $itemId = $item->id;
        $this->_logItem($object['OBJECT_ID'], $itemId, $collectionOmekaId);
        
        // Release the item object to avoid memory leak.
        release_object($item);
        return $itemId;
    }
    
    /**
     * Delete this Omeka collection.
     * 
     * @todo Think of a faster way to delete a collection and its items.
     */
    protected function _deleteCollectionOmeka()
    {
        // Get the corresponding Omeka collection.
        $sql = '
        SELECT * 
        FROM `sept11_import_collections_log` 
        WHERE `collection_id_sept11` = ? 
        LIMIT 1';
        $collectionLog = $this->_dbOmeka->fetchRow($sql, $this->_collectionSept11['COLLECTION_ID']);
        
        // Return if the Omeka collection does not exist.
        if (!$collectionLog) {
            return;
        }
        
        $collectionOmeka = $this->_dbOmeka->getTable('Collection')->find($collectionLog['collection_id_omeka']);
        
        // Get the corresponding items and delete them.
        $sql = '
        SELECT `id` 
        FROM `' . $this->_dbOmeka->prefix . 'items` 
        WHERE `collection_id` = ?';
        $itemIds = $this->_dbOmeka->fetchCol($sql, $collectionOmeka->id);
        foreach ($itemIds as $itemId) {
            $item = $this->_dbOmeka->getTable('Item')->find($itemId);
            if ($item) {
                // Delete the contributor/item relation.
                $this->_dbOmeka->delete("{$this->_dbOmeka->prefix}contributors_items", "item_id = {$item->id}");
                
                // Delete the item.
                $item->delete();
            }
        }
        
        // Delete the collection, item, and error logs.
        $sql = '
        DELETE FROM `sept11_import_collections_log` 
        WHERE `collection_id_omeka` = ?';
        $this->_dbOmeka->query($sql, $collectionOmeka->id);
        
        $sql = '
        DELETE FROM `sept11_import_items_log` 
        WHERE `collection_id_omeka` = ?';
        $this->_dbOmeka->query($sql, $collectionOmeka->id);
        
        $sql = '
        DELETE FROM `sept11_import_error_log` 
        WHERE `collection_id_omeka` = ?';
        $this->_dbOmeka->query($sql, $collectionOmeka->id);
        
        // Delete the Omeka collection.
        $collectionOmeka->delete();
    }
    
    /**
     * Delete the item type and its elements.
     * 
     * @todo Think of a faster way to delete an item type.
     */
    protected function _deleteItemType()
    {
        $itemTypeMetadata = $this->_getItemTypeMetadata();
        $itemType = $this->_dbOmeka->getTable('ItemType')->findByName($itemTypeMetadata['name']);
        
        // The item type may not exist.
        if (!$itemType) {
            return;
        }
        
        $prefix = $this->_dbOmeka->prefix;
        
        // Do not delete the item type if there are element texts assigned to 
        // elements assigned to this item type. This is to prevent deletion of 
        // shared item types.
        $sql = "
        SELECT COUNT(*) AS count 
        FROM `{$prefix}element_texts` et 
        JOIN `{$prefix}elements` e 
        ON et.`element_id` = e.`id` 
        JOIN `{$prefix}item_types_elements` ite 
        ON ite.`element_id` = e.`id` 
        WHERE ite.`item_type_id` = ?";
        $count = (int) $this->_dbOmeka->fetchOne($sql, $itemType->id);
        if ($count) {
            return;
        }
        
        // Delete elements belonging to this item type.
        $sql = "
        SELECT e.id 
        FROM `{$prefix}elements` e 
        JOIN `{$prefix}item_types_elements` ite
        ON ite.`element_id` = e.`id`
        WHERE ite.`item_type_id` = ?";
        $elementIds = $this->_dbOmeka->fetchCol($sql, $itemType->id);
        foreach ($elementIds as $elementId) {
            $this->_dbOmeka->getTable('Element')->find($elementId)->delete();
        }
        
        // Delete the item type.
        $itemType->delete();
    }
    
    /**
     * Log the mapping between a Sept11 collection and an Omeka collection.
     * 
     * @param int $collectionIdOmeka
     */
    protected function _logCollection($collectionIdOmeka)
    {
        $sql = '
        INSERT INTO `sept11_import_collections_log` (
            `collection_id_sept11`, 
            `collection_id_omeka`
        ) VALUES (?, ?)';
        $this->_dbOmeka->query($sql, array($this->_collectionSept11['COLLECTION_ID'], 
                                           $collectionIdOmeka));
    }
    
    /**
     * Log the mapping between a Sept11 object and an Omeka item.
     * 
     * @param int $objectId
     * @param int $itemId
     * @param int $collectionIdOmeka
     */
    protected function _logItem($objectId, $itemId, $collectionIdOmeka)
    {
        $sql = '
        INSERT INTO `sept11_import_items_log` (
            `object_id`, 
            `item_id`, 
            `collection_id_omeka`
        ) VALUES (?, ?, ?)';
        $this->_dbOmeka->query($sql, array($objectId, $itemId, $collectionIdOmeka));
    }
    
    /**
     * Log errors that occur during import.
     * 
     * @param Exception $e
     * @param int $objectId
     * @param int $collectionIdOmeka
     */
    protected function _logError($e, $objectId, $collectionIdOmeka)
    {
        $sql = '
        INSERT INTO `sept11_import_error_log` (
            `object_id`, 
            `collection_id_omeka`, 
            `exception`, 
            `message`,
            `code`,
            `file`,
            `line`,
            `trace`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
        $this->_dbOmeka->query($sql, array($objectId, $collectionIdOmeka, 
                                           get_class($e), $e->getMessage(), 
                                           $e->getCode(), $e->getFile(), 
                                           $e->getLine(), $e->getTraceAsString()));
    }
    
    /**
     * Return the specified Sept11 collection.
     * 
     * @param int $collectionId
     * @return array
     */
    protected function _fetchCollectionSept11($collectionId)
    {
        $sql = '
        SELECT * 
        FROM `COLLECTIONS` 
        WHERE `COLLECTION_ID` = ? 
        LIMIT 1';
        return Sept11_Import::getDbSept11()->fetchRow($sql, $collectionId);
    }
    
    /**
     * Return the specified Sept11 collection objects.
     * 
     * If the collection was previously imported and the process was 
     * interrupted, this will return only those objects that have not already 
     * been imported.
     * 
     * @param bool $idsOnly Return only object IDs, not all object rows. Use 
     * with {@link self::_fetchObject()} for very large collections to avoid 
     * loading the full object result set into memory.
     * @return array
     */
   protected function _fetchCollectionObjectsSept11($idsOnly = false)
    {
        if ($idsOnly) {
            $selectExpression = '`OBJECTS`.`OBJECT_ID`';
            $fetchType = 'fetchCol';
        } else {
            $selectExpression = '*';
            $fetchType = 'fetchAll';
        }
        
        // Get the item log of the last successfully imported object for this 
        // Sept11 collection, if any.
        $sql = '
        SELECT siil.* 
        FROM `sept11_import_collections_log` sicl 
        JOIN `sept11_import_items_log` siil 
        ON sicl.`collection_id_omeka` = siil.`collection_id_omeka` 
        WHERE `collection_id_sept11` = ? 
        ORDER BY siil.`object_id` DESC 
        LIMIT 1';
        $itemLogLastImported = $this->_dbOmeka->fetchRow($sql, $this->_collectionSept11['COLLECTION_ID']);
        
        // If an item log exists, assume the last import was interrupted and the 
        // collection was not deleted. Fetch only those objects that have not 
        // already been imported.
        if ($itemLogLastImported) {
            
            // Delete incompletely imported item(s). This step is necessary 
            // because, if an import is interrupted, one (rarely more than one) 
            // item is likely to have been partially imported after the last 
            // successfully imported item. These items must be deleted so they 
            // can be re-imported.
            $sql = '
            SELECT `id` 
            FROM `' . $this->_dbOmeka->prefix . 'items` 
            WHERE `id` > ?';
            $itemsIncomplete = $this->_dbOmeka->fetchCol($sql, $itemLogLastImported['item_id']);
            foreach ($itemsIncomplete as $itemIncomplete) {
                $item = $this->_dbOmeka->getTable('Item')->find($itemIncomplete);
                if ($item) {
                    $item->delete();
                }
            }
            
            // Fetch only those Sept11 objects with IDs higher than the last 
            // successfully imported object. Returns an empty array if all 
            // objects in this collection have already been imported. It's very 
            // important to order by object ID here, lest resuming an import 
            // will not work.
            $sql = '
            SELECT ' . $selectExpression . ' 
            FROM `COLLECTIONS_OBJECTS` 
            JOIN `OBJECTS` 
            ON `COLLECTIONS_OBJECTS`.`OBJECT_ID` = `OBJECTS`.`OBJECT_ID` 
            WHERE `COLLECTIONS_OBJECTS`.`COLLECTION_ID` = ? 
            AND `COLLECTIONS_OBJECTS`.`OBJECT_ID` > ? 
            ORDER BY `OBJECTS`.`OBJECT_ID`';
            return Sept11_Import::getDbSept11()->$fetchType(
                $sql, 
                array($this->_collectionSept11['COLLECTION_ID'], 
                      $itemLogLastImported['object_id'])
            );
        }
        
        // Otherwise get all objects belonging to this collection. It's very 
        // important to order by object ID here, lest resuming an import will 
        // not work.
        $sql = '
        SELECT ' . $selectExpression . ' 
        FROM `COLLECTIONS_OBJECTS` 
        JOIN `OBJECTS` 
        ON `COLLECTIONS_OBJECTS`.`OBJECT_ID` = `OBJECTS`.`OBJECT_ID` 
        WHERE `COLLECTIONS_OBJECTS`.`COLLECTION_ID` = ? 
        ORDER BY `OBJECTS`.`OBJECT_ID`';
        return Sept11_Import::getDbSept11()->$fetchType($sql, $this->_collectionSept11['COLLECTION_ID']);
    }
    
    /**
     * Return the specified Sept11 object.
     * 
     * @return array
     */
    protected function _fetchObject($objectId)
    {
        $sql = '
        SELECT * 
        FROM `OBJECTS` 
        WHERE `OBJECT_ID` = ? 
        LIMIT 1';
        return Sept11_Import::getDbSept11()->fetchRow($sql, $objectId);
    }
    
    /**
     * Return the specified Sept11 contributor.
     * 
     * @param int $contributorId
     * @return array
     */
    protected function _fetchContributorSept11($contributorId)
    {
        $sql = '
        SELECT * 
        FROM CONTRIBUTORS
        WHERE CONTRIBUTOR_ID = ?';
        return Sept11_Import::getDbSept11()->fetchRow($sql, $contributorId);
    }
    
    /**
     * Determine if the specified collection is public.
     * 
     * @param array $collection Row from Sept11 COLLECTIONS table.
     * @return bool
     */
    protected function _collectionIsPublic(array $collection)
    {
        if (in_array($collection['STATUS_ID'], $this->_privateStatuses)) {
            return false;
        }
        return true;
    }
    
    /**
     * Determine if the specified object is public.
     * 
     * @param array $object Row from Sept11 OBJECTS table.
     * @return bool
     */
    protected function _itemIsPublic(array $object)
    {
        if (in_array($object['OBJECT_POSTING'], $this->_privatePostings)) {
            return false;
        }
        if (in_array($object['CONSENT_ID'], $this->_privateConsents)) {
            return false;
        }
        if (in_array($object['STATUS_ID'], $this->_privateStatuses)) {
            return false;
        }
        return true;
    }
}
