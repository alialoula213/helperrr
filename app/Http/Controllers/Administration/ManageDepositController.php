<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Http\Requests\DepositRequest;
use App\Models\UserMiningPower;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ManageDepositController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:list-deposits|edit-deposits|delete-deposits', ['only' => ['index']]);
        $this->middleware('permission:show-deposits', ['only' => ['edit']]);
        $this->middleware('permission:edit-deposits', ['only' => ['update']]);
        $this->middleware('permission:delete-deposits', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Deposit::select('*');
            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('user_id', '<a href="{{ route(\'admin.users.edit\', $user_id) }}">{{ $user_id }}</a>')
                ->editColumn('amount', '{{ crypto_currency($amount) }}')
                ->editColumn('paid_amount', '{{ crypto_currency($paid_amount) }}')
                ->editColumn('created_at', function($row){ return $row->created_at; })
                ->editColumn('status', 'admin.partials.datatables.deposits-status')
                ->addColumn('actions', 'admin.deposits.datatables.actions')
                ->rawColumns(['id', 'status', 'user_id', 'actions'])
                ->orderColumn('id', '-id $1')
                ->make(true);
        }

        return view('admin.deposits.index')->with([
            'page_title' => 'Manage Deposits',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Deposit $deposit
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit(Deposit $deposit)
    {
        return view('admin.deposits.form')->with([
            'page_title' => 'Edit Deposit',
            'form_params' => ['button_name' => 'Update', 'action' => route('admin.deposits.update', $deposit->id), 'method' => 'put'],
            'item' => $deposit,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\DepositRequest $request
     * @param \App\Models\Deposit $deposit
     * @return \Illuminate\Http\Response
     */
    public function update(DepositRequest $request, Deposit $deposit)
    {
        $deposit->update($request->all());

        $success_message = 'Deposit updated successfully!';

        if($request->filled('create_user_mining_plan')){
            //Create expiration date
            $expire_date = \Carbon\Carbon::now()->addDays(setting('period'))->toDateTimeString();
            //Create user mining
            UserMiningPower::create([
                'user_id' => $deposit->user_id,
                'power' => $deposit->power,
                'expire_date' => $expire_date
            ]);
            //Create user log
            newUserLog($deposit->user_id, 'deposit', [
                'power' => $deposit->power,
                'amount' => $deposit->amount,
                'expire_date' => $expire_date,
            ]);
            $success_message .= ' User #'.$deposit->user_id.' received '.$deposit->power.' '.setting('hashpower_unit').'/s of mining power.';
            //Flush user mining power cache
            \Cache::forget('mining_power_user_'.$deposit->user_id);
        }

        return redirect()->route('admin.deposits.index')->withSuccess($success_message);
    }

    /**
     * Remove the specified resource from storage.
     * @param Deposit $deposit
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(Deposit $deposit)
    {
        $deposit->delete();

        return redirect()->route('admin.deposits.index')->withSuccess('Deposit deleted successfully!');
    }
}
