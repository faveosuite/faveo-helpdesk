<?php

/*
 |-------------------------------------------------------------------------------------
 |      Brazilian Portuguese translation [for version 1.10.6]
 |-------------------------------------------------------------------------------------
 |      Author      : Thiago Alves Cavalcante
 |      Email       : thiagoalcav@gmail.com
 |  Last translated : 13-12-2019
 |********************************************************************************
 |      Details of new words added for translation
 |--------------------------------------------------------------------------------
 |        Added by  :
 |     translated   : [NO/YES]
 |      Added on    :
 */

return [

    /*
      |--------------------------------------------------------------------------
      | Validation Language Lines
      |--------------------------------------------------------------------------
      |
      | The following language lines contain the default error messages used by
      | the validator class. Some of these rules have multiple versions such
      | as the size rules. Feel free to tweak each of these messages here.
      |
     */

    'accepted'   => 'A opção deverá ser aceita.',
    'active_url' => 'A informação não é uma URL válida',
    'after'      => 'A informação deve ser uma data superior a :date.',
    'alpha'      => 'A informação só pode conter letras.',
    'alpha_dash' => 'A informação só pode conter letras, números e traços',
    'alpha_num'  => 'A informação só pode conter letras e números.',
    'array'      => 'A informação deverá ser em ordem.',
    'before'     => 'A informação deverá ser uma data inferior a :date.',
    'between'    => [
        'numeric' => 'A informação deverá estar entre :min e :max.',
        'string'  => 'A informação deverá estar entre :min e :max characters.',
        'file'    => 'A informação deverá estar entre :min e :max kilobytes.',
        'array'   => 'A informação deverá estar entre :min e :max items.',
    ],
    'boolean'        => 'A informação deverá ser verdadeira ou falsa.',
    'confirmed'      => 'A informação não está correta.',
    'date'           => 'A informação não é uma data válida.',
    'date_format'    => 'A informação não corresponde ao formato :format.',
    'different'      => 'A informação :other deverá ser diferente.',
    'digits'         => 'A informação deve ser :digits dígitos.',
    'digits_between' => 'A informação deve estar entre :min e :max dígitos.',
    'email'          => 'A informação deverá ser um e-mail válido.',
    'filled'         => 'A informação é inválida.',
    'exists'         => 'A informação :attribute é inválida.',
    'image'          => 'O arquivo deve ser uma imagem.',
    'in'             => 'A informação selecionada :attribute é inválida.',
    'integer'        => 'O informação deve ser um número inteiro.',
    'ip'             => 'O número deve ser um endereço de IP válido.',
    'max'            => [
        'numeric' => 'O valor não pode ser maior a :max.',
        'file'    => 'O arquivo não pode ser maior a :max kilobytes.',
        'string'  => 'A informação não pode ser maior a :max caracteres.',
        'array'   => 'A informação não pode ter mais do que :max itens.',
    ],
    'mimes' => 'O :attribute deve ser um arquivo do tipo: :values.',
    'min'   => [
        'numeric' => 'O valor deve ser de pelo menos :min.',
        'file'    => 'O arquivo deve ser de pelo menos :min kilobytes.',
        'string'  => 'A informação deve ser de pelo menos :min caracteres.',
        'array'   => 'A informação deve ter pelo menos :min itens.',
    ],
    'not_in'               => 'A informação selecionada é inválida.',
    'numeric'              => 'A informação deverá ser um número.',
    'regex'                => 'O formato está inválido.',
    'required'             => 'O campo é obrigatório.',
    'required_if'          => 'O campo é obrigatório quando :other é :value.',
    'required_with'        => 'O campo é obrigatório quando for informado os dados.',
    'required_with_all'    => 'O campo é obrigatório quando os valores estiverem presentes.',
    'required_without'     => 'O campo é obrigatório quando os valores não estiverem presentes',
    'required_without_all' => 'O campo é obrigatório quando nenhum dos valores estiver presentes',
    'same'                 => 'Os campos deverão estar iguais.',
    'size'                 => [
        'numeric' => 'O valor deve ser :size.',
        'file'    => 'O arquivo deve ser :size kilobytes.',
        'string'  => 'O campo :attribute deve ser :size caracteres.',
        'array'   => 'O campo deve conter :size itens.',
    ],
    'unique'   => 'O campo já está preenchido.',
    'url'      => 'Formato inválido.',
    'timezone' => 'O campo deve ser um fuso horário válido.',
    /*
      |--------------------------------------------------------------------------
      | Custom Validation Language Lines
      |--------------------------------------------------------------------------
      |
      | Here you may specify custom validation messages for attributes using the
      | convention "attribute.rule" to name the lines. This makes it quick to
      | specify a specific custom language line for a given attribute rule.
      |
     */
    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],
    /*
      |--------------------------------------------------------------------------
      | Custom Validation Attributes
      |--------------------------------------------------------------------------
      |
      | The following language lines are used to swap attribute place-holders
      | with something more reader friendly such as E-Mail Address instead
      | of "email". This simply helps us make messages a little cleaner.
      |
     */
    'attributes' => [],
];
