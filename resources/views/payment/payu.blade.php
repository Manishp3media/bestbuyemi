@extends('payment.layouts.master')

@section('content')
    <div>
        <h1 class="text-center">Please do not refresh this page...</h1>
    </div>

    <form action="{{ $url }}" id="payment_form_submit" method="post">
        @csrf
        <input type="hidden" name="surl" value="{{ $success_url }}" />
        <input type="hidden" name="furl" value="{{ $failure_url }}" />
        <input type="hidden" name="key" value="{{ $key }}" />
        <input type="hidden" name="txnid" value="{{ $params['txnid'] }}" />
        <input type="hidden" name="amount" value="{{ $params['amount'] }}" />
        <input type="hidden" name="productinfo" value="{{ $params['productinfo'] }}" />
        <input type="hidden" name="firstname" value="{{ $params['firstname'] }}" />
        <input type="hidden" name="lastname" value="{{ $params['lastname'] }}" />
        <input type="hidden" name="zipcode" value="{{ $params['zipcode'] }}" />
        <input type="hidden" name="email" value="{{ $params['email'] }}" />
        <input type="hidden" name="phone" value="{{ $params['phone'] }}" />
        <input type="hidden" name="address1" value="{{ $params['address1'] }}" />
        <input type="hidden" name="city" value="{{ $params['city'] }}" />
        <input type="hidden" name="country" value="{{ $params['country'] }}" />
        <input type="hidden" name="hash" value="{{ $hash }}" />
    </form>

    <script type="text/javascript">
        document.getElementById("payment_form_submit").submit();
    </script>
@endsection
