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
                        <div class="col-8">
                            <h5 class="h3 mb-0">Support Center</h5>
                        </div>
                        <div class="col-4 text-right">
                            <a href="{{ route('tickets.create') }}" class="btn btn-sm btn-neutral"><i class="fa fa-plus-circle"></i> Create</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped text-center">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col"># ID</th>
                                    <th scope="col">Title</th>
                                    <th scope="col">Category</th>
                                    <th scope="col">Priority</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Unread</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tickets as $ticket)
                                    <tr>
                                        <td>{{ $ticket->ticket_id }}</td>
                                        <td>{{ $ticket->title }}</td>
                                        <td>{{ $ticket->category->name }}</td>
                                        <td><span class="badge badge-{{ $ticket->priority->css_class ?? 'primary' }}">{{ $ticket->priority->name }}</span></td>
                                        <td><span class="badge badge-{{ $ticket->status->css_class ?? 'primary' }}">{{ $ticket->status->name }}</span></td>
                                        <td>
                                            @if ($ticket->read)
                                                <span class="badge badge-success">Read</span>
                                            @else
                                                <span class="badge badge-danger">Unread</span>
                                            @endif
                                        </td>
                                        <td>{{ $ticket->created_at }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('tickets.show', $ticket->ticket_id) }}" class="btn btn-info"><i class="fa fa-eye"></i> View</a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="text-center" colspan="8">No tickets found!</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($tickets->hasPages())
                <div class="card-footer">
                    {{ $tickets->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection