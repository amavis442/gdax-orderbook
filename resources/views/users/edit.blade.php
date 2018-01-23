@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">User</div>
                    <div class="panel-body">
                        <form action="{{ route('users.update', $user) }}" method="post">
                            {{ csrf_field() }}
                            {{ method_field('PUT') }}
                            @include('users.partials.forminputs')
                            <button type="submit">Save</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection