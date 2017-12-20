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
                                <tr>
                                    <td>{{ $order->created_at->format('d-m-Y') }}</td>
                                    <td>{{ $order->wallet }}</td>
                                    <td>{{ $order->trade }}</td>
                                    <td>{{ $order->amount }}</td>
                                    <td>&euro; {{ $order->tradeprice }}</td>
                                    <td>&euro; {{ $order->coinprice }}</td>
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
