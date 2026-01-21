<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Models\TicketStatus;
use App\Http\Requests\TicketStatusRequest;
use Illuminate\Support\Facades\Cache;

class ManageTicketStatusController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:list-ticket-status|create-ticket-status|edit-ticket-status|delete-ticket-status', ['only' => ['index','store']]);
        $this->middleware('permission:create-ticket-status', ['only' => ['create','store']]);
        $this->middleware('permission:show-ticket-status', ['only' => ['edit']]);
        $this->middleware('permission:edit-ticket-status', ['only' => ['update']]);
        $this->middleware('permission:delete-ticket-status', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('admin.tickets.status.index')->with([
            'page_title' => 'Manage Ticket Status',
            'items' => TicketStatus::paginate(setting('admin_pagination'))
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('admin.tickets.status.form')->with([
            'page_title' => 'Create Ticket Status',
            'form_params' => ['button_name' => 'Create', 'action' => route('admin.ticket_status.store'), 'method' => 'post'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\TicketStatusRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TicketStatusRequest $request)
    {
        if($request->default){
            TicketStatus::whereDefault(1)->update(['default' => 0]);
        }

        TicketStatus::create($request->all());
        //Erase cache
        $this->deleteCache();

        return redirect()->route('admin.ticket_status.index')->withSuccess('Ticket Status created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TicketStatus  $ticket_status
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit(TicketStatus $ticket_status)
    {
        return view('admin.tickets.status.form')->with([
            'page_title' => 'Edit Ticket Status',
            'form_params' => ['button_name' => 'Update', 'action' => route('admin.ticket_status.update', $ticket_status->id), 'method' => 'put'],
            'item' => $ticket_status,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\TicketStatusRequest  $request
     * @param  \App\Models\TicketStatus  $ticket_status
     * @return \Illuminate\Http\Response
     */
    public function update(TicketStatusRequest $request, TicketStatus $ticket_status)
    {
        if($request->default){
            TicketStatus::whereDefault(1)->where('id', '!=', $ticket_status->id)->update(['default' => 0]);
        }
        $ticket_status->update($request->all());
        //Erase cache
        $this->deleteCache();

        return redirect()->route('admin.ticket_status.index')->withSuccess('Ticket Status updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TicketStatus  $ticket_status
     * @return \Illuminate\Http\Response
     */
    public function destroy(TicketStatus $ticket_status)
    {
        $ticket_status->delete();
        //Erase cache
        $this->deleteCache();

        return redirect()->route('admin.ticket_status.index')->withSuccess('Ticket Status deleted successfully!');
    }

    private function deleteCache()
    {
        Cache::delete(config('smartyscripts_cache_keys.ticket_status'));
    }
}
