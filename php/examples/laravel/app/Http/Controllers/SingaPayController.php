<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SingaPayService;

class SingaPayController extends Controller
{
    protected $singapayService;

    public function __construct(SingaPayService $singapayService)
    {
        $this->singapayService = $singapayService;
    }

    /**
     * Show dashboard
     */
    public function dashboard()
    {
        $connectionTest = $this->singapayService->testConnection();
        $metrics = $this->singapayService->getMetrics();

        return view('singapay.dashboard', compact('connectionTest', 'metrics'));
    }

    /**
     * Show account management page
     */
    public function accounts()
    {
        $accounts = $this->singapayService->listAccounts();

        return view('singapay.accounts', compact('accounts'));
    }

    /**
     * Create new account
     */
    public function createAccount(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string'
        ]);

        $result = $this->singapayService->createAccount($request->all());

        if ($result['success']) {
            return redirect()->route('singapay.accounts')
                ->with('success', 'Account created successfully!');
        }

        return back()->with('error', $result['message'])
            ->withErrors($result['errors'] ?? []);
    }

    /**
     * Show payment link creation form
     */
    public function showPaymentLinkForm()
    {
        return view('singapay.payment-link');
    }

    /**
     * Create payment link
     */
    public function createPaymentLink(Request $request)
    {
        $request->validate([
            'account_id' => 'required|string',
            'reff_no' => 'required|string',
            'title' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:1000',
        ]);

        $data = [
            'reff_no' => $request->reff_no,
            'title' => $request->title,
            'total_amount' => $request->total_amount,
            'required_customer_detail' => $request->has('required_customer_detail'),
            'max_usage' => $request->max_usage ?? 1,
            'expired_at' => (time() + ($request->expiry_hours ?? 24) * 3600) * 1000,
            'items' => [
                [
                    'name' => $request->title,
                    'quantity' => 1,
                    'unit_price' => $request->total_amount
                ]
            ],
            'whitelisted_payment_method' => $request->payment_methods ?? ['VA_BRI', 'QRIS']
        ];

        $result = $this->singapayService->createPaymentLink($request->account_id, $data);

        if ($result['success']) {
            return view('singapay.payment-link-result')
                ->with('paymentLink', $result['data']);
        }

        return back()->with('error', $result['message'])
            ->withErrors($result['errors'] ?? []);
    }

    /**
     * Show virtual account creation form
     */
    public function showVirtualAccountForm()
    {
        return view('singapay.virtual-account');
    }

    /**
     * Create virtual account
     */
    public function createVirtualAccount(Request $request)
    {
        $request->validate([
            'account_id' => 'required|string',
            'bank_code' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'kind' => 'required|in:temporary,permanent',
            'name' => 'required|string'
        ]);

        $data = $request->only(['bank_code', 'amount', 'kind', 'name']);

        if ($request->kind === 'temporary' && $request->expired_at) {
            $data['expired_at'] = strtotime($request->expired_at) * 1000;
        }

        $result = $this->singapayService->createVirtualAccount($request->account_id, $data);

        if ($result['success']) {
            return view('singapay.virtual-account-result')
                ->with('virtualAccount', $result['data']);
        }

        return back()->with('error', $result['message'])
            ->withErrors($result['errors'] ?? []);
    }

    /**
     * Show disbursement tools
     */
    public function disbursementTools()
    {
        return view('singapay.disbursement');
    }

    /**
     * Check beneficiary
     */
    public function checkBeneficiary(Request $request)
    {
        $request->validate([
            'bank_account_number' => 'required|string',
            'bank_swift_code' => 'required|string'
        ]);

        $result = $this->singapayService->checkBeneficiary(
            $request->bank_account_number,
            $request->bank_swift_code
        );

        return response()->json($result);
    }

    /**
     * Check transfer fee
     */
    public function checkTransferFee(Request $request)
    {
        $request->validate([
            'account_id' => 'required|string',
            'amount' => 'required|numeric|min:1000',
            'bank_swift_code' => 'required|string'
        ]);

        $result = $this->singapayService->checkTransferFee(
            $request->account_id,
            $request->amount,
            $request->bank_swift_code
        );

        return response()->json($result);
    }
}
