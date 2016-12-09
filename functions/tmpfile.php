<?php

namespace IjorTengab\Override\PHP\TmpFile;

/**
 * Override PHP's core function tmpfile. Automatically load content of existing
 * file.
 */
function tmpfile($pathfilename = null) {
    if ($pathfilename === null) {
        return \tmpfile();
    }
    $temp = \tmpfile();
    if (is_readable($pathfilename)) {        
        fwrite($temp, file_get_contents($pathfilename));
        fseek($temp, 0);
    }
    return $temp;    
}

