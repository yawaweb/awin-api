<?php
/**
 * Awin API client for publisher
 *
 * @package   awin-api
 * @author    Ousama Yamine <hello@yawaweb.com>
 * @copyright 2016-2021 Yawaweb <hello@yawaweb.com>
 * @license   http://opensource.org/licenses/MIT MIT Public
 * @version   1.0.0
 * @link      https://yawaweb.com
 *
 */

namespace yawaweb\AwinApi;

/**
 * Transaction part object
 * @package yawaweb\AwinApi
 */
class TransactionPart {
    /**
     * @var integer
     */
    public $commissionGroupId;

    /**
     * @var CommissionGroup|null
     */
    public $commissionGroup;

    /**
     * @var double
     */
    public $amount;

    /**
     * @var double
     */
    public $commissionAmount;
}
