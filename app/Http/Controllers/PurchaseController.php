<?php

namespace App\Http\Controllers;

use App\Models\Deposit;

class PurchaseController extends Controller
{
    public function index($invoice)
    {
        $deposit = Deposit::whereInvoice($invoice)->whereUserId(auth()->user()->id)->whereStatus('pending')->firstOrFail();

        $now = \Carbon\Carbon::now();
        if(isset($deposit->invoice_expire_date) && $now->greaterThanOrEqualTo($deposit->invoice_expire_date)){
            $deposit->update(['status' => 'canceled']);
            return redirect()->route('my-deposits')->withError('The payment term has expired. Make a new deposit.');
        }

        return view('dashboard::payments.invoice')->with([ 'invoice' => $deposit, 'invoice_params' => json_decode($deposit->response)]);
    }
}
