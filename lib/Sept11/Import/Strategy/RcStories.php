<?php
require_once 'Sept11/Import/Strategy/StrategyAbstract.php';

class Sept11_Import_Strategy_RcStories extends Sept11_Import_Strategy_StrategyAbstract
{
    const COLLECTION_ID = 4;
    
    private $_itemTypeMedatada = array(
        'name' => 'RC Story', 
        'description' => '',
    );
    
    private $_itemTypeElementMetadata = array(
        array(
            'name' => 'RC Story: Story', 
            'description' => '',
        ), 
        array(
            'name' => 'RC Story: Response', 
            'description' => '',
        ), 
        array(
            'name' => 'RC Story: RC Volunteer', 
            'description' => '',
        ), 
        array(
            'name' => 'RC Story: RC Employee', 
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
                ElementSet::ITEM_TYPE_NAME => array(
                    'RC Story: Story' => array(array('text' => $xml->STORY, 'html' => false)), 
                    'RC Story: Response' => array(array('text' => $xml->RESPONSE, 'html' => false)), 
                    'RC Story: RC Volunteer' => array(array('text' => $xml->RC_VOLUNTEER, 'html' => false)), 
                    'RC Story: RC Employee' => array(array('text' => $xml->RC_EMPLOYEE, 'html' => false)), 
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
