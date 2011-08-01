<?php
require_once 'Sept11/Import/Strategy/Abstract.php';

class Sept11_Import_Strategy_NmahCards extends Sept11_Import_Strategy_Abstract
{
    const COLLECTION_ID = 31;
    
    public function delete()
    {
        $this->_deleteCollectionOmeka();
    }
    
    public function import()
    {
        // Insert the Omeka collection.
        $collectionOmekaId = $this->_insertCollection();
        
        // Insert an Omeka item for every Sept11 object.
        foreach ($this->_fetchCollectionObjectIdsSept11() as $objectId) {
            
            $object = $this->_fetchObject($objectId);
            
            echo $object['OBJECT_ABSOLUTE_PATH'] . "\n";
            
            $xml = new SimpleXMLElement($object['OBJECT_ABSOLUTE_PATH'], null, true);
            $files = array();
            foreach ($xml->FILEPATH as $filepath) {
                if (preg_match('/^tifs\//', $filepath)) {
                    $files[] = '/websites/911digitalarchive.org/REPOSITORY/SMITHCARDS/' . $filepath; 
                }
            }
            natsort($files);
            
            $metadata = array();
            $elementTexts = array();
            $fileMetadata = array(
                'file_transfer_type' => 'Filesystem', 
                'files' => $files, 
            );
            
            $itemId = $this->_insertItem($collectionOmekaId, $object, $metadata, $elementTexts, $fileMetadata);
        }
    }
    
    public function resume()
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
        
        // Get the last imported item log for this collection.
        $sql = '
        SELECT siil.* 
        FROM `sept11_import_collections_log` sicl 
        JOIN `sept11_import_items_log` siil 
        ON sicl.`collection_id_omeka` = siil.`collection_id_omeka` 
        WHERE `collection_id_sept11` = ? 
        ORDER BY siil.`object_id` DESC 
        LIMIT 1';
        $itemLogLastImported = $this->_dbOmeka->fetchRow($sql, $this->_collectionSept11['COLLECTION_ID']);
        print_r($itemLogLastImported);
        
        // Delete items that were not completely imported during the last 
        // import.
        
        // Fetch only those Sept11 ojects with IDs higher than the last 
        // successfully imported object.
        
        // Import as normal.
    }
    
    public function getCollectionIdSept11()
    {
        return self::COLLECTION_ID;
    }
    
    protected function _getItemTypeMetadata()
    {}
    
    protected function _getItemTypeElementMetadata()
    {}
}
