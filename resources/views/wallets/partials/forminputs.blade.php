<input type='hidden' name='walletname' value='{{ $wallet->wallet}}'>
<div class="form-group">
    <label before="currency" class="col-sm-2 control-label"> Bedrag</label>

    <div class="input-group col-md-4">
        <div class="input-group-addon">&euro;</div>
        <input type="text" class="form-control" name="currency" id="currency" placeholder="00.00">
    </div>

</div>

<div class="form-group">
    <label before="Fee" class="col-sm-2 control-label">Fee</label>

    <div class="input-group col-md-4">
        <div class="input-group-addon">&euro;</div>
        <input type="text" class="form-control" name="fee" id="fee" placeholder="00.00">
    </div>
</div>
