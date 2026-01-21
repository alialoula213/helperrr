<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Http\Requests\WithdrawalRequest;
use App\Traits\WithdrawalTrait;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ManageWithdrawalController extends Controller
{
    use WithdrawalTrait;

    function __construct()
    {
        $this->middleware('permission:list-withdrawals|edit-withdrawals|delete-withdrawals', ['only' => ['index', 'pending']]);
        $this->middleware('permission:show-withdrawals', ['only' => ['edit']]);
        $this->middleware('permission:edit-withdrawals', ['only' => ['update']]);
        $this->middleware('permission:pay-withdrawal-requests', ['only' => ['pay']]);
        $this->middleware('permission:delete-withdrawals', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     * @throws \Exception
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Withdrawal::where('status', '!=', 'pending')->select('*');
            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('user_id', '<a href="{{ route(\'admin.users.edit\', $user_id) }}">{{ $user_id }}</a>')
                ->editColumn('tx_id', function($row){
                    if($row->tx_id){
                        return '<a href="'.setting('blockchain_url').$row->tx_id .'" target="_blank"><i class="fa fa-external-link-alt"></i></a>';
                    }
                })
                ->editColumn('amount', '{{ crypto_currency($amount) }}')
                ->editColumn('paid_amount', '{{ crypto_currency($paid_amount) }}')
                ->editColumn('created_at', function($row){ return $row->created_at; })
                ->editColumn('status', 'admin.partials.datatables.withdrawals-status')
                ->addColumn('actions', 'admin.withdrawals.datatables.actions')
                ->rawColumns(['id', 'status', 'user_id', 'tx_id', 'actions'])
                ->orderColumn('id', '-id $1')
                ->make(true);
        }

        return view('admin.withdrawals.index')->with([
            'page_title' => 'Manage Withdrawals',
        ]);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     * @throws \Exception
     */
    public function pending(Request $request)
    {
        if ($request->ajax()) {
            $data = Withdrawal::where('status', 'pending')->select('*');
            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('user_id', '<a href="{{ route(\'admin.users.edit\', $user_id) }}">{{ $user_id }}</a>')
                ->editColumn('tx_id', function($row){
                    if($row->tx_id){
                        return '<a href="'.setting('blockchain_url').$row->tx_id .'" target="_blank"><i class="fa fa-external-link-alt"></i></a>';
                    }
                })
                ->editColumn('amount', '{{ crypto_currency($amount) }}')
                ->editColumn('paid_amount', '{{ crypto_currency($paid_amount) }}')
                ->editColumn('created_at', function($row){ return $row->created_at; })
                ->editColumn('status', 'admin.partials.datatables.withdrawals-status')
                ->addColumn('actions', 'admin.withdrawals.datatables.actions')
                ->rawColumns(['id', 'status', 'user_id', 'tx_id', 'actions'])
                ->orderColumn('id', '-id $1')
                ->make(true);
        }

        return view('admin.withdrawals.index')->with([
            'page_title' => 'Withdrawal Requests',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Withdrawal $withdrawal
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit(Withdrawal $withdrawal)
    {
        return view('admin.withdrawals.form')->with([
            'page_title' => 'Edit Withdrawal',
            'form_params' => ['button_name' => 'Update', 'action' => route('admin.withdrawals.update', $withdrawal->id), 'method' => 'put'],
            'item' => $withdrawal,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\WithdrawalRequest $request
     * @param \App\Models\Withdrawal $withdrawal
     * @return \Illuminate\Http\Response
     */
    public function update(WithdrawalRequest $request, Withdrawal $withdrawal)
    {
        $withdrawal->update($request->all());

        $success_message = 'Withdrawal updated successfully!';

        if($request->status === 'paid' && $withdrawal->status === 'pending'){
            //Create user log
            newUserLog($withdrawal->user_id, 'withdrawal', [
                'amount' => $withdrawal->amount,
            ]);
            $success_message = 'Withdrawal Request marked as paid successfully!';
        }

        return redirect()->route('admin.withdrawals.index')->withSuccess($success_message);
    }

    /**
     * @param Request $request
     * @param Withdrawal $withdrawal
     * @return \Illuminate\Http\RedirectResponse
     */
    public function pay(Request $request, Withdrawal $withdrawal)
    {
        if(setting('withdrawal_method') !== 'manual' && $withdrawal->amount < setting('withdrawal_max_auto')){
            $message = ['error' => 'You need to activate Manual Withdrawal mode to send manual payments!'];
        }else{
            //Send payment
            $payment = $this->autoWithdrawal($withdrawal, true);

            if(in_array('success', $payment)){
                $message = ['success' => 'Withdrawal Request paid successfully!'];
            }else{
                $message = $payment;
            }
        }

        return redirect()->route('admin.withdrawals.edit', $withdrawal->id)->with($message);
    }

    /**
     * Remove the specified resource from storage.
     * @param Withdrawal $withdrawal
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(Withdrawal $withdrawal)
    {
        $withdrawal->delete();

        return redirect()->route('admin.withdrawals.index')->withSuccess('Withdrawal deleted successfully!');
    }
}
