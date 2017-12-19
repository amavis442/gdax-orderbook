@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>

                <div class="panel-body">
                    <a href="" class="btn btn-default">Add order</a>
                    
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Wallet</th>
                                <th>Currency</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($wallets as $name=> $wallet)
                        <tr>
                            <td>{{ $name }}</td>
                            <td>@if($name == config('coinbase.currency'))&euro; @endif{{ $wallet->sum('currency')}}</td>
                            @if($name != config('coinbase.currency'))
                            <td>Buy | Sell</td>
                            @else
                            <td><a href="{{ route('wallets.create',['action'=>'deposit','walletname' => config('coinbase.currency')]) }}" class="btn btn-default">Deposit</a> | 
                                <a href="{{ route('wallets.create',['action'=>'withdraw','walletname' => config('coinbase.currency')]) }}" class="btn btn-default">Withdraw</a></td>
                            @endif
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

