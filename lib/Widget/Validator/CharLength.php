<?php
/**
 * Widget Framework
 *
 * @copyright   Copyright (c) 2008-2013 Twin Huang
 * @license     http://opensource.org/licenses/mit-license.php MIT License
 */

namespace Widget\Validator;

/**
 * Check if the characters length of input is equals specified length
 *
 * @author      Twin Huang <twinhuang@qq.com>
 */
class CharLength extends Length
{
    protected $lengthMessage = '%name% must have %length% characters';

    protected $notInMessage = '%name% must have %min% to %max% characters';

    protected $countByChars = true;
}