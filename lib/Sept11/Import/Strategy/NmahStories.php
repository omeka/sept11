<?php
// http://911digitalarchive.org/smithsonian/add_story.html
require_once 'Sept11/Import/Strategy/Abstract.php';

class Sept11_Import_Strategy_NmahStories extends Sept11_Import_Strategy_Abstract
{
    const COLLECTION_ID = 30;
    
    private $_itemTypeMedatada = array(
        'name' => 'NMAH Story', 
        'description' => '',
    );
    
    private $_itemTypeElementMetadata = array(
        array(
            'name' => 'NMAH Story: Story', 
            'description' => 'How did you witness history on September 11th? Share your experience.',
        ), 
        array(
            'name' => 'NMAH Story: Life Changed', 
            'description' => 'Has your life changed because of September 11, 2001? If so, tell us how.',
        ), 
        array(
            'name' => 'NMAH Story: Remembered', 
            'description' => 'What do you think should be remembered about September 11th?',
        ), 
        array(
            'name' => 'NMAH Story: Flag', 
            'description' => 'Did you fly an American flag after the events of September 11th? Have your feelings about the American flag changed as a result of September 11th?',
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
        foreach ($this->_fetchCollectionObjectsSept11() as $object) {
            
            $metadata = array('item_type_id' => $itemTypeId);
            
            // Set the story.
            $xml = new SimpleXMLElement($object['OBJECT_ABSOLUTE_PATH'], null, true);
            $elementTexts = array(
                ELEMENT_SET_ITEM_TYPE => array(
                    'NMAH Story: Story' => array(array('text' => $xml->STORY, 'html' => false)), 
                    'NMAH Story: Life Changed' => array(array('text' => $xml->LIFE_CHANGED, 'html' => false)), 
                    'NMAH Story: Remembered' => array(array('text' => $xml->REMEMBERED, 'html' => false)), 
                    'NMAH Story: Flag' => array(array('text' => $xml->FLAG, 'html' => false)), 
                )
            );
            
            $itemId = $this->_insertItem($collectionOmekaId, $object, $metadata, $elementTexts);
        }
    }
    
    public function getCollectionIdSept11()
    {
        return self::COLLECTION_ID;
    }
    
    protected function _getItemTypeMetadata()
    {
        return $this->_itemTypeMedatada;
    }
    
    protected function _getItemTypeElementMetadata()
    {
        return $this->_itemTypeElementMetadata;
    }
}
