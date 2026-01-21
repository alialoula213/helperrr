<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\User;
use App\Http\Requests\UserRequest;
use App\Models\UserLog;
use App\Models\UserMiningPower;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ManageUserController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:list-users|create-users|edit-users|delete-users', ['only' => ['index','store']]);
        $this->middleware('permission:create-users', ['only' => ['create','store']]);
        $this->middleware('permission:show-users', ['only' => ['edit']]);
        $this->middleware('permission:edit-users', ['only' => ['update']]);
        $this->middleware('permission:delete-users', ['only' => ['destroy']]);
        $this->middleware('permission:impersonate-users', ['only' => ['impersonate']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = User::select('*');
            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('balance', '{{ crypto_currency($balance) }}')
                ->editColumn('status', 'admin.partials.datatables.user-status')
                ->addColumn('actions', 'admin.users.datatables.actions')
                ->rawColumns(['actions', 'status'])
                ->make(true);
        }

        return view('admin.users.index')->with([
            'page_title' => 'Manage Users',
            //'items' => User::paginate(setting('admin_pagination'))
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit(User $user)
    {
        return view('admin.users.form')->with([
            'page_title' => 'Edit User',
            'form_params' => ['button_name' => 'Update', 'action' => route('admin.users.update', $user->id), 'method' => 'put'],
            'item' => $user,
            'user_hashpower' => $user->hashpower(),
            'users' => User::all(),
            'statistics' => \DB::select("SELECT
                       (SELECT SUM(amount) FROM deposits WHERE status ='paid' AND user_id = $user->id) as paid_deposits,
                       (SELECT SUM(amount) FROM withdrawals WHERE status ='paid' AND user_id = $user->id) as paid_withdrawals
                   ")[0],
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UserRequest $request
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request, User $user)
    {
        if($request->filled('password')){
            $user->update($request->all());
        }else{
            $user->update($request->except('password'));
        }

        return redirect()->route('admin.users.index')->withSuccess('User updated successfully!');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UserRequest $request
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function bonusAndPenalty(Request $request, User $user)
    {
        $this->validate($request, [
           'type' => 'required|alpha_dash',
           'hashpower' => 'nullable|numeric|min:0.01|prohibited_if:type,penalty',
           'balance' => 'nullable|numeric|min:0.00000001|prohibited_if:type,others',
           'description' => 'required|string',
        ]);

        $expire_date = null;
        if($request->filled('hashpower') && $request->hashpower > 0){
            $expire_date = \Carbon\Carbon::now()->addDays(setting('period'));
            $user->miningplans()->create([
                'type' => $request->type,
                'power' => $request->hashpower,
                'description' => $request->description,
                'expire_date' => $expire_date,
            ]);
            //Flush user mining power cache
            \Cache::forget('mining_power_user_'.$user->id);
        }

        if($request->filled('balance')){
            if($request->type === 'bonus'){
                $user->increment('balance', $request->balance);
            }
            if($request->type === 'penalty'){
                $user->decrement('balance', $request->balance);
            }
        }

        //Create user log
        $user->logs()->create([
            'type' => $request->type,
            'power' => $request->hashpower,
            'description' => $request->description,
            'expire_date' => $expire_date
        ]);

        return redirect()->back()->withSuccess('User updated successfully!');
    }

    /**
     * @param Request $request
     * @param User $user
     * @throws \Exception
     * @return void
     */
    public function datatablesDeposits(Request $request, User $user)
    {
        if ($request->ajax()) {
            $data = Deposit::whereUserId($user->id)->select('*');
            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('id', '<a href="{{ route(\'admin.deposits.edit\', $id) }}">{{ $id }}</a>')
                ->editColumn('amount', '{{ crypto_currency($amount) }}')
                ->editColumn('paid_amount', '{{ crypto_currency($paid_amount) }}')
                ->editColumn('created_at', function($row){ return $row->created_at; })
                ->editColumn('status', 'admin.partials.datatables.deposits-status')
                ->rawColumns(['id', 'status'])
                ->orderColumn('id', '-id $1')
                ->make(true);
        }
    }

    /**
     * @param Request $request
     * @param User $user
     * @throws \Exception
     * @return void
     */
    public function datatablesWithdrawals(Request $request, User $user)
    {
        if ($request->ajax()) {
            $data = Withdrawal::whereUserId($user->id)->select('*');
            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('id', '<a href="{{ route(\'admin.withdrawals.edit\', $id) }}">{{ $id }}</a>')
                ->editColumn('amount', '{{ crypto_currency($amount) }}')
                ->editColumn('fees', '{{ crypto_currency($fees) }}')
                ->editColumn('paid_amount', '{{ crypto_currency($paid_amount) }}')
                ->editColumn('created_at', function($row){ return $row->created_at; })
                ->editColumn('status', 'admin.partials.datatables.withdrawals-status')
                ->rawColumns(['id', 'status'])
                ->orderColumn('id', '-id $1')
                ->make(true);
        }
    }

    /**
     * @param Request $request
     * @param User $user
     * @return void
     * @throws \Exception
     */
    public function datatablesHistory(Request $request, User $user)
    {
        if ($request->ajax()) {
            $data = UserLog::whereUserId($user->id)->select('*');
            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('type', '{{ ucfirst($type) }}')
                ->editColumn('power', '{{ $power ?? 0 }} {{ setting(\'hashpower_unit\') }}')
                ->editColumn('created_at', function($row){ return $row->created_at; })
                ->rawColumns(['id'])
                ->orderColumn('id', '-id $1')
                ->make(true);
        }
    }

    /**
     * @param Request $request
     * @param User $user
     * @return void
     * @throws \Exception
     */
    public function datatablesMiningPlans(Request $request, User $user)
    {
        if ($request->ajax()) {
            $data = UserMiningPower::whereUserId($user->id)->select('*');
            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('power', '{{ $power }} {{ setting(\'hashpower_unit\') }}')
                ->editColumn('created_at', function($row){ return $row->created_at; })
                ->editColumn('expire_date', function($row){ return $row->expire_date; })
                ->editColumn('status', 'admin.partials.datatables.mining-plan-status')
                ->rawColumns(['id', 'status'])
                ->orderColumn('id', '-id $1')
                ->make(true);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param User $user
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')->withSuccess('User deleted successfully!');
    }

    public function impersonate(User $user)
    {
        // Overwrite who we're logging in as, if we're already logged in as someone else.
        if (session()->has('impersonate_user.user_id') && session()->has('impersonate_user.temp_user_id')) {
            // Let's not try to login as ourselves.
            if (auth('web')->id() == $user->id || session()->get('impersonate_user.user_id') == $user->id) {
                return redirect()->back()->withError('Do not try to login as yourself.');
            }

            // Overwrite temp user ID.
            session(['impersonate_user.temp_user_id' => $user->id]);

            // Login.
            auth('web')->loginUsingId($user->id);

            // Redirect.
            return redirect()->route('index');
        }

        // Remove any old session variables
        $this->flushTempSession();

        // Won't break, but don't let them "Login As" themselves
        if (auth('web')->id() == $user->id) {
            return redirect()->back()->withError('Do not try to login as yourself.');
        }

        // Add new session variables
        session(['impersonate_user' => [
            'user_id' => auth('web')->id(),
            'temp_user_id' => $user->id,
        ]]);

        // Login user
        auth('web')->loginUsingId($user->id);

        // Redirect to frontend
        return redirect()->route('index');
    }

    public function impersonateStop()
    {
        //If for some reason route is getting hit without someone already logged in
        if (!auth('web')->user()) {
            return redirect()->route('login');
        }

        //If user id is set, relogin
        if (session()->has('impersonate_user.user_id') && session()->has('impersonate_user.temp_user_id')) {
            // Remove any old session variables
            $this->flushTempSession();

            //Remove user session
            auth('web')->logout();

            //Redirect to frontend page
            return redirect()->route('index');
        }else{
            // Remove any old session variables
            $this->flushTempSession();

            //Otherwise logout and redirect to login
            auth('web')->logout();

            return redirect()->route('login');
        }
    }

    private function flushTempSession()
    {
        session()->forget('impersonate_user');
    }
}
