<?php

namespace SomethingDigital\AjaxLayeredNav\Model\Plugin\Swatches;

use Magento\Swatches\Model\Plugin\FilterRenderer as FilterRendererBase;
use SomethingDigital\AjaxLayeredNav\Model\ConfigInterface;

class FilterRendererPlugin extends FilterRendererBase
{
    /**
     * @var ConfigInterface
     */
    protected $ajaxConfig;

    /**
     * @param ConfigInterface $ajaxConfig
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Swatches\Helper\Data $swatchHelper
     */
    public function __construct(
        ConfigInterface $ajaxConfig,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Swatches\Helper\Data $swatchHelper
    ) {
        parent::__construct($layout, $swatchHelper);
        $this->ajaxConfig = $ajaxConfig;
        if ($this->ajaxConfig->enabled()) {
            $this->block = \SomethingDigital\AjaxLayeredNav\Block\Swatches\RenderLayered::class;
        }
    }
}
