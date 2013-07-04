<?php

/**
 * Deployment-Extension
 * This is an extension to integrate a deployment process for TYPO3 CMS
 * 
 * @category   Extension
 * @package    Deployment
 * @subpackage Utility
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Utility;

/**
 * Arrays
 * Class for advanced array handling
 * 
 * @package    Deployment
 * @subpackage Utility
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
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
                // if the array has no special seperator char, set the kay-value pair
                // if $value is an array, set nested kay-value pairs
                $array[$name] = $value;
            } else {
                // in this case a special node is trying to be reached, without overwriting successors
                // the node or its successors don't exist yet
                $keys = explode($separator, $name);
                // set root of tree
                $opt_tree = & $array;
                // traverse tree, use special keys
                while ($key = array_shift($keys)) {
                    // if there are no successors
                    if ($keys) {
                        if (!isset($opt_tree[$key]) || !is_array($opt_tree[$key])) {
                            // create the node if not already exist
                            $opt_tree[$key] = array();
                        }
                        // define root of the tree as this node (assign by reference)
                        // and then edit next kex
                        $opt_tree = & $opt_tree[$key];
                    } else {
                        // last checkable key
                        $opt_tree[$key] = $value;
                    }
                }
            }
        }
    }
}