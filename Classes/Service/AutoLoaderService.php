<?php
/**
 * Deployment-Extension
 * This is an extension to integrate a deployment process for TYPO3 CMS
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Service;

use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\Deployment\Utility\Arrays;

/**
 * AutoLoader Service
 * Class for autoloading the hooks and command controller
 *
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class AutoLoaderService {

    /**
     * Extension key
     *
     * @var string
     */
    protected $extensionKey;

    
    /**
     * @param string $extensionKey
     *
     * @throws \Exception
     */
    public function __construct($extensionKey) {
        if (!ExtensionManagementUtility::isLoaded($extensionKey)) {
            throw new \Exception('Can not create the Autoloader for a extension that is not loaded', 23146127384);
        }
        
        $this->extensionKey = $extensionKey;
    }
    

    /**
     * Loads the hooks from the hook directory
     * 
     * @return AutoLoaderService
     */
    public function loadExtensionLocalConfigurationHooks() {
        $files = $this->getBaseFilesInDir(ExtensionManagementUtility::extPath($this->extensionKey).'Classes/Hooks/', 'php');

        $extKey = GeneralUtility::underscoredToUpperCamelCase($this->extensionKey);
        foreach ($files as $hookFile) {
            $hookClass = 'TYPO3\\'.$extKey.'\\Hooks\\'.$hookFile;
            $hookBase = 'EXT:'.$this->extensionKey.'/Classes/Hooks/'.$hookFile.'.php:'.$hookClass;

            /** @var \TYPO3\CMS\Extbase\Reflection\ClassReflection $classReflection */
            $classReflection = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Reflection\\ClassReflection', $hookClass);

            // add class hooks
            $classTags = $classReflection->getTagsValues();
            if (isset($classTags['hook'])) {
                if (is_array($classTags['hook'])) {
                    $classTags['hook'] = implode(' ', $classTags['hook']);
                }
                
                $this->addHook($classTags['hook'], $hookBase);
            }

            // add method hooks
            foreach ($classReflection->getMethods() as $methodReflection) {
                /** @var \TYPO3\CMS\Extbase\Reflection\MethodReflection $methodReflection */
                $methodTags = $methodReflection->getTagsValues();
                if (isset($methodTags['hook'])) {
                    if (is_array($methodTags['hook'])) {
                        $methodTags['hook'] = implode(' ', $methodTags['hook']);
                    }

                    $this->addHook($methodTags['hook'], $hookBase.'->'.$methodReflection->getName());
                }
            }
        }
        return $this;
    }

    
    /**
     * Load the command controller
     *
     * @return AutoLoaderService
     */
    public function loadExtensionLocalConfigurationCommandController() {
        $commandControllerPath = ExtensionManagementUtility::extPath($this->extensionKey) . 'Classes/Command/';
        $controllers = $this->getBaseFilesInDir($commandControllerPath, 'php');
        foreach ($controllers as $controller) {
            if ($controller === 'AbstractCommandController') {
                continue;
            }

            $className = 'TYPO3\\'.ucfirst(GeneralUtility::underscoredToUpperCamelCase($this->extensionKey)).'\\Command\\'.$controller;
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = $className;
        }
        return $this;
    }

    
    /**
     * Add a Hook
     *
     * @param string $location Path of Hook seperated by pipes
     * @param string $configuration
     */
    public function addHook($location, $configuration) {
        $location = GeneralUtility::trimExplode('|', $location, TRUE);
        array_push($location, $this->extensionKey.'_'.GeneralUtility::shortMD5($configuration));
        Arrays::setNodes(array(implode('|', $location) => $configuration), $GLOBALS);
    }
    

    /**
     * Read all filenames from the directory and check if directory exists
     *
     * @param $dirPath
     * @param $fileExtension
     *
     * @return array
     */
    private function getBaseFilesInDir($dirPath, $fileExtension) {
        if (!is_dir($dirPath)) {
            return array();
        }

        $files = GeneralUtility::getFilesInDir($dirPath, $fileExtension);
        foreach ($files as $key => $file) {
            $files[$key] = pathinfo($file, PATHINFO_FILENAME);
        }

        return array_values($files);
    }
}