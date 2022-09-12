<?php 
/**
 * Awin API client for publisher
 *
 * @package   awin-api
 * @author    Ousama Yamine <hello@yawaweb.com>
 * @copyright 2016-2021 Yawaweb <hello@yawaweb.com>
 * @license   http://opensource.org/licenses/MIT MIT Public
 * @version   1.0.1
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
    public string $id;

    /**
     * @var string
     */
    public string $clickThroughUrl;

    /**
     * @var string
     */
    public string $displayUrl;

    /**
     * @var string
     */
    public string $logoUrl;

    /**
     * @var string
     */
    public string $name;

    /**
     * @var string
     */
    public string $currencyCode;

    /**
     * @var object
     */
    public object $primaryRegion;

    /**
     * @var array
     */
    public array $validDomains;

    /**
     * @var object
     */
    public object $kpi;

    /**
     * @var object
     */
    public object $commissionRange;

    /**
     * Create programme details object from two JSON objects
     * @param $data
     * @return ProgrammeDetails
     */
    public static function createFromJson($data): ProgrammeDetails
    {
        $programmeDetails = new self();

        $programmeDetails->id = $data['programmeInfo']['id'];
        $programmeDetails->clickThroughUrl = $data['programmeInfo']['clickThroughUrl'];
        $programmeDetails->displayUrl = $data['programmeInfo']['displayUrl'];
        $programmeDetails->logoUrl = $data['programmeInfo']['logoUrl'];
        $programmeDetails->name = $data['programmeInfo']['name'];
        $programmeDetails->currencyCode = $data['programmeInfo']['currencyCode'];
        $programmeDetails->primaryRegion = (object)$data['programmeInfo']['primaryRegion'];
        $programmeDetails->validDomains = $data['programmeInfo']['validDomains'];

        $programmeDetails->kpi = (object)$data['kpi'];
        $programmeDetails->commissionRange = (object)$data['commissionRange'];

        return $programmeDetails;
    }
}
