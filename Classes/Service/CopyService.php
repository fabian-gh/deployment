<?php

/**
 * CopyService
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Service;

use \TYPO3\CMS\Core\Utility\CommandUtility;
use \TYPO3\CMS\Core\Utility\HttpUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Frontend\Page\PageRepository;
use \TYPO3\CMS\Scheduler\Task\AbstractTask;
use \TYPO3\Deployment\Service\FileService;
use \TYPO3\CMS\Core\Resource\ResourceFactory;
use \TYPO3\Deployment\Service\ConfigurationService;
use \TYPO3\Deployment\Service\XmlResourceService;

/**
 * CopyService
 *
 * @package    Deployment
 * @subpackage Domain\Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class CopyService extends AbstractDataService {

    /**
     * @var string
     */
    protected $taskUid;

    /**
     * @var string
     */
    protected $disable;

    
    /**
     * Triggerfunktion zum Aufruf des Command Controllers über das CLI
     */
    public function trigger() {
        if ($this->allPrecautionsSet()) {
            $path = $this->getCliPath();
            $taskUid = $this->getTaskUid();
            // /var/www/public/typo3/cli_dispatch.phpsh scheduler -i 13 -f
            // HDNET
            #\TYPO3\CMS\Core\Utility\CommandUtility::exec('....');
            #CommandUtility::getCommand('..', '..') // php -> /usr/var/php -f
            // '/usr/local/bin/php5-53STABLE-CLI -f '
            
            exec(escapeshellcmd("$path scheduler -f -i $taskUid"));
        }
    }

    
    /**
     * Führt das Kopieren aus
     */
    public function execute() {
        if ($this->allPrecautionsSet()) {
            $this->deployResources();
        }
    }

    
    /**
     * Prüft ob der Command Controller registiert ist
     *
     * @return boolean
     */
    public function checkIfCommandControllerIsRegistered() {
        $pageSelection = new PageRepository();
        $result = $this->getDatabase()->exec_SELECTgetRows('serialized_task_object, disable', 'tx_scheduler_task', '1=1');

        // HDNET
        # return FALSE;

        foreach ($result as $res) {
            /** @var \TYPO3\CMS\Extbase\Scheduler\Task $object */
            $object = unserialize($res['serialized_task_object']);
            if ($object instanceof AbstractTask) {
                $this->setDisable($res['disable']);
                $this->taskUid = $object->getTaskUid();
                $identParts = explode(':', $object->getCommandIdentifier());

                if ($identParts[0] == 'deployment' && $identParts[1] == 'copyresources' && $identParts[2] == 'copy') {
                    return TRUE;
                }
            }
        }

        return FALSE;
    }

    
    /**
     * Prüft ob der benötigte _cli_scheduler-User vorhanden ist
     *
     * @return boolean
     */
    public function checkIfCliUserIsRegistered() {
        $result = $this->getDatabase()->exec_SELECTgetRows('username', 'be_users', "username='_cli_scheduler'");

        if (!empty($result)) {
            foreach ($result as $res) {
                if ($res['username'] == '_cli_scheduler') {
                    return TRUE;
                }
            }
        } else {
            return FALSE;
        }
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

        // @todo: Pfad zu fileadmin ändern
        $path = $fileService->getDeploymentResourcePathWithoutTrailingSlash();
        // Daten aus Konfiguration holen
        $server = $configuration->getPullserver();
        $username = $configuration->getUsername();
        $password = $configuration->getPassword();

        // URL in Teile zerlegen
        $parts = parse_url($server);
        // Username & Password trimmen falls nicht leer
        if (trim($username) != '') {
            $parts['user'] = $username;
        }
        if (trim($password) != '') {
            $parts['pass'] = $password;
        }
        // Pfad mit User und PW wieder zusammensetzen
        $pullServer = trim(HttpUtility::buildUrl($parts), '/');

        // XML einlesen
        $data = $fileService->splitContent($xmlResourceService->readXmlResourceList());

        foreach ($data as $resource) {
            /** @var \TYPO3\CMS\Core\Resource\File $file */
            $file = $resFact->getFileObject($xmlResourceService->getUid($resource['uuid'], $resource['tablename']));
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

            // Dateien mittels OS-Unterscheidung vom Quellsystem kopieren oder syncen
            //if (strpos($os, 'Linux') !== FALSE || strpos($os, 'Mac') !== FALSE) {
            if (TYPO3_OS == 'Linux'|| TYPO3_OS == 'Mac') {
                $sourceDest = escapeshellcmd("$pullServer/fileadmin/$fold/$filename $path/$fold/$filename");
                // Parameter: Dateien bei Übertragung komprimieren, neuere Dateien nicht ersetzen,
                // SymLinks als Syminks kopieren, Dateirechte beibehalten, Quellverzeichnis
                exec("rsync --compress --update --links --perms $sourceDest");
            } else {
                //@todo: Pfad ändern
                GeneralUtility::upload_copy_move($pullServer.'/fileadmin/'.$fold.'/'.$filename, $path.'/'.$fold.'/'.$filename);
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
     * Get the TYPO3 database
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabase() {
        return $GLOBALS['TYPO3_DB'];
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
        $this->disable = $disable;
    }
}