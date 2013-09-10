<?php
require_once 'Sept11/Import/Strategy/StrategyAbstract.php';

/**
 * Extend email import strategies off this class.
 */
abstract class Sept11_Import_Strategy_NmahCardsAbstract extends Sept11_Import_Strategy_StrategyAbstract
{
    private $_itemTypeMetadata = array(
        'name' => 'NMAH Card', 
        'description' => '',
    );
    
    private $_itemTypeElementMetadata = array(
        array(
            'name' => 'NMAH Card: Card Version', 
            'description' => '',
        ), 
        array(
            'name' => 'NMAH Card: Image Available', 
            'description' => '',
        ), 
    );
    
    public function delete()
    {
        $this->_deleteCollectionOmeka();
        $this->_deleteItemType();
    }
    
    public function import()
    {
        // Insert the Omeka item type.
        $itemTypeId = $this->_insertItemType();
        
        // Insert the Omeka collection.
        $collectionOmekaId = $this->_insertCollection();
        
        // Insert an Omeka item for every Sept11 object.
        foreach ($this->_fetchCollectionObjectsSept11(true) as $objectId) {
            
            $object = $this->_fetchObject($objectId);
            
            $xml = new SimpleXMLElement($object['OBJECT_ABSOLUTE_PATH'], null, true);
            $files = array();
            foreach ($xml->FILEPATH as $filepath) {
                if (preg_match('/^tifs\//', $filepath)) {
                    $files[] = '/websites/sept11/home/www/911digitalarchive.org/REPOSITORY/SMITHCARDS/' . $filepath; 
                }
            }
            natsort($files);
            
            $metadata = array('item_type_id' => $itemTypeId);
            $elementTexts = array();
            if (isset($xml->META)) {
                if (isset($xml->META['card_version'])) {
                    $elementTexts[ElementSet::ITEM_TYPE_NAME]['NMAH Card: Card Version'] = array(array('text' => (string) $xml->META['card_version'], 'html' => false));
                }
                if (isset($xml->META['card_version'])) {
                    $elementTexts[ElementSet::ITEM_TYPE_NAME]['NMAH Card: Image Available'] = array(array('text' => (string) $xml->META['image_available'], 'html' => false));
                }
            }
            $fileMetadata = array(
                'file_transfer_type' => 'Filesystem', 
                'files' => $files, 
            );
            
            $itemId = $this->_insertItem($collectionOmekaId, $object, $metadata, $elementTexts, $fileMetadata);
        }
    }
    
    protected function _getItemTypeMetadata()
    {
        return $this->_itemTypeMetadata;
    }
    
    protected function _getItemTypeElementMetadata()
    {
        return $this->_itemTypeElementMetadata;
    }
}
