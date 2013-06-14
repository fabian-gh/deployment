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
        $dbService = new DatabaseService();
        $dbService->connectTestDatabaseIfExistInternal();
    }

    
    /**
     * Verbindung zur Datenbank zurück setzen
     */
    public static function reset() {
        $dbService = new DatabaseService();
        $dbService->resetInternal();
    }

    
    /**
     * Verbindung zur Datenbank zurück setzen
     */
    public function resetInternal() {
        $this->getDatabase()->setDatabaseHost(TYPO3_db_host);
        $this->getDatabase()->setDatabaseUsername(TYPO3_db_username);
        $this->getDatabase()->setDatabasePassword(TYPO3_db_password);
        $this->getDatabase()->setDatabaseName(TYPO3_db);
        $this->getDatabase()->connectDB();
    }

    
    /**
     * Prüfen ob Verbindungsdaten existieren
     */
    public function connectTestDatabaseIfExistInternal() {
        if($this->host !== '' && $this->user !== '' && $this->pass !== '' && $this->db !== ''){
            $this->connect($this->host, $this->user, $this->pass, $this->db);
        }
    }

    
    /**
     * Mit Testdatenbank verbinden
     * 
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param string $db
     */
    protected function connect($host, $user, $pass, $db) {
        $this->getDatabase()->setDatabaseHost($host);
        $this->getDatabase()->setDatabaseUsername($user);
        $this->getDatabase()->setDatabasePassword($pass);
        $this->getDatabase()->setDatabaseName($db);
        $this->getDatabase()->connectDB();
    }

}