<?php

/**
 * DatabaseService
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Service;

/**
 * DatabaseService
 *
 * @package    Deployment
 * @subpackage Domain\Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class DatabaseService extends AbstractDataService {

    /**
     * quick access databse function
     */
    public static function connectTestDatabaseIfExist() {
        $dbSerice = new DatabaseService();
        $dbSerice->connectTestDatabaseIfExistInternal();
    }

    
    /**
     * quick access databse function
     */
    public static function reset() {
        $dbSerice = new DatabaseService();
        $dbSerice->resetInternal();
    }

    
    public function resetInternal() {
        // Verbindet mit der Datenbank aus
        // $GLOBALS['TYPO3_CONF_VARS']['DB']
        // Orgainal verbidungdaten ermitteln
        # $this->connect();
    }

    
    /**
     *
     */
    public function connectTestDatabaseIfExistInternal() {

        // Configuration
        // wenn Test daten konfiguriert sind dann
        // wenn keine Testdaten, nichts machen, ansonsten: $this->connect();
    }

    
    protected function connect($host, $name, $pass, $db) {
        #$this->getDatabase()->
        #$this->getDatabase()->setDatabaseHost();
        #$this->getDatabase()->setDatabaseName();
    }

}