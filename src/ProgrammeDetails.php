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
 * Get all Programmes
 * @package yawaweb\AwinApi
 */
class ProgrammeDetails {

    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $clickThroughUrl;

    /**
     * @var string
     */
    public $displayUrl;

    /**
     * @var string
     */
    public $logoUrl;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $currencyCode;

    /**
     * @var object
     */
    public $primaryRegion;

    /**
     * @var array
     */
    public $validDomains;

    /**
     * @var object
     */
    public $kpi;

    /**
     * @var object
     */
    public $commissionRange;

    /**
     * Create programme details object from two JSON objects
     * @param \stdClass $data
     * @return ProgrammeDetails
     */
    public static function createFromJson(\stdClass $data) {
        $programmeDetails = new ProgrammeDetails();

        $programmeDetails->id = $data->programmeInfo->id;
        $programmeDetails->clickThroughUrl = $data->programmeInfo->clickThroughUrl;
        $programmeDetails->displayUrl = $data->programmeInfo->displayUrl;
        $programmeDetails->logoUrl = $data->programmeInfo->logoUrl;
        $programmeDetails->name = $data->programmeInfo->name;
        $programmeDetails->currencyCode = $data->programmeInfo->currencyCode;
        $programmeDetails->primaryRegion = $data->programmeInfo->primaryRegion;
        $programmeDetails->validDomains = $data->programmeInfo->validDomains;

        $programmeDetails->kpi = $data->kpi;
        $programmeDetails->commissionRange = $data->commissionRange;

        return $programmeDetails;
    }
}
