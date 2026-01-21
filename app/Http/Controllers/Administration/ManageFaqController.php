<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Http\Requests\FaqRequest;
use Illuminate\Support\Facades\Cache;

class ManageFaqController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:list-faqs|create-faqs|edit-faqs|delete-faqs', ['only' => ['index','store']]);
        $this->middleware('permission:create-faqs', ['only' => ['create','store']]);
        $this->middleware('permission:show-faqs', ['only' => ['edit']]);
        $this->middleware('permission:edit-faqs', ['only' => ['update']]);
        $this->middleware('permission:delete-faqs', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('admin.faqs.index')->with([
            'page_title' => 'Manage Faqs',
            'items' => Faq::paginate(setting('admin_pagination'))
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('admin.faqs.form')->with([
            'page_title' => 'Create Faq',
            'form_params' => ['button_name' => 'Create', 'action' => route('admin.faqs.store'), 'method' => 'post'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\FaqRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(FaqRequest $request)
    {
        Faq::create($request->all());
        //Erase cache
        $this->deleteCache();

        return redirect()->route('admin.faqs.index')->withSuccess('Faq created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Faq  $faq
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit(Faq $faq)
    {
        return view('admin.faqs.form')->with([
            'page_title' => 'Edit Faq',
            'form_params' => ['button_name' => 'Update', 'action' => route('admin.faqs.update', $faq->id), 'method' => 'put'],
            'item' => $faq,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\FaqRequest  $request
     * @param  \App\Models\Faq  $faq
     * @return \Illuminate\Http\Response
     */
    public function update(FaqRequest $request, Faq $faq)
    {
        $faq->update($request->all());
        //Erase cache
        $this->deleteCache();

        return redirect()->route('admin.faqs.index')->withSuccess('Faq updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Faq  $faq
     * @return \Illuminate\Http\Response
     */
    public function destroy(Faq $faq)
    {
        $faq->delete();
        //Erase cache
        $this->deleteCache();

        return redirect()->route('admin.faqs.index')->withSuccess('Faq deleted successfully!');
    }

    private function deleteCache()
    {
        Cache::delete(config('smartyscripts_cache_keys.faqs'));
    }
}
