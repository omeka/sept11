<?php
// http://911digitalarchive.org/contribute.php?type=story
require_once 'Sept11/Import/Strategy/StrategyAbstract.php';

class Sept11_Import_Strategy_Sept11Stories extends Sept11_Import_Strategy_StrategyAbstract
{
    const COLLECTION_ID = 23;
    
    private $_itemTypeMedatada = array(
        'name' => '911DA Story', 
        'description' => '',
    );
    
    private $_itemTypeElementMetadata = array(
        array(
            'name' => '911DA Story: Story', 
            'description' => 'Tell us about what you did, saw, or heard on September 11th. Feel free to write as much or as little as you like. Tell us your story:',
        )
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
                ElementSet::ITEM_TYPE_NAME => array(
                    '911DA Story: Story' => array(array('text' => $xml->STORY, 'html' => false))
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
