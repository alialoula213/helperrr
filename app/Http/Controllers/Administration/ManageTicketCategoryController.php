<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Models\TicketCategory;
use App\Http\Requests\TicketCategoryRequest;
use Illuminate\Support\Facades\Cache;

class ManageTicketCategoryController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:list-ticket-categories|create-ticket-categories|edit-ticket-categories|delete-ticket-categories', ['only' => ['index','store']]);
        $this->middleware('permission:create-ticket-categories', ['only' => ['create','store']]);
        $this->middleware('permission:show-ticket-categories', ['only' => ['edit']]);
        $this->middleware('permission:edit-ticket-categories', ['only' => ['update']]);
        $this->middleware('permission:delete-ticket-categories', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('admin.tickets.categories.index')->with([
            'page_title' => 'Manage Ticket Category',
            'items' => TicketCategory::paginate(setting('admin_pagination'))
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('admin.tickets.categories.form')->with([
            'page_title' => 'Create Ticket Category',
            'form_params' => ['button_name' => 'Create', 'action' => route('admin.ticket_categories.store'), 'method' => 'post'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\TicketCategoryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TicketCategoryRequest $request)
    {
        TicketCategory::create($request->all());
        //Erase cache
        $this->deleteCache();

        return redirect()->route('admin.ticket_categories.index')->withSuccess('Ticket Category created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TicketCategory  $ticket_category
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit(TicketCategory $ticket_category)
    {
        return view('admin.tickets.categories.form')->with([
            'page_title' => 'Edit Ticket Category',
            'form_params' => ['button_name' => 'Update', 'action' => route('admin.ticket_categories.update', $ticket_category->id), 'method' => 'put'],
            'item' => $ticket_category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\TicketCategoryRequest  $request
     * @param  \App\Models\TicketCategory  $ticket_category
     * @return \Illuminate\Http\Response
     */
    public function update(TicketCategoryRequest $request, TicketCategory $ticket_category)
    {
        $ticket_category->update($request->all());
        //Erase cache
        $this->deleteCache();

        return redirect()->route('admin.ticket_categories.index')->withSuccess('Ticket Category updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TicketCategory  $ticket_category
     * @return \Illuminate\Http\Response
     */
    public function destroy(TicketCategory $ticket_category)
    {
        $ticket_category->delete();
        //Erase cache
        $this->deleteCache();

        return redirect()->route('admin.ticket_categories.index')->withSuccess('Ticket Category deleted successfully!');
    }

    private function deleteCache()
    {
        Cache::delete(config('smartyscripts_cache_keys.ticket_categories'));
    }
}
