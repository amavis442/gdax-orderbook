<div class="row">
    <div class="col-md-10 col-md-offset-2">
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
                
                <div class="row"><br/></div>
                
                <div class='row'>
                    <div class='col-md-12'>
                        <div class="pull-right">
                            <form method='post' action="{{ route('wallets.order.search') }}" class="form-inline">
                                {{  csrf_field() }}
                                <input type="hidden" name="tab" value="{{ $tab }}">
                                <div class="form-group">
                                    <input type="text" name="searchstr" id="searchstr" class="form-control">
                                </div>
                                <div class="form-group">
                                    <select name="searchmode" id="searchmode" class="form-control">
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

                <div class='row'>
                    <div class='col-md-12'>
                        {{ $orders->links() }}
                    </div>
                </div>



                <div class='row'>
                    <div class='col-md-12'>
                        <ul class="nav nav-tabs">
                            <li @if(!isset($tab) || $tab == 1) class="active" @endif><a href="{{ route('wallets.index.tab',1) }}">All</a></li>
                            <li @if(isset($tab) && $tab == 2) class="active" @endif><a href="{{ route('wallets.index.tab',2) }}">BTC</a></li>
                            <li @if(isset($tab) && $tab == 3) class="active" @endif><a href="{{ route('wallets.index.tab',3) }}">ETH</a></li>
                            <li @if(isset($tab) && $tab == 4) class="active" @endif><a href="{{ route('wallets.index.tab',4) }}">LTC</a></li>
                        </ul>


                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <td>#</td>
                                    <th>Datum</th>
                                    <th>Wallet</th>
                                    <th>Trade</th>
                                    <th>Hoeveelheid</th>
                                    <th>Handelsprijs</th>
                                    <th>Koers munt</th>
                                    <th>Kosten</th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                <tr class='orders'>
                                    <td class="orderid">{{ $order->id }}</td>
                                    <td>{{ $order->created_at->format('d-m-Y') }}</td>
                                    <td><span id="orderwallet{{ $order->id }}">{{ $order->wallet }}</span></td>
                                    <td><span id="ordertrade{{ $order->id }}">{{ $order->trade }}</span></td>
                                    <td><span id="orderamount{{ $order->id }}">{{ $order->amount }}</span></td>
                                    <td>&euro; {{ $order->tradeprice }}</td>
                                    <td>&euro; <span id="ordercoinprice{{ $order->id }}">{{ $order->coinprice }}</span> <span id="profit{{ $order->id }}"></span></td>
                                    <td>&euro; {{ $order->fee > 0.0 ? $order->fee : '0.00' }}</td>
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
            </div>
        </div>
    </div>
</div>
