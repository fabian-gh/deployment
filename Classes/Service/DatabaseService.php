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
    }

}