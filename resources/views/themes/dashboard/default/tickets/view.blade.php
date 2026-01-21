@extends('themes.dashboard.default.layout')

@section('header_content')
    <!-- Header -->
    <div class="header bg-gradient-primary pb-8"></div>
@endsection
@section('content')
    <div class="row mt-5">
        <div class="col-xl-12 mb-5 mb-xl-0">
            @include('themes.dashboard.default.partials.alerts')
            <div class="card shadow">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">Ticket #{{ $ticket->ticket_id }}</h3>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-lg-2 col-sm-12">Category</dt>
                        <dd class="col-lg-10 col-sm-12">{{ $ticket->category->name }}</dd>
                        <dt class="col-lg-2 col-sm-12">Priority</dt>
                        <dd class="col-lg-10 col-sm-12"><span class="badge badge-{{ $ticket->priority->css_class ?? 'primary' }}">{{ $ticket->priority->name }}</span></dd>
                        <dt class="col-lg-2 col-sm-12">Status</dt>
                        <dd class="col-lg-10 col-sm-12"><span class="badge badge-{{ $ticket->status->css_class ?? 'primary' }}">{{ $ticket->status->name }}</span></dd>
                        <dt class="col-lg-2 col-sm-12">Created At</dt>
                        <dd class="col-lg-10 col-sm-12">{{ $ticket->created_at }}</dd>
                        <dt class="col-lg-2 col-sm-12">Message</dt>
                        <dd class="col-lg-10 col-sm-12">{{ $ticket->message }}</dd>
                    </dl>
                    @if ($ticket->status_id !== 3)
                    <form action="{{ route('tickets.update', $ticket->ticket_id) }}" method="post">
                        @method('put')
                        @csrf
                        <button class="btn btn-danger"><i class="fa fa-times"></i> Close Ticket</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-5">
        <div class="col-xl-12 mb-5 mb-xl-0">
            <div class="card shadow">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0"><i class="fa fa-comments"></i> Messages</h3>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @forelse ($ticket->comments()->with('user','admin')->get() as $comment)
                        <!-- List group -->
                        <div class="list-group list-group-flush">
                            @if($comment->user_id)
                                <li href="#" class="list-group-item list-group-item-action flex-column align-items-start py-4 px-4 text-right">
                                    <small>{{ $comment->created_at->diffForHumans() }}</small>
                                    <p class="text-sm mb-0">{{ $comment->comment }}</p>
                                </li>
                            @else
                                <li href="#" class="list-group-item list-group-item-action flex-column align-items-start py-4 px-4 bg-primary text-white">
                                    <div class="d-flex w-100 justify-content-between">
                                        <div>
                                            <div class="d-flex w-100 align-items-center">
                                                <h5 class="mb-1 text-white">{{ $comment->admin->username }}</h5>
                                            </div>
                                        </div>
                                        <small>{{ $comment->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="text-sm mb-0">{{ $comment->comment }}</p>
                                </li>
                            @endif
                        </div>
                    @empty
                        No messages found!
                    @endforelse
                </div>
                @if ($ticket->status_id !== 3)
                    <div class="card-footer">
                        <form action="{{ route('tickets.comment', $ticket->ticket_id) }}" method="post">
                            @csrf
                            <div class="input-group input-group-merge">
                                <input type="text" name="comment" class="form-control @error('comment')is-invalid @enderror" placeholder="Write your comment">
                                <div class="input-group-append">
                                    <button class="btn btn-primary"><i class="fa fa-paper-plane"></i> Send</button>
                                </div>
                                @error('comment')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection