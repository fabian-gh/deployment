<?php

/**
 * Repository
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Repository
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Domain\Repository;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\Deployment\Domain\Model\Log;
use \TYPO3\Deployment\Service\ConfigurationService;

/**
 * Log Repository
 *
 * @package    Deployment
 * @subpackage Domain\Repository
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class LogRepository extends AbstractRepository {

	/**
	 * Gibt alle noch nicht deployeten Datensätze zurück
	 *
	 * @param string $timestamp
	 *
	 * @return array<\TYPO3\Deployment\Domain\Model\Log>
	 */
	public function findYoungerThen($timestamp) {
		$query = $this->createQuery();

		$constraints = array();
		$constraints[] = $query->greaterThanOrEqual('tstamp', $timestamp);
		$constraints[] = $query->equals('error', '0');
		$constraints[] = $query->greaterThan('action', '0');
		$constraints[] = $query->logicalNot($query->equals('tablename', ''));

		$query->matching($query->logicalAnd($constraints));
		$result = $query->execute();

		// Filterung von Datensätzen die in Tabellen stehen, die auch in der
		// Konfiguration aufgelistet sind
		return $this->filterEntries($result);
	}

	/**
	 * Filtert alle Einträge heraus, die aus Tabellen kommen, die nicht deployed
	 * werden sollen
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult $result
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult
	 */
	public function filterEntries($result) {
		$count = 0;
		$config = new ConfigurationService();
		$tables = $config->getDeploymentTables();

		foreach ($result as $count => $res) {
			/** @var Log $res */
			if (!in_array($res->getTablename(), $tables)) {
				unset($result[$count]);
			}
		}

		return $result;
	}

	/**
	 * Beispiel für individuelle Abfrage. Bitte in find* umbennnen und korrigieren
	 */
	/*public function customQuery() {
		/** @var $query \TYPO3\CMS\Extbase\Persistence\Generic\Query */
	/*$query = $this->createQuery();

	$query->statement('SELECT sys_log.* FROM sys_log');

	return $query->execute();
}*/

}