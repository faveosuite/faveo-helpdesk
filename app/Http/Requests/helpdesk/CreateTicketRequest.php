<?php

namespace App\Http\Requests\helpdesk;

use App\Http\Requests\Request;
use App\Model\helpdesk\Settings\CommonSettings;

/**
 * CreateTicketRequest.
 *
 * @author  Ladybird <info@ladybirdweb.com>
 */
class CreateTicketRequest extends Request
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

    public function wantsJson()
    {
        if (in_array('api', $this->segments())) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $check = $this->check(new CommonSettings());
        if ($check != 0) {
            return $check;
        }

        return [
            'email'      => 'required|email|max:60',
            'first_name' => 'required|min:3|max:40',
            'helptopic'  => 'required',
            // 'dept' => 'required',
            'sla'      => 'required',
            'subject'  => 'required|min:5',
            'body'     => 'required|min:10',
            'priority' => 'required',
            'phone'    => 'nullable|digits_between:4,15',
            'mobile'   => 'nullable|digits_between:4,15',
        ];
    }

    /**
     *@category Funcion to set rule if send opt is enabled
     *
     * @param object $settings (instance of Model common settings)
     *
     *@author manish.verma@ladybirdweb.com
     *
     * @return array|int
     */
    public function check($settings)
    {
        $settings = $settings->select('status')->where('option_name', '=', 'send_otp')->first();
        $email_mandatory = CommonSettings::select('status')->where('option_name', '=', 'email_mandatory')->first();
        // dd($settings->status, $email_mandatory->status);
        if (($settings->status == '1' || $settings->status == 1) && ($email_mandatory->status == '1' || $email_mandatory->status == 1)) {
            return [
                'email'      => 'required|email|max:60',
                'first_name' => 'required|min:3|max:40',
                'helptopic'  => 'required',
                // 'dept' => 'required',
                'sla'      => 'required',
                'subject'  => 'required|min:5',
                'body'     => 'required|min:10',
                'priority' => 'required',
                'phone'    => 'nullable|digits_between:4,15',
                'code'     => 'required',
                'mobile'   => 'required|digits_between:4,15',
            ];
        } elseif (($settings->status == '0' || $settings->status == 0) && ($email_mandatory->status == '1' || $email_mandatory->status == 1)) {
            return 0;
        } elseif (($settings->status == '0' || $settings->status == 0) && ($email_mandatory->status == '0' || $email_mandatory->status == 0)) {
            $rule = $this->onlyMobleRequired();

            return $rule;
        } elseif (($settings->status == '1' || $settings->status == 1) && ($email_mandatory->status == '0' || $email_mandatory->status == 0)) {
            $rule = $this->onlyMobleRequired();

            return $rule;
        } else {
            return 0;
        }
    }

    /**
     *@category function to make only moble required rule
     *
     *@param null
     *
     * @return array
     */
    public function onlyMobleRequired()
    {
        return [
            'email'      => 'email|max:60',
            'first_name' => 'required|min:3|max:40',
            'helptopic'  => 'required',
            // 'dept' => 'required',
            'sla'      => 'required',
            'subject'  => 'required|min:5',
            'body'     => 'required|min:10',
            'priority' => 'required',
            'phone'    => 'nullable|digits_between:4,15',
            'code'     => 'required',
            'mobile'   => 'required|digits_between:4,15',
        ];
    }
}
