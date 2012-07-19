<?php
require_once 'Sept11/Import/Strategy/StrategyAbstract.php';
class Sept11_Import_Strategy_Hiny extends Sept11_Import_Strategy_StrategyAbstract
{
    // Set an arbitrarily large and unique Sept11 collection ID.
    const COLLECTION_ID = 1000001;
    
    private $_dbHiny;
    
    private $_contributorIds = array();
    
    private $_itemTypeMedatada = array(
        'name' => 'HINY Photo', 
        'description' => '',
    );
    
    private $_itemTypeElementMetadata = array(
        array('name' => 'HINY Photo: Description', 
              'description' => ''), 
        array('name' => 'HINY Photo: Notes', 
              'description' => ''), 
        array('name' => 'HINY Photo: Caption', 
              'description' => ''), 
        array('name' => 'HINY Photo: Submitted On', 
              'description' => ''), 
        array('name' => 'HINY Photo: Category', 
              'description' => ''), 
        array('name' => 'HINY Photo: Comment', 
              'description' => ''), 
    );
    
    public function __construct()
    {
        parent::__construct();
        
        $this->_dbHiny = Sept11_Import::getDbHiny();
        
        $collectionDescription = <<<DESCRIPTION
In response to the World Trade Center tragedy, and to the unprecedented flood of images that have resulted from it, a unique exhibition and sale of photographs was displayed in a store front in SOHO. HERE IS NEW YORK is not a conventional gallery show. It is something new, a show tailored to the nature of the event, and to the response it has elicited. The exhibition is subtitled "A Democracy of Photographs" because anyone and everyone who has taken pictures relating to the tragedy was invited to submit their images to the gallery, where they were digitally scanned, printed and displayed on the walls alongside the work of top photojournalists and other professional photographers. All of the prints which HERE IS NEW YORK displays were sold to the public for $25, regardless of their provenance. The net proceeds from the sale of these prints went to the Children's Aid Society WTC Relief Fund, for the benefit of the thousands of children who are among the greatest victims of this catastrophe.

The causes and effects of the events of 9/11/2001 are by no means clear, and will not be for a very long time. What is clear, though, is this: in order to restore our sense of equilibrium as a nation, as a city, and particularly as a community, we need to develop a new way of looking at and thinking about history, as well as a way of making sense of all of the images which continue to haunt us.

Now That we no longer have a gallery, it is over the web that we keep this project alive. Our intention is to display the widest possible variety of pictures from the widest possible variety of sources, believing as we do that the World Trade Center disaster and its aftermath has ushered in a new period in our history, one which demands that we look at and think about images in a new and unconventional way.

HERE IS NEW YORK is a non for profit 501(c)(3) foundation organized for purposes that are exclusively charitable and educational.
DESCRIPTION;
        
        $this->_hinyCollections = array(
            0 => array(
                'COLLECTION_ID'              => 1000001, 
                'COLLECTION_TITLE'           => 'Here Is New York Photos', 
                'COLLECTION_AUTHOR_DESCRIBE' => 'yes', 
                'COLLECTION_DESC'            => $collectionDescription, 
                'COLLECTION_FOLDER_NAME'     => null, 
                'COLLECTION_ANNOTATION'      => null, 
                'COLLECTION_NOTES'           => null, 
                'COLLECTION_PARENT_ID'       => null, 
                'STATUS_ID'                  => 2, 
                'CONTRIBUTOR_ID'             => 0, 
                'INGEST_ID'                  => null, 
            ), 
            1 => array(
                'COLLECTION_ID'              => 1000002, 
                'COLLECTION_TITLE'           => 'World Trade Center', 
                'COLLECTION_AUTHOR_DESCRIBE' => 'yes', 
                'COLLECTION_DESC'            => null, 
                'COLLECTION_FOLDER_NAME'     => null, 
                'COLLECTION_ANNOTATION'      => null, 
                'COLLECTION_NOTES'           => null, 
                'COLLECTION_PARENT_ID'       => null, 
                'STATUS_ID'                  => 2, 
                'CONTRIBUTOR_ID'             => 0, 
                'INGEST_ID'                  => null, 
            ), 
            2 => array(
                'COLLECTION_ID'              => 1000003, 
                'COLLECTION_TITLE'           => 'HUF', 
                'COLLECTION_AUTHOR_DESCRIBE' => 'yes', 
                'COLLECTION_DESC'            => null, 
                'COLLECTION_FOLDER_NAME'     => null, 
                'COLLECTION_ANNOTATION'      => null, 
                'COLLECTION_NOTES'           => null, 
                'COLLECTION_PARENT_ID'       => null, 
                'STATUS_ID'                  => 2, 
                'CONTRIBUTOR_ID'             => 0, 
                'INGEST_ID'                  => null, 
            ),
            3 => array(
                'COLLECTION_ID'              => 1000004, 
                'COLLECTION_TITLE'           => 'Here is New York', 
                'COLLECTION_AUTHOR_DESCRIBE' => 'yes', 
                'COLLECTION_DESC'            => null, 
                'COLLECTION_FOLDER_NAME'     => null, 
                'COLLECTION_ANNOTATION'      => null, 
                'COLLECTION_NOTES'           => null, 
                'COLLECTION_PARENT_ID'       => null, 
                'STATUS_ID'                  => 2, 
                'CONTRIBUTOR_ID'             => 0, 
                'INGEST_ID'                  => null, 
            ),
            4 => array(
                'COLLECTION_ID'              => 1000005, 
                'COLLECTION_TITLE'           => 'Washington, D.C.', 
                'COLLECTION_AUTHOR_DESCRIBE' => 'yes', 
                'COLLECTION_DESC'            => null, 
                'COLLECTION_FOLDER_NAME'     => null, 
                'COLLECTION_ANNOTATION'      => null, 
                'COLLECTION_NOTES'           => null, 
                'COLLECTION_PARENT_ID'       => null, 
                'STATUS_ID'                  => 2, 
                'CONTRIBUTOR_ID'             => 0, 
                'INGEST_ID'                  => null, 
            ),
            5 => array(
                'COLLECTION_ID'              => 1000006, 
                'COLLECTION_TITLE'           => 'Pennsylvania', 
                'COLLECTION_AUTHOR_DESCRIBE' => 'yes', 
                'COLLECTION_DESC'            => null, 
                'COLLECTION_FOLDER_NAME'     => null, 
                'COLLECTION_ANNOTATION'      => null, 
                'COLLECTION_NOTES'           => null, 
                'COLLECTION_PARENT_ID'       => null, 
                'STATUS_ID'                  => 2, 
                'CONTRIBUTOR_ID'             => 0, 
                'INGEST_ID'                  => null, 
            ),
        );
    }
    
    public function delete()
    {
        $this->_deleteItemType();
        foreach ($this->_hinyCollections as $hinyCollectionId => $hinyCollection) {
            $this->_collectionSept11 = $hinyCollection;
            $this->_deleteCollectionOmeka();
        }
    }
    
    public function import()
    {
        $itemTypeId = $this->_insertItemType();
        
        // Set the collections.
        foreach ($this->_hinyCollections as $hinyCollectionId => $hinyCollection) {
            
            // Be sure to set parent::$_collectionSept11 before calling 
            // parent::_insertCollection().
            $this->_collectionSept11 = $hinyCollection;
            
            if (0 == $hinyCollectionId) {
                $parentCollectionOmekaId = $this->_insertCollection();
                // Cache the Omeka collection ID.
                $this->_hinyCollections[0]['omeka_collection_id'] = $parentCollectionOmekaId;
            } else {
                $childCollectionOmekaId = $this->_insertCollection();
                // Cache the Omeka collection ID.
                $this->_hinyCollections[$hinyCollectionId]['omeka_collection_id'] = $childCollectionOmekaId;
                
                // Save the collection parent/child relationship.
                $collectionTree = new CollectionTree;
                $collectionTree->collection_id = $childCollectionOmekaId;
                $collectionTree->parent_collection_id = $parentCollectionOmekaId;
                $collectionTree->save();
            }
        }
        
        // Set the items.
        $sql = "
        SELECT PhotoID, ContributorID, Description, Notes, Caption, SubmittedOn 
        FROM Photo";
        foreach ($this->_dbHiny->fetchAll($sql) as $photo) {
            
            // Set the item's collection.
            $sql = "
            SELECT PhotoCollectionID 
            FROM PhotoCollectionLK 
            WHERE PhotoID = ? 
            LIMIT 1";
            $hinyCollectionId = $this->_dbHiny->fetchOne($sql, $photo['PhotoID']);
            if ($hinyCollectionId) {
                $collectionOmekaId = $this->_hinyCollections[$hinyCollectionId]['omeka_collection_id'];
            } else {
                $collectionOmekaId = $this->_hinyCollections[0]['omeka_collection_id'];
            }
            
            
            // Build the pseudo Sept11 object array.
            $object = array(
                'OBJECT_ID'              => 0, 
                'CONTRIBUTOR_ID'         => $this->_getContributorId($photo), 
                'STATUS_ID'              => $this->_getStatusId($photo), 
                'CONSENT_ID'             => $this->_getConsentId($photo), 
                'SOURCE_ID'              => Sept11_Import::SOURCE_DIGITAL_IMAGE, 
                'OBJECT_MEDIA_TYPE_ID'   => Sept11_Import::MEDIA_TYPE_STILL_IMAGE, 
                'OBJECT_TITLE'           => null, 
                'OBJECT_DESC'            => null, 
                'OBJECT_POSTING'         => 'unknown', 
                'OBJECT_COPYRIGHT'       => 'unknown', 
                'OBJECT_ORIG_NAME'       => null, 
                'OBJECT_AUTHOR_CREATE'   => null, 
                'OBJECT_AUTHOR_DESCRIBE' => null, 
                'OBJECT_DATE_ENTERED'    => null, 
                'OBJECT_IP_SOURCE'       => null, 
                'OBJECT_ANNOTATION'      => null, 
                'OBJECT_NOTES'           => null, 
            );
            
            // Set photo categories.
            $sql = "
            SELECT pc.PhotoCategoryName 
            FROM PhotoCategoryLK pclk 
            JOIN PhotoCategory pc 
            ON pclk.PhotoCategoryID = pc.PhotoCategoryID 
            WHERE pclk.PhotoID = ?";
            $categories = $this->_dbHiny->fetchAll($sql, $photo['PhotoID']);
            $elementTextCategories = array();
            foreach ($categories as $category) {
                $elementTextCategories[] = array('text' => $category['PhotoCategoryName'], 'html' => false);
            }
            
            // Set photo comments.
            $sql = "SELECT * FROM Comments WHERE photoID = ?";
            $comments = $this->_dbHiny->fetchAll($sql, $photo['PhotoID']);
            $elementTextComments = array();
            foreach ($comments as $comment) {
                $commentText = array($comment['title'], $comment['comment'], $comment['posted']);
                $elementTextComments[] = array('text' => implode("\n\n", $commentText), 'html' => false);
            }
            
            $metadata = array('item_type_id' => $itemTypeId);
            $elementTexts = array(
                ELEMENT_SET_ITEM_TYPE => array(
                    'HINY Photo: Description' => array(array('text' => $photo['Description'], 'html' => false)), 
                    'HINY Photo: Notes' => array(array('text' => $photo['Notes'], 'html' => false)), 
                    'HINY Photo: Caption' => array(array('text' => $photo['Caption'], 'html' => false)), 
                    'HINY Photo: Submitted On' => array(array('text' => $photo['SubmittedOn'], 'html' => false)), 
                    'HINY Photo: Category' => $elementTextCategories, 
                    'HINY Photo: Comment' => $elementTextComments, 
                )
            );
            
            $fileMetadata = array(
                'file_transfer_type' => 'Filesystem', 
                'files' => '/websites/911digitalarchive.org/hiny/jpegs/photos/' . str_pad($photo['PhotoID'], 4, '0', STR_PAD_LEFT) . '.jpg', 
            );
            
            $itemId = $this->_insertItem($collectionOmekaId, $object, $metadata, 
                                         $elementTexts, $fileMetadata);
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
    
    private function _getContributorId($photo)
    {
        // The contributor has already been inserted.
        if (array_key_exists($photo['ContributorID'], $this->_contributorIds)) {
            $contributorId = $this->_contributorIds[$photo['ContributorID']];
        // The contributor has not been imported.
        } else {
            $sql = "SELECT * FROM Contributor WHERE ContributorID = ?";
            $contributor = $this->_dbHiny->fetchRow($sql, $photo['ContributorID']);
            $contributorId = $this->_dbOmeka->insert(
                'sept11_contributors', 
                array('name'        => $contributor['FirstName'] . ' ' . $contributor['LastName'], 
                      'phone'       => $contributor['Phone1'] . ' ' . $contributor['Phone2'], 
                      'email'       => $contributor['Email'], 
                      'residence'   => $contributor['Address1'] . ' ' . $contributor['Address2'] . ' ' . $contributor['Region'], 
                      'zipcode'     => $contributor['PostalCode'], 
                      'notes'       => $contributor['Notes'], 
                      'posting'     => 'no')
            );
            // Cache the ID.
            $this->_contributorIds[$photo['ContributorID']] = $contributorId;
        }
        return $contributorId;
    }
    
    private function _getStatusId($photo)
    {
        // Photos where PhotoStatusID != 3 are not public.
        $sql = "
        SELECT PhotoStatusId 
        FROM PhotoStatusLK 
        WHERE PhotoStatusID = 3 
        AND PhotoID = ?";
        if ($this->_dbHiny->fetchOne($sql, $photo['PhotoID'])) {
            return Sept11_Import::STATUS_APPROVED;
        } else {
            return Sept11_Import::STATUS_PRIVATE;
        }
    }
    
    private function _getConsentId($photo)
    {
        // Photos where PhotoStatusID != 3 are not public.
        $sql = "
        SELECT PhotoRightId 
        FROM PhotoRightsLK 
        WHERE PhotoRightID = 3 
        AND PhotoID = ?";
        if ($this->_dbHiny->fetchOne($sql, $photo['PhotoID'])) {
            return Sept11_Import::CONSENT_FULL;
        } else {
            return Sept11_Import::CONSENT_CONDITIONAL;
        }
    }
}
