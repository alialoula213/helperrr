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
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">Account History</h3>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped text-center">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">Type</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($items as $item)
                                    <tr>
                                        <td>{{ ucfirst($item->type) }}</td>
                                        <td>
                                            {{ $item->description }}
                                            @if ($item->expire_date)
                                                . Expiration date: {{ $item->expire_date }}
                                            @endif
                                        </td>
                                        <td>{{ $item->created_at }}</td>
                                    </tr>
                                @empty
                                    <tr><td class="text-center" colspan="3">No items found!</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($items->hasPages())
                <div class="card-footer">
                    {{ $items->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection