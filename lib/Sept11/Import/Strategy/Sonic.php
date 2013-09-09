<?php
class Sept11_Import_Strategy_Sonic extends Sept11_Import_Strategy_StrategyAbstract
{
    const REPOSITORY_PATH = '/websites/sept11/home/www/911digitalarchive.org/sonicmedia/';
    
    protected $_dbSonic;
    
    protected $_contributorIds = array();
    
    protected $_itemTypeMedatada = array(
        'name' => 'Sonic Memorial', 
        'description' => '',
    );
    
    protected $_itemTypeElementMetadata = array(
        array('name' => 'Sonic Memorial: Name', 
              'description' => ''), 
        array('name' => 'Sonic Memorial: Source Ref Name', 
              'description' => ''), 
        array('name' => 'Sonic Memorial: Comments', 
              'description' => ''),
        array('name' => 'Sonic Memorial: Source Type', 
              'description' => ''),
        array('name' => 'Sonic Memorial: Duration', 
              'description' => ''),
        array('name' => 'Sonic Memorial: Permission', 
              'description' => ''),
        array('name' => 'Sonic Memorial: Creation Date', 
              'description' => ''),
        array('name' => 'Sonic Memorial: Contact', 
              'description' => ''),
        array('name' => 'Sonic Memorial: Status', 
              'description' => ''),
        array('name' => 'Sonic Memorial: Is Browsable', 
              'description' => ''),
        array('name' => 'Sonic Memorial: Keyword', 
              'description' => ''),
    );
    
    protected $_collectionMetadata = array(
        'name' => 'The Sonic Memorial Project', 
        'public' => true, 
    );
    
    public function __construct()
    {
        parent::__construct();
        
        // Connect to the sonic database.
        $this->_dbSonic = Sept11_Import::getDbSonic();
        
        // Set the collection description.
        $this->_collectionMetadata['description'] = <<<DESCRIPTION
SonicMemorial.org is an open archive and an online audio installation of the history of The World Trade Center. It collected stories, ambient sounds, voicemails, and archival recordings to tell the rich history of the twin towers, the neighborhood and the events of 9/11.

Led by NPR's Lost & Found Sound, The Sonic Memorial Project is a cross-media collaboration of more than 50 independent radio and new media producers, artists, historians, and people from around the world who have contributed personal and archival recordings. To date, we have gathered more than 1,000 contributions, many of which have been woven into feature stories by Lost & Found Sound and broadcast on NPR.

SonicMemorial.org is produced by Picture Projects and dotsperinch in collaboration with Lost & Found Sound. 
DESCRIPTION;
    }
    
    public function delete(){}
    
    public function import()
    {
        // Insert the Omeka item type.
        $itemTypeId = $this->_insertItemType();
        
        // Insert the Omeka collection.
        $collectionOmekaId = $this->_insertCollection();
        
        // Insert the items.
        $sql = 'SELECT * FROM sound_unit';
        foreach ($this->_dbSonic->fetchAll($sql) as $soundUnit) {
            
            // Build the pseudo Sept11 object array.
            $object = array(
                'OBJECT_ID'              => 0, 
                'CONTRIBUTOR_ID'         => $this->_getContributorId($soundUnit), 
                'STATUS_ID'              => $this->_getStatusId($soundUnit), 
                'CONSENT_ID'             => Sept11_Import::CONSENT_UNKNOWN, 
                'SOURCE_ID'              => Sept11_Import::SOURCE_DIGITALLY_RECORDED, 
                'OBJECT_MEDIA_TYPE_ID'   => Sept11_Import::MEDIA_TYPE_AUDIO, 
                'OBJECT_TITLE'           => $soundUnit['title'], 
                'OBJECT_DESC'            => $soundUnit['description'], 
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
            
            // Set the item metadata.
            $metadata = array('item_type_id' => $itemTypeId);
            
            // Set the element texts.
            $elementTexts = array(
                ElementSet::ITEM_TYPE_NAME => array(
                    'Sonic Memorial: Name'            => array(array('text' => $soundUnit['name'], 'html' => false)), 
                    'Sonic Memorial: Source Ref Name' => array(array('text' => $soundUnit['source_ref_name'], 'html' => false)), 
                    'Sonic Memorial: Comments'        => array(array('text' => $soundUnit['comments'], 'html' => false)), 
                    'Sonic Memorial: Source Type'     => array(array('text' => $soundUnit['source_type'], 'html' => false)), 
                    'Sonic Memorial: Duration'        => array(array('text' => $soundUnit['duration'], 'html' => false)), 
                    'Sonic Memorial: Permission'      => array(array('text' => $soundUnit['permission'], 'html' => false)), 
                    'Sonic Memorial: Creation Date'   => array(array('text' => $soundUnit['creation_date'], 'html' => false)), 
                    'Sonic Memorial: Contact'         => array(array('text' => $soundUnit['contact'], 'html' => false)), 
                    'Sonic Memorial: Status'          => array(array('text' => $soundUnit['status'], 'html' => false)), 
                    'Sonic Memorial: Is Browsable'    => array(array('text' => $soundUnit['is_browsable'], 'html' => false)), 
                ), 
            );
            
            // Set the keywords.
            $sql = '
            SELECT word 
            FROM keyword 
            JOIN su_2_keyword 
            ON id = keyword_id 
            WHERE su_id = ?';
            $keywords = $this->_dbSonic->fetchAll($sql, $soundUnit['id'], Zend_Db::FETCH_COLUMN);
            foreach ($keywords as $keyword) {
                $elementTexts[ElementSet::ITEM_TYPE_NAME]['Sonic Memorial: Keyword'][] = array('text' => $keyword, 'html' => false);
            }
            
            // Set the file metadata, including sound unit children and assets.
            $fileMetadata = array('file_transfer_type' => 'Filesystem');
            
            $sql = '
            SELECT * 
            FROM sound_unit_child 
            JOIN su_2_child 
            ON id = su_child_id 
            WHERE su_id = ?';
            $children = $this->_dbSonic->fetchAll($sql, $soundUnit['id']);
            foreach ($children as $child) {
                if (!is_file(self::REPOSITORY_PATH . $child['uri'])) {
                    continue;
                }
                $fileMetadata['files'][] = array(
                    'source' => self::REPOSITORY_PATH . $child['uri'], 
                    'name' => $child['pre_upload_filename'], 
                    'metadata' => array(
                        'Dublin Core' => array(
                            'Description' => array(array('text' => $child['description'], 'html' => false))
                        ) 
                    )
                );
            }
            
            $sql = '
            SELECT * 
            FROM associated_asset 
            JOIN su_2_asset 
            ON id = asset_id 
            WHERE su_id = ?';
            $assets = $this->_dbSonic->fetchAll($sql, $soundUnit['id']);
            foreach ($assets as $asset) {
                if (!is_file(self::REPOSITORY_PATH . $asset['asset_uri'])) {
                    continue;
                }
                $fileMetadata['files'][] = array(
                    'source' => self::REPOSITORY_PATH . $asset['asset_uri'], 
                    'name' => $asset['pre_upload_filename'], 
                    'metadata' => array(
                        'Dublin Core' => array(
                            'Title' => array(array('text' => $asset['title'], 'html' => false)), 
                            'Description' => array(array('text' => $asset['caption'], 'html' => false)), 
                        ) 
                    )
                );
            }
            
            $itemId = $this->_insertItem($collectionOmekaId, $object, $metadata, $elementTexts, $fileMetadata);
        }
    }
    
    /**
     * Get the contributor ID.
     * 
     * Insert the contributor if it has not already been imported.
     * 
     * @param array $soundUnit
     * @return int
     */
    protected function _getContributorId($soundUnit)
    {
        // The contributor has already been inserted.
        if (array_key_exists($soundUnit['id'], $this->_contributorIds)) {
            $contributorId = $this->_contributorIds[$soundUnit['id']];
        // The contributor has not been imported.
        } else {
            $contributorId = $this->_dbOmeka->insert(
                'sept11_contributors', 
                array('name'        => "{$soundUnit['first_name']} {$soundUnit['last_name']}", 
                      'phone'       => "{$soundUnit['phone_1']} {$soundUnit['phone_2']}", 
                      'email'       => $soundUnit['email'], 
                      'residence'   => "{$soundUnit['street_1']} {$soundUnit['street_2']} {$soundUnit['city']} {$soundUnit['state']} {$soundUnit['zipcode']} {$soundUnit['country']} {$soundUnit['province']}", 
                      'zipcode'     => $soundUnit['zipcode'], 
                      'posting'     => 'no')
            );
            // Cache the ID.
            $this->_contributorIds[$soundUnit['id']] = $contributorId;
        }
        return $contributorId;
    }
    
    /**
     * Get the status ID.
     * 
     * As far as I can tell, the only sound units that are not public are those 
     * with status="new submission". Apparently the is_browsable and permission 
     * fields play no part in a sound unit's visibility.
     * 
     * @param array $soundUnit
     * @return int
     */
    protected function _getStatusId($soundUnit)
    {
        if ('new submission' == $soundUnit['status']) {
            return Sept11_Import::STATUS_REVIEW;
        }
        return Sept11_Import::STATUS_APPROVED;
    }
    
    /**
     * Override parent::_insertCollection()
     * 
     * @return int
     */
    protected function _insertCollection()
    {
        $collectionOmeka = insert_collection($this->_collectionMetadata);
        return $collectionOmeka->id;
    }
    
    public function getCollectionIdSept11(){}
    
    protected function _getItemTypeMetadata()
    {
        return $this->_itemTypeMedatada;
    }
    
    protected function _getItemTypeElementMetadata()
    {
        return $this->_itemTypeElementMetadata;
    }
}
