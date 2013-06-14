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
     * @var string
     */
    protected $host = 'localhost';
    
    /**
     * @var string
     */
    protected $user = 'root';
    
    /**
     * @var string
     */
    protected $pass = 'root';
    
    /**
     * @var string
     */
    protected $db = 't3masterdeploy2';

    
    /**
     * Schnellzugriff auf die Testdatenbank
     */
    public static function connectTestDatabaseIfExist() {
        $dbSerice = new DatabaseService();
        $dbSerice->connectTestDatabaseIfExistInternal();
    }

    
    /**
     * Verbindung zur Datenbank zurück setzen
     */
    public static function reset() {
        $dbSerice = new DatabaseService();
        $dbSerice->resetInternal();
    }

    
    /**
     * Verbindung zur Datenbank zurück setzen
     */
    public function resetInternal() {
        $this->getDatabase();
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