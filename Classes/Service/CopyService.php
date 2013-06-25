<?php

/**
 * CopyService
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
use \TYPO3\CMS\Scheduler\Task\AbstractTask;
use \TYPO3\Deployment\Service\FileService;
use \TYPO3\CMS\Core\Resource\ResourceFactory;
use \TYPO3\Deployment\Service\ConfigurationService;
use \TYPO3\Deployment\Service\XmlResourceService;

/**
 * CopyService
 *
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class CopyService extends AbstractDataService {

    /**
     * @var string
     */
    protected $taskUid;

    /**
     * @var int
     */
    protected $disable;

    
    /**
     * Triggerfunktion zum Aufruf des Command Controllers über das CLI
     */
    public function trigger() {
        if ($this->allPrecautionsSet()) {
            $arr = array();
            $path = $this->getCliPath();
            $taskUid = $this->getTaskUid();
            
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            if (preg_match('/linux/i', $userAgent) == 1 || preg_match('/mac/i', $userAgent) == 1) {
                CommandUtility::exec('whereis -b php', $arr);
                $phpPath = GeneralUtility::trimExplode(':', $arr[0]);
                
                CommandUtility::exec(escapeshellcmd("$phpPath[1] $path scheduler -f -i $taskUid"));
            }
        }
    }

    
    /**
     * Führt das Kopieren aus
     */
    public function execute() {
        if($this->allPrecautionsSet()) {
            $this->deployResources();
        }
    }

    
    /**
     * Prüft ob der Command Controller registiert ist
     *
     * @return boolean
     */
    public function checkIfCommandControllerIsRegistered() {
        $identParts = array();
        
        $result = $this->getDatabase()->exec_SELECTgetRows('serialized_task_object, disable', 'tx_scheduler_task', '1=1');
        
        foreach ($result as $res) {
            /** @var \TYPO3\CMS\Extbase\Scheduler\Task $object */
            $object = unserialize($res['serialized_task_object']);
            
            if($object instanceof AbstractTask) {
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
     * Prüft ob der benötigter _cli_scheduler-User vorhanden ist
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
     * Dateien aus der sys_file-Tabelle über die XML-Datei einlesen und diese
     * mittels des Command Controller Tasks vom Quellsystem kopieren
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
        
        // User-Agent auslesen
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        // TODO: Pfad zu fileadmin ändern
        $path = $fileService->getDeploymentResourcePathWithoutTrailingSlash();
        // Daten aus Konfiguration holen
        $server = $configuration->getPullserver();
        $username = $configuration->getUsername();
        $password = $configuration->getPassword();

        // letztes Slash entfernen falls vorhanden
        if(substr($server, strlen($server)-1) == "/"){
            $server = substr($server, 0, -1);
        }

        // XML einlesen
        $data = $fileService->splitContent($xmlResourceService->readXmlResourceList());

        foreach ($data as $resource) {
            /** @var \TYPO3\CMS\Core\Resource\File $file */
            $file = $resFact->getFileObject($xmlResourceService->getUidByUuid($resource['uuid'], $resource['tablename']));
            $split = explode('/', $file->getIdentifier());
            $filename = array_pop($split);

            // Pfad wieder zusammensetzen
            $folder = '';
            foreach ($split as $sp) {
                if ($sp != '' && $sp != 'fileadmin') {
                    $folder = $folder . '/' . $sp;
                }
            }

            // erste Slash entfernen und Ordnerstruktur erstellen
            $fold = substr($folder, 1);
            if (!is_dir($path . '/' . $fold)) {
                GeneralUtility::mkdir_deep($path . '/' . $fold);
            }
            
            // Pfade festlegen
            $dest = "$path/$fold/";
            $source = "$server/fileadmin/$fold/$filename";
            
            // Dateien mittels OS-Unterscheidung vom Quellsystem kopieren oder syncen
            if (preg_match('/linux/i', $userAgent) == 1) {
                // In Zielverzeichnis wechseln und über wget nur Dateien holen, die neuer als Zieldatei sind
                CommandUtility::exec("cd $dest; wget --user=$username --password=$password --timestamping $source");
            } else {
                //TODO: Pfad ändern
                GeneralUtility::upload_copy_move($source, $dest.$filename);
            }
        }
    }

    
    /**
     * Prüft ob alle Vorkehrungen getroffen sind
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
     * @return string
     */
    public function getCliPath() {
        return GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'typo3/cli_dispatch.phpsh';
    }

    /**
     * @return string
     */
    public function getTaskUid() {
        return $this->taskUid;
    }

    /**
     * @return string
     */
    public function getDisable() {
        return $this->disable;
    }

    /**
     * @param string $disable
     */
    public function setDisable($disable) {
        $this->disable = (int) $disable;
    }
}