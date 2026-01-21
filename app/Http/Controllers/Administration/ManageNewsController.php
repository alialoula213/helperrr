<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Http\Requests\NewsRequest;
use Illuminate\Support\Facades\Cache;

class ManageNewsController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:list-news|create-news|edit-news|delete-news', ['only' => ['index','store']]);
        $this->middleware('permission:create-news', ['only' => ['create','store']]);
        $this->middleware('permission:show-news', ['only' => ['edit']]);
        $this->middleware('permission:edit-news', ['only' => ['update']]);
        $this->middleware('permission:delete-news', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('admin.news.index')->with([
            'page_title' => 'Manage News',
            'items' => News::latest()->paginate(setting('admin_pagination'))
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('admin.news.form')->with([
            'page_title' => 'Create News',
            'form_params' => ['button_name' => 'Create', 'action' => route('admin.news.store'), 'method' => 'post'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\NewsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(NewsRequest $request)
    {
        News::create($request->all());
        //Erase cache
        $this->deleteCache();

        return redirect()->route('admin.news.index')->withSuccess('News created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\News  $news
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit(News $news)
    {
        return view('admin.news.form')->with([
            'page_title' => 'Edit News',
            'form_params' => ['button_name' => 'Update', 'action' => route('admin.news.update', $news->id), 'method' => 'put'],
            'item' => $news,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\NewsRequest  $request
     * @param  \App\Models\News  $news
     * @return \Illuminate\Http\Response
     */
    public function update(NewsRequest $request, News $news)
    {
        $news->update($request->all());
        //Erase cache
        $this->deleteCache();

        return redirect()->route('admin.news.index')->withSuccess('News updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\News  $news
     * @return \Illuminate\Http\Response
     */
    public function destroy(News $news)
    {
        $news->delete();
        //Erase cache
        $this->deleteCache();

        return redirect()->route('admin.news.index')->withSuccess('News deleted successfully!');
    }

    private function deleteCache()
    {
        Cache::delete(config('smartyscripts_cache_keys.news'));
    }
}
