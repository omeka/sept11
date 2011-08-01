<?php
interface Sept11_Import_Strategy_Interface
{
    /**
     * Delete the Omeka collection.
     */
    public function delete();
    
    /**
     * Import the collection.
     */
    public function import();
    
    /**
     * Resume a previously initialized import.
     */
    public function resume();
}
