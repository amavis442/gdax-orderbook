<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Orders</div>

            <div class="panel-body">

                <div class='row'>
                    <div class='col-md-12'>
                        <div class='pull-right'>
                            <a href="{{ route('orders.create') }}" class="btn btn-default">Add order</a>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class='col-md-12'>
                        <br/>
                    </div>
                </div>
                
                <!-- Search form -->
                <div class='row'>
                    <div class='col-md-12'>
                        <div class="pull-right">
                            <form method='post' action="{{ route('wallets.order.search') }}" class="form-inline">
                                {{  csrf_field() }}
                                <input type="hidden" name="tab" value="{{ $tab }}">
                                <div class="form-group">
                                    <label>Filter</label>
                                    <label class="radio-inline">
                                        <input type="radio" id="inlineCheckbox1" name="searchBuySell" value="all" @if($searchBuySell == 'all') checked @endif>Buy/Sell
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" id="inlineCheckbox2" name="searchBuySell" value="buy" @if($searchBuySell == 'buy') checked @endif>Buy
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" id="inlineCheckbox3" name="searchBuySell" value="sell" @if($searchBuySell == 'sell') checked @endif>Sell
                                    </label>
                                </div> |
                                <div class="form-group">
                                    <label class="radio-inline">
                                        <input type="radio" id="inlineCheckbox1" name="searchOpen" value="all" @if($searchOpen == 'all') checked @endif>All
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" id="inlineCheckbox2" name="searchOpen" value="open" @if($searchOpen == 'open') checked @endif>open
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" id="inlineCheckbox3" name="searchOpen" value="closed" @if($searchOpen == 'closed') checked @endif>closed
                                    </label>

                                </div> |
                                <div class="form-group">
                                    <input type="text" name="searchString" id="searchstr" class="form-control" value='@if(isset($searchString)) {{ $searchString }} @endif '>
                                </div>
                                <div class="form-group">
                                    <select name="searchMode" id="searchmode" class="form-control">
                                        <option value="">&mdash;</option>
                                        <option value="like">like</option>
                                        <option value=">">&gt;</option>
                                        <option value="<">&lt;</option>
                                        <option value="=">=</option>

                                    </select>
                                </div>

                                <button type="submit" class="btn btn-default">Zoeken</button>
                            </form>
                        </div>
                    </div>
                </div>
                <br/>

                <!-- Paginator -->
                <div class='row'>
                    <div class='col-md-12'>
                        {{ $orders->links() }}
                    </div>
                </div>

                <!-- Current value of portfolio (can change depeding prices) -->
                <div class="row">
                    <div class='col-md-12'>
                        <div class='pull-right'>
                            Portfolio waarde: <span class='portfolioValue'></span>
                        </div>
                    </div>
                </div>

                <div class='row'>
                    <div class='col-md-12'>
                        <ul class="nav nav-tabs">
                            @foreach($tabProducts as $tabIndex => $tabProduct)
                            <li @if(!isset($tab) || $tab == $tabIndex) class="active" @endif><a href="{{ route('wallets.index.tab',$tabIndex) }}">{{$tabProduct}}</a></li>
                            @endforeach
                        </ul>


                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <td>#</td>
                                    <th>Datum</th>
                                    <th>Wallet</th>
                                    <th>Side</th>
                                    <th>Hoeveelheid</th>
                                    <th>Handelsprijs</th>
                                    <th>Koers munt gekocht/ Huidige koers</th>
                                    <th>Advies verkoopprijs</th>
                                    <th>P/L</th>
                                    <th>Kosten</th>
                                    <th>Verkocht voor</th>
                                    <th>Genomen P/L</th>
                                    <th>Closed</th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                <tr class="orders 
                                    @if($order->side == 'BUY')
                                        @if($order->filled)success @else danger @endif
                                    @endif">
                                    <td class="orderid">{{ $order->id }}</td>
                                    <td>{{ $order->created_at->format('d-m-Y H:i') }}</td>
                                    <td><span id="orderwallet{{ $order->id }}">{{ $order->wallet }}</span></td>
                                    <td><span id="ordertrade{{ $order->id }}">{{ $order->side }}</span></td>
                                    <td><span id="orderamount{{ $order->id }}">{{ $order->amount }}</span></td>
                                    <td>&euro; {{ $order->tradeprice }}</td>
                                    <td><span id="ordercoinprice{{ $order->id }}" class="label label-primary">{{ $order->coinprice }}</span>
                                        @if(!$order->filled) / <span class='koers_{{ $order->wallet }} currentcoinprice label'></span> @endif</td>
                                    <td>
                                        @if($order->side == 'BUY')
                                        <span class='label label-info'>
                                            &euro;
                                            @if($order->product_id == 'LTC-EUR')
                                            {{ number_format($order->coinprice + config('coinbase.ltc_spread'),2) }}
                                            @endif
                                            @if($order->product_id == 'BTC-EUR')
                                            {{ number_format($order->coinprice + config('coinbase.btc_spread'),2) }}
                                            @endif
                                            @if($order->product_id == 'ETH-EUR')
                                            {{ number_format($order->coinprice + config('coinbase.eth_spread'),2) }}
                                            @endif
                                        </span>
                                        @endif
                                    </td>
                                    <td><span id="profit{{ $order->id }}" class='label'></span></td>
                                    <td>&euro; {{ $order->fee > 0.0 ? $order->fee : '0.00' }}</td>
                                    <td>&euro; {{ $order->soldfor > 0.0 ? number_format($order->soldfor,8) : '0.00' }}</td>
                                    <td>@if($order->profit != 0.0)
                                        @if($order->profit > 0.0)
                                            <span class='label label-success'>
                                        @else
                                            <span class='label label-danger'>
                                        @endif
                                            &euro; {!! number_format($order->profit,8)!!}
                                            </span>
                                        @else
                                        &euro;  0.00
                                        @endif

                                    </td>

                                    <td><span id="ordercoinclosed{{ $order->id }}">{{ $order->filled }}</a></td>
                                    <td>
                                        <a href="{{ route('orders.edit', $order) }}" class="btn btn-default">Bewerken</a>
                                    </td>
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

                <div class='row'>
                    <div class='col-md-12'>
                        {{ $orders->links() }}
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
