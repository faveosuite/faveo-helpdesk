<?php

namespace App\Http\Controllers\Agent\kb;

// Controllers
use App\Http\Controllers\Controller;
use App\Http\Requests\kb\ArticleRequest;
// Requests
use App\Http\Requests\kb\ArticleUpdate;
use App\Model\kb\Article;
// Models
use App\Model\kb\Category;
use App\Model\kb\Comment;
use App\Model\kb\Relationship;
use App\Model\kb\Settings;
use Auth;
// Classes
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Lang;
use Redirect;
use Yajra\DataTables\Facades\DataTables;

/**
 * ArticleController
 * This controller is used to CRUD Articles.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class ArticleController extends Controller
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
        SettingsController::language();
    }

    public function test()
    {
        //$table = $this->setDatatable();
        return view('themes.default1.agent.kb.article.test');
    }

    /**
     * Fetching all the list of articles in a chumper datatable format.
     *
     * @return type void
     */
    public function getData()
    {
        $article = new Article();
        $articles = $article
                ->select('id', 'name', 'description', 'publish_time', 'slug')
                ->orderBy('publish_time', 'desc')
                ->get();

        return DataTables::of($articles)
                        ->addColumn('name', function ($model) {
                            $name = Str::limit($model->name, 20, '...');

                            return "<p title=$model->name>$name</p>";
                        })
                        /* add column Created */
                        ->addColumn('publish_time', function ($model) {
                            $t = $model->publish_time;

                            return $t;
                        })
                        /* add column action */
                        ->addColumn('Actions', function ($model) {
                            /* here are all the action buttons and modal popup to delete articles with confirmations */
                            return '<span  data-toggle="modal" data-target="#deletearticle'.$model->id.'"><a href="#" ><button class="btn btn-danger btn-xs"></a> '.\Lang::get('lang.delete').' </button></span>&nbsp;<a href='.url("article/$model->id/edit").' class="btn btn-warning btn-xs">'.\Lang::get('lang.edit').'</a>&nbsp;<a href='.url("show/$model->slug").' class="btn btn-primary btn-xs">'.\Lang::get('lang.view').'</a>
				<div class="modal fade" id="deletearticle'.$model->id.'">
        			<div class="modal-dialog">
            			<div class="modal-content">
                			<div class="modal-header">
                                <h4 class="modal-title">'.Lang::get('lang.delete').'</h4>
                    			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                			</div>
                			<div class="modal-body">
                				<span>'.Lang::get('lang.are_you_sure_you_want_to_delete').'</span>&nbsp; <b> '.$model->name.' </b> ?
                			</div>
                			<div class="modal-footer justify-content-between">
                    			<button type="button" class="btn btn-default" data-dismiss="modal" id="dismis2">'.Lang::get('lang.close').'</button>
                    			<a href='.url("article/delete/$model->slug").'><button class="btn btn-danger">'.Lang::get('lang.delete').'</button></a>
                			</div>
            			</div>
        			</div>
    			</div>';
                        })
                        ->rawColumns(['name', 'Actions'])
                        ->make(true);
    }

    /**
     * List of Articles.
     *
     * @return type view
     */
    public function index()
    {
        /* show article list */
        try {
            return view('themes.default1.agent.kb.article.index');
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Creating a Article.
     *
     * @param type Category $category
     *
     * @return type view
     */
    public function create(Category $category)
    {
        /* get the attributes of the category */
        $category = $category->pluck('id', 'name');

        /* get the create page  */
        try {
            return view('themes.default1.agent.kb.article.create', compact('category'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Insert the values to the article.
     *
     * @param type Article        $article
     * @param type ArticleRequest $request
     *
     * @return type redirect
     */
    public function store(Article $article, ArticleRequest $request)
    {
        // requesting the values to store article data
        $publishTime = $request->input('year').'-'.$request->input('month').'-'.$request->input('day').' '.$request->input('hour').':'.$request->input('minute').':00';

        $sl = $request->input('name');
        $slug = Str::slug($sl, '-');
        $article->slug = $slug;
        $article->publish_time = $publishTime;
        $article->fill($request->except('created_at', 'slug'))->save();
        // creating article category relationship
        $requests = $request->input('category_id');
        $id = $article->id;

        foreach ($requests as $req) {
            DB::insert('insert into kb_article_relationship (category_id, article_id) values (?,?)', [$req, $id]);
        }

        /* insert the values to the article table  */
        try {
            $article->fill($request->except('slug'))->save();

            return redirect('article')->with('success', Lang::get('lang.article_inserted_successfully'));
        } catch (Exception $e) {
            return redirect('article')->with('fails', Lang::get('lang.article_not_inserted').'<li>'.$e->getMessage().'</li>');
        }
    }

    /**
     * Edit an Article by id.
     *
     * @param type Integer      $id
     * @param type Article      $article
     * @param type Relationship $relation
     * @param type Category     $category
     *
     * @return view
     */
    public function edit($slug)
    {
        $article = new Article();
        $relation = new Relationship();
        $category = new Category();
        $aid = $article->where('id', $slug)->first();
        $id = $aid->id;
        /* define the selected fields */
        $assign = $relation->where('article_id', $id)->pluck('category_id');
        /* get the attributes of the category */
        $category = $category->pluck('id', 'name');
        /* get the selected article and display it at edit page  */
        /* Get the selected article with id */
        $article = $article->whereId($id)->first();

        /* send to the edit page */
        try {
            return view('themes.default1.agent.kb.article.edit', compact('assign', 'article', 'category'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Update an Artile by id.
     *
     * @param type Integer        $id
     * @param type Article        $article
     * @param type Relationship   $relation
     * @param type ArticleRequest $request
     *
     * @return Response
     */
    public function update($slug, ArticleUpdate $request)
    {
        $article = new Article();
        $relation = new Relationship();
        $aid = $article->where('id', $slug)->first();
        $publishTime = $request->input('year').'-'.$request->input('month').'-'.$request->input('day').' '.$request->input('hour').':'.$request->input('minute').':00';

        $id = $aid->id;
        $sl = $request->input('slug');
        $slug = Str::slug($sl, '-');
        // dd($slug);

        $article->slug = $slug;
        /* get the attribute of relation table where id==$id */
        $relation = $relation->where('article_id', $id);
        $relation->delete();
        /* get the request of the current articles */
        $article = $article->whereId($id)->first();
        $requests = $request->input('category_id');
        $id = $article->id;
        foreach ($requests as $req) {
            DB::insert('insert into kb_article_relationship (category_id, article_id) values (?,?)', [$req, $id]);
        }

        /* update the value to the table */
        try {
            $article->fill($request->all())->save();
            $article->slug = $slug;
            $article->publish_time = $publishTime;
            $article->save();

            return redirect()->back()->with('success', Lang::get('lang.article_updated_successfully'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', Lang::get('lang.article_not_updated').'<li>'.$e->getMessage().'</li>');
        }
    }

    /**
     * Delete an Agent by id.
     *
     * @param type $id
     * @param type Article $article
     *
     * @return Response
     */
    public function destroy($slug, Article $article, Relationship $relation, Comment $comment)
    {
        /* delete the selected article from the table */
        $article = $article->where('slug', $slug)->first(); //get the selected article via id
        $id = $article->id;
        $comments = $comment->where('article_id', $id)->get();
        if ($comments) {
            foreach ($comments as $comment) {
                $comment->delete();
            }
        }
        // deleting relationship
        $relation = $relation->where('article_id', $id)->first();
        if ($relation) {
            $relation->delete();
        }
        if ($article) {
            if ($article->delete()) {//true:redirect to index page with success message
                return redirect('article')->with('success', Lang::get('lang.article_deleted_successfully'));
            } else { //redirect to index page with fails message
                return redirect('article')->with('fails', Lang::get('lang.article_not_deleted'));
            }
        } else {
            return redirect('article')->with('fails', Lang::get('lang.article_can_not_deleted'));
        }
    }

    /**
     * user time zone
     * fetching timezone.
     *
     * @param type $utc
     *
     * @return type
     */
    public static function usertimezone($utc)
    {
        $user = Auth::user();
        $tz = $user->timezone;
        $set = Settings::whereId('1')->first();
        $format = $set->dateformat;
        //$utc = date('M d Y h:i:s A');
        date_default_timezone_set($tz);
        $offset = date('Z', strtotime($utc));
        $date = date($format, strtotime($utc) + $offset);
        echo $date;
    }
}
