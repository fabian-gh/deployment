<?php

/**
 * Auto Publish Command Controller
 *
 * @category   Extension
 * @package    Deployment
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */

namespace TYPO3\Deployment\Command;

use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Auto Publish Command Controller
 *
 * @package    Deployment
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class AutoPublishCommandController extends CommandController {

    /**
     * Run the publish command
     *
     * @param int     $lastDeployments
     * @param boolean $dryRun
     */
    public function publishCommand($lastDeployments, $dryRun) {
        // todo: depyloment einlesen und ausführen
    }

}
