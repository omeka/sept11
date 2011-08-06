<?php
require_once 'Sept11/Import/Strategy/StrategyAbstract.php';

class Sept11_Import_Strategy_SeiuStories extends Sept11_Import_Strategy_StrategyAbstract
{
    const COLLECTION_ID = 6;
    
    private $_itemTypeMedatada = array(
        'name' => 'SEIU Story', 
        'description' => '',
    );
    
    private $_itemTypeElementMetadata = array(
        array(
            'name' => 'SEIU Story: Story', 
            'description' => '',
        ), 
        array(
            'name' => 'SEIU Story: Local Union', 
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
        foreach ($this->_fetchCollectionObjectsSept11() as $object) {
            
            $metadata = array('item_type_id' => $itemTypeId);
            
            // Set the story.
            $xml = new SimpleXMLElement($object['OBJECT_ABSOLUTE_PATH'], null, true);
            $elementTexts = array(
                ELEMENT_SET_ITEM_TYPE => array(
                    'SEIU Story: Story' => array(array('text' => $xml->STORY, 'html' => false)), 
                    'SEIU Story: Local Union' => array(array('text' => $xml->LOCAL_UNION, 'html' => false)), 
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
