<?php

/**
 * AutoLoader Service
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Service;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\Deployment\Utility\Arrays;

/**
 * AutoLoader Service
 * Controller Abstraction for the ExtBase framework
 *
 * @package    Deployment
 * @subpackage Domain\Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class AutoLoaderService {

    /**
     * The Extension key
     *
     * @var string
     */
    protected $extensionKey;

    /**
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     */
    protected $signalSlotDispatcher;

    
    /**
     * @param string $extensionKey
     *
     * @throws \Exception
     */
    public function __construct($extensionKey) {
        if (!ExtensionManagementUtility::isLoaded($extensionKey)) {
            throw new \Exception('Can not create the autoLoader for a extension that is not loaded', 23146127384);
        }
        $this->signalSlotDispatcher = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
        $this->extensionKey = $extensionKey;
    }

    
    /**
     * Load slot by the Slot directory and bind them to the dispatcher
     *
     * @return AutoLoaderService
     */
    public function loadExtensionLocalConfigurationSlots() {
        $slotPath = ExtensionManagementUtility::extPath($this->extensionKey) . 'Classes/Slots/';
        $slotClasses = $this->getBaseFilesInDir($slotPath, 'php');

        $extKey = GeneralUtility::underscoredToUpperCamelCase($this->extensionKey);

        foreach ($slotClasses as $slot) {
            $slotClass = 'TYPO3\\' . $extKey . '\\Slots\\' . $slot;

            /** @var $classReflection \TYPO3\CMS\Extbase\Reflection\ClassReflection */
            $classReflection = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Reflection\\ClassReflection', $slotClass);

            // add method slots
            foreach ($classReflection->getMethods() as $methodReflection) {
                /** @var $methodReflection \TYPO3\CMS\Extbase\Reflection\MethodReflection */
                $methodTags = $methodReflection->getTagsValues();

                if (isset($methodTags['signalClass'][0]) && isset($methodTags['signalName'][0])) {
                    $this->addSlot(trim($methodTags['signalClass'][0], '\\'), $methodTags['signalName'][0], $slotClass, $methodReflection->getName());
                }
            }
        }

        return $this;
    }

    
    /**
     * Load Static TypoScripts for the ext_tables.php file
     *
     * @return AutoLoaderService
     */
    public function loadExtensionTablesStaticTypoScript() {
        $extPath = ExtensionManagementUtility::extPath($this->extensionKey);
        $baseDir = $extPath . 'Configuration/TypoScript/';
        if (!is_dir($baseDir)) {
            return $this;
        }
        $typoScriptFolder = GeneralUtility::getAllFilesAndFoldersInPath(array(), $baseDir, '', TRUE, 99, '(.*)\\.(.*)');
        $extensionName = GeneralUtility::underscoredToUpperCamelCase($this->extensionKey);

        foreach ($typoScriptFolder as $folder) {
            if (file_exists($folder . 'setup.txt') || file_exists($folder . 'constants.txt')) {
                $extensionName = $extensionName . '/' . str_replace($baseDir, '', $folder);
                $extensionName = implode(' - ', GeneralUtility::trimExplode('/', $extensionName, TRUE));
                $folder = str_replace($extPath, '', $folder);
                ExtensionManagementUtility::addStaticFile($this->extensionKey, $folder, $extensionName);
            }
        }
        return $this;
    }

    
    /**
     * Load all hooks from the Hook dir and checked classes and functions if there is a Tag like: [at]hook TYPO3_CONF_VARS|SC_OPTIONS|tslib/class.tslib_content.php|stdWrap
     *
     * @return AutoLoaderService
     */
    public function loadExtensionLocalConfigurationHooks() {
        $files = $this->getBaseFilesInDir(ExtensionManagementUtility::extPath($this->extensionKey) . 'Classes/Hooks/', 'php');

        $extKey = GeneralUtility::underscoredToUpperCamelCase($this->extensionKey);
        foreach ($files as $hookFile) {
            $hookClass = 'TYPO3\\' . $extKey . '\\Hooks\\' . $hookFile;
            $hookBase = 'EXT:' . $this->extensionKey . '/Classes/Hooks/' . $hookFile . '.php:' . $hookClass;

            /** @var $classReflection \TYPO3\CMS\Extbase\Reflection\ClassReflection */
            $classReflection = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Reflection\\ClassReflection', $hookClass);

            // add class hook
            $classTags = $classReflection->getTagsValues();
            if (isset($classTags['hook'])) {
                if (is_array($classTags['hook'])) {
                    $classTags['hook'] = implode(' ', $classTags['hook']);
                }
                $this->addHook($classTags['hook'], $hookBase);
            }

            // add method hooks
            foreach ($classReflection->getMethods() as $methodReflection) {
                /** @var $methodReflection \TYPO3\CMS\Extbase\Reflection\MethodReflection */
                $methodTags = $methodReflection->getTagsValues();
                if (isset($methodTags['hook'])) {
                    if (is_array($methodTags['hook'])) {
                        $methodTags['hook'] = implode(' ', $methodTags['hook']);
                    }
                    $this->addHook($methodTags['hook'], $hookBase . '->' . $methodReflection->getName());
                }
            }
        }
        return $this;
    }

    
    /**
     * AutoLoader for all XClasses
     *
     * @return AutoLoaderService
     */
    public function loadExtensionLocalConfigurationXclasses() {
        $xClassesPath = ExtensionManagementUtility::extPath($this->extensionKey) . 'Classes/Xclass/';
        $xClasses = $this->getBaseFilesInDir($xClassesPath, 'php');

        $extKey = GeneralUtility::underscoredToUpperCamelCase($this->extensionKey);
        foreach ($xClasses as $xClass) {
            $xclassName = 'TYPO3\\' . $extKey . '\\Xclass\\' . $xClass;

            /** @var $xclassReflection \TYPO3\CMS\Extbase\Reflection\ClassReflection */
            $xclassReflection = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Reflection\\ClassReflection', $xclassName);
            $originalName = $xclassReflection
                    ->getParentClass()
                    ->getName();

            $this->addXclass($originalName, $xclassName);
        }

        return $this;
    }

    
    /**
     * Load FlexForms in relation to the plugin name
     *
     * @return AutoLoaderService
     */
    public function loadExtensionTablesFlexForms() {
        global $TCA;
        $FlexFormPath = ExtensionManagementUtility::extPath($this->extensionKey) . 'Configuration/FlexForms/';
        $extensionName = GeneralUtility::underscoredToUpperCamelCase($this->extensionKey);
        $FlexForms = $this->getBaseFilesInDir($FlexFormPath, 'xml');
        foreach ($FlexForms as $fileKey) {
            $pluginSignature = strtolower($extensionName . '_' . $fileKey);
            $TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,recursive';
            $TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
            ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $this->extensionKey . '/Configuration/FlexForms/' . $fileKey . '.xml');
        }
        return $this;
    }

    
    /**
     * Load CommandController by filename
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
            $className = 'TYPO3\\' . ucfirst(GeneralUtility::underscoredToUpperCamelCase($this->extensionKey)) . '\\Command\\' . $controller;
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = $className;
        }
        return $this;
    }

    
    /**
     * Set smart Xclass logic
     *
     * @param string $source
     * @param string $target
     */
    public function addXclass($source, $target) {
        if (isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][$source])) {
            return;
        }
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][$source] = array(
            'className' => $target,
        );
    }

    
    /**
     * Add a hook
     *
     * @param string $location The location of the hook separated bei pipes
     * @param string $configuration
     */
    public function addHook($location, $configuration) {
        $location = GeneralUtility::trimExplode('|', $location, TRUE);
        array_push($location, $this->extensionKey . '_' . GeneralUtility::shortMD5($configuration));
        Arrays::setNodes(array(implode('|', $location) => $configuration), $GLOBALS);
    }

    
    /**
     * Add a Slot dispatcher
     *
     * @param string $signalClassName
     * @param string $signalName
     * @param string $slotClassNameOrObject
     * @param string $slotMethodName
     * @param bool   $passSignalInformation
     */
    public function addSlot($signalClassName, $signalName, $slotClassNameOrObject, $slotMethodName, $passSignalInformation = TRUE) {
        $this->signalSlotDispatcher->connect($signalClassName, $signalName, $slotClassNameOrObject, $slotMethodName, $passSignalInformation);
    }

    
    /**
     * Get all base filenames in the given directory with the given file extension
     * Check also if the directory exists
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