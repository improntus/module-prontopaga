<?php
/*
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Improntus\ProntoPaga\Model\Config\Comment;

use Magento\Config\Model\Config\CommentInterface;
use Magento\Framework\UrlInterface;

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
