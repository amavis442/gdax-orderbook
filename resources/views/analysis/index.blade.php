@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>

                <div class="panel-body">
                    @foreach($wallets as $name => $wallet)
                    <ul><li>{{ $name }} 
                            <ul>
                                <li><label>Sellout:</label> &euro; {{ $wallet['selltrade'] }}</li>
                                <li><label>Buyin:</label> &euro; {{ $wallet['buytrade'] }}</li>
                                <li> &euro; {{ $wallet['diff'] }}</li>
                            </ul>
                        </li>
                    </ul>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
