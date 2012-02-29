<?php
require_once 'Sept11/Import/Strategy/StrategyAbstract.php';

class Sept11_Import_Strategy_Chinatown extends Sept11_Import_Strategy_StrategyAbstract
{
    // Set an arbitrarily large and unique Sept11 collection ID.
    const COLLECTION_ID = 1000000;
    
    private $_dbChinatown;
    
    private $_itemTypeMedatada = array(
        'name' => 'Chinatown Interview', 
        'description' => '',
    );
    
    private $_itemTypeElementMetadata = array(
        array('name' => 'Chinatown Interview: Interviewee', 
              'description' => ''), 
        array('name' => 'Chinatown Interview: Interviewer', 
              'description' => ''),
        array('name' => 'Chinatown Interview: Date', 
              'description' => ''),
        array('name' => 'Chinatown Interview: Language', 
              'description' => ''),
        array('name' => 'Chinatown Interview: Occupation', 
              'description' => ''),
        array('name' => 'Chinatown Interview: Interview (en)', 
              'description' => ''),
        array('name' => 'Chinatown Interview: Interview (zh)', 
              'description' => ''),
    );
    
    public function __construct()
    {
        parent::__construct();
        
        // Connect to the Chinatown database.
        $this->_dbChinatown = Zend_Db::factory('Pdo_Mysql', array(
            'host'     => '', 
            'username' => '', 
            'password' => '', 
            'dbname'   => '', 
            'charset'  => 'utf8', // must pass utf8 connection charset
        ));
        
        // Build the pseudo Sept11 collection array.
        $collectionDescription = <<<DESCRIPTION
New York City and the nation were deeply affected by the terrorist attacks of September 11, 2001. But the attacks also had significant consequences on a more local scale: neighborhoods throughout New York City experienced profound changes that will shape their future for some time.

Located just ten blocks from Ground Zero, Chinatown is the largest residential area affected by 9/11. Much of the impact was strikingly visible. For eight days following the attack, for example, Chinatown south of Canal Street was a “frozen zone” in which all vehicular and non-residential pedestrian traffic was prohibited; and, for nearly two months, Chinatown residents and businesses were effectively isolated by the loss of telephone service. But much of 9/11’s impact on Chinatown was less evident.

To better understand the consequences of 9/11 on Chinatown and Chinese New Yorkers, the Museum of Chinese in the Americas partnered with the Columbia University Oral History Research Office (OHRO), the September 11 Digital Archive (911 DA) at The Graduate Center of the City University of New York, and New York University's Asian/Pacific/American Studies Program and Institute (A/P/A). “Ground One” aims to provide an in-depth portrait of the ways in which the identity of a community, largely neglected by national media following 9/11, has been indelibly shaped by that day.

Beginning in Fall 2003, “Ground One” interviewed 30 individuals who lived and worked in Manhattan’s Chinatown. The interviewees represented a diverse cross-section of Chinese Americans, including garment and restaurant workers, community activists, non-profit administrators, union organizers, healthcare and law professionals, senior citizens, and youth. Oral history was employed to understand how people perceived and responded to the tragic events of 9/11 in the context of their life histories. Several overarching themes were selected for this website: Personal Accounts of September 11th; Air Quality/ Health; Jobs, Language & Access; Garment Industry; 9/11 Relief; and Political and Civic Engagement. Presented here is an assemblage of voices from the perspective of a neighborhood just ten blocks away from Ground Zero.
DESCRIPTION;
        
        $this->_collectionSept11 = array(
            'COLLECTION_ID'              => self::COLLECTION_ID, 
            'COLLECTION_TITLE'           => 'Ground One: Voices from Post-911 Chinatown', 
            'COLLECTION_AUTHOR_DESCRIBE' => 'yes', 
            'COLLECTION_DESC'            => $collectionDescription, 
            'COLLECTION_FOLDER_NAME'     => null, 
            'COLLECTION_ANNOTATION'      => null, 
            'COLLECTION_NOTES'           => null, 
            'COLLECTION_PARENT_ID'       => null, 
            'STATUS_ID'                  => 2, 
            'CONTRIBUTOR_ID'             => 0, 
            'INGEST_ID'                  => null, 
        );
    }
    
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
        
        // Insert the Omeka items.
        
        // Make sure GROUP_CONCAT() is not truncated.
        $sql = 'SET SESSION group_concat_max_len = 1000000';
        $this->_dbChinatown->exec($sql);
        
        // Get the interviews.
        $sql = "
        SELECT i.interview_id, i.interviewee, i.interviewer, i.date, i.language, i.occupation, 
        GROUP_CONCAT(t_en.page_text ORDER BY mk.page_number SEPARATOR '\n') interview_en, 
        GROUP_CONCAT(t_zh.page_text ORDER BY mk.page_number SEPARATOR '\n') interview_zh 
        FROM eg_texts t_en 
        JOIN ch_texts_test t_zh 
        ON t_en.page_id = t_zh.page_id 
        JOIN master_key mk 
        ON t_en.page_id = mk.page_id 
        JOIN interview i 
        ON mk.interview_id = i.interview_id 
        GROUP BY mk.interview_id 
        ORDER BY mk.interview_id";
        foreach ($this->_dbChinatown->fetchAll($sql) as $interview) {
            
            // Build the pseudo Sept11 object array.
            $object = array(
                'OBJECT_ID'              => 0, 
                'CONTRIBUTOR_ID'         => 0, 
                'STATUS_ID'              => 2, 
                'CONSENT_ID'             => 5, 
                'SOURCE_ID'              => 6, 
                'OBJECT_MEDIA_TYPE_ID'   => 13, 
                'OBJECT_TITLE'           => $interview['interviewee'], 
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
            
            $metadata = array('item_type_id' => $itemTypeId);
            $elementTexts = array(
                ELEMENT_SET_ITEM_TYPE => array(
                    'Chinatown Interview: Interviewee'    => array(array('text' => $interview['interviewee'], 'html' => false)), 
                    'Chinatown Interview: Interviewer'    => array(array('text' => $interview['interviewer'], 'html' => false)), 
                    'Chinatown Interview: Date'           => array(array('text' => $interview['date'], 'html' => false)), 
                    'Chinatown Interview: Language'       => array(array('text' => $interview['language'], 'html' => false)), 
                    'Chinatown Interview: Occupation'     => array(array('text' => $interview['occupation'], 'html' => false)), 
                    'Chinatown Interview: Interview (en)' => array(array('text' => $interview['interview_en'], 'html' => true)), 
                    'Chinatown Interview: Interview (zh)' => array(array('text' => $interview['interview_zh'], 'html' => false)), 
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
