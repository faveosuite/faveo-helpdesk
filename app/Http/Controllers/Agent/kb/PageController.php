<?php

namespace App\Http\Controllers\Agent\kb;

use App\Http\Controllers\Agent\helpdesk\TicketController;
use App\Http\Controllers\Controller;
use App\Http\Requests\kb\PageRequest;
// request
use App\Model\kb\Page;
use Exception;
// Model
use Illuminate\Http\Request;
// classes
use Illuminate\Support\Str;
use Lang;
use Yajra\DataTables\Facades\DataTables;

/**
 * PageController
 * This controller is used to CRUD Pages.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class PageController extends Controller
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
    public function __construct(Page $page)
    {
        // checking authentication
        $this->middleware('auth');
        // checking roles
        $this->middleware('role.agent');
        $this->page = $page;
        SettingsController::language();
    }

    /**
     * Display the list of pages.
     *
     * @return type
     */
    public function index()
    {
        $pages = $this->page->paginate(3);
        $pages->setPath('page');

        try {
            return view('themes.default1.agent.kb.pages.index', compact('pages'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * fetching pages list in chumper datatables.
     *
     * @return type
     */
    public function getData()
    {
        return DataTables::of(Page::All())
                        ->addColumn('name', function ($model) {
                            return $model->name;
                        })
                        /* add column Created */
                        ->addColumn('Created', function ($model) {
                            $t = $model->created_at;

                            return TicketController::usertimezone($t);
                        })
                        /* add column Actions */
                        /* there are action buttons and modal popup to delete a data column */
                        ->addColumn('Actions', function ($model) {
                            $delete = Lang::get('lang.delete');

                            return '<button type="button" class="btn btn-danger btn-xs" data-bs-toggle="modal" data-bs-target="#deletepage'.$model->id.'">'.$delete.'</button>&nbsp;<a href="'.url("page/$model->id/edit").'" class="btn btn-warning btn-xs">'.Lang::get('lang.edit').'</a>&nbsp;<a href="'.url("pages/$model->slug").'" class="btn btn-primary btn-xs">'.Lang::get('lang.view').'</a>
                <div class="modal fade" id="deletepage'.$model->id.'" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">'.$delete.'</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                '.Lang::get('lang.are_you_sure_you_want_to_delete').' <b>'.e($model->name).'</b> ?
                            </div>
                            <div class="modal-footer justify-content-between">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'.Lang::get('lang.close').'</button>
                                <a href="'.url("page/delete/$model->id").'" class="btn btn-danger">'.$delete.'</a>
                            </div>
                        </div>
                    </div>
                </div>';
                        })
                        ->rawColumns(['Actions'])
                        ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return type view
     */
    public function create()
    {
        return view('themes.default1.agent.kb.pages.create');
    }

    /**
     * To insert a value to the table Page.
     *
     * @param type Request $request
     *
     * @return type
     */
    public function store(PageRequest $request)
    {
        $sl = $request->input('name');
        $slug = Str::slug($sl, '-');
        $this->page->slug = $slug;

        try {
            $this->page->fill($request->input())->save();

            return redirect('page')->with('success', Lang::get('lang.page_created_successfully'));
        } catch (Exception $e) {
            return redirect('page')->with('fails', $e->getMessage());
        }
    }

    /**
     * To edit a page.
     *
     * @param type $slug
     *
     * @return type view
     */
    public function edit($slug)
    {
        try {
            $page = $this->page->where('id', $slug)->first();

            return view('themes.default1.agent.kb.pages.edit', compact('page'));
        } catch (Exception $e) {
            return redirect('page')->with('fails', $e->getMessage());
        }
    }

    /**
     * To update a page.
     *
     * @param type $slug
     * @param type PageUpdate $request
     *
     * @return type redirect
     */
    public function update($slug, PageRequest $request)
    {
        // get pages with respect to slug
        $pages = $this->page->where('id', $slug)->first();
        $sl = $request->input('name');
        $slug = Str::slug($sl, '-');

        try {
            $pages->fill($request->all())->save();
            $pages->slug = $slug;
            $pages->save();

            return redirect('page')->with('success', Lang::get('lang.your_page_updated_successfully'));
        } catch (Exception $e) {
            return redirect('page')->with('fails', $e->getMessage());
        }
    }

    /**
     * To Delete a Page.
     *
     * @param type $id
     *
     * @return type redirect
     */
    public function destroy($id)
    {
        try {
            // get the page to be deleted
            $page = $this->page->whereId($id)->first();
            $page->delete();

            return redirect('page')->with('success', Lang::get('lang.page_deleted_successfully'));
        } catch (Exception $e) {
            return redirect('page')->with('fails', $e->getMessage());
        }
    }
}
