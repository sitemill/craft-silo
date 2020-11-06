<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace sitemill\dam\elements\actions;

use Craft;
use craft\base\Element;
use craft\base\ElementAction;
use craft\base\ElementInterface;
use craft\elements\db\ElementQueryInterface;

/**
 * @author    SiteMill
 * @author    SiteMill
 * @package   Dam
 * @since     1.0.0
 */
class Approve extends ElementAction
{

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return Craft::t('app', 'Approve');
    }


    /**
     * @inheritdoc
     */
    public function getTriggerHtml()
    {
        return Craft::$app->getView()->renderTemplate('dam/_components/elementactions/approve/trigger.twig');
    }


    /**
     * @inheritdoc
     */
    public function performAction(ElementQueryInterface $query): bool
    {
//        $this->setMessage(Craft::t('app', 'Status updated, with some failures due to validation errors.'));
        $elementsService = Craft::$app->getElements();
        $elements = $query->all();
        $failCount = 0;

        foreach ($elements as $element) {
            $element->approved = true;

            if ($elementsService->saveElement($element) === false) {
                // Validation error
                $failCount++;
            }
        }

        if ($failCount !== 0) {
            $this->setMessage(Craft::t('app', 'Status updated, with some failures due to validation errors.'));
        } else {
            if (count($elements) === 1) {
                $this->setMessage(Craft::t('dam', 'Asset approved.'));
            } else {
                $this->setMessage(Craft::t('dam', 'Assets approved.'));
            }
        }

        return true;
    }
}
