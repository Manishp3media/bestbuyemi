<?php

namespace App\Http\Controllers\Payment_Methods;

use App\Models\PaymentRequest;
use App\Models\User;
use App\Traits\Processor;
use Carbon\Carbon;
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
use Illuminate\Support\Facades\Validator;
use juspaypay\Api\Api;

class JusPayController extends Controller
{
    use Processor;

    private PaymentRequest $payment;
    private $user;

    public function __construct(PaymentRequest $payment, User $user)
    {
        $config = $this->payment_config('jus_pay', 'payment_config');
        $juspay = false;
        if (!is_null($config) && $config->mode == 'live') {
            $juspay = json_decode($config->live_values);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $juspay = json_decode($config->test_values);
        }

        if ($juspay) {
            $config = array(
                'api_key' => $juspay->api_key,
                'api_secret' => $juspay->api_secret
            );
            Config::set('juspay_config', $config);
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

        if (!isset($data)) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }
        $payer = json_decode($data['payer_information']);
        $user = User::where('email', $payer->email)->first();
        $customer_id = 'CUST-'.$user->id;
        $order_id = 'ORD-'.Carbon::now()->format('Y').rand(1000,9999);

        $api_key = "52B5EAE481F44D18C09086AAF57D5C";
        $authorization = "Basic " . base64_encode($api_key . ":");
        $response = Http::withHeaders([
            'Authorization' => $authorization,
            'x-merchantid' => 'fastemi',
            'Content-Type' => 'application/json',
        ])->post('https://api.juspay.in/session', [
            "order_id" => $order_id,
            "amount" => "10.0",
            "customer_id" => $customer_id,
            "customer_email" => $user->email,
            "customer_phone" => $user->phone,
            "payment_page_client_id" => "fastemi",
            "action" => "paymentPage",
            "return_url" => "https://shop.merchant.com",
            "description" => "Complete your payment",
            "first_name" => $user->f_name,
            "last_name" => $user->l_name
        ]);

        if ($response->successful()) {
            $json_data = json_decode($response->body());
            return redirect($json_data->payment_links->web);
        } else {
           dd($response->reason());
        }
    }

    public function payment(Request $request): JsonResponse|Redirector|RedirectResponse|Application
    {
        $input = $request->all();
        // dd($input);
        $api = new Api(config('juspay_config.api_key'), config('juspay_config.api_secret'));
        $payment = $api->payment->fetch($input['juspaypay_payment_id']);

        if (count($input) && !empty($input['juspaypay_payment_id'])) {
            $response = $api->payment->fetch($input['juspaypay_payment_id'])->capture(array('amount' => $payment['amount']));

            $this->payment::where(['id' => $request['payment_id']])->update([
                'payment_method' => 'juspay_pay',
                'is_paid' => 1,
                'transaction_id' => $input['juspaypay_payment_id'],
            ]);
            $data = $this->payment::where(['id' => $request['payment_id']])->first();
            if (isset($data) && function_exists($data->success_hook)) {
                call_user_func($data->success_hook, $data);
            }
            return $this->payment_response($data, 'success');
        }
        $payment_data = $this->payment::where(['id' => $request['payment_id']])->first();
        if (isset($payment_data) && function_exists($payment_data->failure_hook)) {
            call_user_func($payment_data->failure_hook, $payment_data);
        }
        return $this->payment_response($payment_data, 'fail');
    }
}
