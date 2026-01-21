@extends('themes.dashboard.default.layout')

@section('header_content')
    <!-- Header -->
    <div class="header bg-gradient-primary pb-8"></div>
@endsection
@section('content')
    <div class="row mt-5">
        <div class="col-xl-12 mb-5 mb-xl-0">
            <div class="card shadow">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">Latest News</h3>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @forelse ($news as $item)
                        <h4>{{ $item->title }}</h4>
                        <div class="mb-2"><small class="mt-2"><i class="fa fa-calendar"></i> {{ $item->created_at }}</small></div>
                        {!! $item->content !!}
                        @if(!$loop->last)
                            <hr>
                        @endif
                    @empty
                        <p>No news found!</p>
                    @endforelse
                </div>
                @if($news->hasPages())
                <div class="card-footer">
                    {{ $news->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection