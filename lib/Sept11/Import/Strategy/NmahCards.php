<?php
require_once 'Sept11/Import/Strategy/NmahCardsAbstract.php';

class Sept11_Import_Strategy_NmahCards extends Sept11_Import_Strategy_NmahCardsAbstract
{
    const COLLECTION_ID = 31;
    
    public function getCollectionIdSept11()
    {
        return self::COLLECTION_ID;
    }
}
