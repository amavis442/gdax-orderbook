@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Wallets</div>

                <div class="panel-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Wallet</th>
                                <th>Currency</th>
                                <th>Koers</th>
                                <th>Oude Koers</th>
                                <th>Koers verschil</th>
                                <th>Waarde</th>
                                <th>Sells-Buys vandaag / overall</th>
                                <th>Avg(buy)</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($wallets as $name=> $wallet)
                            <tr>
                                <td>{{ $name }}</td>
                                <td>@if($name == config('coinbase.currency'))&euro;  <span id="currencWallet">{!! number_format($balances[$name],2) !!}</span>
                                    @else
                                    {!! number_format($balances[$name],8) !!}
                                    @endif
                                </td>
                                @if($name == config('coinbase.currency'))
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>&euro;  {!! number_format($balances[$name],2) !!}</td>
                                <td>&euro;  {!! number_format($balances[$name],2) !!}</td>
                                
                                @else
                                <td>
                                    <span class='koers_{{ $name }}'></span>
                                </td>
                                <td>
                                    <span class='koers_oude_{{ $name }}'></span>
                                </td>
                                <td>
                                    <span class='koers_verschil_{{ $name }}'></span>
                                </td>
                                <td>
                                    <span class='waarde_{{ $name }}'></span>
                                </td>
                                <td></td>
                                <td></td>
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
                            <td colspan='9'>
                                <div class='pull-right'>
                                    Portfolio waarde: <span class='portfolioValue'></span>
                                </div>
                            </td>
                        </tr>
                        <tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Projections</div>
                
                <div class="col-md-4">
                    <div class="panel panel-default">
                        <div class="panel-heading">BTC - <span class="koers_BTC"></span> - <span class="inkas"></span> - <span id="amount_BTC"></span></div>
                        <div id="projections_BTC"></div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="panel panel-default">
                        <div class="panel-heading">ETH - <span class="koers_ETH"></span> - <span class="inkas"></span> - <span id="amount_ETH"></span></div>
                        <div id="projections_ETH"></div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="panel panel-default">
                        <div class="panel-heading">LTC - <span class="koers_LTC"></span> - <span class="inkas"></span> - <span id="amount_LTC"></span></div>
                        <div id="projections_LTC"></div>
                    </div>
                </div> 

                <div class="panel-body">
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
    var portfolio_{!! $name !!} = {!! number_format($balances[$name],8) !!}; 
    @endforeach

    $(document).ready(function () {
        getCurrencys();
        
        setInterval(getCurrencys, 1100);
    });
    
    function calcPortfolioCurrentValue()
    {
        var portfolio_value = parseFloat(waarde_btc) + parseFloat(waarde_eth) + parseFloat(waarde_ltc) + parseFloat(portfolio_EUR);
        
        console.log(portfolio_value);
        
        $('.portfolioValue').html('&euro; ' + portfolio_value.toFixed(2));
    }
    
    
    function calcProjections(currency, inkas, lastTradePrice)
    {
        $('.inkas').html('&euro; '+ inkas);
        
        var amount = inkas / lastTradePrice;
        var increases = function(price , procentpoint) {
            return price * (1 + procentpoint / 100);
        };

        var decreases = function(price , procentpoint) {
            return price * (1 - procentpoint / 100);
        };
        
        var profits = function(lastTradePrice, amount, price) {
            var delta = price - lastTradePrice;
            return delta * amount;            
        };
        
        var s = '';
        s += '<ul>';
        
        for (i = 1; i < 10;i++) {
            var price = increases(lastTradePrice, i);
            var lossprice = decreases(lastTradePrice, i);
            var profit = profits(lastTradePrice, amount, price); 
            var losses = profits(lastTradePrice, amount, lossprice);
            var delta = price - lastTradePrice;
            var deltaLosses = lossprice - lastTradePrice;
            s += '<li>+/- <span class="badge">' + i + '</span> procent<ul>';
            s += '<li>Price : <span class="label label-primary">&euro;' + (price).toFixed(2) + '</span>/<span class="label label-danger">&euro;'+  (lossprice).toFixed(2) + '</span></li>';
            s += '<li>Delta : &euro;' + (delta).toFixed(2) + '/ &euro;' + (deltaLosses).toFixed(2) + '</li>';
            s += '<li>Profit : <span class="label label-primary">&euro; ' + (profit).toFixed(2) + '</span> / <span class="label label-danger">&euro; ' + (losses).toFixed(2)+ '<span></li>';
            s += '</ul></li>';
        }
        s += '</ul>';
        
        $('#amount_' + currency).html((amount).toFixed(8));
        
        $('#projections_' + currency).html(s);
    }
    
    function updateProfits(useWallet, currentPrice)
    {
        $('.orders').each(function(i, row) {
            orderid = $(row).find('.orderid').html();
            wallet = $(row).find('#orderwallet' + orderid).html();
            trade = $(row).find('#ordertrade' + orderid).html();
            ordercoinclosed = $(row).find('#ordercoinclosed' + orderid).html();
            if (wallet != useWallet || trade != 'BUY' || ordercoinclosed == 1) {
               return true;
            }
            
            amount = $(row).find('#orderamount'+orderid).html();
            coinprice = $(row).find('#ordercoinprice'+ orderid).html();
            
            currentcoinprice = $(row).find('.currentcoinprice');
            
            if (currentPrice > coinprice) {
                currentcoinprice.addClass('label-success').removeClass('label-danger');
            } else {
                currentcoinprice.addClass('label-danger').removeClass('label-success');
            }
            
            console.log('Amount ' + amount);
            console.log('Coinprice bought '+ coinprice);
            console.log('Coinprice current '+ currentPrice);
            
            var diff = 0.00;
            diff = parseFloat(currentPrice) - parseFloat(coinprice);
            console.log('Diff ' + diff);
            
            var profit = 0.00;
            profit = (diff * amount).toFixed(8);
            console.log('Profit ' +profit);
            
            $(row).find('#profit' + orderid).html('&euro; ' + profit);
            if (profit < 0) {
                $(row).find('#profit' + orderid).addClass('label-danger').removeClass('label-success');
            } else {
                $(row).find('#profit' + orderid).addClass('label-success').removeClass('label-danger');
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
            $('.koers_' + wallet).html('&euro; ' + bidprice);
            if (oude_koers_ltc > bidprice) {
                $('.koers_' + wallet).css('color','red');
            } else {
                $('.koers_' + wallet).css('color','green');
            }
            oude_koers_ltc = bidprice;
        
            waarde_ltc = (bidprice * portfolio_LTC).toFixed(8);
            $('.waarde_' + wallet).html('&euro; ' + waarde_ltc);
            
            calcPortfolioCurrentValue();
            
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
            
            var diff = (btc - oude_koers_btc).toFixed(8);
            $('.koers_BTC').html('&euro; ' + btc);
            
            $('.koers_oude_BTC').html('&euro; ' + oude_koers_btc);
            $('.koers_verschil_BTC').html('&euro; ' + diff);
            
            if (diff < 0) {
                $('.koers_verschil_BTC').css('color','red');
            } else {
                $('.koers_verschil_BTC').css('color','green');
            }
            oude_koers_btc = btc;
            
            waarde_btc = (btc * portfolio_BTC).toFixed(8);
            $('.waarde_BTC').html('&euro; ' + waarde_btc);
            
            calcPortfolioCurrentValue();
            
            updateProfits('BTC', btc);
            
            calcProjections('BTC', $('#currencWallet').html(), btc);
        });
        
        $.get(endpoint + '/products/ETH-EUR/book', function (data) {
            var eth = data.asks[0][0];    
            
            if (oude_koers_eth == 0.00) {
                oude_koers_eth = eth;
            }
            
            
            var diff = (eth - oude_koers_eth).toFixed(8);
            $('.koers_ETH').html('&euro; ' + eth); // Css Class
            $('.koers_oude_ETH').html('&euro; ' + oude_koers_eth);
            $('.koers_verschil_ETH').html('&euro; ' + diff);
            
            if (diff < 0) {
                $('.koers_verschil_ETH').css('color','red');
            } else {
                $('.koers_verschil_ETH').css('color','green');
            }
            oude_koers_eth = eth;
        
            waarde_eth = (eth * portfolio_ETH).toFixed(8);
            $('.waarde_ETH').html('&euro; ' + waarde_eth);
            
            calcPortfolioCurrentValue();
            
            updateProfits('ETH', eth);
            
            calcProjections('ETH', $('#currencWallet').html(), eth);
        });
        
        $.get(endpoint + '/products/LTC-EUR/book', function (data) {
            var ltc = data.asks[0][0];    
            
            if (oude_koers_ltc == 0.00) {
                oude_koers_ltc = ltc;
            }
            
            var diff = (ltc - oude_koers_ltc).toFixed(8);
            
            $('.koers_LTC').html('&euro; ' + ltc);
            $('.koers_oude_LTC').html('&euro; ' + oude_koers_ltc);
            $('.koers_verschil_LTC').html('&euro; ' + diff);
            
            if (diff < 0) {
                $('.koers_verschil_LTC').css('color','red');
            } else {
                $('.koers_verschil_LTC').css('color','green');
            }
            oude_koers_ltc = ltc;
        
            waarde_ltc = (ltc * portfolio_LTC).toFixed(8);
            $('.waarde_LTC').html('&euro; ' + waarde_ltc);
            
            calcPortfolioCurrentValue();
            
            updateProfits('LTC', ltc);
            
            calcProjections('LTC', $('#currencWallet').html(), ltc);
        });
        
        
    }
</script>

@endpush
