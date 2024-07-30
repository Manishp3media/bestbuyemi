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
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Razorpay\Api\Api;
use Tzsk\Payu\Concerns\Attributes;
use Tzsk\Payu\Concerns\Customer;
use Tzsk\Payu\Concerns\Transaction;
use Tzsk\Payu\Facades\Payu;

class PayUController extends Controller
{
    use Processor;

    private PaymentRequest $payment;
    private $user;

    public function __construct(PaymentRequest $payment, User $user)
    {
        $config = $this->payment_config('payu', 'payment_config');
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
            Config::set('payu_config', $config);
        }

        $this->payment = $payment;
        $this->user = $user;
    }

    public function index(Request $request): View|Factory|JsonResponse|Application
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

        $customer = Customer::make()
            ->firstName($cust_user->f_name)
            ->email($cust_user->email);
        $transaction = Transaction::make()
            ->charge(round($data->payment_amount, 2))
            ->for('Product')
            ->to($customer);
        $params = [
            'txnid' => $transaction->transactionId,
            'amount' => $data->payment_amount,
            'productinfo' => 'Product',
            'firstname' => $cust_user->f_name,
            'lastname' => $cust_user->l_name,
            'zipcode' => $cust_user->zip,
            'email' => $cust_user->email,
            'phone' => $cust_user->phone,
            'address1' => $cust_user->street_address,
            'city' => $cust_user->city,
            'country' => $cust_user->country,
            'udf1' => '', // User-defined field 1
            'udf2' => '', // User-defined field 2
            'udf3' => '', // User-defined field 3
            'udf4' => '', // User-defined field 4
            'udf5' => '', // User-defined field 5
        ];

        $key = 'TCFfmU';
        $salt = '4ZWfDhLzg7HxMzhKgsrmktbtu9s9Rxce';
        $url = 'https://test.payu.in/_payment';
        $success_url = route('payu.payment');
        $failure_url = route('payu.payment');
        $hash_string = $key . '|' . $params['txnid'] . '|' . $params['amount'] . '|' . $params['productinfo'] . '|' . $params['firstname'] . '|' . $params['email'] . '|' . $params['udf1'] . '|' . $params['udf2'] . '|' . $params['udf3'] . '|' . $params['udf4'] . '|' . $params['udf5'] . '||||||' . $salt;
        $hash = hash('sha512', $hash_string);

        return view('payment.payu', compact('data', 'payer', 'business_logo', 'business_name', 'url', 'key', 'success_url', 'failure_url', 'params', 'hash'));
    }


    public function payment(Request $request): JsonResponse|Redirector|RedirectResponse|Application
    {
        $input = $request->all();
        $payment_id = Session::get('payment_id');

        if (count($input) && !empty($input['mihpayid']) && isset($payment_id) && $input['status'] == 'success' ) {

            $this->payment::where(['id' => $payment_id])->update([
                'payment_method' => 'payu',
                'is_paid' => 1,
                'transaction_id' => $input['mihpayid'],
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
