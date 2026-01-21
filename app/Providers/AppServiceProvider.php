<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

namespace App\Providers;
use App\Traits\LicenseTrait;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
class AppServiceProvider extends ServiceProvider
{
   
    public function register()
    {
    }
    public function boot()
    {
        \Illuminate\Support\Facades\Schema::defaultStringLength(125);
        \Illuminate\Pagination\Paginator::useBootstrap();
        define("SLM_URL", "https://slm.smartyscripts.com/api/v1/");
        $this->copyrightCheck();
        if (install_status()) {
            $this->registerViewNamespaces();
            \Illuminate\Support\Facades\Cache::flush();
            $this->exchangeRates();
            $this->cacheStatic();
            $this->expireMiningPlans();
        }
    }
    private function exchangeRates()
    {
        $rates_file = storage_path("framework/exchange_rates.json");
        $rate = 0;
        if (!file_exists($rates_file)) {
            $content = ["next_check" => now()->addMinutes(setting("rates_api_interval", 5)), "rate" => $this->getRates()];
            file_put_contents($rates_file, json_encode($content, JSON_THROW_ON_ERROR));
        } else {
            $parsed = json_decode(file_get_contents($rates_file), false);
            if (now()->greaterThanOrEqualTo(\Carbon\Carbon::createFromTimeString($parsed->next_check))) {
                $content = ["next_check" => now()->addMinutes(setting("rates_api_interval", 5)), "rate" => $this->getRates()];
                file_put_contents($rates_file, json_encode($content, JSON_THROW_ON_ERROR));
            }
            $rate = $parsed->rate;
        }
        \Illuminate\Support\Facades\View::share("exchange_rate", $rate);
    }
    private function getRates()
    {
        $crypto_currency_name = strtolower(setting("rates_api_crypto_currency", setting("currency_name")));
        $crypto_currency_code = strtoupper(setting("rates_api_crypto_currency", setting("currency_code")));
        $currency_code = strtoupper(setting("rates_api_currency", "USD"));
        if (setting("rates_api") === "coingecko") {
            try {
                $fetch_url = file_get_contents("https://api.coingecko.com/api/v3/simple/price?ids=" . $crypto_currency_name . "&vs_currencies=" . $currency_code);
                $result = json_decode($fetch_url, true);
                $fiat_code = strtolower($currency_code);
                $rate = $result[$crypto_currency_name][$fiat_code];
            } catch (\Exception $e) {
                logger()->error($e->getMessage());
                $rate = 0;
            }
        }
        if (setting("rates_api") === "cryptonator") {
            try {
                $fetch_url = file_get_contents("https://api.cryptonator.com/api/ticker/" . $crypto_currency_code . "-" . $currency_code);
                $result = json_decode($fetch_url, true);
                $rate = $result["ticker"]["price"];
            } catch (\Exception $e) {
                logger()->error($e->getMessage());
                $rate = 0;
            }
        }
        return $rate;
    }
    private function cacheStatic()
    {
        $pages = \Illuminate\Support\Facades\Cache::rememberForever(config("smartyscripts_cache_keys.pages"), function () {
            return \App\Models\Page::published()->get();
        });
        $faqs = \Illuminate\Support\Facades\Cache::rememberForever(config("smartyscripts_cache_keys.faqs"), function () {
            return \App\Models\Faq::all();
        });
        $terms = $pages->where("type", "tos")->first();
        $privacy = $pages->where("type", "privacy")->first();
        \Illuminate\Support\Facades\View::share("static_pages", $pages);
        \Illuminate\Support\Facades\View::share("page_tos", $terms);
        \Illuminate\Support\Facades\View::share("page_privacy", $privacy);
        \Illuminate\Support\Facades\View::share("faqs", $faqs);
    }
    private function expireMiningPlans()
    {
        $affected = \App\Models\UserMiningPower::whereStatus("active")->whereDate("expire_date", "<=", now())->update(["status" => "expired"]);
        if ($affected) {
            \Illuminate\Support\Facades\Cache::flush();
        }
    }
    private function registerViewNamespaces()
    {
        \Illuminate\Support\Facades\View::addNamespace("theme", resource_path() . "/views/themes/frontend/" . setting("frontend_theme") . "/");
        \Illuminate\Support\Facades\View::addNamespace("dashboard", resource_path() . "/views/themes/dashboard/" . setting("dashboard_theme") . "/");
    }
    private function copyrightCheck()
    {
        define("SMARTY_SCRIPTS_COPYRIGHT", config("smartyscripts.script_name") . " v" . config("smartyscripts.script_version") . " by <a href=\"" . config("smartyscripts.script_creator_site") . "\" target=\"_blank\"><strong>" . config("smartyscripts.script_creator") . "</strong></a>");
        $has_error = true;
        if (config("smartyscripts.script_creator") !== "DoniaWeB") {
            $has_error = true;
        }
        if (config("smartyscripts.script_creator_site") !== "https://doniaweb.com") {
            $has_error = true;
        }
        if (strpos(file_get_contents(resource_path("views/layouts/admin/default.blade.php")), "{!! SMARTY_SCRIPTS_COPYRIGHT !!}") === false) {
            $has_error = true;
        }
        if (!$has_error) {
            exit(view("license")->with(["message" => config("smartyscripts.license.errors.copyright")]));
        }
    
        }
    }


?>