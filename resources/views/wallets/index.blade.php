@extends('layouts.app')

@section('content')
    <div class="container">

        <div class="well">
            <div class="row">
            Heartbeat bot: <botheartbeat></botheartbeat>
            </div>
            <div class="row">
            currentprices: <botcurrentprice></botcurrentprice>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Accounts</div>
                    <div class="panel-body">
                        <portfolio></portfolio>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Positions</div>
                    <div class="panel-body">
                        <positions></positions>
                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Trailing</div>
                    <div class="panel-body">
                        <trailingpositions></trailingpositions>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Orders</div>
                    <div class="panel-body">
                        <orders></orders>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection