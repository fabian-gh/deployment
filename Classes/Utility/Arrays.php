<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Fabian Martinovic <fabian.martinovic@t-online.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * @category   Extension
 * @package    Deployment
 */

namespace TYPO3\Deployment\Utility;

/**
 * @package    Deployment
 */
class Arrays {

    /**
     * @param $data
     * @param $array
     * @see http://www.php.net/manual/de/function.array-walk-recursive.php#106340
     */
    public static function setNodes($data, &$array) {
        $separator = '|'; // Pipe als Separatorzeichen setzen
        foreach ($data as $name => $value) {
            if (strpos($name, $separator) === FALSE) {
                // Wenn das Array keinen spezielles Separatorzeichen besitzt, setze das Schlüssel-Wert-Paar
                // Wenn $value ein Array ist, setze nested Schlüssel-Wert-Paare
                $array[$name] = $value;
            } else {
                // In diesem Fall wird versucht einen speziellen Knoten zu erreichen, 
                // ohne Folgeknoten zu überschreiben
                // Der Knoten oder seine Nachfolger existieren noch nicht.
                $keys = explode($separator, $name);
                // Wurzel des Baums setzen
                $opt_tree = & $array;
                // Baum traversieren, spezielle Schlüssel benutzen
                while ($key = array_shift($keys)) {
                    // Wenn weitere Schlüssel nach dem folgen
                    if ($keys) {
                        if (!isset($opt_tree[$key]) || !is_array($opt_tree[$key])) {
                            // Erstelle den Knoten falls dieser nich nicht existiert
                            $opt_tree[$key] = array();
                        }
                        // Wurzel des Baums zu diesem Knoten neu definieren(assign by reference)
                        // dann den nächsten Schlüssel bearbeiten
                        $opt_tree = & $opt_tree[$key];
                    } else {
                        // letzter zu prüfneder Schlüssel
                        $opt_tree[$key] = $value;
                    }
                }
            }
        }
    }
}