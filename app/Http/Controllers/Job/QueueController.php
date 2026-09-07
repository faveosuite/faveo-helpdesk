<?php

namespace App\Http\Controllers\Job;

use App\Http\Controllers\Controller;
use App\Http\Requests\helpdesk\Queue\QueueRequest;
use App\Model\MailJob\FaveoQueue;
use App\Model\MailJob\QueueService;
use Exception;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'roles']);
    }

    public function index()
    {
        try {
            $queue = new QueueService();
            $queues = $queue->select('id', 'name', 'status')->get();

            return view('themes.default1.admin.helpdesk.queue.index', compact('queues'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $queues = new QueueService();
            $queue = $queues->find($id);
            if (!$queue) {
                throw new Exception('Sorry we can not find your request');
            }

            return view('themes.default1.admin.helpdesk.queue.edit', compact('queue'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function update($id, QueueRequest $request)
    {
        try {
            $values = $request->except('_token');
            $queues = new QueueService();
            $queue = $queues->find($id);
            if (!$queue) {
                throw new Exception('Sorry we can not find your request');
            }
            $setting = new FaveoQueue();
            $settings = $setting->where('service_id', $id)->get();
            if ($settings->count() > 0) {
                foreach ($settings as $set) {
                    $set->delete();
                }
            }
            if (count($values) > 0) {
                foreach ($values as $key => $value) {
                    $setting->create([
                        'service_id' => $id,
                        'key'        => $key,
                        'value'      => $value,
                    ]);
                }
            }

            return redirect()->back()->with('success', trans('lang.updated'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function activate($id)
    {
        try {
            $queues = new QueueService();
            $queue = $queues->find($id);
            $active_queue = $queues->where('status', 1)->first();
            if (!$queue) {
                throw new Exception('Sorry we can not find your request');
            }
            if ($queue->isActivate() == false && $id != 1 && $id != 2) {
                throw new Exception("To activate $queue->name , Please configure it first");
            }
            if ($active_queue) {
                $active_queue->status = 0;
                $active_queue->save();
            }
            $queue->status = 1;
            $queue->save();

            return redirect()->back()->with('success', trans('lang.activated'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getForm(Request $request)
    {
        $queueid = $request->input('queueid');
        $form = $this->getFormById($queueid);

        return $form;
    }

    public function getShortNameById($queueid)
    {
        $short = '';
        $queues = new QueueService();
        $queue = $queues->find($queueid);
        if ($queue) {
            $short = $queue->short_name;
        }

        return $short;
    }

    public function getIdByShortName($short)
    {
        $id = '';
        $queues = new QueueService();
        $queue = $queues->where('short_name', $short)->first();
        if ($queue) {
            $id = $queue->id;
        }

        return $id;
    }

    public function getFormById($id)
    {
        $short = $this->getShortNameById($id);
        $form = '';
        switch ($short) {
            case 'beanstalkd':
                $form .= "<div class='row'>";
                $form .= $this->form($short, 'Driver', 'driver', 'col-md-6 form-group', 'beanstalkd');
                $form .= $this->form($short, 'Host', 'host', 'col-md-6 form-group', 'localhost');
                $form .= $this->form($short, 'Queue', 'queue', 'col-md-6 form-group', 'default');
                $form .= '</div>';

                return $form;
            case 'sqs':
                $form .= "<div class='row'>";
                $form .= $this->form($short, 'Driver', 'driver', 'col-md-6 form-group', 'sqs');
                $form .= $this->form($short, 'Key', 'key', 'col-md-6 form-group', 'your-public-key');
                $form .= $this->form($short, 'Secret', 'secret', 'col-md-6 form-group', 'your-queue-url');
                $form .= $this->form($short, 'Region', 'region', 'col-md-6 form-group', 'us-east-1');
                $form .= '</div>';

                return $form;
            case 'iron':
                $form .= "<div class='row'>";
                $form .= $this->form($short, 'Driver', 'driver', 'col-md-6 form-group', 'iron');
                $form .= $this->form($short, 'Host', 'host', 'col-md-6 form-group', 'mq-aws-us-east-1.iron.io');
                $form .= $this->form($short, 'Token', 'token', 'col-md-6 form-group', 'your-token');
                $form .= $this->form($short, 'Project', 'project', 'col-md-6 form-group', 'your-project-id');
                $form .= $this->form($short, 'Queue', 'queue', 'col-md-6 form-group', 'your-queue-name');
                $form .= '</div>';

                return $form;
            case 'redis':
                $form .= "<div class='row'>";
                $form .= $this->form($short, 'Driver', 'driver', 'col-md-6 form-group', 'redis');
                $form .= $this->form($short, 'Queue', 'queue', 'col-md-6 form-group', 'default');
                $form .= '</div>';

                return $form;
            default:
                return $form;
        }
    }

    public function form($short, $label, $name, $class, $placeholder = '')
    {
        $queueid = $this->getIdByShortName($short);
        $queues = new QueueService();
        $queue = $queues->find($queueid);
        if ($queue) {
            $form = "<div class='".$class."'>".html()->label($label, $name)."<span class='text-red'> *</span>".
                    html()->text($name, $queue->getExtraField($name))->class('form-control')->placeholder($placeholder).'</div>';
        } else {
            $form = "<div class='".$class."'>".html()->label($label, $name)."<span class='text-red'> *</span>".
                    html()->text($name, null)->class('form-control')->placeholder($placeholder).'</div>';
        }

        return $form;
    }
}
