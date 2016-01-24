<?php namespace Comodojo\Installer\Components;

use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;
use \Composer\Util\Filesystem as ComposerFilesystem;

/**
 *
 *
 * @package     Comodojo Framework
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
 * @author      Marco Castiello <marco.castiello@gmail.com>
 * @license     GPL-3.0+
 *
 * LICENSE:
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
 
class Filesystem extends ComposerFilesystem {
    
    public function rcopy($source, $target) {
        
        if (!is_dir($source)) {
            
            copy($source, $target);
            
            return;
            
        }
        
        $it = new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS);
        
        $ri = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::SELF_FIRST);
        
        $this->ensureDirectoryExists($target);
        
        foreach ($ri as $file) {
            
            $targetPath = $target . DIRECTORY_SEPARATOR . $ri->getSubPathName();
            
            if ($file->isDir()) {
                
                $this->ensureDirectoryExists($targetPath);
                
            } else {
                
                copy($file->getPathname(), $targetPath);
                
            }
            
        }
        
    }
    
}