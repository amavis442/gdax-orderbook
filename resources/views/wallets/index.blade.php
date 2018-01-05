@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Wallets</div>
                    <div class="panel-body">
                        <portfolio></portfolio>
                        <div id="chart-container"></div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('javascript')
    <script type="text/javascript" src="https://static.cryptowat.ch/assets/scripts/embed.bundle.js"></script>
    <script>
        var chart = new cryptowatch.Embed('gdax', 'btceur', {
            timePeriod: '1d',
            width: 650,
            presetColorScheme: 'delek'
        });
        chart.mount('#chart-container');
    </script>
@endpush