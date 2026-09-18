   {!! html()->form('POST', route('settings.create'))->attributes(['id' => 'form-add-setting'])->open() !!}
     
    {!! html()->label('Setting Name:', 'setting_name') !!}
    {!! html()->text('setting_name', '')->id('setting_name')->placeholder('Enter Setting Name')->required()->attributes(['maxlength' => 20]) !!}
    {!! html()->label('Setting Value:', 'setting_value') !!}
    {!! html()->text('setting_value', '')->id('setting_value')->placeholder('Enter Setting Value')->required()->attributes(['maxlength' => 255]) !!}
     
    {!! html()->submit('Add Setting')->id('btn-add-setting') !!}
     
    {!! html()->closeModelForm() !!}

