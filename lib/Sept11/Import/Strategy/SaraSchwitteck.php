<?php
require_once 'Sept11/Import/Strategy/StrategyAbstract.php';

class Sept11_Import_Strategy_SaraSchwitteck extends Sept11_Import_Strategy_StrategyAbstract
{
    const COLLECTION_ID = 44;
    const PATH_ZIP_TMP = '/websites/911digitalarchive.org/omeka/sept11/tmp';
    const PATH_MISC_COLLECTION = '/websites/911digitalarchive.org/REPOSITORY/MISC_COLLECTIONS';
    
    public function delete()
    {
        $this->_deleteCollectionOmeka();
    }
    
    public function import()
    {
        // Insert the Omeka collection.
        $collectionOmekaId = $this->_insertCollection();
        
        // Create a temporary Zip archive from the MISC_COLLECTIONS directory 
        // and remove read_me.txt from it.
        $zipDirName = strrchr($this->_collectionSept11['COLLECTION_FOLDER_NAME'], '/');
        $zipArchivePath = self::PATH_ZIP_TMP . $zipDirName;
        chdir(self::PATH_MISC_COLLECTION);
        exec("zip -r $zipArchivePath .$zipDirName");
        exec("zip -d $zipArchivePath .$zipDirName/read_me.txt");
        
        // Build the pseudo object array.
        $object = array(
            'OBJECT_ID' => 0, 
            'CONTRIBUTOR_ID' => $this->_collectionSept11['CONTRIBUTOR_ID'], 
            'STATUS_ID' => $this->_collectionSept11['STATUS_ID'], 
            'CONSENT_ID' => '', 
            'SOURCE_ID' => '', 
            'OBJECT_MEDIA_TYPE_ID' => '', 
            'OBJECT_TITLE' => '', 
            'OBJECT_DESC' => '', 
            'OBJECT_POSTING' => '', 
            'OBJECT_COPYRIGHT' => '', 
            'OBJECT_ORIG_NAME' => '', 
            'OBJECT_AUTHOR_CREATE' => '', 
            'OBJECT_AUTHOR_DESCRIBE' => '', 
            'OBJECT_DATE_ENTERED' => '', 
            'OBJECT_IP_SOURCE' => '', 
            'OBJECT_ANNOTATION' => '', 
            'OBJECT_NOTES' => '', 
        );
        
        // Insert the item, attaching the Zip archive.
        $metadata = array();
        $elementTexts = array();
        $fileMetadata = array(
            'file_transfer_type' => 'Filesystem', 
            'files' => array("$zipArchivePath.zip"), 
        );
        
        $itemId = $this->_insertItem($collectionOmekaId, $object, 
                                     $metadata, $elementTexts, $fileMetadata);
        
        // Delete the temporary Zip archive.
        unlink("$zipArchivePath.zip");
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
