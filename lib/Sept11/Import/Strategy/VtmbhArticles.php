<?php
require_once 'Sept11/Import/Strategy/Abstract.php';

class Sept11_Import_Strategy_VtmbhArticles extends Sept11_Import_Strategy_Abstract
{
    const COLLECTION_ID = 10;
    
    private $_itemTypeMedatada = array(
        'name' => 'VTMBH Article', 
        'description' => '',
    );
    
    private $_itemTypeElementMetadata = array(
        array(
            'name' => 'VTMBH Article: Edition', 
            'description' => '',
        ), 
        array(
            'name' => 'VTMBH Article: Article Order', 
            'description' => '',
        ), 
        array(
            'name' => 'VTMBH Article: Title', 
            'description' => '',
        ), 
        array(
            'name' => 'VTMBH Article: Author', 
            'description' => '',
        ), 
        array(
            'name' => 'VTMBH Article: Publication', 
            'description' => '',
        ), 
        array(
            'name' => 'VTMBH Article: Original Language', 
            'description' => '',
        ), 
        array(
            'name' => 'VTMBH Article: Translator', 
            'description' => '',
        ), 
        array(
            'name' => 'VTMBH Article: Section', 
            'description' => '',
        ), 
        array(
            'name' => 'VTMBH Article: Blurb', 
            'description' => '',
        ), 
        array(
            'name' => 'VTMBH Article: Keywords', 
            'description' => '',
        ), 
        array(
            'name' => 'VTMBH Article: Body', 
            'description' => '',
        ), 
        array(
            'name' => 'VTMBH Article: Line Breaks', 
            'description' => '',
        ), 
        array(
            'name' => 'VTMBH Article: Date', 
            'description' => '',
        ), 
        array(
            'name' => 'VTMBH Article: Thumb', 
            'description' => '',
        ), 
        array(
            'name' => 'VTMBH Article: Article File', 
            'description' => '',
        ), 
        array(
            'name' => 'VTMBH Article: Hit Count', 
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
                    'VTMBH Article: Edition' => array(array('text' => $xml->EDITION, 'html' => false)), 
                    'VTMBH Article: Article Order' => array(array('text' => $xml->ARTICLE_ORDER, 'html' => false)), 
                    'VTMBH Article: Title' => array(array('text' => $xml->TITLE, 'html' => false)), 
                    'VTMBH Article: Author' => array(array('text' => $xml->AUTHOR, 'html' => false)), 
                    'VTMBH Article: Publication' => array(array('text' => $xml->PUBLICATION, 'html' => false)), 
                    'VTMBH Article: Original Language' => array(array('text' => $xml->ORIGINAL_LANGUAGE, 'html' => false)), 
                    'VTMBH Article: Translator' => array(array('text' => $xml->TRANSLATOR, 'html' => false)), 
                    'VTMBH Article: Section' => array(array('text' => $xml->SECTION, 'html' => false)), 
                    'VTMBH Article: Blurb' => array(array('text' => $xml->BLURB, 'html' => false)), 
                    'VTMBH Article: Keywords' => array(array('text' => $xml->KEYWORDS, 'html' => false)), 
                    'VTMBH Article: Body' => array(array('text' => $xml->BODY, 'html' => false)), 
                    'VTMBH Article: Line Breaks' => array(array('text' => $xml->LINE_BREAKS, 'html' => false)), 
                    'VTMBH Article: Date' => array(array('text' => $xml->DATE, 'html' => false)), 
                    'VTMBH Article: Thumb' => array(array('text' => $xml->THUMB, 'html' => false)), 
                    'VTMBH Article: Article File' => array(array('text' => $xml->ARTICLE_FILE, 'html' => false)), 
                    'VTMBH Article: Hit Count' => array(array('text' => $xml->HIT_COUNT, 'html' => false)), 
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
