<?php
/**
 * Awin API client for publisher
 *
 * @package   awin-api
 * @author    Ousama Yamine <hello@yawaweb.com>
 * @copyright 2016-2021 Yawaweb <hello@yawaweb.com>
 * @license   http://opensource.org/licenses/MIT MIT Public
 * @version   1.0.2
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
    public int $commissionGroupId;

    /**
     * @var CommissionGroup|null
     */
    public ?CommissionGroup $commissionGroup;

    /**
     * @var double
     */
    public float $amount;

    /**
     * @var double
     */
    public float $commissionAmount;
}
