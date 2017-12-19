@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>

                <div class="panel-body">
                    <form method="post" action="{{ route('wallets.store') }}">
                        {{ csrf_field() }}
                        
                        @include('wallets.partials.forminputs')

                        <div class="form-group">
                            <label before="transfertype">Transfertype</label>
                            <div class="col-md-4">
                                <select class="form-control" name="action">
                                    <option value="DEPOSIT" @if($action == 'DEPOSIT')selected @endif>Deposit</option>
                                    <option value="WITHDRAW" @if($action == 'WITHDRAW')selected @endif>Deposit</option>
                                </select>
                            </div>
                        </div>
                        <input type='submit' name='btnSubmit' value='Opslaan' class='btn btn-default'>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


