<?php
/*
 * @ https://doniaweb.com
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

namespace App\Http\Controllers;
use Illuminate\Http\Request;
class InstallController extends Controller
{
    public function index()
    {
        if (config("app.key") === "base64:fs6/1NRzaWenKeo/hjGLzjDZkZyXIbiRDUitWWCfHuA=") {
            try {
                \Illuminate\Support\Facades\Artisan::call("key:generate", ["--force" => true]);
                return redirect()->refresh();
            } catch (\Exception $e) {
                session()->flash("error", "Unable to generate Encryption Key, Please generate it manually!");
            }
        }
        if (!session()->exists("current_step")) {
            session()->put("current_step", 0);
        }
        return view("install.index")->with(["page_title" => "Welcome"]);
    }
    public function step1()
    {
        if (session("current_step") === 0) {
            session()->increment("current_step");
        } 
        $data = ["hasErrors" => false, "php" => ["version" => "7.4", "extensions" => ["openssl", "pdo", "mbstring", "tokenizer", "json", "curl", "ctype", "xml", "bcmath", "gmp", "imagick", "ionCube Loader"]], "apache" => ["extensions" => ["mod_rewrite"]]];
        $data["supported"] = 0 <= version_compare(PHP_VERSION, $data["php"]["version"]);
        return view("install.step1", $data)->with(["page_title" => "Script Requirements"]);
    }
    public function step2()
    {
        if (session("current_step") === 1) {
            session()->increment("current_step");
        }
        $data = ["hasErrors" => false, "folders" => ["storage/framework/" => 775, "storage/logs/" => 775, "bootstrap/cache/" => 775]];
        return view("install.step2", $data)->with(["page_title" => "Folder Permissions"]);
    }
    public function step3()
    {
        if (session("current_step") === 2) {
            session()->increment("current_step");
        }
        return view("install.step3")->with(["page_title" => "License Check"]);
    }
    public function step4()
    {
        if (session("current_step") === 3) {
            session()->increment("current_step");
        }
        return view("install.step4")->with(["page_title" => "Configure Environment"]);
    }
    public function step5()
    {
        if (!config("smartyscripts.license_key")) {
            return redirect()->route("install.step4");
        }
        if (!session()->exists("current_step") || session("current_step") === 4) {
            session()->put("current_step", 5);
        }
        if (!session()->exists("environment")) {
            $this->recreateEnvironmentSessionData();
        }
        return view("install.step5")->with(["page_title" => "Import Database"]);
    }
    public function step6()
    {
        if (!config("smartyscripts.license_key")) {
            return redirect()->route("install.step4");
        }
        if (session("current_step") === 5) {
            session()->increment("current_step");
        }
        return view("install.step6")->with(["page_title" => "Installation Complete"]);
    }
    public function finishInstallation(Request $request)
    {
        file_put_contents(storage_path("installed"), time());
        return redirect()->route("index");
    }
    private function recreateEnvironmentSessionData()
    {
        $environment = ["app_name" => config("app.name"), "app_url" => config("app.url"), "admin_prefix" => config("pro_doubler.backend_prefix"), "db_driver" => config("database.default"), "db_host" => config("database.connections." . config("database.default") . ".host"), "db_port" => config("database.connections." . config("database.default") . ".port"), "db_name" => config("database.connections." . config("database.default") . ".database"), "db_user" => config("database.connections." . config("database.default") . ".username"), "db_pass" => config("database.connections." . config("database.default") . ".password"), "mail_driver" => config("mail.default"), "mail_host" => config("mail.mailers.smtp.host"), "mail_port" => config("mail.mailers.smtp.port"), "mail_user" => config("mail.mailers.smtp.username"), "mail_pass" => config("mail.mailers.smtp.password"), "mail_encryption" => config("mail.mailers.smtp.encryption"), "mail_sender" => config("mail.from.address"), "app_environment" => config("app.env"), "app_debug" => config("app.debug"), "app_debugbar" => config("debugbar.enabled"), "app_log" => config("logging.channels.daily.level")];
        session()->put("environment", $environment);
    }
    public function licenseApi(Request $request)
    {
        if (!$request->has("action")) {
            return response()->json(["message" => "Error: No command sent!"], 400);
        }
        if ($request->input("action") === "revalidate") {
            try {
                unlink(storage_path("framework/.lic"));
                return response()->json(["message" => "License file deleted successfully!"], 200);
            } catch (\Exception $e) {
                return response()->json(["message" => "License file not found!"], 404);
            }
        }
    }
}

?>