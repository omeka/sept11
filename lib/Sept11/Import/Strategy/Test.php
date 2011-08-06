<?php
require_once 'Sept11/Import/Strategy/StrategyAbstract.php';

/**
 * Test import strategy. 
 * 
 * Use this to run discrete tests on the import process. Provides a mock Sept11 
 * collection and object to test against.
 */
class Sept11_Import_Strategy_Test extends Sept11_Import_Strategy_StrategyAbstract
{
    const COLLECTION_ID = 0;
    
    /** mock Sept11 collection */
    protected $_collectionSept11 = array(
        'COLLECTION_ID' => 0, 
        'COLLECTION_TITLE' => 'Test Collection Name', 
        'COLLECTION_AUTHOR_DESCRIBE' => null, 
        'COLLECTION_DESC' => 'Test Collection Description', 
        'COLLECTION_FOLDER_NAME' => null, 
        'COLLECTION_ANNOTATION' => null, 
        'COLLECTION_NOTES' => null, 
        'COLLECTION_PARENT_ID' => 0, 
        'STATUS_ID' => 0, 
        'CONTRIBUTOR_ID' => 0, 
        'INGEST_ID' => 0, 
    );
    
    /** mock Sept11 object */
    protected $_object = array(
        'CO_ID' => 0, 
        'CO_TMESTAMP' => null, 
        'CO_NOTES' => null, 
        'CO_TYPE' => null, 
        'OBJECT_ID' => 0, 
        'COLLECTION_ID' => 0, 
        'OBJECT_TITLE' => null, 
        'OBJECT_ORIG_NAME' => null, 
        'OBJECT_AUTHOR_CREATE' => null, 
        'OBJECT_AUTHOR_DESCRIBE' => null, 
        'OBJECT_DATE_ENTERED' => null, 
        'OBJECT_IP_SOURCE' => null, 
        'OBJECT_DESC' => null, 
        'OBJECT_ABSOLUTE_PATH' => null, 
        'OBJECT_MD5_CHECKSUM' => null, 
        'OBJECT_MIME' => null, 
        'OBJECT_TYPE' => null, 
        'OBJECT_SIZE' => null, 
        'OBJECT_POSTING' => 'yes', 
        'OBJECT_COPYRIGHT' => 'yes', 
        'OBJECT_ANNOTATION' => null, 
        'OBJECT_SEARCHABLE_TEXT' => null, 
        'OBJECT_NOTES' => null, 
        'SOURCE_ID' => 0, 
        'OBJECT_MEDIA_TYPE_ID' => 0, 
        'STATUS_ID' => 0, 
        'CONSENT_ID' => 0, 
        'CONTRIBUTOR_ID' => 0, 
        'INGEST_ID' => 0, 
    );
    
    /** Override the constructor */
    public function __construct()
    {
        // Cache databases.
        $this->_dbSept11 = Sept11_Import::getDbSept11();
        $this->_dbOmeka = Sept11_Import::getDbOmeka();
    }
    
    public function delete()
    {
        $this->_deleteCollectionOmeka();
    }
    
    public function import()
    {
        $collectionOmekaId = $this->_insertCollection();
        $object = $this->_object;
        
        $metadata = array();
        $elementTexts = array();
        $fileMetadata = array();
        
        $itemId = $this->_insertItem($collectionOmekaId, $object, $metadata, 
                                     $elementTexts, $fileMetadata);
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
