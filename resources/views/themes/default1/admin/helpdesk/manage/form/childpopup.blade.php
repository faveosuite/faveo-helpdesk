<a href="#child"  data-bs-toggle="modal" class="btn btn-primary" data-bs-target="#child{{$field->id}}" style="margin-top: 29px;">
    <i class="fa-solid fa-plus"></i> Add Child
</a>
<div class="modal fade" id="child{{$field->id}}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Child</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                {!! html()->modelForm($field, 'POST', url('forms/field/'.$field->id.'/child'))->open() !!}
            </div>
            <div class="modal-body">
                <!-- Form  -->
                <div class="row">
                    @forelse($field->values()->get() as $value)
                    <div class="col-md-12">
                        <b>{{$value->field_value}}</b>
                         {!! html()->select($value->id, [''=>'Select','Forms'=>$select_forms], $value->childId())->class('form-control') !!}
                    </div>
                    @empty 
                    <div class="col-md-12">
                        <p>No values</p>
                    </div>
                    @endforelse
                </div>

            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" id="close" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <input type="submit" class="btn btn-primary" value="{{Lang::get('lang.save')}}">
                {!! html()->closeModelForm() !!}
            </div>
            <!-- /Form -->
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
