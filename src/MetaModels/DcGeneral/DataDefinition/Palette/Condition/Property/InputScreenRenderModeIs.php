<?php
/**
 * PHP version 5
 *
 * @package    MetaModels
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\LegendInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;

/**
 * Condition for the default palette.
 */
class InputScreenRenderModeIs implements PropertyConditionInterface
{

    /**
     * The expected property value.
     *
     * @var string
     */
    protected $desiredState;

    /**
     * The expected property value.
     *
     * @var string[]
     */
    protected static $stateBuffer;

    /**
     * Create a new instance.
     *
     * @param string $desiredState The desired state.
     */
    public function __construct($desiredState)
    {
        $this->setRenderMode($desiredState);
    }

    /**
     * Set the desired state.
     *
     * @param string $desiredState The desired state.
     *
     * @return InputScreenAttributeIs
     */
    public function setRenderMode($desiredState)
    {
        $this->desiredState = $desiredState;

        return $this;
    }

    /**
     * Retrieve the desired state.
     *
     * @return mixed
     */
    public function getRenderMode()
    {
        return $this->desiredState;
    }

    /**
     * Retrieve the type name from an attribute.
     *
     * @param int $value The id of an input screen.
     *
     * @return string
     */
    public function getInputScreenRenderMode($value)
    {
        if (!isset(self::$stateBuffer[$value])) {
            self::$stateBuffer[$value] = \Database::getInstance()
                ->prepare('SELECT rendermode FROM tl_metamodel_dca WHERE id=?')
                ->limit(1)
                ->executeUncached($value)
                ->rendermode;
        }

        return self::$stateBuffer[$value];
    }

    /**
     * {@inheritdoc}
     */
    public function match(
        ModelInterface $model = null,
        PropertyValueBag $input = null,
        PropertyInterface $property = null,
        LegendInterface $legend = null
    ) {
        if ($input && $input->hasPropertyValue('pid')) {
            $value = $input->getPropertyValue('pid');
        } elseif ($model) {
            $value = $model->getProperty('pid');
        } else {
            return false;
        }

        return $this->getInputScreenRenderMode($value) == $this->getRenderMode();
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
    }
}
