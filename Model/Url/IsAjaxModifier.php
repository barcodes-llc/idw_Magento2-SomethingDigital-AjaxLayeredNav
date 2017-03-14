<?php

namespace SomethingDigital\AjaxLayeredNav\Model\Url;

use Magento\Framework\App\Area;
use Magento\Framework\Url\ModifierInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Modifier of base URLs on staging preview.
 */
class IsAjaxModifier implements \Magento\Framework\Url\ModifierInterface
{
    /**
     * @var string
     */
    protected $mode = ModifierInterface::MODE_ENTIRE;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @param \Magento\Framework\App\State $state ,
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->state = $state;
        $this->request = $request;
    }

    /**
     * Modify URL to remove unnecessary param "is_ajax=1"
     *
     * Magento adds it automatically as param from "current" request. This param has been added to identify ajax
     * requests from product list (pagination, filters) to cache them separatedly in FPC. Use non-standard param name
     * for ajax (standard params are "ajax" and "isAjax") to don't impact on any existing functionality.
     *
     * @param string $url
     * @param string $mode
     * @return string
     */
    public function execute($url, $mode = ModifierInterface::MODE_ENTIRE)
    {
        if ($mode == $this->mode) {
            try {
                $areaCode = $this->state->getAreaCode();
            } catch (LocalizedException $e) {
                return $url;
            }

            if ($areaCode == Area::AREA_FRONTEND) {
                if ($this->request->getParam('is_ajax')) {
                    $url = str_replace(array('&amp;is_ajax=1', '&is_ajax=1', 'is_ajax=1&amp;', 'is_ajax=1&', '?is_ajax=1'), '', $url);
                }
            }
        }

        return $url;
    }
}
