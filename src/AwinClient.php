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
     * DaisyconClient constructor.
     * @param $authToken   string Awin auth token
     * @param $publisherId integer Awin Publisher ID
     */
    public function __construct(string $authToken, int $publisherId) {
        $this->authToken = $authToken;
        $this->publisherId = $publisherId;
    }

    /**
     * Request
     * @throws GuzzleException|JsonException
     */
    protected function makeRequest($resource, $params = []): array
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
     * Get all active programmes
     * @param null $countryCode
     * @return array
     * @throws Exception
     * @throws GuzzleException
     */
    public function getActiveProgrammes($countryCode = null): array
    {
        $response = $this->makeRequest("/publishers/$this->publisherId/programmes/", [
            'relationship' => 'joined',
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

    /**
     * Get all transactions from $startDate until $endDate.
     *
     * @param DateTime $startDate Start date
     * @param DateTime $endDate End date
     * @param string $timezone Awin timezone format, see http://wiki.awin.com/index.php/API_get_transactions_list
     * @return array Transaction objects. Each part of a transaction is returned as a separate Transaction.
     * @throws Exception
     * @throws GuzzleException
     */
    public function getTransactions(DateTime $startDate, DateTime $endDate, string $timezone = 'Europe/Paris'): array
    {
        $response = $this->makeRequest("/publishers/$this->publisherId/transactions/", [
            'startDate' => $startDate->format('Y-m-d\TH:i:s'),
            'endDate'   => $endDate->format('Y-m-d\TH:i:s'),
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
}
