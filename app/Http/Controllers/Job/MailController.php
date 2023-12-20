<?php

namespace App\Http\Controllers\Job;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Input;

class MailController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'roles']);
    }

    public function serviceForm(Request $request)
    {
        $serviceid = $request->input('service');

        $short_name = '';
        $mail_services = new \App\Model\MailJob\MailService();
        $mail_service = $mail_services->find($serviceid);
        if ($mail_service) {
            $short_name = $mail_service->short_name;
        }
        $form = $this->getServiceForm($short_name);

        return $form;
    }

    public function form($label, $name, $class)
    {
        $mailid = Input::get('emailid');
        if ($mailid) {
            $emails = new \App\Model\helpdesk\Email\Emails();
            $email = $emails->find($mailid);
            $form = "<div class='".$class."'>".html()->label($label, $name)."<span class='text-red'> *</span>".
                html()->text($name, $email->getExtraField($name))->class('form-control').'</div>';
        } else {
            $form = "<div class='".$class."'>".html()->label($label, $name)."<span class='text-red'> *</span>".
                html()->text($name, null)->class('form-control').'</div>';
        }

        return $form;
    }

    public function getServiceForm($short_name)
    {
        $form = '';

        try {
            switch ($short_name) {
                case 'smtp':
                    return $form;
                case 'mail':
                    return $form;
                case 'sendmail':
                    return $form;
                case 'mailgun':
                    $form .= "<div class='row'>".$this->form('Domain', 'domain', 'col-md-6 form-group');
                    $form .= $this->form('Secret Key', 'secret', 'col-md-6 form-group').'</div>';

                    return $form;
                case 'mandrill':
                    $form .= "<div class='row'>".$this->form('Secret Key', 'secret', 'col-md-6 form-group').'</div>';

                    return $form;
                case 'log':
                    return $form;
                case 'ses':
                    $form .= "<div class='row'>".$this->form('Key', 'key', 'col-md-6 form-group');
                    $form .= $this->form('Secret Key', 'secret', 'col-md-6 form-group').$this->form('Region', 'region', 'col-md-6 form-group').'</div>';

                    return $form;
                default:
                    return $form;
            }
        } catch (Exception $ex) {
            dd($ex);
        }
    }
}
