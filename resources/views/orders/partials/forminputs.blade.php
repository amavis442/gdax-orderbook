<div class="form-group">
    <label before="amount"  class="col-sm-2 control-label">Hoeveelheid</label>
    <div class="col-md-4">
        <input type="text" class="form-control" name="amount" id="amount" placeholder="00.00">
    </div>
</div>



<div class="form-group">
    <label before="coinprice"  class="col-sm-2 control-label">Koers munt</label>

    <div class="input-group col-md-4">
        <div class="input-group-addon">&euro;</div>
        <input type="text" class="form-control" name="coinprice" id="coinprice" placeholder="00.00">

    </div>
</div>
<div class="form-group">
<div class="col-md-offset-2 col-md-6">
    <button id="calcPrice" name="calcPrice" class="form-control btn btn-default">Bereken handelsprijs</button>
</div>
</div>

<div class="form-group">
    <label before="tradeprice"  class="col-sm-2 control-label">Handelprijs <small>(normaal Hoeveelheid * Koers voor limit orders)</small> </label>

    <div class="input-group col-md-4">
        <div class="input-group-addon">&euro;</div>
        <input type="text" class="form-control" name="tradeprice" id="tradeprice" placeholder="00.00">
    </div>
</div>

<div class="form-group">
    <label before="Fee"  class="col-sm-2 control-label">Kosten</label>

    <div class="input-group col-md-4">
        <div class="input-group-addon">&euro;</div>
        <input type="text" class="form-control" name="fee" id="fee" placeholder="00.00">
    </div>
</div>

@push('javascript')
<script>
    $(document).ready(function() {
        $('#calcPrice').on('click', function() {
            var amount = $('#amount').val();
            var coinprice = $('#coinprice').val();
        
            var tradeprice = amount * coinprice;
            
            $('#tradeprice').val(tradeprice);
        
            return false;
        });
    });
</script>
@endpush
