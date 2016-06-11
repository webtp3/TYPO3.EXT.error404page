<?php
namespace R3H6\Error404page\Controller;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 R3 H6 <r3h6@outlook.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use R3H6\Error404page\Domain\Model\Dto\ErrorDemand;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;
/**
 * ErrorController
 */
class ErrorController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * ErrorRepository
     *
     * @var R3H6\Error404page\Domain\Repository\ErrorRepository
     * @inject
     */
    protected $errorRepository = null;

    /**
     * action dashboard
     *
     * @param \R3H6\Error404page\Domain\Model\Dto\ErrorDemand $demand
     * @return void
     */
    public function dashboardAction(ErrorDemand $demand = null)
    {
        if ($demand === null) {
            $demand = $this->objectManager->get(ErrorDemand::class);
            $demand->setMinTime(strtotime(ErrorDemand::TIME_ONE_WEEK_AGO));
        }
        $errors = $this->errorRepository->findErrorTopUrls(100);
        $this->view->assign('errors', $errors);
        $this->view->assign('demand', $demand);
    }

    protected function initializeListAction()
    {
        $propertyMappingConfiguration = $this->arguments->getArgument('demand')->getPropertyMappingConfiguration();
        $propertyMappingConfiguration->allowAllProperties();
        $propertyMappingConfiguration->setTypeConverterOption(PersistentObjectConverter::class, PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, true);
    }


    /**
     * action list
     *
     * @param \R3H6\Error404page\Domain\Model\Dto\ErrorDemand $demand
     * @return void
     */
    public function listAction(ErrorDemand $demand)
    {
        switch ($demand->getType()) {
            case ErrorDemand::TYPE_GROUPED_BY_DAY:
                $errors = $this->errorRepository->findErrorGroupedByDay(
                    $demand->getMinTime() ? new \DateTime('@' . $demand->getMinTime()): null
                );
                break;
            case ErrorDemand::TYPE_TOP_URLS:
                $errors = $this->errorRepository->findErrorTopUrls(
                    new \DateTime('@' . $demand->getMinTime())
                );
                break;
        }
        $this->view->assign('errors', $errors);
        $this->view->assign('demand', $demand);
    }

    /**
     * action deleteAll
     *
     * @return void
     */
    public function deleteAllAction()
    {
        $this->errorRepository->deleteAll();
        $this->addFlashMessage('Truncated errors log.');
        $this->redirect('dashboard');
    }
}
