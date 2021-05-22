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

use DateTime;
use Httpful\Request;

class AwinClient {
    /**
     * @var boolean Whether to request commission groups
     */
    public $verboseCommissionGroups = false;

    /**
     * @var string
     */
    private $authToken;

    /**
     * @var integer
     */
    private $publisherId;

    /**
     * @var string Endpoint for Awin API
     */
    protected $endpoint = 'https://api.awin.com';

    /**
     * @var integer Max transactions per request
     */
    protected $itemsPerPage = 200;

    /**
     * @var CommissionGroup[] Commission groups
     */
    protected $commissionGroups = [];

    /**
     * DaisyconClient constructor.
     * @param $authToken   string Awin auth token
     * @param $publisherId integer Awin Publisher ID
     */
    public function __construct($authToken, $publisherId) {
        $this->authToken = $authToken;
        $this->publisherId = $publisherId;
    }

    protected function makeRequest($resource, $query = "") {
        $uri = $this->endpoint . $resource;

        $request = Request::get($uri . $query)
            ->addHeader('Authorization', 'Bearer ' . $this->authToken)
            ->expectsJson();

        $response = $request->send();

        // Check for errors
        if ($response->hasErrors()) {
            if (isset($response->body->description)) {
                throw new \Exception('API Error: ' . $response->body->description);
            } else {
                throw new \Exception('Invalid data');
            }
        }

        return $response;
    }

    /**
     * Get all transactions from $startDate until $endDate.
     *
     * @param DateTime $startDate Start date
     * @param DateTime $endDate   End date
     * @param string   $timezone  Awin timezone format, see http://wiki.awin.com/index.php/API_get_transactions_list
     * @return array Transaction objects. Each part of a transaction is returned as a separate Transaction.
     */
    public function getTransactions(DateTime $startDate, DateTime $endDate, $timezone = 'Europe/Paris') {
        $params = [
            'startDate' => $startDate->format('Y-m-d\TH:i:s'),
            'endDate'   => $endDate->format('Y-m-d\TH:i:s'),
            'timezone'  => $timezone
        ];

        $query = '?' . http_build_query($params);
        $response = $this->makeRequest("/publishers/{$this->publisherId}/transactions/", $query);

        $transactions = [];
        $transactionsData = $response->body;

        if ($transactionsData != null) {
            foreach ($transactionsData as $transactionData) {
                $transaction = Transaction::createFromJson($transactionData);

                if ($this->verboseCommissionGroups == true) {
                    // Search commission groups for this transaction
                    foreach ($transaction->transactionParts as $transactionPart) {
                        $transactionPart->commissionGroup = $this->findCommissionGroup($transactionPart->commissionGroupId, $transaction->advertiserId);
                    }
                }

                $transactions[] = $transaction;
            }
        }

        return $transactions;
    }


    /**
     * @param $advertiserId integer Advertiser ID
     * @return CommissionGroup[]
     */
    public function getCommissionGroups($advertiserId) {
        $params = [
            'advertiserId' => $advertiserId,
        ];

        $query = '?' . http_build_query($params);
        $response = $this->makeRequest("/publishers/{$this->publisherId}/commissiongroups/", $query);

        $commissionGroups = [];
        $cgData = $response->body;

        if ($cgData != null) {
            foreach ($cgData->commissionGroups as $commissionGroupData) {
                $commissionGroup = CommissionGroup::createFromJson((array)$commissionGroupData, $cgData->advertiser);

                $commissionGroups[] = $commissionGroup;
            }
        }

        return $commissionGroups;
    }

    /**
     * Get all programmes
     * @param string $relationShip
     * @param null $countryCode
     * @return array|object|string
     * @throws \Exception
     */
    public function getActiveProgrammes($countryCode = null)
    {
        $params = [
            'relationship' => 'joined',
            'countryCode' => $countryCode
        ];

        $query = '?' . http_build_query($params);

        $response = $this->makeRequest("/publishers/{$this->publisherId}/programmes/", $query);
        $data = $response->body;
        $result = [];

        if(!empty($data)) {
            foreach ($data as $key => $value) {
                $result[$key] = $this->getProgrammeDetails($value->id);
            }
        }

        return $result;
    }

    /**
     * Get programme details by advertiser ID
     * @param $advertiserId
     * @return array|object|string
     * @throws \Exception
     */
    private function getProgrammeDetails($advertiserId)
    {
        $params = [
            'advertiserId' => $advertiserId
        ];

        $query = '?' . http_build_query($params);
        $response = $this->makeRequest("/publishers/{$this->publisherId}/programmedetails", $query);

        $data = $response->body;
        $programmeDetails = ProgrammeDetails::createFromJson($data);

        return $programmeDetails;
    }

    /**
     * @param $commissionGroupID integer
     * @param $advertiserId      integer
     * @return null|CommissionGroup
     */
    private function findCommissionGroup($commissionGroupID, $advertiserId) {
        if (empty($commissionGroupID)) {
            return null;
        }

        if (isset($this->commissionGroups[$commissionGroupID])) {
            return $this->commissionGroups[$commissionGroupID];
        }

        // Request commission groups:
        $commissionGroups = $this->getCommissionGroups($advertiserId);

        foreach ($commissionGroups as $commissionGroup) {
            $this->commissionGroups[$commissionGroup->id] = $commissionGroup;
        }

        if (isset($this->commissionGroups[$commissionGroupID])) {
            return $this->commissionGroups[$commissionGroupID];
        } else {
            return null;
        }
    }
}
