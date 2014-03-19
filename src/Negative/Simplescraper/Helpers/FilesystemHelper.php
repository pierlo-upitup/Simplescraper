<?php namespace Negative\Simplescraper\Helpers;

 class FilesystemHelper {

 	/**
	 * Delete all files older than a specified age from the specified folder.
	 *
	 * @param int $file_ttl Max age in seconds
	 * @param string $path The absolute path of the folder
	 * @return void
	 */
	public static function deleteFilesOlderThan($file_ttl, $path)
	{
		$files = glob( $path."*");
	    foreach($files as $file) {
	        if(is_file($file) && 
	        	time() - filemtime($file) >= $file_ttl
	        ) {
	            unlink($file);
	        }
	    }
	}
 }