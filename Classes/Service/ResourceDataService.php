<?php

/**
 * ResourceDataService
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */

namespace TYPO3\Deployment\Service;

use TYPO3\CMS\Core\Utility\HttpUtility;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\Deployment\Domain\Repository\AbstractRepository;
use \TYPO3\CMS\Core\Resource\ResourceFactory;

/**
 * ResourceDataService
 *
 * @package    Deployment
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class ResourceDataService extends AbstractRepository {

    /**
     * max file size in Bytes
     * @var int 
     */
    protected $maxFileSize = 10000000;

    /**
     * @var \TYPO3\Deployment\Domain\Model\File
     */
    protected $fileList;

    /**
     * @var \XmlWriter
     */
    protected $xmlwriter;

    /**
     * @var \SimpleXml
     */
    protected $xmlreader;

    
    /**
     * Schreibt eine XML-Datei mit allen im Fileadmin befindlichen Dateien, 
     * ohne Pfadangabe zum Fileadmin
     */
    public function writeXmlResourceList() {
        /** @var \TYPO3\CMS\Core\Resource\ResourceFactory $resFact */
        $resFact = ResourceFactory::getInstance();
        
        // Neues XMLWriter-Objekt
        $this->xmlwriter = new \XMLWriter();

        // Dokumenteneigenschaften
        $this->xmlwriter->openMemory();
        $this->xmlwriter->setIndent(TRUE);
        $this->xmlwriter->startDocument('1.0');
        // Document Type Definition (DTD)
        $this->xmlwriter->startDtd('resourcelist');
        $this->xmlwriter->writeDtdElement('resourcelist', '(file)');
        $this->xmlwriter->writeDtdElement('file', '(tstamp,crdate,type,storage,identifier,extension,mime_type,name,title,sha1,size,creation_date,modification_date,width,height,uuid)');
        //$this->xmlwriter->writeDtdElement('uid', '(#PCDATA)');
        //$this->xmlwriter->writeDtdElement('pid', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('tstamp', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('crdate', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('type', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('storage', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('identifier', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('extension', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('mime_type', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('name', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('title', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('sha1', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('size', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('creation_date', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('modification_date', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('width', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('height', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('uuid', '(#PCDATA)');
        $this->xmlwriter->endDtd();

        // Daten schreiben
        $this->xmlwriter->startElement('resourcelist');

        foreach ($this->fileList as $file) {
            $FileObj = $resFact->getFileObject($file->getUid());
            $this->xmlwriter->startElement('file');
            //$this->xmlwriter->writeElement('pid', $this->getPageUuid($FileObj->getProperty('uid')));
            $this->xmlwriter->writeElement('tstamp', $FileObj->getProperty('tstamp'));
            $this->xmlwriter->writeElement('crdate', $FileObj->getProperty('crdate'));
            $this->xmlwriter->writeElement('type', $FileObj->getProperty('type'));
            $this->xmlwriter->writeElement('storage', $FileObj->getProperty('storage'));
            $this->xmlwriter->writeElement('identifier', $FileObj->getProperty('identifier'));
            $this->xmlwriter->writeElement('extension', $FileObj->getProperty('extension'));
            $this->xmlwriter->writeElement('mime_type', $FileObj->getProperty('mime_type'));
            $this->xmlwriter->writeElement('name', $FileObj->getProperty('name'));
            $this->xmlwriter->writeElement('title', $FileObj->getProperty('title'));
            $this->xmlwriter->writeElement('sha1', $FileObj->getProperty('sha1'));
            $this->xmlwriter->writeElement('size', $FileObj->getProperty('size'));
            $this->xmlwriter->writeElement('creation_date', $FileObj->getProperty('creation_date'));
            $this->xmlwriter->writeElement('modification_date', $FileObj->getProperty('modification_date'));
            $this->xmlwriter->writeElement('width', $FileObj->getProperty('width'));
            $this->xmlwriter->writeElement('height', $FileObj->getProperty('height'));
            //$this->xmlwriter->writeElement('uuid', $this->getUuid($file->getUid(), 'sys_file'));
            $this->xmlwriter->writeElement('uuid', $FileObj->getProperty('uuid'));
            $this->xmlwriter->endElement();
        }

        //$this->xmlwriter->endElement();
        $this->xmlwriter->endElement();
        $this->xmlwriter->endDocument();
        $writeString = $this->xmlwriter->outputMemory();

        $file = GeneralUtility::tempnam('resource_');
        GeneralUtility::writeFile($file, $writeString);

        $folder = GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment/media/' . date('Y_m_d', time());
        GeneralUtility::mkdir($folder);

        GeneralUtility::upload_copy_move($file, $folder . '/' . date('H-i-s', time()) . '_resource.xml');
    }

    
    /**
     * Liest alle noch nicht deployten Datensätze aus der Resource-XML Datei 
     * und gibt diese als Array zurück.
     * 
     * @return array
     */
    public function readXmlResourceList() {
        $arrcount = 0;
        $fileArr = $dateFolder = $contentArr = $exFaf = $splittedDateTime = array();
        /** @var \TYPO3\CMS\Core\Registry $registry */
        $registry = GeneralUtility::makeInstance('t3lib_Registry');
        $timestamp = $registry->get('deployment', 'last_deploy', time());
        $filesAndFolders = GeneralUtility::getAllFilesAndFoldersInPath($fileArr, GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment/media/');

        if ($filesAndFolders) {
            // Dateipfad ausplitten
            foreach ($filesAndFolders as $faf) {
                $exFaf[] = str_replace('media/', '', strstr($faf, 'media'));
            }

            // Datum und Uhrzeit splitten
            foreach ($exFaf as $dateTime) {
                $splittedDateTime[] = explode('/', $dateTime);
            }

            // pro Ordner/Datum ein Array mit allen Dateinamen darin
            foreach ($splittedDateTime as $dateTime) {
                $dateFolder[$dateTime[0]][] = $dateTime[1];
            }
        }

        //Dateien einlesen
        foreach ($dateFolder as $folder => $filename) {
            // Datum aus Ordner extrahieren
            $expDate = explode('_', $folder);

            foreach ($filename as $file) {
                // für jede Datei die Uhrzeit extrahieren
                $temp = explode('_', $file);
                $expTime = explode('-', $temp[0]);
                // Timestamp erstellen
                $dateAsTstamp = mktime($expTime[0], $expTime[1], $expTime[2], $expDate[1], $expDate[2], $expDate[0]);

                // wenn Datei-Timestamp später als letztes Deployment,
                // dann die Datei lesen und umwandeln
                if ($dateAsTstamp >= $timestamp) {
                    $xmlString = file_get_contents(GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment/media/' . $folder . '/' . $file);

                    $this->xmlreader = new \SimpleXMLElement($xmlString);
                    foreach ($this->xmlreader->file as $fileset) {
                        foreach ($fileset as $key => $value) {
                            $contentArr[$arrcount][$key] = (string) $value;
                        }
                        $arrcount++;
                    }
                }
            }
        }

        return $contentArr;
    }

    
    /**
     * Schreibt eine Dateiliste des Fileadmins, ohne Deploymentdateien
     */
    public function readFilesInFileadmin() {
        $fileArr = $newArr = array();

        // direktes auslesen des Ordners, da evtl. nicht alle Dateien in Tabellen indexiert sind
        $path = GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/';
        $fileList = GeneralUtility::getAllFilesAndFoldersInPath($fileArr, $path);

        $pathCount = strlen($path);
        // deployment-Ordner exkludieren
        foreach ($fileList as $filekey => $filevalue) {
            if (strstr($filevalue, '/fileadmin/deployment') == FALSE) {
                $newArr[$filekey] = substr($filevalue, $pathCount);
            }
        }

        return $newArr;
    }

    
    /**
     * Filtert alle nicht indizierten Dateien und fügt diese in die sys-file Tabelle ein
     * 
     * @return array 
     */
    public function getNotIndexedFiles() {
        $fileArr = $newFileArr = $notIndexedFiles = $filesInFileadmin = array();

        $filesInFileadmin = $this->readFilesInFileadmin();

        // processed Data raus
        foreach ($filesInFileadmin as $filevalue) {
            if (strstr($filevalue, '_processed_/') == FALSE) {
                $fileArr[] = $filevalue;
            }
        }

        // temp Data raus
        foreach ($fileArr as $filevalue) {
            if (strstr($filevalue, '_temp_/') == FALSE) {
                $newFileArr[] = $filevalue;
            }
        }

        foreach ($newFileArr as $file) {
            /** @var \TYPO3\Deployment\Domain\Repository\FileRepository $fileRef */
            $fileRef = GeneralUtility::makeInstance('TYPO3\\Deployment\\Domain\\Repository\\FileRepository');
            /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $result */
            $result = $fileRef->findByIdentifier($file);

            if ($result->getFirst() == NULL) {
                $notIndexedFiles[] = $file;
            }
        }

        return $notIndexedFiles;
    }

    
    /**
     * Dateien aus der sys_file-Tabelle holen und in den Deployment-Ordner kopieren.
     * Falls nötig, vorher die Ordnerstruktur erstellen.
     * Wenn $filesOverLimit = true dann werden Dateien über der Grenze deployed.
     * Nur für Scheduler Task wichtig
     * 
     * @param boolean $filesOverLimit
     */
    public function deployResources($filesOverLimit = FALSE) {
        /** @var \TYPO3\CMS\Core\Resource\ResourceFactory $resFact */
        $resFact = ResourceFactory::getInstance();
        $fileAdminPath = GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin';
        $path = GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment/resource';
        $os = get_browser()->platform;

        $data = $this->readXmlResourceList();
        
        foreach ($data as $resource) {
            /** @var \TYPO3\CMS\Core\Resource\File $file */
            $file = $resFact->getFileObject($resource['uid']);
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

            if ($filesOverLimit === FALSE) {
                // Nur Dateien <= 10 MB auf Dateiebene kopieren 
                if ($file->getSize() <= $this->maxFileSize) {
                    if (strpos($os, 'Linux') !== FALSE || strpos($os, 'Mac') !== FALSE) {
                        // falls Linux oder Mac das Betriebssystem ist über rsync
                        $sourceDest = escapeshellcmd("$fileAdminPath/$fold/$filename $path/$fold/$filename");
                        exec("rsync --compress --update --links --perms --max-size=$this->maxFileSize $sourceDest");
                    } else {
                        // ansonsten "normales" kopieren über PHP
                        copy($fileAdminPath . '/' . $fold . '/' . $filename, $path . '/' . $fold . '/' . $filename);
                    }
                }
            } else {
                // Daten aus Konfiguration holen
                $configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['deployment']);
                $server = $configuration['pullServer'];
                $username = $configuration['username'];
                $password = $configuration['password'];
                
                // URL in Teile zerlegen
                $parts = parse_url($server);

                // Username & Password trimmen falls nicht leer
                if(trim($username) != ''){
                    $parts['user'] = $username;
                }
                if(trim($password) != ''){
                    $parts['pass'] = $password;
                }
                
                // Pfad mit User und PW wieder zusammensetzen
                $pullServer = trim(HttpUtility::buildUrl($parts), '/');
                
                // Nur Dateien >= 10 MB auf Dateiebene kopieren 
                if ($file->getSize() >= $this->maxFileSize) {
                    if (strpos($os, 'Linux') !== FALSE || strpos($os, 'Mac') !== FALSE) {
                        $sourceDest = escapeshellcmd("$pullServer/fileadmin/$fold/$filename $path/$fold/$filename");
                        exec("rsync --compress --update --links --perms --min-size=$this->maxFileSize $sourceDest");
                    } else {
                        copy($pullServer . '/fileadmin/' . $fold . '/' . $filename, $path . '/' . $fold . '/' . $filename);
                    }
                }
            }
        }
    }

    
    /**
     * Prüft ob die Dateien im resource-Ordner innerhalb des fileadmins vorhanden
     * sind. Falls nein werden diese kopiert.
     */
    public function checkIfFileExists() {
        $resourceFiles = $newArr = $newFileList = array();
        $path = GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/';
        $resPath = GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment/resource/';

        // Dateilisten 
        $fileadminFiles = $this->readFilesInFileadmin();
        $fileList = GeneralUtility::getAllFilesAndFoldersInPath($resourceFiles, $resPath);

        // Pfade kürzen und in Array abspeichern
        foreach ($fileList as $paths) {
            $newFileList[] = str_replace('resource/', '', strstr($paths, 'resource'));
        }

        // Unterschiede ermitteln
        $diffFiles = array_diff($newFileList, $fileadminFiles);

        // Dateien aus resource ind fileadmin kopieren
        foreach ($diffFiles as $file) {
            if (!file_exists($path . '/' . $file)) {
                copy($resPath . $file, $path . '/' . $file);
            }
        }
    }

    
    /**
     * Gibt die entsprechende UUID passend zum Datensatz zurück
     * 
     * @param string $uid
     * @param string $table
     * @return string
     */
    protected function getUuid($uid, $table) {
        $con = $this->getDatabase();
        $uuid = $con->exec_SELECTgetSingleRow('uuid', $table, 'uid = ' . $uid);

        return $uuid['uuid'];
    }
    
    
    /**
     * Gibt die uuid der übergebenen pid zurück
     * 
     * @param string $pid
     * @return string
     */
    public function getPageUuid($pid){
        /** @var TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = $this->getDatabase();
        $uuid = $con->exec_SELECTgetSingleRow('uuid', 'pages', 'uid = '.$pid);
        
        return (!empty($uuid['uuid'])) ? $uuid['uuid'] : 0;
    }

   
    
    // ============================ Getter & Setter ================================

    /**
     * @return array
     */
    public function getFileList() {
        return $this->fileList;
    }

    /**
     * @param array $fileList
     */
    public function setFileList($fileList) {
        $this->fileList = $fileList;
    }

    /**
     * @return \XMLWriter
     */
    public function getXmlwriter() {
        return $this->xmlwriter;
    }

    /**
     * @param \XmlWriter $xmlwriter
     */
    public function setXmlwriter(\XmlWriter $xmlwriter) {
        $this->xmlwriter = $xmlwriter;
    }

    /**
     * @return \SimpleXml
     */
    public function getXmlreader() {
        return $this->xmlreader;
    }

    /**
     * @param \SimpleXml $xmlreader
     */
    public function setXmlreader(\SimpleXml $xmlreader) {
        $this->xmlreader = $xmlreader;
    }

    /**
     * @return int
     */
    public function getMaxFileSize() {
        return $this->maxFileSize;
    }

    /**
     * @param int $maxFileSize
     */
    public function setMaxFileSize() {
        $configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['deployment']);
        (!empty($configuration)) ? $this->maxFileSize = $configuration['maxFileSize'] : $this->maxFileSize = 10000000;
    }

    /**
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabase() {
        return $GLOBALS['TYPO3_DB'];
    }

}



// FileReference

//            // FileRefenrece einfügen
//            $this->fileReference = $this->getFileReferenceFromTable($file->getUid());
//            if($this->fileReference != null){
//                $this->xmlwriter->startElement('fileReference');
//                $this->xmlwriter->writeElement('tablenames', ($this->fileReference->getTablenames() == null) ? '' :  $this->fileReference->getTablenames());
//                $this->xmlwriter->writeElement('fieldname', ($this->fileReference->getFieldname() == null) ? '' :  $this->fileReference->getFieldname());
//                $this->xmlwriter->writeElement('title', ($this->fileReference->getTitle() == null) ? null :  $this->fileReference->getTitle());
//                $this->xmlwriter->writeElement('description', ($this->fileReference->getDescription() == null) ? '' :  $this->fileReference->getDescription());
//                $this->xmlwriter->writeElement('alternative', ($this->fileReference->getAlternative() == null) ? '' :  $this->fileReference->getAlternative());
//                $this->xmlwriter->writeElement('link', ($this->fileReference->getLink() == null) ? '' :  $this->fileReference->getLink());
//                $this->xmlwriter->writeElement('uuid', ($this->fileReference->getUuid() == null) ? '' :  $this->fileReference->getUuid());
//                $this->xmlwriter->endElement();
//            }
//            $this->xmlwriter->endElement();




    /**
     * Gibt die referenzierten Daten für den übergebenen Datensatz zurück
     * 
     * @param string $uid
     * @return \TYPO3\Deployment\Domain\Model\FileReference
     */
//    protected function getFileReferenceFromTable($uid){
//        /** @var \TYPO3\Deployment\Domain\Repository\FileReferenceRepository $fileRefObj */
//        $fileRefObj = GeneralUtility::makeInstance('TYPO3\\Deployment\\Domain\\Repository\\FileReferenceRepository');
//        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $res */
//        $res = $fileRefObj->findByUidForeign($uid);
//
//        return ($res != null) ? $res[0] : null;
//    }



    /**
//     * @return \TYPO3\Deployment\Domain\Model\FileReference
//     */
//    public function getFileReference() {
//        return $this->fileReference;
//    }
//
//    /**
//     * @param \TYPO3\Deployment\Domain\Model\FileReference $fileReference
//     */
//    public function setFileReference($fileReference) {
//        $this->fileReference = $fileReference;
//    }