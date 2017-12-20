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
                                <th>Oude Koers (10 sec interval)</th>
                                <th>Koers verschil</th>
                                <th>Waarde</th>
                                <th>Avg(buy)</th>
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
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>&euro;  {!! number_format($wallet->sum('currency'),2) !!}</td>
                                <td>&euro;  {!! number_format($wallet->sum('currency'),2) !!}</td>
                                
                                @else
                                <td>
                                    <span id='koers_{{ $name }}'></span>
                                </td>
                                <td>
                                    <span id='koers_oude_{{ $name }}'></span>
                                </td>
                                <td>
                                    <span id='koers_verschil_{{ $name }}'></span>
                                </td>
                                <td>
                                    <span id='waarde_{{ $name }}'></span>
                                </td>
                                <td>
                                    &euro; {!!  number_format($orderBuyAvg[$name],2) !!}
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
                            <td colspan='8'>
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
        
    var oude_koers_btc = 0.00;
    var oude_koers_eth = 0.00;
    var oude_koers_ltc = 0.00;
    
    // Maybe place this in array so i can use a 1 function to get the data from gdax and calculate the rest???    
    @foreach($wallets as $name=> $wallet)
    var portfolio_{!! $name !!} = {!! number_format($wallet->sum('currency'),8) !!}; 
    @endforeach

    $(document).ready(function () {
        getCurrencys();
        
        setInterval(getCurrencys, 10000);
    });
    
    function calcPortfolion()
    {
        var portfolio_value = parseFloat(waarde_btc) + parseFloat(waarde_eth) + parseFloat(waarde_ltc) + parseFloat(portfolio_EUR);
        
        console.log(portfolio_value);
        
        $('#portfolio').html('&euro; ' + portfolio_value.toFixed(2));
    }
    
    
    function updateProfits(useWallet, currentPrice)
    {
        $('.orders').each(function(i, row) {
            orderid = $(row).find('.orderid').html();
            wallet = $(row).find('#orderwallet' + orderid).html();
            trade = $(row).find('#ordertrade' + orderid).html();
            
            if (wallet != useWallet || trade != 'BUY') {
               return true;
            }
            
            amount = $(row).find('#orderamount'+orderid).html();
            coinprice = $(row).find('#ordercoinprice'+ orderid).html();
            
            console.log('Amount ' + amount);
            console.log('Coinprice bought '+ coinprice);
            console.log('Coinprice current '+ currentPrice);
            
            var diff = 0.00;
            diff = parseFloat(currentPrice) - parseFloat(coinprice);
            console.log('Diff ' + diff);
            
            var profit = 0.00;
            profit = (diff * amount).toFixed(2);
            console.log('Profit ' +profit);
            
            $(row).find('#profit' + orderid).html('&euro; ' + profit);
            if (profit < 0) {
                $(row).find('#profit' + orderid).css('color','red');
            } else {
                $(row).find('#profit' + orderid).css('color','green');
            }
        });
    }
    
    /** 
     * WIP
     */
    function updateKoers(wallet)
    {
        $.get(endpoint + '/products/' + wallet + '-EUR/book', function (data) {
            var bidprice = data.asks[0][0];    
            $('#koers_' + wallet).html('&euro; ' + bidprice);
            if (oude_koers_ltc > bidprice) {
                $('#koers_' + wallet).css('color','red');
            } else {
                $('#koers_' + wallet).css('color','green');
            }
            oude_koers_ltc = bidprice;
        
            waarde_ltc = (bidprice * portfolio_LTC).toFixed(2);
            $('#waarde_' + wallet).html('&euro; ' + waarde_ltc);
            
            calcPortfolion();
            
            updateProfits(wallet, ltc);
        });
    }
    
    
    function getCurrencys()
    {
        $.get(endpoint + '/products/BTC-EUR/book', function (data) {
            var btc = data.asks[0][0];
            
            if (oude_koers_btc == 0.00) {
                oude_koers_btc = btc;
            }
            
            var diff = (btc - oude_koers_btc).toFixed(2);
            $('#koers_BTC').html('&euro; ' + btc);
            $('#koers_oude_BTC').html('&euro; ' + oude_koers_btc);
            $('#koers_verschil_BTC').html('&euro; ' + diff);
            
            if (diff < 0) {
                $('#koers_verschil_BTC').css('color','red');
            } else {
                $('#koers_verschil_BTC').css('color','green');
            }
            oude_koers_btc = btc;
            
            waarde_btc = (btc * portfolio_BTC).toFixed(2);
            $('#waarde_BTC').html('&euro; ' + waarde_btc);
            
            calcPortfolion();
            
            updateProfits('BTC', btc);
        });
        
        $.get(endpoint + '/products/ETH-EUR/book', function (data) {
            var eth = data.asks[0][0];    
            
            if (oude_koers_eth == 0.00) {
                oude_koers_eth = eth;
            }
            
            
            var diff = (eth - oude_koers_eth).toFixed(2);
            $('#koers_ETH').html('&euro; ' + eth);
            $('#koers_oude_ETH').html('&euro; ' + oude_koers_eth);
            $('#koers_verschil_ETH').html('&euro; ' + diff);
            
            if (diff < 0) {
                $('#koers_verschil_ETH').css('color','red');
            } else {
                $('#koers_verschil_ETH').css('color','green');
            }
            oude_koers_eth = eth;
        
            waarde_eth = (eth * portfolio_ETH).toFixed(2);
            $('#waarde_ETH').html('&euro; ' + waarde_eth);
            
            calcPortfolion();
            
            updateProfits('ETH', eth);
        });
        
        $.get(endpoint + '/products/LTC-EUR/book', function (data) {
            var ltc = data.asks[0][0];    
            
            if (oude_koers_ltc == 0.00) {
                oude_koers_ltc = ltc;
            }
            
            var diff = (ltc - oude_koers_ltc).toFixed(2);
            
            $('#koers_LTC').html('&euro; ' + ltc);
            $('#koers_oude_LTC').html('&euro; ' + oude_koers_ltc);
            $('#koers_verschil_LTC').html('&euro; ' + diff);
            
            if (diff < 0) {
                $('#koers_verschil_LTC').css('color','red');
            } else {
                $('#koers_verschil_LTC').css('color','green');
            }
            oude_koers_ltc = ltc;
        
            waarde_ltc = (ltc * portfolio_LTC).toFixed(2);
            $('#waarde_LTC').html('&euro; ' + waarde_ltc);
            
            calcPortfolion();
            
            updateProfits('LTC', ltc);
        });
        
        
    }
</script>

@endpush
