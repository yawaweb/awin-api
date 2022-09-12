<?php
/**
 * Awin API client for publisher
 *
 * @package   awin-api
 * @author    Ousama Yamine <hello@yawaweb.com>
 * @copyright 2016-2022 Yawaweb <hello@yawaweb.com>
 * @license   http://opensource.org/licenses/MIT MIT Public
 * @version   1.0.1
 * @link      https://yawaweb.com
 *
 */

namespace yawaweb\AwinApi;

/**
 * Commission group object
 * @package yawaweb\AwinApi
 */
class CommissionGroup {
    const TYPE_FIXED = 'fix';
    const TYPE_PERCENTAGE = 'percentage';

    /**
     * @var integer
     */
    public int $id;

    /**
     * @var integer
     */
    public int $advertiserId;

    /**
     * @var string
     */
    public string $code;

    /**
     * @var string
     */
    public string $name;

    /**
     * @var string
     */
    public string $type;

    /**
     * @var double
     */
    public float $amount;

    /**
     * @var string
     */
    public string $currency;

    /**
     * @var double
     */
    public float $percentage;

    /**
     * Construct a CommissionGroup from JSON
     * @param $commissionGroupData array Commission group JSON data
     * @param $advertiserId        integer Advertiser ID
     * @return CommissionGroup
     */
    public static function createFromJson(array $commissionGroupData, int $advertiserId): CommissionGroup
    {
        $commissionGroup = new self();

        $commissionGroup->id = $commissionGroupData['groupId'];
        $commissionGroup->advertiserId = $advertiserId;
        $commissionGroup->code = $commissionGroupData['groupCode'];
        $commissionGroup->name = $commissionGroupData['groupName'];
        $commissionGroup->type = $commissionGroupData['type'];

        if ($commissionGroup->type == self::TYPE_FIXED) {
            $commissionGroup->currency = $commissionGroupData['currency'];
            $commissionGroup->amount = $commissionGroupData['amount'];
        } else {
            $commissionGroup->percentage = $commissionGroupData['percentage'];
        }

        return $commissionGroup;
    }
}
