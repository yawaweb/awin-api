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

class Transaction {
    /**
     * @var string
     */
    public string $id;


    /**
     * @var string|null
     */
    public null|string $transactionDate;

    /**
     * @var string|null
     */
    public ?string $clickDate;

    /**
     * @var string|null
     */
    public ?string $validationDate;

    /**
     * @var string
     */
    public string $advertiserId;

    /**
     * @var string|null
     */
    public ?string $clickDevice;

    /**
     * @var string|null
     */
    public ?string $transactionDevice;

    /**
     * @var string
     */
    public string $commissionStatus;

    /**
     * @var string|null
     */
    public ?string $declineReason;

    /**
     * @var string []
     */
    public array $clickRefs;

    /**
     * @var double Effective commission for this sale
     */
    public float $commissionAmount;

    /**
     * @var double Total commission for this sale
     */
    public float $totalCommissionAmount;

    /**
     * @var boolean Whether the commission for this sale is shared with a service provider
     */
    public bool $sharedCommission;

    /**
     * @var double Percentage of the total sale commission
     */
    public float $commissionPercentage;

    /**
     * @var string|null
     */
    public ?string $orderReference;

    /**
     * @var double
     */
    public float $saleAmount;

    /**
     * @var string
     */
    public string $siteName;

    /**
     * @var string|null
     */
    public ?string $url;

    /**
     * @var boolean
     */
    public bool $paid;

    /**
     * @var TransactionPart[]
     */
    public array $transactionParts = [];

    /**
     * @var string
     */
    public string $transactionType;

    /**
     * Create a Transaction object from two JSON objects
     * @param $transData \stdClass Transaction data
     * @return Transaction
     */
    public static function createFromJson($transData) {
        $transaction = new Transaction();

        $transaction->id = $transData['id'];
        $transaction->transactionDate = $transData['transactionDate'];
        $transaction->clickDate = $transData['clickDate'];
        $transaction->validationDate = $transData['validationDate'];
        $transaction->advertiserId = $transData['advertiserId'];
        $transaction->clickDevice = $transData['clickDevice'];
        $transaction->transactionDevice = $transData['transactionDevice'];
        $transaction->commissionStatus = $transData['commissionStatus'];
        $transaction->declineReason = $transData['declineReason'];
        $transaction->clickRefs = (array)$transData['clickRefs'];
        $transaction->commissionAmount = $transData['commissionAmount']['amount'];
        $transaction->orderReference = $transData['orderRef'];
        $transaction->saleAmount = $transData['saleAmount']['amount'];
        $transaction->siteName = $transData['siteName'];
        $transaction->url = $transData['publisherUrl'];
        $transaction->paid = $transData['paidToPublisher'];
        $transaction->transactionType = $transData['type'];

        $transaction->totalCommissionAmount = 0;

        // Process transaction parts:
        foreach ($transData['transactionParts'] as $transactionPartData) {
            $transactionPart = new TransactionPart();

            $transactionPart->commissionGroupId = $transactionPartData['commissionGroupId'];
            $transactionPart->amount = $transactionPartData['amount'];
            $transactionPart->commissionAmount = $transactionPartData['commissionAmount'];

            // Add transaction part
            $transaction->transactionParts[] = $transactionPart;

            // Keep track of total commission (over all transaction parts)
            $transaction->totalCommissionAmount += $transactionPart->commissionAmount;
        }

        // Determine whether the commission for this sale is shared with other publisher:
        if ($transaction->totalCommissionAmount > 0 && $transaction->totalCommissionAmount != $transaction->commissionAmount) {
            $transaction->sharedCommission = true;
            $transaction->commissionPercentage = $transaction->commissionAmount / $transaction->totalCommissionAmount * 100;
        } else {
            $transaction->sharedCommission = false;
            $transaction->commissionPercentage = 100;
        }

        return $transaction;
    }
}
