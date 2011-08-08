<?php
require_once 'Sept11/Import/Strategy/NmahCardsAbstract.php';

class Sept11_Import_Strategy_NmahCardsUncategorized extends Sept11_Import_Strategy_NmahCardsAbstract
{
    const COLLECTION_ID = 32;
    
    public function getCollectionIdSept11()
    {
        return self::COLLECTION_ID;
    }
}
