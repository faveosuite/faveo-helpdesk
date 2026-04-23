<?php

/*
 |============================================================================
 |  Faveo Spanish Translation
 |============================================================================
 |  Author details
 | Name: Dionis Hernandez
 | email: dionisus.78@gmail.com
 | ---------------------------------------------------------------------
 | Collaborators
 |
 |----------------------------------------------------------------------
 |  Last updates : 26-1-2016
 |
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

    'accepted'   => 'El atributo: debe ser acceptado.',
    'active_url' => 'El atributo: no es un atributo valid URL.',
    'after'      => 'El atributo: debe ser a date after :date.',
    'alpha'      => 'El atributo: sólo puede contener letters.',
    'alpha_dash' => 'El atributo: sólo puede contener letters, numbers, and dashes.',
    'alpha_num'  => 'El atributo: sólo puede contener letters and numbers.',
    'array'      => 'El atributo: debe ser an array.',
    'before'     => 'El atributo: debe ser a date before :date.',
    'between'    => [
        'numeric' => 'El atributo: debe ser between :min and :max.',
        'file'    => 'El atributo: debe ser between :min and :max kilobytes.',
        'string'  => 'El atributo: debe ser between :min and :max characters.',
        'array'   => 'El atributo: must have between :min and :max items.',
    ],
    'boolean'        => 'El atributo: field must be true or false.',
    'confirmed'      => 'El atributo: confirmation does not match.',
    'date'           => 'El atributo: no es un atributo valid date.',
    'date_format'    => 'El atributo: does not match the format :format.',
    'different'      => 'El atributo: and :other must be different.',
    'digits'         => 'El atributo: debe ser :digits digits.',
    'digits_between' => 'El atributo: debe ser between :min and :max digits.',
    'email'          => 'El atributo: debe ser a valid email address.',
    'filled'         => 'Se requiere el campo de atributo:.',
    'exists'         => 'The selected :attribute es inválido.',
    'image'          => 'El atributo: debe ser an image.',
    'in'             => 'The selected :attribute es inválido.',
    'integer'        => 'El atributo: debe ser an integer.',
    'ip'             => 'El atributo: debe ser a valid IP address.',
    'max'            => [
        'numeric' => 'El atributo: no puede ser mayor que :max.',
        'file'    => 'El atributo: no puede ser mayor que :max kilobytes.',
        'string'  => 'El atributo: no puede ser mayor que :max characters.',
        'array'   => 'El atributo: no puede tener más de :max items.',
    ],
    'mimes' => 'El atributo: debe ser a file of type: :values.',
    'min'   => [
        'numeric' => 'El atributo: debe ser at least :min.',
        'file'    => 'El atributo: debe ser at least :min kilobytes.',
        'string'  => 'El atributo: debe ser at least :min characters.',
        'array'   => 'El atributo: must have at least :min items.',
    ],
    'not_in'               => 'The selected :attribute es inválido.',
    'numeric'              => 'El atributo: debe ser a number.',
    'regex'                => 'El atributo: format es inválido.',
    'required'             => 'Se requiere el campo de atributo:.',
    'required_if'          => 'Se requiere el campo de atributo: when :other is :value.',
    'required_with'        => 'Se requiere el campo de atributo: Cuando: valores están presentes.',
    'required_with_all'    => 'Se requiere el campo de atributo: Cuando: valores están presentes.',
    'required_without'     => 'Se requiere el campo de atributo: when :values is not present.',
    'required_without_all' => 'Se requiere el campo de atributo: when none of :values are present.',
    'same'                 => 'El atributo: and :other must match.',
    'size'                 => [
        'numeric' => 'El atributo: debe ser :size.',
        'file'    => 'El atributo: debe ser :size kilobytes.',
        'string'  => 'El atributo: debe ser :size characters.',
        'array'   => 'El atributo: must contain :size items.',
    ],
    'unique'   => 'El atributo: has already been taken.',
    'url'      => 'El atributo: format es inválido.',
    'timezone' => 'El atributo: debe ser una zona válida.',
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
            'rule-name' => 'mensaje personalizado',
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
