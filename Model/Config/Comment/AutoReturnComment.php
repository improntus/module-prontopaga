<?php

namespace Improntus\ProntoPaga\Model\Config\Comment;

use Magento\Config\Model\Config\CommentInterface;
use Magento\Framework\UrlInterface;

/**
 * @author Improntus <http://www.improntus.com> - Adobe Gold Partner - Elevating digital experience
 * @copyright Copyright (c) 2025 Improntus
 */
class AutoReturnComment implements CommentInterface
{
    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * Constructor
     *
     * @param UrlInterface $urlInterface
     */
    public function __construct(
        UrlInterface $urlInterface
    ) {
        $this->urlInterface = $urlInterface;
    }

    /**
     * Retrieve element comment by element value
     * @param string $elementValue
     * @return string
     */
    public function getCommentText($elementValue)
    {
        $url = $this->urlInterface->getUrl('*/*/*/section/cataloginventory');
        return __('Please make sure that the <strong style="text-decoration: underline;">Automatically Return Credit Memo Item to Stock</strong> is <strong>enabled</strong> in<br>
        <a href="%1" target="_blank" no-referrer>Inventory > Product Stock Options > Automatically Return Credit Memo Item to Stock</a>.', $url, ['a']);
    }
}
