<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketComment;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index()
    {
        return view('dashboard::tickets.index')->with([
            'tickets' => Ticket::with('category','priority','status')->whereUserId(auth()->user()->id)->latest()->paginate(setting('site_pagination'))
        ]);
    }

    public function create()
    {
        return view('dashboard::tickets.create')->with([
            'categories' => TicketCategory::all()
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'category_id' => 'required|integer|exists:ticket_categories,id',
            'title' => 'required|string',
            'message' => 'required|string',
        ]);

        Ticket::create([
            'user_id' => auth()->user()->id,
            'category_id' => $request->category_id,
            'priority_id' => 1,
            'status_id' => 1,
            'ticket_id' => \Str::random(),
            'title' => $request->title,
            'message' => $request->message,
        ]);

        return redirect()->route('tickets.index')->withSuccess('Ticket created successfully!');
    }

    public function show(Ticket $ticket)
    {
        if($ticket->user_id != auth()->user()->id){
            return redirect()->route('tickets.index')->withError('Ticket not found!');
        }

        if(!$ticket->read){
            $ticket->update(['read' => 1]);
        }

        return view('dashboard::tickets.view')->with([
            'ticket' => $ticket
        ]);
    }

    public function update(Request $request, Ticket $ticket)
    {
        $ticket->update(['status_id' => 3]);

        return redirect()->route('tickets.index')->withSuccess('Ticket closed successfully!');
    }

    public function comment(Request $request, $ticket)
    {
        $this->validate($request, [
            'comment' => 'required|string'
        ]);

        $ticket = Ticket::whereTicketId($ticket)->firstOrFail();

        //Insert comment
        TicketComment::create([
            'user_id'	=> auth()->user()->id,
            'ticket_id' => $ticket->id,
            'comment'	=> $request->comment,
        ]);
        //Mark ticket as unread to admin
        $ticket->update(['admin_read' => 0]);

        return redirect()->route('tickets.index')->withSuccess('Message sent successfully!');
    }
}
