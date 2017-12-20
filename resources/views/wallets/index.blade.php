@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Wallets</div>

                <div class="panel-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Wallet</th>
                                <th>Currency</th>
                                <th>Koers</th>
                                <th>Waarde</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($wallets as $name=> $wallet)
                            <tr>
                                <td>{{ $name }}</td>
                                <td>@if($name == config('coinbase.currency'))&euro;  {!! number_format($wallet->sum('currency'),2) !!}
                                    @else
                                    {!! number_format($wallet->sum('currency'),8) !!}
                                    @endif
                                </td>
                                @if($name == config('coinbase.currency'))
                                <td>&euro;  {!! number_format($wallet->sum('currency'),2) !!}</td>
                                <td>&euro;  {!! number_format($wallet->sum('currency'),2) !!}</td>
                                @else
                                <td>
                                    <span id='koers_{{ $name }}'></span>
                                </td>
                                <td>
                                    <span id='waarde_{{ $name }}'></span>
                                </td>
                                @endif
                                @if($name != config('coinbase.currency'))
                                <td> <a href="{{ route('orders.create',['trade'=>'BUY','wallet'=> $name]) }}" class="btn btn-default">Buy</a> |  <a href="{{ route('orders.create',['trade'=>'SELL','wallet'=> $name]) }}" class="btn btn-default">Sell</a></td>
                                @else
                                <td><a href="{{ route('wallets.create',['action'=>'deposit','walletname' => config('coinbase.currency')]) }}" class="btn btn-default">Deposit</a> | 
                                    <a href="{{ route('wallets.create',['action'=>'withdraw','walletname' => config('coinbase.currency')]) }}" class="btn btn-default">Withdraw</a></td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                        <tr>    
                            <td colspan='5'>
                                <div class='pull-right'>
                                    Portfolio waarde: <span id='portfolio'></span>
                                </div>
                            </td>
                        </tr>
                        <tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>


    @include('orders.partials.view')

</div>
@endsection

@push('javascript')
<script>
    var endpoint = '{!! config('coinbase.endpoint') !!}';
    var waarde_ltc = 0.00;
    var waarde_btc = 0.00;
    var waarde_eth = 0.00;
        
    @foreach($wallets as $name=> $wallet)
    var portfolio_{!! $name !!} = {!! number_format($wallet->sum('currency'),8) !!}; 
    @endforeach

    $(document).ready(function () {
        getCurrencys();
        
        setInterval(getCurrencys, 20000);
    });
    
    function calcPortfolion()
    {
        var portfolio_value = parseFloat(waarde_btc) + parseFloat(waarde_eth) + parseFloat(waarde_ltc) + parseFloat(portfolio_EUR);
        
        console.log(portfolio_value);
        
        $('#portfolio').html('&euro; ' + portfolio_value.toFixed(2));
    }
    
    
    
    function getCurrencys()
    {
        $.get(endpoint + '/products/BTC-EUR/book', function (data) {
            var btc = data.asks[0][0];    
            $('#koers_BTC').html('&euro; ' + btc);
            waarde_btc = (btc * portfolio_BTC).toFixed(2);
            $('#waarde_BTC').html('&euro; ' + waarde_btc);
            
            calcPortfolion();
        });
        
        $.get(endpoint + '/products/ETH-EUR/book', function (data) {
            var eth = data.asks[0][0];    
            $('#koers_ETH').html('&euro; ' + eth);
            waarde_eth = (eth * portfolio_ETH).toFixed(2);
            $('#waarde_ETH').html('&euro; ' + waarde_eth);
            
            calcPortfolion();
        });
        
        $.get(endpoint + '/products/LTC-EUR/book', function (data) {
            var ltc = data.asks[0][0];    
            $('#koers_LTC').html('&euro; ' + ltc);
            waarde_ltc = (ltc * portfolio_LTC).toFixed(2);
            $('#waarde_LTC').html('&euro; ' + waarde_ltc);
            
            calcPortfolion();
        });
        
        
    }
</script>

@endpush
