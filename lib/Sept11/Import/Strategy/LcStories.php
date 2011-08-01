<?php
// http://old.911digitalarchive.org/lc/911-add-story.html
require_once 'Sept11/Import/Strategy/Abstract.php';

class Sept11_Import_Strategy_LcStories extends Sept11_Import_Strategy_Abstract
{
    const COLLECTION_ID = 3;
    
    private $_itemTypeMedatada = array(
        'name' => 'LC Story', 
        'description' => '',
    );
    
    private $_itemTypeElementMetadata = array(
        array(
            'name' => 'LC Story: Story', 
            'description' => 'Where were you on September 11, 2001 when you heard the news? (Please share your experience of learning of the events and what you did afterwards.)',
        ), 
        array(
            'name' => 'LC Story: Memory', 
            'description' => 'What is your strongest memory of that day?',
        ), 
        array(
            'name' => 'LC Story: Affects', 
            'description' => 'How do you perceive that the events of September 11, 2001 have affected this country and/or you personally?',
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
                    'LC Story: Story' => array(array('text' => $xml->STORY, 'html' => false)), 
                    'LC Story: Memory' => array(array('text' => $xml->MEMORY, 'html' => false)), 
                    'LC Story: Affects' => array(array('text' => $xml->AFFECTS, 'html' => false)), 
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
