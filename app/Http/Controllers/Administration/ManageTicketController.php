<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Http\Requests\TicketRequest;
use App\Models\TicketCategory;
use App\Models\TicketComment;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use Illuminate\Http\Request;

class ManageTicketController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:list-tickets|edit-tickets|delete-tickets', ['only' => ['index']]);
        $this->middleware('permission:show-tickets', ['only' => ['edit']]);
        $this->middleware('permission:edit-tickets', ['only' => ['update']]);
        $this->middleware('permission:delete-tickets', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('admin.tickets.index')->with([
            'page_title' => 'Manage Ticket',
            'items' => Ticket::with('category', 'status', 'priority')->latest()->paginate(setting('admin_pagination'))
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit(Ticket $ticket)
    {
        return view('admin.tickets.form')->with([
            'page_title' => 'Edit Ticket',
            'form_params' => ['button_name' => 'Update', 'action' => route('admin.tickets.update', $ticket->id), 'method' => 'put'],
            'item' => $ticket,
            'categories' => TicketCategory::cursor(),
            'statuses' => TicketStatus::cursor(),
            'priorities' => TicketPriority::cursor(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\TicketRequest  $request
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function update(TicketRequest $request, Ticket $ticket)
    {
        $ticket->update($request->all());

        return redirect()->route('admin.tickets.index')->withSuccess('Ticket updated successfully!');
    }

    public function comment(Request $request, Ticket $ticket)
    {
        $this->validate($request, [
           'comment' => 'required|string'
        ]);
        //Insert comment
        TicketComment::create([
            'admin_id'	=> auth()->user()->id,
            'ticket_id' => $ticket->id,
            'comment'	=> $request->comment,
        ]);
        //Mark ticket as unread to user and read for admin
        $ticket->update(['read' => 0, 'admin_read' => 1]);

        return redirect()->back()->withSuccess('New comment added successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function destroy(Ticket $ticket)
    {
        $ticket->delete();

        return redirect()->route('admin.tickets.index')->withSuccess('Ticket deleted successfully!');
    }
}
