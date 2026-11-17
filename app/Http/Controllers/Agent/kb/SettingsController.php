<?php

namespace App\Http\Controllers\Agent\kb;

// Controllers
use App\Http\Controllers\Agent\helpdesk\TicketController;
use App\Http\Controllers\Controller;
// Request
use App\Http\Requests\kb\SettingsRequests;
use App\Model\helpdesk\Utility\Date_format;
// Model
use App\Model\helpdesk\Utility\Timezones;
use App\Model\kb\Comment;
use App\Model\kb\Settings;
// Classes
use Config;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Input;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Lang;
use Yajra\DataTables\Facades\DataTables;

/**
 * SettingsController
 * This controller is used to perform settings in the setting page of knowledgebase.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class SettingsController extends Controller
{
    /**
     * Create a new controller instance.
     * constructor to check
     * 1. authentication
     * 2. user roles
     * 3. roles must be agent.
     *
     * @return void
     */
    public function __construct()
    {
        // checking authentication
        $this->middleware('auth');
        // checking roles
        $this->middleware('role.agent');
        $this->language();
    }

    /**
     * to get the settings page.
     *
     * @return response
     */
    public function settings(Settings $settings, Timezones $time, Date_format $date)
    {
        /* get the setting where the id == 1 */
        $settings = $settings->whereId('1')->first();
        $time = $time->get();

        //$date = $date->get();
        return view('themes.default1.agent.kb.settings.settings', compact('date', 'settings', 'time'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function postSettings($id, Settings $settings, SettingsRequests $request)
    {
        try {
            /* fetch the values of company request  */
            $settings = $settings->whereId('1')->first();
            if (Input::file('logo')) {
                $name = Input::file('logo')->getClientOriginalName();
                $destinationPath = 'lb-faveo/dist/image';
                $fileName = rand(0000, 9999).'.'.$name;
                //echo $fileName;
                Input::file('logo')->move($destinationPath, $fileName);
                $settings->logo = $fileName;
                //$thDestinationPath = 'dist/th';
                (new ImageManager(new Driver()))
                    ->read($destinationPath.'/'.$fileName)
                    ->scale(300, 300)
                    ->save('lb-faveo/dist/image/'.$fileName);
            }
            if (Input::file('background')) {
                $name = Input::file('background')->getClientOriginalName();
                $destinationPath = 'lb-faveo/dist/image';
                $fileName = rand(0000, 9999).'.'.$name;
                echo $fileName;
                Input::file('background')->move($destinationPath, $fileName);
                $settings->background = $fileName;
                //$thDestinationPath = 'dist/th';
                (new ImageManager(new Driver()))
                    ->read($destinationPath.'/'.$fileName)
                    ->scale(300, 300)
                    ->save('lb-faveo/dist/image/'.$fileName);
            }
            /* Check whether function success or not */
            if ($settings->fill($request->except('logo', 'background'))->save() == true) {
                /* redirect to Index page with Success Message */
                return redirect()->back()->with('success', Lang::get('lang.settings_updated_successfully'));
            } else {
                /* redirect to Index page with Fails Message */
                return redirect()->back()->with('fails', Lang::get('lang.settings_can_not_updated'));
            }
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect()->back()->with('fails', Lang::get('lang.settings_can_not_updated'));
        }
    }

    /**
     * To Moderate the commenting.
     *
     * @param type Comment $comment
     *
     * @return Response
     */
    public function comment(Comment $comment)
    {
        return view('themes.default1.agent.kb.settings.comment');
    }

    /**
     * getdata.
     *
     * @return type
     */
    public function getData()
    {
        // Query builder (not Comment::all()) so paging, search and sort run in SQL.
        $comments = Comment::query()
            ->leftJoin('kb_article', 'kb_article.id', '=', 'kb_comment.article_id')
            ->select(
                'kb_comment.id',
                'kb_comment.name',
                'kb_comment.email',
                'kb_comment.website',
                'kb_comment.comment',
                'kb_comment.status',
                'kb_comment.created_at',
                'kb_article.name as article_name',
                'kb_article.slug as article_slug'
            );

        return DataTables::eloquent($comments)
                        // Author column searches both name and email
                        ->filterColumn('kb_comment.name', function ($query, $keyword) {
                            $query->where(function ($q) use ($keyword) {
                                $q->where('kb_comment.name', 'like', "%{$keyword}%")
                                  ->orWhere('kb_comment.email', 'like', "%{$keyword}%");
                            });
                        })
                        ->addColumn('details', function ($model) {
                            $html = '<div class="fw-semibold">'.e($model->name).'</div>';
                            if ($model->email) {
                                $html .= '<div class="text-muted small text-break">'.e($model->email).'</div>';
                            }
                            if ($model->website) {
                                $html .= '<div class="text-muted small text-break">'.e($model->website).'</div>';
                            }

                            return $html;
                        })
                        ->addColumn('article', function ($model) {
                            if (!$model->article_slug) {
                                return '<span class="text-muted">-</span>';
                            }

                            return '<a href="'.url('show/'.$model->article_slug).'" target="_blank" rel="noopener">'.e($model->article_name).'</a>';
                        })
                        ->editColumn('comment', function ($model) {
                            $created = TicketController::usertimezone(date($model->created_at));

                            return '<div class="text-break" style="white-space:pre-line">'.e($model->comment).'</div>'
                                ."<div class=\"text-muted small mt-1\"><i class=\"fa-regular fa-clock\"></i> {$created}</div>";
                        })
                        ->editColumn('status', function ($model) {
                            return $model->status == 1
                                ? '<span class="badge text-bg-success">'.e(langText('lang.published')).'</span>'
                                : '<span class="badge text-bg-warning">'.e(langText('lang.not_published')).'</span>';
                        })
                        ->addColumn('Actions', function ($model) {
                            $buttons = '';
                            if ($model->status != 1) {
                                $buttons .= '<a href="'.url('published/'.$model->id).'" class="btn btn-success btn-xs me-1">'
                                    .'<i class="fa-solid fa-check"></i> '.e(langText('lang.publish')).'</a>';
                            }
                            $buttons .= '<a href="'.url('comment/delete/'.$model->id).'" class="btn btn-danger btn-xs" data-confirm="'
                                .e(langText('lang.are_you_sure_you_want_to_delete')).'">'
                                .'<i class="fa-solid fa-trash"></i> '.e(langText('lang.delete')).'</a>';

                            return '<div class="text-nowrap">'.$buttons.'</div>';
                        })
                        ->rawColumns(['details', 'article', 'comment', 'status', 'Actions'])
                        ->make(true);
    }

    /**
     * Admin can publish the comment.
     *
     * @param type $id
     * @param type Comment $comment
     *
     * @return bool
     */
    public function publish($id, Comment $comment)
    {
        $comment = $comment->whereId($id)->first();
        $comment->status = 1;
        if ($comment->save()) {
            return redirect('comment')->with('success', $comment->name.'-'.Lang::get('lang.comment_published'));
        } else {
            return redirect('comment')->with('fails', Lang::get('lang.can_not_process'));
        }
    }

    /**
     * delete the comment.
     *
     * @param type $id
     * @param type Comment $comment
     *
     * @return type
     */
    public function delete($id, Comment $comment)
    {
        $comment = $comment->whereId($id)->first();
        if ($comment->delete()) {
            return redirect('comment')->with('success', $comment->name."'s!".Lang::get('lang.comment_deleted'));
        } else {
            return redirect('comment')->with('fails', Lang::get('lang.can_not_process'));
        }
    }

    /**
     * het locale for language.
     *
     * @return type config set
     */
    public static function language()
    {
        // $set = Settings::whereId(1)->first();
        // $lang = $set->language;
        Config::set('app.locale', 'en');
        Config::get('app');
    }
}
