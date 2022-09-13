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
 * @since     1.0.1
 */

namespace yawaweb\AwinApi;

/**
 * Get all Programmes
 * @package yawaweb\AwinApi
 */
class Programmes {

    /**
     * @var string
     */
    public string $id;

    /**
     * @var string
     */
    public string $status;

    /**
     * @var string
     */
    public string $name;

    /**
     * @var string
     */
    public string $description;

    /**
     * @var string|null
     */
    public ?string $logoUrl;

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
     * Create programme details object from two JSON objects
     * @param $data
     * @return Programmes
     */
    public static function createFromJson($data): Programmes
    {
        $programmes = new self();

        $programmes->id = $data['id'];
        $programmes->status = $data['status'];
        $programmes->name = $data['name'];
        $programmes->description = $data['description'];
        $programmes->logoUrl = $data['logoUrl'];
        $programmes->clickThroughUrl = $data['clickThroughUrl'];
        $programmes->displayUrl = $data['displayUrl'];

        $programmes->currencyCode = $data['currencyCode'];
        $programmes->primaryRegion = (object)$data['primaryRegion'];
        $programmes->validDomains = $data['validDomains'];

        return $programmes;
    }
}
