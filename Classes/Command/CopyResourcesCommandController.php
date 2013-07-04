<?php

/**
 * Deployment-Extension
 * This is an extension to integrate a deployment process for TYPO3 CMS
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Command
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Command;

use \TYPO3\CMS\Extbase\Mvc\Controller\CommandController;
use \TYPO3\Deployment\Service\CopyService;

/**
 * CopyResourcesCommandController
 * Class for the Command Controller Task
 *
 * @package    Deployment
 * @subpackage Domain\Command
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class CopyResourcesCommandController extends CommandController {

    /**
     * Execute the copying task
     */
    public function copyCommand() {
        /** @var \TYPO3\Deployment\Service\CopyService $copyService */
        $copyService = new CopyService();
        $copyService->execute();
    }

}