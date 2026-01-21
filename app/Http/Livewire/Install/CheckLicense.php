<?php


namespace App\Http\Livewire\Install;
use Livewire\Component;
class CheckLicense extends Component
{
    public $license = NULL;
    public function mount()
    {
        $this->license = session("license") ?? config("smartyscripts.license_key");
    }
    public function render()
    {
        return view("install.livewire.check-license");
    }
    public function rules()
    {
        return ["license" => "required|string"];
    }
    public function validateLicense()
    {
        $this->validate();
        $req = \Illuminate\Support\Facades\Http::retry(5, 1000)->post(SLM_URL . "activate", ["key" => $this->license, "domain" => request()->getSchemeAndHttpHost()]);
        if ($req->failed()) {
            session()->flash("error", "There was an error trying to validate your license. If the error persists, contact smartyscripts.com for help.");
            return redirect()->route("install.step3");
        }
        $result = json_decode($req->body(), false, 512, JSON_THROW_ON_ERROR);
        if (!$result->status === "error") {
            session()->flash("error", $result->message);
            return redirect()->route("install.step3");
        }
        session()->flash("success", "License successfully validated.");
        return redirect()->route("install.step4");
    }
}

?>