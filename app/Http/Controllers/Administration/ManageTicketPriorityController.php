<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Models\TicketPriority;
use App\Http\Requests\TicketPriorityRequest;
use Illuminate\Support\Facades\Cache;

class ManageTicketPriorityController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:list-ticket-priorities|create-ticket-priorities|edit-ticket-priorities|delete-ticket-priorities', ['only' => ['index','store']]);
        $this->middleware('permission:create-ticket-priorities', ['only' => ['create','store']]);
        $this->middleware('permission:show-ticket-priorities', ['only' => ['edit']]);
        $this->middleware('permission:edit-ticket-priorities', ['only' => ['update']]);
        $this->middleware('permission:delete-ticket-priorities', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('admin.tickets.priorities.index')->with([
            'page_title' => 'Manage Ticket Priority',
            'items' => TicketPriority::paginate(setting('admin_pagination'))
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('admin.tickets.priorities.form')->with([
            'page_title' => 'Create Ticket Priority',
            'form_params' => ['button_name' => 'Create', 'action' => route('admin.ticket_priorities.store'), 'method' => 'post'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\TicketPriorityRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TicketPriorityRequest $request)
    {
        if($request->default){
            TicketPriority::whereDefault(1)->update(['default' => 0]);
        }

        TicketPriority::create($request->all());
        //Erase cache
        $this->deleteCache();

        return redirect()->route('admin.ticket_priorities.index')->withSuccess('Ticket Priority created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TicketPriority  $ticket_priority
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit(TicketPriority $ticket_priority)
    {
        return view('admin.tickets.priorities.form')->with([
            'page_title' => 'Edit Ticket Priority',
            'form_params' => ['button_name' => 'Update', 'action' => route('admin.ticket_priorities.update', $ticket_priority->id), 'method' => 'put'],
            'item' => $ticket_priority,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\TicketPriorityRequest  $request
     * @param  \App\Models\TicketPriority  $ticket_priority
     * @return \Illuminate\Http\Response
     */
    public function update(TicketPriorityRequest $request, TicketPriority $ticket_priority)
    {
        if($request->default){
            TicketPriority::whereDefault(1)->where('id', '!=', $ticket_priority->id)->update(['default' => 0]);
        }
        $ticket_priority->update($request->all());
        //Erase cache
        $this->deleteCache();

        return redirect()->route('admin.ticket_priorities.index')->withSuccess('Ticket Priority updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TicketPriority  $ticket_priority
     * @return \Illuminate\Http\Response
     */
    public function destroy(TicketPriority $ticket_priority)
    {
        $ticket_priority->delete();
        //Erase cache
        $this->deleteCache();

        return redirect()->route('admin.ticket_priorities.index')->withSuccess('Ticket Priority deleted successfully!');
    }

    private function deleteCache()
    {
        Cache::delete(config('smartyscripts_cache_keys.ticket_priorities'));
    }
}
