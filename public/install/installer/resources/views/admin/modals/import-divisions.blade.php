<!-- add currency modal -->
<form action="{{route('division.import')}}" method="post" enctype="multipart/form-data">
    @csrf @method('post')
    <div class="modal-body modal-padding-bottom modal-body-overflow-unset">
        <p class="text-capitalize">{{ __('note') }} : {!! __('division_import_msg') !!} :</p>

        <div class="d-flex">
            <p class="text-success warning_txt mt-0"><i class="bx bxs-check-circle"></i></p>
            <p>{{ __('division_delete_msg') }}</p>
        </div>
    </div>
    <div class="modal-footer modal-padding-bottom text-center">
        <button type="submit" class="btn btn-outline-primary">{{ __('confirm') }}</button>
    </div>
</form>
