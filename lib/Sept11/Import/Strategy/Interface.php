<?php
interface Sept11_Import_Strategy_Interface
{
    /**
     * Delete the Omeka collection.
     */
    public function delete();
    
    /**
     * Import the collection.
     * 
     * Recommended use:
     * <ol>
     *     <li>Insert the item type (if any) needed for this collection using 
     *     {@link Sept11_Import_Strategy_Abstract::_insertItemType()}, getting 
     *     the item type ID</li>
     *     <li>Insert the Omeka collection using 
     *     {@link Sept11_Import_Strategy_Abstract::_insertCollection()}, getting 
     *     the collection ID</li>
     *     <li>Iterate the Sept11 collection objects using 
     *     {@link Sept11_Import_Strategy_Abstract::_fetchCollectionObjectsSept11()}, 
     *     assigning collection-specific metadata and passing them to 
     *     {@link Sept11_Import_Strategy_Abstract::_insertItem()} to insert the 
     *     item</li>
     * </ol>
     */
    public function import();
}
