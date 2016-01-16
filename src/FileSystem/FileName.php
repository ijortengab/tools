<?php

namespace IjorTengab\FileSystem;
  
class FileName
{
    /**
     * Mendapatkan nama file baru jika nama file pada argument $basename
     * sudah exists di dalam $directory. Nama file baru didapat dengan
     * auto increment dari $basename. Code bersumber dari drupal versi 7.
     * 
     * @link
     *   https://api.drupal.org/api/function/file_create_filename/7
     */
    public static function createUnique($basename, $directory)
    {
        // Strip control characters (ASCII value < 32). Though these are allowed in
        // some filesystems, not many applications handle them well.
        $basename = preg_replace('/[\x00-\x1F]/u', '_', $basename);
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            // These characters are not allowed in Windows filenames
            $basename = str_replace(array(':', '*', '?', '"', '<', '>', '|'), '_', $basename);
        }
        // A URI or path may already have a trailing slash or look like "public://".
        if (substr($directory, -1) == DIRECTORY_SEPARATOR) {
            $separator = '';
        }
        else {
            $separator = DIRECTORY_SEPARATOR;
        }
        $destination = $directory . $separator . $basename;
        if (file_exists($destination)) {
            // Destination file already exists, generate an alternative.
            $pos = strrpos($basename, '.');
            if ($pos !== FALSE) {
                $name = substr($basename, 0, $pos);
                $ext = substr($basename, $pos);
            }
            else {
                $name = $basename;
                $ext = '';
            }
            $counter = 0;
            do {
                $destination = $directory . $separator . $name . '_' . $counter++ . $ext;
            } while (file_exists($destination));
        }
        return $destination;
    }
}
