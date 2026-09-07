<?php

namespace App\Http\Requests\helpdesk;

use App\Http\Requests\Request;
use Lang;

/**
 * Sys_userRequest.
 *
 * @author  Ladybird <info@ladybirdweb.com>
 */
class WorkflowUpdateRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'            => 'required|max:50',
            'execution_order' => 'required',
            'target_channel'  => 'required',
            'rule'            => 'required|array',
            'rule.*.a'        => 'required',
            'rule.*.b'        => 'required',
            'rule.*.c'        => 'required',
            'action'          => 'required|array',
            'action.*.a'      => 'required',
            'action.*.b'      => 'required',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'rule.*.a.required'   => Lang::get('lang.workflow_rule_incomplete'),
            'rule.*.b.required'   => Lang::get('lang.workflow_rule_incomplete'),
            'rule.*.c.required'   => Lang::get('lang.workflow_rule_incomplete'),
            'action.*.a.required' => Lang::get('lang.workflow_action_incomplete'),
            'action.*.b.required' => Lang::get('lang.workflow_action_incomplete'),
        ];
    }
}
