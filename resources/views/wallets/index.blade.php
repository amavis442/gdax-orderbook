@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Wallets</div>

                <div class="panel-body">
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
                            <td> <a href="{{ route('orders.create',['trade'=>'BUY']) }}" class="btn btn-default">Buy</a> |  <a href="{{ route('orders.create',['trade'=>'SELL']) }}" class="btn btn-default">Sell</a></td>
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
    
    
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Orders</div>

                <div class="panel-body">
                    
                    
                    <a href="{{ route('orders.create') }}" class="btn btn-default">Add order</a>
                    
                    {{ $orders->links() }}
                    
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Datum</th>
                                <th>Wallet</th>
                                <th>Trade</th>
                                <th>Hoeveelheid</th>
                                <th>Handelsprijs</th>
                                <th>Koers munt</th>
                                <th>Kosten</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($orders as $order)
                        <tr>
                            <td>{{ $order->created_at->format('d-m-Y') }}</td>
                            <td>{{ $order->wallet }}</td>
                            <td>{{ $order->trade }}</td>
                            <td>{{ $order->amount }}</td>
                            <td>&euro; {{ $order->tradeprice }}</td>
                            <td>&euro; {{ $order->coinprice }}</td>
                            <td>&euro; {{ $order->fee > 0.0 ? $order->fee : '0.00' }}</td>
                            <td>
                                <form action="{{ route('orders.destroy', $order) }}" method="post">
                                    {{ csrf_field()}}
                                    {{ method_field('DELETE') }}
                                    <input type="submit" value="Verwijderen" class="btn btn-default">
                                </form>
                            </td>
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

