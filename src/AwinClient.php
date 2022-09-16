<?php
/**
 * Awin API client for publisher
 *
 * @package   awin-api
 * @author    Ousama Yamine <hello@yawaweb.com>
 * @copyright 2016-2021 Yawaweb <hello@yawaweb.com>
 * @license   http://opensource.org/licenses/MIT MIT Public
 * @version   1.0.3
 * @link      https://yawaweb.com
 *
 */

namespace yawaweb\AwinApi;

use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;

class AwinClient {
    /**
     * @var boolean Whether to request commission groups
     */
    public bool $verboseCommissionGroups = false;

    /**
     * @var string
     */
    private string $authToken;

    /**
     * @var integer
     */
    private int $publisherId;

    /**
     * @var string Endpoint for Awin API
     */
    protected string $endpoint = 'https://api.awin.com';

    /**
     * @var CommissionGroup[] Commission groups
     */
    protected array $commissionGroups = [];

    /**
     * Constructor.
     * @param $authToken   string Awin auth token
     * @param $publisherId integer Awin Publisher ID
     */
    public function __construct(string $authToken, int $publisherId) {
        $this->authToken = $authToken;
        $this->publisherId = $publisherId;
    }

    //region CONNECTION & RESPONSE
    /**
     * Request
     * @param string $resource
     * @param array $params
     * @return array
     * @throws GuzzleException
     * @throws JsonException
     */
    protected function makeRequest(string $resource, array $params = []): array
    {
        $client = new Client([
            'base_uri' => $this->endpoint
        ]);

        $response = $client->request('GET', $resource, [
            'headers' =>  [
                'Authorization' => 'Bearer ' . $this->authToken,
                'Accept'        => 'application/json'
            ],
            'query' => $params,
            'http_errors' => false
        ]);

        if($response->getStatusCode() !== 200){
            return [
                'status' => false,
                'code' => $response->getStatusCode(),
                'description' => $response->getReasonPhrase()
            ];
        }

        return [
          'status' => true,
          'data' => json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR)
        ];
    }

    protected function result($data): array
    {
        return ['status' => true, 'data' => $data];
    }

    /**
     * Check connection.
     *
     * @throws GuzzleException
     * @throws JsonException
     *
     * @since 1.0.2
     */
    public function checkConnection(): array
    {
        $response = $this->getProgrammes('joined');

        if($response['status'] === true){
            return ['status' => true];
        }

        return $response;
    }

    /**
     * Debugger.
     *
     * @param $data
     * @return string
     *
     * @since 1.0.3
     */
    public function debug($data): string
    {
        return '<pre>'.print_r($data, true).'</pre>';
    }
    //endregion

    //region PROGRAMMES
    /**
     * Get all programmes by relationship
     * @param string|null $relationShip possible values are: <b>joined, pending, suspended, rejected, notjoined</b>
     * @param string|null $countryCode
     * @param bool $includeHidden
     * @return array
     * @throws GuzzleException
     * @throws JsonException
     * @since 1.0.3
     */
    public function getProgrammes(string $relationShip = null, string $countryCode = null): array
    {
        $response = $this->makeRequest("/publishers/$this->publisherId/programmes/", [
            'relationship' => $relationShip,
            'countryCode' => $countryCode
        ]);

        if($response['status'] === true && !empty($response['data'])) {
            $result = [];

            foreach($response['data'] as $key => $value){
                $result[$key] = Programmes::createFromJson($value);
            }

            return $this->result($result);
        }

        return $response;
    }

    /**
     * Get programme details by advertiser ID
     * @param $advertiserId
     * @return array
     * @throws Exception
     * @throws GuzzleException
     */
    public function getProgrammeDetails($advertiserId): array
    {
        $response = $this->makeRequest("/publishers/$this->publisherId/programmedetails", [
            'advertiserId' => $advertiserId
        ]);

        if($response['status'] === true && !empty($response['data'])) {
            return $this->result(ProgrammeDetails::createFromJson($response['data']));
        }

        return $response;
    }
    //endregion

    //region TRANSACTIONS
    /**
     * Get all transactions from $startDate until $endDate.
     *
     * @param string $startDate Start date Y-m-d
     * @param string $endDate End date Y-m-d
     * @param string $timezone Awin timezone format, possible values are:
     * <b>Europe/Berlin, Europe/Paris, Europe/London, Europe/Dublin, Canada/Eastern Canada/Central,
     * Canada/Mountain, Canada/Pacific, US/Eastern, US/Central, US/Mountain, US/Pacific, UTC</b>
     * @return array Transaction objects. Each part of a transaction is returned as a separate Transaction.
     * @throws Exception
     * @throws GuzzleException
     */
    public function getTransactions(string $startDate, string $endDate, string $timezone = 'Europe/Paris'): array
    {
        $response = $this->makeRequest("/publishers/$this->publisherId/transactions/", [
            'startDate' => $startDate.'T00:00:00',
            'endDate'   => $endDate.'T00:00:00',
            'timezone'  => $timezone
        ]);

        $transactions = [];

        if($response['status'] === true && !empty($response['data'])) {
            foreach ($response['data'] as $transactionData) {
                $transaction = Transaction::createFromJson($transactionData);

                if ($this->verboseCommissionGroups) {
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
    //endregion

    //region COMISSIONGROUPS
    /**
     * Get commission groups from an advertiser.
     *
     * @param $advertiserId integer Advertiser ID
     * @return CommissionGroup[]
     * @throws Exception
     * @throws GuzzleException
     */
    public function getCommissionGroups(int $advertiserId): array
    {
        $response = $this->makeRequest("/publishers/$this->publisherId/commissiongroups/", [
            'advertiserId' => $advertiserId,
        ]);

        if ($response['status'] === true && !empty($response['data'])) {
            $commissionGroups = [];

            foreach ($response['data']['commissionGroups'] as $commissionGroupData) {
                $commissionGroup = CommissionGroup::createFromJson((array)$commissionGroupData, $response['data']['advertiser']);

                $commissionGroups[] = $commissionGroup;
            }

            return $this->result($commissionGroups);
        }

        return [];
    }

    /**
     * @param $commissionGroupID integer
     * @param $advertiserId      integer
     * @return null|CommissionGroup
     * @throws Exception|GuzzleException
     */
    private function findCommissionGroup(int $commissionGroupID, int $advertiserId): ?CommissionGroup
    {
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

        return $this->commissionGroups[$commissionGroupID] ?? null;
    }
    //endregion
}
