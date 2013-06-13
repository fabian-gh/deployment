<?php

/**
 * CopyResourcesCommandController
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Command
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Command;

use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;
use TYPO3\Deployment\Service\CopyService;

/**
 * CopyResourcesCommandController
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