<?php
require_once 'Sept11/Import/Strategy/EmailsAbstract.php';

class Sept11_Import_Strategy_DojEmails extends Sept11_Import_Strategy_EmailsAbstract
{
    const COLLECTION_ID = 24;
    
    public function delete()
    {
        $this->_deleteCollectionOmeka();
        $this->_deleteItemType();
    }
    
    public function import()
    {
        // Insert the Omeka item type.
        $itemTypeId = $this->_insertItemType();
        
        // Insert an Omeka collection.
        $collectionOmekaId = $this->_insertCollection();
        
        // Insert an Omeka item for every Sept11 object.
        foreach ($this->_fetchCollectionObjectsSept11() as $object) {
            
            $metadata = array('item_type_id' => $itemTypeId);
            
            // Set the email.
            $xml = new SimpleXMLElement($object['OBJECT_ABSOLUTE_PATH'], null, true);
            $elementTexts = array(
                ELEMENT_SET_ITEM_TYPE => array(
                    'Body' => array(array('text' => $xml->EMAIL_TEXT, 'html' => false)), 
                    'Date' => array(array('text' => $xml->EMAIL_DATE, 'html' => false)), 
                )
            );
            
            $itemId = $this->_insertItem($collectionOmekaId, $object, $metadata, $elementTexts);
        }
    }
    
    public function getCollectionIdSept11()
    {
        return self::COLLECTION_ID;
    }
}
