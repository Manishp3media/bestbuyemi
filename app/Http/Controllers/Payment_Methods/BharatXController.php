<?php

namespace App\Http\Controllers\Payment_Methods;

use App\Models\PaymentRequest;
use App\Models\User;
use App\Traits\Processor;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Razorpay\Api\Api;
use Tzsk\Payu\Concerns\Attributes;
use Tzsk\Payu\Concerns\Customer;
use Tzsk\Payu\Concerns\Transaction;
use Tzsk\Payu\Facades\Payu;

class BharatXController extends Controller
{
    use Processor;

    private PaymentRequest $payment;
    private $user;

    public function __construct(PaymentRequest $payment, User $user)
    {
        $config = $this->payment_config('bharatx', 'payment_config');
        $razor = false;
        if (!is_null($config) && $config->mode == 'live') {
            $razor = json_decode($config->live_values);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $razor = json_decode($config->test_values);
        }

        if ($razor) {
            $config = array(
                'api_key' => $razor->api_key,
                'api_secret' => $razor->api_secret
            );
            Config::set('bharatx_config', $config);
        }

        $this->payment = $payment;
        $this->user = $user;
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, $this->error_processor($validator)), 400);
        }

        $data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        Session::put('payment_id', $request['payment_id']);

        if (!isset($data)) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }
        $payer = json_decode($data['payer_information']);

        if ($data['additional_data'] != null) {
            $business = json_decode($data['additional_data']);
            $business_name = $business->business_name ?? "my_business";
            $business_logo = $business->business_logo ?? url('/');
        } else {
            $business_name = "my_business";
            $business_logo = url('/');
        }
        $cust_user = User::find($data->payer_id);

        // Define your variables
        $privateApiKey = '8FOIS24SGUP1S2S3MYFOM2EHS839RYKI';
        $partnerId = 'fast_emi';
        $baseUrl = 'https://web.bharatx.tech';
        $endpoint = '/api/transaction';
        $url = $baseUrl . $endpoint;

        // Prepare the request body
        $body = [
            "id" => "txnId1".rand(1000,9999),
            "amount" => 1000, // in paise
            "user" => [
                "name" => $cust_user->name,
                "phoneNumber" => $cust_user->phone,
                "email" => $cust_user->email
            ],
            "redirect" => [
                "url" => route('bharatx.payment'),
                "logoOverride" => "https://your-logo-url.com/logo.png",
                "colorOverride" => "#123456"
            ]
        ];

        // Convert the body to JSON
        $jsonBody = json_encode($body);

        // Generate the X-Signature
        $signatureData = $jsonBody . $endpoint . $privateApiKey;
        $xSignature = base64_encode(hash('sha256', $signatureData, true));

        try {
            // Make the HTTP POST request
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Signature' => $xSignature,
                'X-Partner-Id' => $partnerId,
            ])->post($url, $body);

            // Handle the response
            if ($response->successful()) {
                $response_body = $response->json();
                // dd($response_body);
                return redirect($response_body['redirectUrl']);
            } else {
                return response()->json($response->json(), $response->status());
            }
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error creating transaction: ' . $e->getMessage());

            // Return an error response
            return response()->json(['error' => 'An error occurred while creating the transaction.'], 500);
        }

    }


    public function payment(Request $request): JsonResponse|Redirector|RedirectResponse|Application
    {
        $input = $request->all();

        $payment_id = Session::get('payment_id');

        if (count($input) && !empty($input['txnId']) && isset($payment_id) && $input['status'] == 'SUCCESS' ) {

            $this->payment::where(['id' => $payment_id])->update([
                'payment_method' => 'bharatx',
                'is_paid' => 1,
                'transaction_id' => $input['txnId'],
            ]);
            $data = $this->payment::where(['id' => $payment_id])->first();
            if (isset($data) && function_exists($data->success_hook)) {
                call_user_func($data->success_hook, $data);
            }
            return $this->payment_response($data, 'success');
        }
        $payment_data = $this->payment::where(['id' => $payment_id])->first();
        if (isset($payment_data) && function_exists($payment_data->failure_hook)) {
            call_user_func($payment_data->failure_hook, $payment_data);
        }
        return $this->payment_response($payment_data, 'fail');
    }
}
