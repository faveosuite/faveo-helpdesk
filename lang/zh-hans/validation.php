<?php

/*
  |--------------------------------------
  |   中文语言包
  |--------------------------------------
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

    'accepted'   => '属性:attribute不被接受.',
    'active_url' => '属性:attribute不是一个有效的url.',
    'after'      => '属性:attribute必须是一个日期.',
    'alpha'      => '属性:attribute只能包含字母.',
    'alpha_dash' => '属性:attribute只能包含数字、字母、下划线.',
    'alpha_num'  => '属性:attribute只能包含数字、字母.',
    'array'      => '属性:attribute必须是个数组.',
    'before'     => '属性:attribute必须是个日期.',
    'between'    => [
        'numeric' => '属性:attribute必须在最小数与最大数之间.',
        'file'    => '属性:attribute必须在最小与最大之间千字节.',
        'string'  => '属性:attribute必须在最小与最大字符之间.',
        'array'   => '属性:attribute必须在最小与最大之间.',
    ],
    'boolean'        => '属性:attribute 必须为true或者false.',
    'confirmed'      => '属性attribut不匹配.',
    'date'           => '属性:attribute 不是个有效日期.',
    'date_format'    => '属性:attribute 格式错误.',
    'different'      => '属性:attribute 与 :other 不能一样.',
    'digits'         => '属性:attribute 必须是数字.',
    'digits_between' => '属性:attribute 必须在最小数:min与最大数:max 之间.',
    'email'          => '属性:attribute 不是一个有效的邮箱地址.',
    'filled'         => '属性:attribute 必填.',
    'exists'         => '属性选择:attribute 无效.',
    'image'          => '属性:attribute 必须是图片.',
    'in'             => '属性选择 :attribute 无效.',
    'integer'        => '属性:attribute 必须是整型.',
    'ip'             => '属性:attribute 必须是有效的ip地址.',
    'max'            => [
        'numeric' => '属性 :attribute 不能超过最大值.',
        'file'    => '属性 :attribute 不能超过最大值.',
        'string'  => '属性 :attribute 不能超过最大值.',
        'array'   => '属性 :attribute 不能超过最大值.',
    ],
    'mimes' => '属性 :attribute 必须是个文件类型: :values.',
    'min'   => [
        'numeric' => '属性 :attribute 不能小于最小值 :min.',
        'file'    => '属性 :attribute 不能小于最小值 :min 字节.',
        'string'  => '属性 :attribute 不能小于最小值 :min 字符.',
        'array'   => '属性 :attribute不能小于最小值 :min 个数.',
    ],
    'not_in'               => '属性选择 :attribute 无效.',
    'numeric'              => '属性:attribute 必须是数字.',
    'regex'                => '属性:attribute 格式无效.',
    'required'             => '属性:attribute 字段必填.',
    'required_if'          => '属性:attribute 字段必填 当 :other 是 :value.',
    'required_with'        => '属性:attribute 字段必填 当 :values 是 存在的.',
    'required_with_all'    => '属性:attribute 字段必填 当 :values 是 存在的.',
    'required_without'     => '属性:attribute 字段必填 当 :values 是 不存在的.',
    'required_without_all' => '属性:attribute 字段必填 当 none of :values 是存在的.',
    'same'                 => '属性:attribute 与 :other 必须一致.',
    'size'                 => [
        'numeric' => '属性 :attribute 必须是 :size.',
        'file'    => '属性 :attribute 必须是 :size 字节.',
        'string'  => '属性 :attribute 必须是 :size 字符.',
        'array'   => '属性 :attribute 必须包含 :size 项.',
    ],
    'unique'   => '属性 :attribute 已经存在.',
    'url'      => '属性 :attribute 格式无效.',
    'timezone' => '属性 :attribute 必须是有效的地区.',
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
