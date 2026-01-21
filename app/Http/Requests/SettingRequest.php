<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //Design
            'admin_pagination' => 'required|integer|min:1',
            'site_pagination' => 'required|integer|min:1',
            'default_editor' => 'required|alpha_dash',
            'default_alerts' => 'required|alpha_dash',
            'frontend_theme' => 'required|alpha_dash',
            'dashboard_theme' => 'required|alpha_dash',
            'frontend_statistics' => 'required|boolean',
            'fake_users' => 'nullable|integer|min:0',
            'fake_days' => 'nullable|integer|min:0',
            'fake_deposits' => 'nullable|numeric|min:0.00000001',
            'fake_withdrawals' => 'nullable|numeric|min:0.00000001',
            'custom_css_site' => 'nullable|string',
            'custom_css_dashboard' => 'nullable|string',
            'frontend_latest_transactions' => 'required|boolean',
            'frontend_latest_transactions_txid' => 'required|boolean',
            'frontend_latest_deposits' => 'nullable|integer|min:5|required_if:frontend_latest_transactions,1',
            'frontend_latest_withdrawals' => 'nullable|integer|min:5|required_if:frontend_latest_transactions,1',
            //Configs
            'rates_api' => 'required|alpha_dash',
            'rates_api_crypto_currency' => 'required|string',
            'rates_api_currency' => 'required|alpha',
            'rates_api_interval' => 'required|integer|min:1',
            'reinvest_status' => 'required|alpha_dash',
            'auto_suspend_users_interval' => 'required|integer|min:0',
            'signup_bonus' => 'required|integer|min:0',
            'referral_bonus' => 'required|integer|min:0',
            'purchase_min' => 'required|integer|min:1',
            'start_date' => 'required|date_format:Y-m-d H:i:s',
            'blockchain_url' => 'required|url',
            'hashpower_price' => 'required|numeric|min:0.00000001',
            'daily_profit' => 'required|numeric|min:0.00000001',
            'mining_counter_seconds' => 'required|numeric|min:1',
            'mining_counter_speed' => 'required|numeric|min:1',
            'hashpower_unit' => 'required|alpha_dash',
            'period' => 'required|integer|min:1',
            'calculator_periods' => 'required|string',
            'currency_name' => 'required|string',
            'currency_code' => 'required|string',
            'currency_decimals' => 'required|integer|min:0',
            'balance_decimals' => 'required|integer|min:0',
            'fiat_balance_decimals' => 'required|integer|min:0',
            'withdrawal_method' => 'required|alpha_dash',
            'withdrawal_deposit_required' => 'required|alpha_dash',
            'withdrawal_min' => 'required|numeric|min:0.00000001',
            'withdrawal_max_auto' => 'required|numeric|gte:withdrawal_min',
            'withdrawal_fee_fixed' => 'required|numeric|min:0.00000000',
            'withdrawal_fee_percent' => 'required|integer|min:0|max:100',
            'withdrawal_max_daily' => 'required|integer|min:0',
            //Gateways
            'deposit_gateway' => 'required|alpha_dash',
            'withdrawal_gateway' => 'required|alpha_dash',
            'deposit_currency_code' => 'required|string',
            'withdrawal_currency_code' => 'required|string',
            //Coinpayments
            'coinpayments_pvk' => 'nullable|required_if:deposit_gateway,coinpayments|required_if:withdrawal_gateway,coinpayments|string',
            'coinpayments_pbk' => 'nullable|required_if:deposit_gateway,coinpayments|required_if:withdrawal_gateway,coinpayments|string',
            'coinpayments_mode' => 'nullable|required_if:deposit_gateway,coinpayments|required_if:withdrawal_gateway,coinpayments|alpha_dash',
            'coinpayments_email' => 'nullable|required_if:deposit_gateway,coinpayments|required_if:withdrawal_gateway,coinpayments|alpha_dash',
            'coinpayments_fee' => 'nullable|required_if:deposit_gateway,coinpayments|required_if:withdrawal_gateway,coinpayments|integer',
            'coinpayments_mid' => 'nullable|required_if:deposit_gateway,coinpayments|required_if:withdrawal_gateway,coinpayments|string',
            'coinpayments_ipn' => 'nullable|required_if:deposit_gateway,coinpayments|required_if:withdrawal_gateway,coinpayments|string',
            //Block.io
            'blockio_mode' => 'nullable|required_if:withdrawal_gateway,blockio|alpha_dash',
            'blockio_withdrawal_fee' => 'nullable|required_if:withdrawal_gateway,blockio|alpha_dash',
            'blockio_pin' => 'nullable|required_if:withdrawal_gateway,blockio|string',
            'blockio_api' => 'nullable|required_if:withdrawal_gateway,blockio|string',
            'blockio_testapi' => 'nullable|required_if:withdrawal_gateway,blockio|string',
            //PayKassa
            'paykassa_timeout' => 'nullable|required_if:deposit_gateway,paykassa|required_if:withdrawal_gateway,paykassa|integer|min:1',
            'paykassa_confirmations' => 'nullable|required_if:deposit_gateway,paykassa|required_if:withdrawal_gateway,paykassa|integer|min:1',
            'paykassa_secret' => 'nullable|required_if:deposit_gateway,paykassa|required_if:withdrawal_gateway,paykassa|string',
            'paykassa_api_id' => 'nullable|required_if:deposit_gateway,paykassa|required_if:withdrawal_gateway,paykassa|string',
            'paykassa_api_secret' => 'nullable|required_if:deposit_gateway,paykassa|required_if:withdrawal_gateway,paykassa|string',
            'paykassa_api_currency' => 'nullable|required_if:deposit_gateway,paykassa|required_if:withdrawal_gateway,paykassa|alpha_dash',
            'paykassa_api_priority' => 'nullable|required_if:deposit_gateway,paykassa|required_if:withdrawal_gateway,paykassa|alpha_dash',
            'paykassa_fee' => 'nullable|required_if:deposit_gateway,paykassa|required_if:withdrawal_gateway,paykassa|alpha_dash',
            'paykassa_mid' => 'nullable|required_if:deposit_gateway,paykassa|required_if:withdrawal_gateway,paykassa|string',
            //CoinGate
            'coingate_mode' => 'nullable|required_if:deposit_gateway,coingate|alpha_dash',
            'coingate_auth_token' => 'nullable|required_if:deposit_gateway,coingate|string',
            'coingate_receive' => 'nullable|required_if:deposit_gateway,coingate|alpha_dash',
            //GoUrl
            'gourl_timeout' => 'nullable|required_if:deposit_gateway,gourl|integer|min:1',
            'gourl_pvk' => 'nullable|required_if:deposit_gateway,gourl|string',
            'gourl_pbk' => 'nullable|required_if:deposit_gateway,gourl|string',
            //FaucetPay
            'faucetpay_username' => 'nullable|required_if:deposit_gateway,faucetpay|string',
            'faucetpay_api_key' => 'nullable|required_if:withdrawal_gateway,faucetpay|string',
            'faucetpay_referral' => 'nullable|integer',
            //CryptAPI
            'cryptapi_wallet' => 'nullable|required_if:withdrawal_gateway,cryptapi|string',
            'cryptapi_network' => 'nullable|required_if:withdrawal_gateway,cryptapi|alpha_dash',
            'cryptapi_timeout' => 'nullable|required_if:withdrawal_gateway,cryptapi|integer|min:1',
            'cryptapi_confirmations' => 'nullable|required_if:withdrawal_gateway,cryptapi|integer|min:1',
            //SendBit
            'sendbit_wallet' => 'nullable|required_if:deposit_gateway,sendbit|string',
            'sendbit_timeout' => 'nullable|required_if:deposit_gateway,sendbit|integer|min:1',
            'sendbit_api_key' => 'nullable|required_if:deposit_gateway,sendbit|alpha_dash',
            'sendbit_api_secret' => 'nullable|required_if:deposit_gateway,sendbit|alpha_dash',
            'sendbit_api_hash' => 'nullable|required_if:deposit_gateway,sendbit|string',
            //Maintenance
            'maintenance_status' => 'required|alpha_dash',
            'maintenance_retry' => 'nullable|integer',
            'maintenance_excluded_uris' => 'nullable|string',
            'maintenance_excluded_ips' => 'nullable|string',
            'maintenance_secret' => 'nullable|alpha_dash',
            'maintenance_message' => 'nullable|string',
            //General
            'header_codes' => 'nullable|string',
            'footer_codes' => 'nullable|string',
            'force_https' => 'required|alpha',
            'multiple_accounts' => 'required|alpha',
            //SEO
            'site_name' => 'required|string',
            'meta_keywords' => 'nullable|string',
            'meta_description' => 'nullable|string',
            //Company
            'company_name' => 'string|nullable',
            'company_address' => 'string|nullable',
            'company_city' => 'string|nullable',
            'company_state' => 'string|nullable',
            'company_zip_code' => 'string|nullable',
            'company_country' => 'string|nullable',
            'company_phone' => 'string|nullable',
            'company_opening_hours' => 'string|nullable',
            //Social
            'facebook' => 'nullable|url',
            'telegram' => 'nullable|url',
            'twitter' => 'nullable|url',
            'vk' => 'nullable|url',
            'instagram' => 'nullable|url',
            'pinterest' => 'nullable|url',
            'medium' => 'nullable|url',
            'discord' => 'nullable|string',
            'github' => 'nullable|url',
            'linkedin' => 'nullable|url',
            'okru' => 'nullable|url',
            'reddit' => 'nullable|url',
            'skype' => 'nullable|url',
            'snapchat' => 'nullable|url',
            'spotify' => 'nullable|url',
            'soundcloud' => 'nullable|url',
            'steam' => 'nullable|url',
            'twitch' => 'nullable|url',
            'tumblr' => 'nullable|url',
            'vimeo' => 'nullable|url',
            'viber' => 'nullable|string',
            'weibo' => 'nullable|string',
            'whatsapp' => 'nullable|string',
            'youtube' => 'nullable|url',
            //Cookie Consent
            'cookie_consent_status' => 'required|alpha_dash',
            'cookie_consent_position' => 'nullable|required_if:cookie_consent_status,yes|alpha_dash',
            'cookie_consent_layout' => 'nullable|required_if:cookie_consent_status,yes|alpha_dash',
            'cookie_consent_message' => 'nullable|required_if:cookie_consent_status,yes|string',
            'cookie_consent_popup_background' => 'nullable|required_if:cookie_consent_status,yes|string',
            'cookie_consent_popup_text_color' => 'nullable|required_if:cookie_consent_status,yes|string',
            'cookie_consent_dismiss' => 'nullable|required_if:cookie_consent_status,yes|string',
            'cookie_consent_button_background' => 'nullable|required_if:cookie_consent_status,yes|string',
            'cookie_consent_button_border_color' => 'nullable|required_if:cookie_consent_layout,wire|string',
            'cookie_consent_button_text_color' => 'nullable|required_if:cookie_consent_status,yes|string',
            'cookie_consent_link' => 'nullable|required_if:cookie_consent_status,yes|string',
            'cookie_consent_url' => 'nullable|required_if:cookie_consent_status,yes|url',
        ];
    }
}
