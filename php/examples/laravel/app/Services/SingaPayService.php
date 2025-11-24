<?php

namespace App\Services;

use SingaPay\SingaPay;
use SingaPay\Exceptions\SingaPayException;

class SingaPayService
{
    protected $singapay;

    public function __construct(SingaPay $singapay)
    {
        $this->singapay = $singapay;
    }

    /**
     * Test connection to SingaPay API
     */
    public function testConnection()
    {
        try {
            return $this->singapay->testConnection();
        } catch (SingaPayException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        }
    }

    /**
     * Create new account
     */
    public function createAccount($data)
    {
        try {
            return [
                'success' => true,
                'data' => $this->singapay->account->create($data)
            ];
        } catch (SingaPayException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e instanceof \SingaPay\Exceptions\ValidationException ? $e->getErrors() : []
            ];
        }
    }

    /**
     * List accounts
     */
    public function listAccounts($page = 1, $perPage = 25)
    {
        try {
            return [
                'success' => true,
                'data' => $this->singapay->account->list($page, $perPage)
            ];
        } catch (SingaPayException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create payment link
     */
    public function createPaymentLink($accountId, $data)
    {
        try {
            return [
                'success' => true,
                'data' => $this->singapay->paymentLink->create($accountId, $data)
            ];
        } catch (SingaPayException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e instanceof \SingaPay\Exceptions\ValidationException ? $e->getErrors() : []
            ];
        }
    }

    /**
     * Create virtual account
     */
    public function createVirtualAccount($accountId, $data)
    {
        try {
            return [
                'success' => true,
                'data' => $this->singapay->virtualAccount->create($accountId, $data)
            ];
        } catch (SingaPayException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e instanceof \SingaPay\Exceptions\ValidationException ? $e->getErrors() : []
            ];
        }
    }

    /**
     * Check beneficiary
     */
    public function checkBeneficiary($bankAccountNumber, $bankSwiftCode)
    {
        try {
            return [
                'success' => true,
                'data' => $this->singapay->disbursement->checkBeneficiary($bankAccountNumber, $bankSwiftCode)
            ];
        } catch (SingaPayException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Check transfer fee
     */
    public function checkTransferFee($accountId, $amount, $bankSwiftCode)
    {
        try {
            return [
                'success' => true,
                'data' => $this->singapay->disbursement->checkFee($accountId, $amount, $bankSwiftCode)
            ];
        } catch (SingaPayException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get SDK metrics
     */
    public function getMetrics()
    {
        try {
            return $this->singapay->getMetrics();
        } catch (\Exception $e) {
            return [];
        }
    }
}
