@extends('layouts.front-end.app')

@section('title', translate('sign_in'))

@section('content')
    <div class="container py-4 py-lg-5 my-4 text-align-direction">
         <div class="login-card">
            <div class="mx-auto __max-w-360">
                <h2 class="text-center h4 mb-4 font-bold text-capitalize fs-18-mobile">{{ translate('sign_in')}}</h2>
                <form class="needs-validation mt-2" autocomplete="off" action="{{route('customer.auth.login')}}"
                        method="post" id="customer-login-form">
                    @csrf
                    <div class="form-group">
                        <label class="form-label font-semibold">
                            {{ translate('phone')}}
                        </label>
                        <input class="form-control text-align-direction" type="number" name="phone" id="si-phone"
                                value="{{old('phone')}}" placeholder="{{ translate('Enter_phone_number') }}"
                                required>
                        <div class="invalid-feedback">{{ translate('please_provide_valid_phone_number') }} .</div>
                    </div>
                    <button class="btn btn--primary btn-block btn-shadow" type="submit">{{ translate('sign_in') }}</button>
                </form>
                @if($web_config['social_login_text'])
                <div class="text-center m-3 text-black-50">
                    <small>{{ translate('or_continue_with') }}</small>
                </div>
                @endif
                <div class="d-flex justify-content-center my-3 gap-2">
                @foreach (getWebConfig(name: 'social_login') as $socialLoginService)
                    @if (isset($socialLoginService) && $socialLoginService['status'])
                        <div>
                            <a class="d-block" href="{{ route('customer.auth.service-login', $socialLoginService['login_medium']) }}">
                                <img src="{{theme_asset(path: 'public/assets/front-end/img/icons/'.$socialLoginService['login_medium'].'.png') }}" alt="">
                            </a>
                        </div>
                    @endif
                @endforeach
                </div>
                <div class="text-black-50 text-center">
                    <small>
                        {{  translate('Enjoy_New_experience') }}
                        <a class="text-primary text-underline" href="{{route('customer.auth.sign-up')}}">
                            {{ translate('sign_up') }}
                        </a>
                    </small>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
    @if(isset($recaptcha) && $recaptcha['status'] == 1)
        <script type="text/javascript">
            "use strict";
            var onloadCallback = function () {
                grecaptcha.render('recaptcha_element', {
                    'sitekey': '{{ getWebConfig(name: 'recaptcha')['site_key'] }}'
                });
            };
        </script>
        <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit"
                async defer></script>
    @endif
@endpush
