@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Order</div>

                <div class="panel-body">
                    <form method="post" action="{{ route('orders.update', $order) }}" class="form-horizontal" autocomplete="off">
                        {{ csrf_field() }}
                        {{ method_field('PUT') }}
                        
                        <div class="form-group">
                            <label before="trade" class="col-sm-2 control-label">Trade</label>
                            <div class="col-md-4">
                                <select class="form-control" name="trade" id='trade'>
                                    <option value="BUY" @if(isset($order->trade) && $order->trade == 'BUY')selected @endif>BUY</option>
                                    <option value="SELL" @if(isset($order->trade) &&  $order->trade == 'SELL')selected @endif>SELL</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label before="wallet" class="col-sm-2 control-label">Wallet</label>
                            <div class="col-md-4">
                                <select class="form-control" name="wallet">
                                    <option value="BTC" @if(isset($order->wallet) && $order->wallet == 'BTC')selected @endif>BTC</option>
                                    <option value="ETH" @if(isset($order->wallet) &&  $order->wallet == 'ETH')selected @endif>ETH</option>
                                    <option value="LTC" @if(isset($order->wallet) &&  $order->wallet == 'LTC')selected @endif>LTC</option>
                                </select>
                            </div>
                        </div>
                        
                        @include('orders.partials.forminputs')

                        
                        <input type='submit' name='btnSubmit' value='Opslaan' class='btn btn-default'>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
