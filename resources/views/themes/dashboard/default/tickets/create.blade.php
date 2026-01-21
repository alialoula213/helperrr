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
                            <h3 class="mb-0">New Ticket</h3>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('tickets.store') }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category_id" class="form-control @error('category_id')is-invalid @enderror">
                                <option value="">-- Select --</option>
                                @foreach($categories as $category)
                                    <option value="{{$category->id}}">{{$category->name}}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                            <div class="invalid-feedback" role="alert">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="title">Subject</label>
                            <input type="text" name="title" id="title" class="form-control @error('title')is-invalid @enderror" value="{{ old('title') }}">
                            @error('title')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea name="message" id="message" cols="30" rows="10" class="form-control @error('message')is-invalid @enderror">{{ old('message') }}</textarea>
                            @error('message')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <button class="btn btn-primary"><i class="fa fa-plus-circle"></i> Create</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection