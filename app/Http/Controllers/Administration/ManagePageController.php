<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Http\Requests\PageRequest;
use Illuminate\Support\Facades\Cache;

class ManagePageController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:list-pages|create-pages|edit-pages|delete-pages', ['only' => ['index','store']]);
        $this->middleware('permission:create-pages', ['only' => ['create','store']]);
        $this->middleware('permission:show-pages', ['only' => ['edit']]);
        $this->middleware('permission:edit-pages', ['only' => ['update']]);
        $this->middleware('permission:delete-pages', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('admin.pages.index')->with([
            'page_title' => 'Manage Pages',
            'items' => Page::paginate(setting('admin_pagination'))
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('admin.pages.form')->with([
            'page_title' => 'Create Page',
            'form_params' => ['button_name' => 'Create', 'action' => route('admin.pages.store'), 'method' => 'post'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\PageRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PageRequest $request)
    {
        if($request->type === 'tos' || $request->type === 'privacy'){
            Page::whereType($request->type)->update(['type' => 'page']);
        }
        Page::create($request->all());
        //Erase cache
        $this->deleteCache();

        return redirect()->route('admin.pages.index')->withSuccess('Page created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit(Page $page)
    {
        return view('admin.pages.form')->with([
            'page_title' => 'Edit Page',
            'form_params' => ['button_name' => 'Update', 'action' => route('admin.pages.update', $page->id), 'method' => 'put'],
            'item' => $page,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\PageRequest  $request
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function update(PageRequest $request, Page $page)
    {
        if($request->type === 'tos' || $request->type === 'privacy'){
            Page::whereType($request->type)->where('id','!=', $page->id)->update(['type' => 'page']);
        }

        $page->update($request->all());
        //Erase cache
        $this->deleteCache();

        return redirect()->route('admin.pages.index')->withSuccess('Page updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function destroy(Page $page)
    {
        $page->delete();
        //Erase cache
        $this->deleteCache();

        return redirect()->route('admin.pages.index')->withSuccess('Page deleted successfully!');
    }

    private function deleteCache()
    {
        Cache::delete(config('smartyscripts_cache_keys.pages'));
    }
}
