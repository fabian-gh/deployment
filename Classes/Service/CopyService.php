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

use \TYPO3\CMS\Core\Utility\CommandUtility;
use \TYPO3\CMS\Core\Utility\HttpUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Extbase\Scheduler\Task;
use \TYPO3\Deployment\Service\FileService;
use \TYPO3\CMS\Core\Resource\ResourceFactory;
use \TYPO3\Deployment\Service\ConfigurationService;
use \TYPO3\Deployment\Service\XmlResourceService;

/**
 * CopyService
 * Class for copying the resources from one to another system
 *
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class CopyService extends AbstractDataService {

    /**
     * Task uid
     * 
     * @var string $taskUid
     */
    protected $taskUid;

    /**
     * Disable status
     * 
     * @var int $disable
     */
    protected $disable;

    
    /**
     * Trigger function for invoking the command controller over the cli
     */
    public function trigger() {
        /** @var \TYPO3\Deployment\Service\ConfigurationService $configurationService */
        $configurationService = new ConfigurationService();
        
        if ($this->allPrecautionsSet()) {
            $path = $this->getCliPath();
            $taskUid = $this->getTaskUid();
            
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            if (preg_match('/linux/i', $userAgent) == 1 || preg_match('/mac/i', $userAgent) == 1) {
                $phpPath = $configurationService->getPhpPath();
                
                CommandUtility::exec(escapeshellcmd("$phpPath $path scheduler -f -i $taskUid"));
            }
        }
    }

    
    /**
     * Execute the copying
     */
    public function execute() {
        if($this->allPrecautionsSet()) {
            $this->deployResources();
        }
    }

    
    /**
     * Check if the command controller is registered
     *
     * @return boolean
     */
    public function checkIfCommandControllerIsRegistered() {
        $identParts = array();
        
        $result = $this->getDatabase()->exec_SELECTgetRows('serialized_task_object, disable', 'tx_scheduler_task', '1=1');
        
        foreach ($result as $res) {
            /** @var \TYPO3\CMS\Extbase\Scheduler\Task $object */
            $object = unserialize($res['serialized_task_object']);
            
            if($object instanceof Task) {
                if($object->getCommandIdentifier() !== '' || $object->getCommandIdentifier() !== null){
                    $this->setDisable($res['disable']);
                    $this->taskUid = $object->getTaskUid();
                    $identParts = explode(':', $object->getCommandIdentifier());
                    
                    if($identParts[0] === 'deployment' && $identParts[1] === 'copyresources' && $identParts[2] === 'copy') {
                        return TRUE;
                    }
                }
            }
        }
        return FALSE;
    }

    
    /**
     * Check if required _cli_scheduler-user is available
     *
     * @return boolean
     */
    public function checkIfCliUserIsRegistered() {
        if($this->getDatabase()->isConnected()){
            $result = $this->getDatabase()->exec_SELECTgetSingleRow('username', 'be_users', "username='_cli_scheduler'");
            
            if(!empty($result) && $result['username'] == '_cli_scheduler') {
                return TRUE;
            }
        }
        return FALSE;
    }

    
    /**
     * Read files from the xml file list and copy them over the command
     * controller task to the destination
     */
    protected function deployResources() {
        /** @var \TYPO3\Deployment\Service\FileService $fileService */
        $fileService = new FileService();
        /** @var \TYPO3\CMS\Core\Resource\ResourceFactory $resFact */
        $resFact = ResourceFactory::getInstance();
        /** @var \TYPO3\Deployment\Service\ConfigurationService $configuration */
        $configuration = new ConfigurationService();
        /** @var \TYPO3\Deployment\Service\XmlResourceService $xmlResourceService */
        $xmlResourceService = new XmlResourceService();
        
        // read user agent
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        //$path = $fileService->getFileadminPathWithoutTrailingSlash();
        GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment/resource';
        // get data from configuration
        $server = $configuration->getPullserver();
        $username = $configuration->getUsername();
        $password = $configuration->getPassword();

        // remove last slash if it exists
        if(substr($server, strlen($server)-1) == "/"){
            $server = substr($server, 0, -1);
        }

        // read xml
        $data = $fileService->splitContent($xmlResourceService->readXmlResourceList());

        foreach ($data as $resource) {
            /** @var \TYPO3\CMS\Core\Resource\File $file */
            $file = $resFact->getFileObject($xmlResourceService->getUidByUuid($resource['uuid'], $resource['tablename']));
            $split = explode('/', $file->getIdentifier());
            $filename = array_pop($split);

            // compose the path
            $folder = '';
            foreach ($split as $sp) {
                if ($sp != '' && $sp != 'fileadmin') {
                    $folder = $folder . '/' . $sp;
                }
            }

            // remove first slash and create directory structure
            $fold = substr($folder, 1);
            if (!is_dir($path . '/' . $fold)) {
                GeneralUtility::mkdir_deep($path . '/' . $fold);
            }
            
            // define paths
            $dest = "$path/$fold/";
            $source = "$server/fileadmin/$fold/$filename";
            
            // copy files over OS-Determination from the source
            if (preg_match('/linux/i', $userAgent) == 1) {
                // change to the destination directory and get the file over wget 
                // (only if the file is newer than destination)
                CommandUtility::exec("cd $dest; wget --user=$username --password=$password --timestamping $source");
            } else {
                GeneralUtility::upload_copy_move($source, $dest.$filename);
            }
        }
    }

    
    /**
     * Check if all precautions are set
     *
     * @return boolean
     */
    public function allPrecautionsSet() {
        if ($this->checkIfCommandControllerIsRegistered() && $this->checkIfCliUserIsRegistered() && $this->getDisable() == '1') {
            return TRUE;
        }
        return FALSE;
    }

    
    /**
     * Returns CLI path
     * 
     * @return string
     */
    public function getCliPath() {
        return GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'typo3/cli_dispatch.phpsh';
    }

    /**
     * Returns task uid
     * 
     * @return string
     */
    public function getTaskUid() {
        return $this->taskUid;
    }

    /**
     * Returns disable status
     * 
     * @return string
     */
    public function getDisable() {
        return $this->disable;
    }

    /**
     * Sets disable status
     * 
     * @param string $disable
     */
    public function setDisable($disable) {
        $this->disable = (int) $disable;
    }
}