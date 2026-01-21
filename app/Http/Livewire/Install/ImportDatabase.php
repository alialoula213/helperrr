<?php

namespace App\Http\Livewire\Install;

use Illuminate\Support\Facades\Artisan;
use Livewire\Component;
use Symfony\Component\Console\Output\BufferedOutput;

class ImportDatabase extends Component
{
    public $page_title;
    public $response;
    public $status;

    public function render()
    {
        return view('install.livewire.import-database');
    }

    public function startMigration()
    {
        $outputLog = new BufferedOutput();

        $this->migrate($outputLog);
    }

    private function migrate($outputLog)
    {
        try {
           Artisan::call('migrate:fresh', ["--force" => true], $outputLog);
        } catch (\Exception $e) {
            return $this->response($e->getMessage(), 'error', $outputLog);
        }

        return $this->seed($outputLog);
    }

    private function seed($outputLog)
    {
        try {
            Artisan::call('db:seed', ['--force' => true], $outputLog);
        } catch (\Exception $e) {
            return $this->response($e->getMessage(), 'error', $outputLog);
        }

        $this->status = 'success';
        return $this->response('Application has been successfully installed', 'success', $outputLog);
    }

    /**
     * @param $message
     * @param string $status
     * @param $outputLog
     * @return array
     */
    private function response($message, $status, $outputLog)
    {
        $status_code = $status ? $status : 'danger';
        return $this->response = [
            'status' => $status_code,
            'message' => $message,
            'dbOutputLog' => $outputLog->fetch()
        ];
    }
}
