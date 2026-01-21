<?php

namespace App\Http\Livewire\Install;

use Livewire\Component;
use Swift_Mailer;
use Swift_SmtpTransport;

class Environment extends Component
{
    public $page_title;
    public $app_name;
    public $app_url;
    public $admin_prefix;
    public $db_driver;
    public $db_host;
    public $db_port;
    public $db_name;
    public $db_user;
    public $db_pass;
    public $mail_driver;
    public $mail_host;
    public $mail_port;
    public $mail_user;
    public $mail_pass;
    public $mail_encryption;
    public $mail_sender;
    public $mail_recipient;
    public $app_environment;
    public $app_debug;
    public $app_debugbar;
    public $app_log;

    public function mount()
    {
        $this->app_name = session('environment.app_name');
        $this->app_url = session('environment.app_url') ?? request()->getSchemeAndHttpHost();
        $this->admin_prefix = session('environment.admin_prefix');
        $this->db_driver = session('environment.db_driver') ?? 'mysql';
        $this->db_host = session('environment.db_host') ?? 'localhost';
        $this->db_port = session('environment.db_port') ?? '3306';
        $this->db_name = session('environment.db_name');
        $this->db_user = session('environment.db_user');
        $this->db_pass = session('environment.db_pass');
        $this->mail_driver = session('environment.mail_driver') ?? 'smtp';
        $this->mail_host = session('environment.mail_host');
        $this->mail_port = session('environment.mail_port') ?? '465';
        $this->mail_user = session('environment.mail_user');
        $this->mail_pass = session('environment.mail_pass');
        $this->mail_encryption = session('environment.mail_encryption') ?? 'ssl';
        $this->mail_sender = session('environment.mail_sender');
        $this->app_environment = session('environment.app_environment') ?? 'production';
        $this->app_debug = session('environment.app_debug') ?? 'false';
        $this->app_debugbar = session('environment.app_debugbar') ?? 'false';
        $this->app_log = session('environment.app_log') ?? 'debug';
    }

    public function render()
    {
        return view('install.livewire.environment');
    }

    public function install()
    {
        //Validate inputs
        $validated_data = $this->validate([
            'app_name' => 'required|string',
            'app_url' => 'required|url',
            'admin_prefix' => 'required|alpha_dash',
            'db_driver' => 'required|alpha',
            'db_host' => 'required',
            'db_port' => 'required|integer',
            'db_name' => 'required|string',
            'db_user' => 'required|string',
            'db_pass' => 'required|string',
            'mail_driver' => 'required|alpha',
            'mail_host' => 'required',
            'mail_port' => 'required|integer',
            'mail_user' => 'required|string',
            'mail_pass' => 'required|string',
            'mail_encryption' => 'required|alpha',
            'mail_sender' => 'required|email',
            'app_environment' => 'required|alpha',
            'app_debug' => 'required',
            'app_debugbar' => 'required',
            'app_log' => 'required|alpha',
        ]);
        //Save settings on session
        session()->put('environment', $validated_data);
        //Try to create .env
        if (!$this->createEnvFile()) {
            session()->flash('error', 'Unable to save the .env file. Please create it manually!');
            return redirect()->route('install.step4');
        }
        //session()->put($old_session);
        //Set admin prefix
        if (!$this->setAdminPrefix()) {
            session()->flash('error', 'Unable to save admin prefix. Please update it manually!');
            return redirect()->route('install.step4');
        }

        //Redirect
        return redirect()->route('install.step5');
    }

    public function testConnection()
    {
        //Validate inputs
        $this->validate([
            'db_driver' => 'required|alpha',
            'db_host' => 'required',
            'db_port' => 'required|integer',
            'db_name' => 'required|string',
            'db_user' => 'required|string',
            'db_pass' => 'required|string',
        ]);
        $host = $this->db_host;
        $database = $this->db_name;
        $user = $this->db_user;
        $password = $this->db_pass;
        try {
            $conn = new \PDO("mysql:host=${host};dbname=${database}", $user, $password);
            // set the PDO error mode to exception
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            session()->flash('connected', 'Connection successfully!');
        } catch (\PDOException $e) {
            session()->flash('connection_error', $e->getMessage());
        }
    }

    public function testMail()
    {
        //Validate inputs
        $this->validate([
            'app_name' => 'required|string',
            'mail_driver' => 'required|alpha',
            'mail_host' => 'required',
            'mail_port' => 'required|integer',
            'mail_user' => 'required|string',
            'mail_pass' => 'required|string',
            'mail_encryption' => 'required|alpha',
            'mail_sender' => 'required|email',
            'mail_recipient' => 'required|email',
        ]);
        $smtp_encryption = $this->mail_encryption === 'null' ? null : $this->mail_encryption;
        \Config::set([
            'mail.default' => $this->mail_driver,
            'mail.mailers.smtp.host' => $this->mail_host,
            'mail.mailers.smtp.port' => $this->mail_port,
            'mail.mailers.smtp.username' => $this->mail_user,
            'mail.mailers.smtp.password' => $this->mail_pass,
            'mail.mailers.smtp.encryption' => $smtp_encryption,
            'mail.from.address' => $this->mail_sender,
        ]);
        (new \Illuminate\Mail\MailServiceProvider(app()))->register();
        $transport = new Swift_SmtpTransport($this->mail_host, $this->mail_port, $smtp_encryption);
        $transport->setUsername($this->mail_user);
        $transport->setPassword($this->mail_pass);
        $mailer = app(\Illuminate\Mail\Mailer::class);
        $mailer->setSwiftMailer(new Swift_Mailer($transport));
        $mailer->alwaysFrom($this->mail_sender, $this->app_name);
        $mailer->alwaysReplyTo($this->mail_sender, $this->app_name);
        try {
            $mailer->raw('If you received this email, your SMTP configuration is correct.', function($message){
                $message->subject($this->app_name . ' - Email Configuration Test')->to($this->mail_recipient);
            });
            session()->flash('mail_sent', 'Mail sent successfully!');
        }catch (\Swift_TransportException $e){
            session()->flash('mail_error', $e->getMessage());
        }
    }

    private function createEnvFile()
    {
        $config = file_get_contents(base_path('.env.example'));
        //Replace strings
        $new_content = str_replace([
            'SM_SCRIPT_LICENSE=',
            'SM_SCRIPT_LICENSE_EXPIRE=',
            'APPNAME',
            'APP_ENV=local',
            'APP_DEBUG=true',
            'DEBUGBARSTATUS',
            'APPURL',
            'LOGLEVEL',
            'DB_CONNECTION=mysql',
            'MYSQLHOST',
            'MYSQLPORT',
            'MYSQLDATABASE',
            'MYSQLUSER',
            'MYSQLPASS',
            'MAILDRIVER',
            'MAILHOST',
            'MAILPORT',
            'MAILUSER',
            'MAILPASS',
            'MAILENCRYPT',
            'MAILFROM',
        ], [
            'SM_SCRIPT_LICENSE=' . session('license'),
            'SM_SCRIPT_LICENSE_EXPIRE=' . session('licenseExpire'),
            session('environment.app_name'),
            'APP_ENV=' . session('environment.app_environment'),
            'APP_DEBUG=' . session('environment.app_debug', false),
            'DEBUGBAR_ENABLED=' . session('environment.app_debugbar', false),
            session('environment.app_url'),
            session('environment.app_log'),
            'DB_CONNECTION=' . session('environment.db_driver'),
            session('environment.db_host'),
            session('environment.db_port'),
            session('environment.db_name'),
            session('environment.db_user'),
            session('environment.db_pass'),
            session('environment.mail_driver'),
            session('environment.mail_host'),
            session('environment.mail_port'),
            session('environment.mail_user'),
            session('environment.mail_pass'),
            session('environment.mail_encryption'),
            session('environment.mail_sender'),
        ], $config);
        if(!$new_content){
            return false;
        }
        //Update file
        $update = file_put_contents(base_path('.env'), $new_content);
        if ($update) {
            return true;
        }
        return false;
    }

    private function setAdminPrefix()
    {
        $config = file_get_contents(config_path('cyber_miner.php'));
        //Replace strings
        $new_content = str_replace('admin', $this->admin_prefix, $config);
        //Update file
        $update = file_put_contents(config_path('cyber_miner.php'), $new_content);
        if ($update) {
            return true;
        }
        return false;
    }
}
