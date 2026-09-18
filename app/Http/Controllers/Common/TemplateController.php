<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\helpdesk\TemplateRequest;
use App\Http\Requests\helpdesk\TemplateUdate;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use Illuminate\Http\Request;
use Lang;
use Yajra\DataTables\Facades\DataTables;

/**
 * |======================================================
 * | Class Template Controller
 * |======================================================
 * This controller is for CRUD email templates.
 */
class TemplateController extends Controller
{
    public $template;

    public $type;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('roles');

        $template = new Template();
        $this->template = $template;

        $type = new TemplateType();
        $this->type = $type;
    }

    /**
     * get the list of templates.
     *
     * @return type view
     */
    public function index()
    {
        try {
            return view('themes.default1.common.template.inbox');
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Show template
     * This template to show a particular template.
     *
     * @param type $id
     *
     * @return type view
     */
    public function showTemplate($id)
    {
        try {
            $templates = Template::where('set_id', '=', $id)->get();

            return view('themes.default1.common.template.list-templates', compact('templates'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * This function is used to display chumper datatables of the template list.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return type datatable
     */
    public function GetTemplates(Request $request)
    {
        $id = $request->input('id');

        return DataTables::of($this->template->where('set_id', '=', $id)->select('id', 'name', 'type')->get())
                        ->addColumn('type', function ($model) {
                            return $this->type->where('id', $model->type)->first()->name;
                        })
                        ->addColumn('action', function ($model) {
                            return '<a href='.url('templates/'.$model->id.'/edit')." class='btn btn-sm btn-primary'>Edit</a>";
                        })
                        ->rawColumns(['action'])
                        ->make(true);
    }

    /**
     * @return type view
     */
    public function create()
    {
        try {
            $i = $this->template->orderBy('created_at', 'desc')->first()->id + 1;
            $type = $this->type->pluck('name', 'id')->toArray();

            return view('themes.default1.common.template.create', compact('type'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * To store a set of templates.
     *
     * @param \App\Http\Requests\helpdesk\TemplateRequest $request
     *
     * @return type redirect
     */
    public function store(TemplateRequest $request)
    {
        try {
            $this->template->fill($request->input())->save();

            return redirect('templates')->with('success', Lang::get('lang.template_saved_successfully'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * function to get the edit page of template.
     *
     * @param type $id
     *
     * @return type
     */
    public function edit($id)
    {
        try {
            $i = $this->template->orderBy('created_at', 'desc')->first()->id + 1;
            $template = $this->template->where('id', $id)->first();
            $type = $this->type->pluck('name', 'id')->toArray();

            return view('themes.default1.common.template.edit', compact('type', 'template'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * function to update a template.
     *
     * @param type                                      $id
     * @param \App\Http\Requests\helpdesk\TemplateUdate $request
     *
     * @return type
     */
    public function update($id, TemplateUdate $request)
    {
        try {
            //dd($request);
            $template = $this->template->where('id', $id)->first();
            $template->fill($request->input())->save();

            return redirect()->back()->with('success', Lang::get('lang.template_updated_successfully'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * function to delete a template.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy(Request $request)
    {
        try {
            $ids = $request->input('select');
            if (!empty($ids)) {
                foreach ($ids as $id) {
                    $template = $this->template->where('id', $id)->first();
                    if ($template) {
                        $template->delete();
                    } else {
                        echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>".\Lang::get('message.alert').'!</b>
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.\Lang::get('message.no-record').'
                </div>';
                    }
                }
                echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        ".\Lang::get('message.deleted-successfully').'
                </div>';
            } else {
                echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>".\Lang::get('message.alert').'!</b> 
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.\Lang::get('message.select-a-row').'
                </div>';
            }
        } catch (\Exception $e) {
            echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>".\Lang::get('message.alert').'!</b>
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.$e->getMessage().'
                </div>';
        }
    }

    /**
     * function to show the templates.
     *
     * @param type $id
     *
     * @return type Mixed
     */
    public function show($id)
    {
        //dd($currency);
        try {
            if ($this->template->where('type', 3)->where('id', $id)->first()) {
                $data = $this->template->where('type', 3)->where('id', $id)->first()->data;
                $products = $this->product->where('id', '!=', 1)->take(4)->get();
                if (count($products) > 0) {
                    $template = '';
                    foreach ($products as $product) {
                        //dd($this->checkPriceWithTaxClass($product->id, $currency));
                        $url = $product->shoping_cart_link;
                        $title = $product->name;
                        if ($product->description) {
                            $description = str_replace('</ul>', '', str_replace('<ul>', '', $product->description));
                        } else {
                            $description = '';
                        }
                        $currency = \Session::get('currency');
                        if ($this->price->where('product_id', $product->id)->where('currency', $currency)->first()) {
                            $product_currency = $this->price->where('product_id', $product->id)->where('currency', $currency)->first();
                            $code = $product_currency->currency;
                            $currency = $this->currency->where('code', $code)->first();
                            if ($currency->symbol) {
                                $currency = $currency->symbol;
                            } else {
                                $currency = $currency->code;
                            }
                            $price = \App\Http\Controllers\Front\CartController::calculateTax($product->id, $product_currency->currency, 1, 0, 1);

                            $subscription = $this->plan->where('id', $product_currency->subscription)->first()->name;
                        } else {
                            return redirect('/')->with('fails', \Lang::get('message.no-such-currency-in-system'));
                        }

                        $array1 = ['{{title}}', '{{currency}}', '{{price}}', '{{subscription}}', '<li>{{feature}}</li>', '{{url}}'];
                        $array2 = [$title, $currency, $price, $subscription, $description, $url];
                        $template .= str_replace($array1, $array2, $data);
                    }

                    //dd($template);
                    return view('themes.default1.common.template.shoppingcart', compact('template'));
                } else {
                    $template = '<p>No Products</p>';

                    return view('themes.default1.common.template.shoppingcart', compact('template'));
                }
            } else {
                return redirect('/')->with('fails', 'no such record');
            }
        } catch (\Exception $e) {
            dd($e);

            return redirect('/')->with('fails', $e->getMessage());
        }
    }
}
