<?php

namespace Pecee\IO;

class Directory {

    /**
     * Recursivly delete folder from filesystem
     * @param string $path
     * @return boolean
     */
    public static function delete($path) {
        $path = self::HasEndingDirectorySeperator($path) ? $path : $path . DIRECTORY_SEPARATOR;
        $files = glob($path . '*', GLOB_MARK);
        foreach($files as $file){
            if(substr($file, -1) == DIRECTORY_SEPARATOR)
                self::DeleteTree($file);
            else
                @unlink($file);
        }
        @rmdir($path);
    }

    public static function normalize($path) {
        return rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

}