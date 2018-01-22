@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Settings</div>
                    <div class="panel-body">
                        <form action="{{ route('settings.update', $setting) }}" method="post">
                            {{ csrf_field() }}
                            {{ method_field('PUT') }}

                            <div class="row">
                                <div class="col-lg-6">
                                    <label class="radio-inline">
                                        <input type="radio" name="botactive" id="botactive1" value="0"
                                               @if($setting->botactive == 0) checked @endif> off
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="botactive" id="botactive2" value="1"
                                               @if($setting->botactive == 1) checked @endif> on
                                    </label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="max_orders">Max orders</label>
                                        <input type="text" class="form-control" id="max_orders" name="max_orders"
                                               placeholder="1" value="{{ $setting->max_orders }}">
                                    </div>

                                    <div class="form-group">
                                        <label for="size">Order size</label>
                                        <input type="text" class="form-control" id="size" name="size"
                                               placeholder="0.001"
                                               value="{{ $setting->size }}">
                                    </div>


                                    <div class="form-group">
                                        <label class="sr-only" for="trailingstop">Trailingstop</label>
                                        <div class="input-group">
                                            <div class="input-group-addon">&euro;</div>
                                            <input type="text" class="form-control" id="trailingstop"
                                                   name="trailingstop"
                                                   placeholder="trailingstop" value="{{ $setting->trailingstop }}">
                                            <div class="input-group-addon">.00</div>
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label class="sr-only" for="top">Top</label>
                                        <div class="input-group">
                                            <div class="input-group-addon">&euro;</div>
                                            <input type="text" class="form-control" id="top" name="top"
                                                   placeholder="10000"
                                                   value="{{ $setting->top }}">
                                            <div class="input-group-addon">.00</div>
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label class="sr-only" for="bottom">Bottom</label>
                                        <div class="input-group">
                                            <div class="input-group-addon">&euro;</div>
                                            <input type="text" class="form-control" id="bottom" name="bottom"
                                                   placeholder="15000" value="{{ $setting->bottom }}">
                                            <div class="input-group-addon">.00</div>
                                        </div>
                                    </div>


                                    <button type="submit" class="btn btn-default">Save</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection