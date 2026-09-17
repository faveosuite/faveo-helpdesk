<?php

namespace App\Http\Controllers\Agent\kb;

// Controllers
use App\Http\Controllers\Agent\helpdesk\TicketController;
use App\Http\Controllers\Controller;
use App\Http\Requests\kb\CategoryRequest;
// Requests
use App\Http\Requests\kb\CategoryUpdate;
use App\Model\kb\Category;
// Model
use App\Model\kb\Relationship;
use Exception;
// Classes
use Illuminate\Support\Str;
use Lang;
use Redirect;
use Yajra\DataTables\Facades\DataTables;

/**
 * CategoryController
 * This controller is used to CRUD category.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class CategoryController extends Controller
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

    /**
     * Indexing all Category.
     *
     * @param type Category $category
     *
     * @return Response
     */
    public function index()
    {
        /*  get the view of index of the catogorys with all attributes
          of category model */
        try {
            return view('themes.default1.agent.kb.category.index');
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * fetching category list in chumper datatables.
     *
     * @return type chumper datatable
     */
    public function getData()
    {
        return DataTables::of(Category::All())
                        ->addColumn('name', function ($model) {
                            $string = strip_tags($model->name);

                            return Str::limit($string, 20);
                        })
                        /* add column Created */
                        ->addColumn('Created', function ($model) {
                            $t = $model->created_at;

                            return TicketController::usertimezone($t);
                        })
                        /* add column Actions */
                        /* there are action buttons and modal popup to delete a data column */
                        ->addColumn('Actions', function ($model) {
                            return '<span  data-toggle="modal" data-target="#deletecategory'.$model->slug.'"><a href="#" ><button class="btn btn-danger btn-xs"></a>'.\Lang::get('lang.delete').'</button></span>&nbsp;<a href=category/'.$model->id.'/edit class="btn btn-warning btn-xs">'.\Lang::get('lang.edit').'</a>&nbsp;<a href=article-list class="btn btn-primary btn-xs">'.\Lang::get('lang.view').'</a>
				<div class="modal fade" id="deletecategory'.$model->slug.'">
        			<div class="modal-dialog">
            			<div class="modal-content">
                			<div class="modal-header">
                                <h4 class="modal-title">'.Lang::get('lang.delete').'</h4>
                    			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                			</div>
                			<div class="modal-body">
                				<span>'.Lang::get('lang.are_you_sure_you_want_to_delete').'</span>&nbsp;<b>'.$model->name.'</b>
                			</div>
                			<div class="modal-footer justify-content-between">
                    			<button type="button" class="btn btn-default" data-dismiss="modal" id="dismis2">'.Lang::get('lang.close').'</button>
                    			<a href="category/delete/'.$model->id.'"><button class="btn btn-danger">'.Lang::get('lang.delete').'</button></a>
                			</div>
            			</div>
        			</div>
    			</div>';
                        })
                        ->rawColumns(['Actions'])
                        ->make(true);
    }

    /**
     * Create a Category.
     *
     * @param type Category $category
     *
     * @return type view
     */
    public function create(Category $category)
    {
        /* Get the all attributes in the category model */
        $category = $category->pluck('name', 'id')->toArray();

        /* get the view page to create new category with all attributes
          of category model */
        try {
            return view('themes.default1.agent.kb.category.create', compact('category'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * To store the selected category.
     *
     * @param type Category        $category
     * @param type CategoryRequest $request
     *
     * @return type Redirect
     */
    public function store(Category $category, CategoryRequest $request)
    {
        /* Get the whole request from the form and insert into table via model */
        $sl = $request->input('name');
        $slug = Str::slug($sl, '-');
        $category->slug = $slug;

        // send success message to index page
        try {
            $category->fill($request->input())->save();

            return redirect('category')->with('success', Lang::get('lang.category_inserted_successfully'));
        } catch (Exception $e) {
            return redirect('category')->with('fails', Lang::get('lang.category_not_inserted').'<li>'.$e->getMessage().'</li>');
        }
    }

    /**
     * Show the form for editing the specified category.
     *
     * @param type $slug
     * @param type Category $category
     *
     * @return type view
     */
    public function edit($id)
    {
        /* get the atributes of the category model whose id == $id */
        $category = Category::whereId($id)->first();
        $categories = Category::pluck('name', 'id')->toArray();

        /* get the Edit page the selected category via id */
        return view('themes.default1.agent.kb.category.edit', compact('category', 'categories'));
    }

    /**
     * Update the specified Category in storage.
     *
     * @param type $slug
     * @param type Category       $category
     * @param type CategoryUpdate $request
     *
     * @return type redirect
     */
    public function update($id, CategoryRequest $request)
    {
        /* Edit the selected category via id */
        $category = Category::where('id', $id)->first();
        $sl = $request->input('name');
        $slug = Str::slug($sl, '-');

        /* update the values at the table via model according with the request */
        //redirct to index page with success message
        try {
            $category->slug = $slug;
            $category->fill($request->input())->save();

            return redirect('category')->with('success', Lang::get('lang.category_updated_successfully'));
        } catch (Exception $e) {
            //redirect to index with fails message
            return redirect('category')->with('fails', Lang::get('lang.category_not_updated').'<li>'.$e->getMessage().'</li>');
        }
    }

    /**
     * Remove the specified category from storage.
     *
     * @param type $id
     * @param type Category     $category
     * @param type Relationship $relation
     *
     * @return type Redirect
     */
    public function destroy($id, Category $category, Relationship $relation)
    {
        $relation = $relation->where('category_id', $id)->first();
        if ($relation != null) {
            return Redirect::back()->with('fails', Lang::get('lang.category_not_deleted'));
        } else {
            /*  delete the category selected, id == $id */
            $category = $category->whereId($id)->first();

            // redirect to index with success message
            try {
                $category->delete();

                return Redirect::back()->with('success', Lang::get('lang.category_deleted_successfully'));
            } catch (Exception $e) {
                return Redirect::back()->with('fails', Lang::get('lang.category_not_deleted').'<li>'.$e->getMessage().'</li>');
            }
        }
    }
}
