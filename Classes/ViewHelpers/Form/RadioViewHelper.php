<?php

/**
 * RadioViewHelper
 * 
 * @category   Extension
 * @package    Deployment
 * @subpackage ViewHelpers\Form
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\ViewHelpers\Form;

/**
 * RadioViewHelper
 * 
 * @package    Deployment
 * @subpackage ViewHelpers\Form
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class RadioViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\RadioViewHelper {

    /**
     * Initialize the arguments.
     *
     * @return void
     * @api
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('radioIndex', 'int', 'Index of the radio box', FALSE, 0);
    }

    
    /**
     * Renders the checkbox.
     *
     * @param boolean $checked Specifies that the input element should be preselected
     * @return string
     * @api
     */
    public function render($checked = NULL) {
        $this->tag->addAttribute('type', 'radio');

        $nameAttribute = $this->getName();
        $valueAttribute = $this->getValue();
        if ($checked === NULL && $this->isObjectAccessorMode()) {
            if ($this->hasMappingErrorOccured()) {
                $propertyValue = $this->getLastSubmittedFormData();
            } else {
                $propertyValue = $this->getPropertyValue();
            }

            // no type-safe comparison by intention
            $checked = $propertyValue == $valueAttribute;
        }

        // =====  Neu: Erweiterung der Radio Buttons um Arrays anhängen zu können ====
        if ($this->isObjectAccessorMode()) {
            $propertyValue = $this->getPropertyValue();
            if (is_array($propertyValue)) {
                $nameAttribute .= '[' . $this->arguments['radioIndex'] . ']';
            }
        }
        // ============================================================================

        $this->registerFieldNameForFormTokenGeneration($nameAttribute);
        $this->tag->addAttribute('name', $nameAttribute);
        $this->tag->addAttribute('value', $valueAttribute);
        if ($checked) {
            $this->tag->addAttribute('checked', 'checked');
        }

        $this->setErrorClassAttribute();

        return $this->tag->render();
    }

}