<?php

namespace App\Http\Controllers\Client\kb;

use App\Http\Controllers\Controller;
use App\Http\Requests\kb\ContactRequest;
use App\Http\Requests\kb\SearchRequest;
use App\Model\kb\Article;
use App\Model\kb\Category;
use App\Model\kb\Comment;
use App\Model\kb\Contact;
use App\Model\kb\Faq;
use App\Model\kb\Page;
use App\Model\kb\Relationship;
use App\Model\kb\Settings;
use Auth;
// use Creativeorange\Gravatar\Gravatar;
use Config;
use Illuminate\Http\Request;
use Lang;
use Mail;
use Redirect;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('board');
    }

    /**
     * @param
     *
     * @return response
     */
    public function getArticle(Article $article, Category $category, Settings $settings)
    {
        $setting = $settings->first();
        $pagination = $setting->pagination;
        if (!Auth::check() || \Auth::user()->role == 'user') {
            $article = $article->where('status', '1');
        }
        $article = $article->where('type', '1');
        $article = $article->orderBy('publish_time', 'desc');
        $article = $article->paginate($pagination);

        $article->setPath('article-list');
        $categorys = $category->get();

        return view('themes.default1.client.kb.article-list.articles', compact('categorys', 'article'));
    }

    /**
     * Get excerpt from string.
     *
     * @param string $str       String to get an excerpt from
     * @param int    $startPos  Position int string to start excerpt from
     * @param int    $maxLength Maximum length the excerpt may be
     *
     * @return string excerpt
     */
    public static function getExcerpt($str, $startPos = 0, $maxLength = 50)
    {
        if (strlen($str) > $maxLength) {
            $excerpt = substr($str, $startPos, $maxLength - 3);
            $lastSpace = strrpos($excerpt, ' ');
            $excerpt = substr($excerpt, 0, $lastSpace);
            $excerpt .= '...';
        } else {
            $excerpt = $str;
        }

        return $excerpt;
    }

    /**
     * function to search an article.
     *
     * @param \App\Http\Requests\kb\SearchRequest $request
     * @param \App\Model\kb\Category              $category
     * @param \App\Model\kb\Article               $article
     * @param \App\Model\kb\Settings              $settings
     *
     * @return type view
     */
    public function search(SearchRequest $request, Category $category, Article $article, Settings $settings)
    {
        $settings = $settings->first();
        $pagination = $settings->pagination;
        $search = $request->input('s');
        $result = $article->where('name', 'LIKE', '%'.$search.'%')
            ->orWhere('slug', 'LIKE', '%'.$search.'%')
            ->orWhere('description', 'LIKE', '%'.$search.'%')
            ->paginate($pagination);
        $result->setPath('search?s='.$search);
        $categorys = $category->get();

        return view('themes.default1.client.kb.article-list.search', compact('categorys', 'result'));
    }

    /**
     * to show the seleted article.
     *
     * @return response
     */
    public function show($slug, Article $article, Category $category)
    {
        //ArticleController::timezone();
        $tz = \App\Model\helpdesk\Settings\System::where('id', '1')->first()->time_zone;
        $tz = \App\Model\helpdesk\Utility\Timezones::where('id', $tz)->first()->name;
        date_default_timezone_set($tz);
        $date = \Carbon\Carbon::now()->toDateTimeString();
        $arti = $article->where('slug', $slug);

        if (!Auth::check() || \Auth::user()->role == 'user') {
            $arti = $arti->where('status', '1');
            $arti = $arti->where('publish_time', '<', $date);
        }

        $arti = $arti->where('type', '1');

        $arti = $arti->first();

        if ($arti) {
            return view('themes.default1.client.kb.article-list.show', compact('arti'));
        } else {
            return Redirect::back()->with('fails', Lang::get('lang.sorry_not_processed'));
        }
    }

    public function getCategory($slug, Article $article, Category $category, Relationship $relation)
    {
        /* get the article_id where category_id == current category */
        $catid = $category->where('slug', $slug)->first();
        if (!$catid) {
            return redirect()->back()->with('fails', Lang::get('lang.we_are_sorry_but_the_page_you_are_looking_for_can_not_be_found'));
        }
        $id = $catid->id;
        $all = $relation->where('category_id', $id)->get();
        // $all->setPath('');
        /* from whole attribute pick the article_id */
        $article_id = $all->pluck('article_id');
        $categorys = $category->get();

        /* direct to view with $article_id */
        return view('themes.default1.client.kb.article-list.category', compact('all', 'id', 'categorys', 'article_id'));
    }

    public function home(Article $article, Category $category, Relationship $relation)
    {
        if (Config::get('database.install') == '%0%') {
            return redirect('/probe.php');
        } else {
            $categorys = $category->get();
            // $categorys->setPath('home');
            /* direct to view with $article_id */
            $page = Relationship::where('category_id', '=', $category->id)->get();
            /* from whole attribute pick the article_id */
            $articles_id = $page->pluck('article_id');

            return view('themes.default1.client.kb.article-list.home', compact('categorys', 'articles_id'));
        }
    }

    public function Faq(Faq $faq, Category $category)
    {
        $faq = $faq->where('id', '1')->first();
        $categorys = $category->get();

        return view('themes.default1.client.kb.article-list.faq', compact('categorys', 'faq'));
    }

    /**
     * get the contact page for user.
     *
     * @return response
     */
    public function contact(Category $category, Settings $settings)
    {
        $settings = $settings->whereId('1')->first();
        $categorys = $category->get();

        return view('themes.default1.client.kb.article-list.contact', compact('settings', 'categorys'));
    }

    /**
     * send message to the mail adderess that define in the system.
     *
     * @return response
     */
    public function postContact(ContactRequest $request, Contact $contact)
    {
        $this->port();
        $this->host();
        $this->encryption();
        $this->email();
        $this->password();
        //return Config::get('mail');
        $contact->fill($request->input())->save();
        $name = $request->input('name');
        //echo $name;
        $email = $request->input('email');
        //echo $email;
        $subject = $request->input('subject');
        //echo $subject;
        $details = $request->input('message');
        //echo $message;
        //echo $contact->email;
        $mail = Mail::send('themes.default1.client.kb.article-list.contact-details', ['name' => $name, 'email' => $email, 'subject' => $subject, 'details' => $details], function ($message) use ($contact) {
            $message->to($contact->email, $contact->name)->subject('Contact');
        });
        if ($mail) {
            return redirect('contact')->with('success', Lang::get('lang.your_details_send_to_system'));
        } else {
            return redirect('contact')->with('fails', Lang::get('lang.your_details_can_not_send_to_system'));
        }
    }

    public function contactDetails()
    {
        return view('themes.default1.client.kb.article-list.contact-details');
    }

    /**
     * To insert the values to the comment table.
     *
     * @param type Article $article
     * @param type Request $request
     * @param type Comment $comment
     * @param type Id      $id
     *
     * @return type response
     */
    public function postComment($slug, Article $article, Request $request, Comment $comment)
    {
        $request->validate([
            'comment' => 'required',
        ]);

        $article = $article->where('slug', $slug)->first();
        if (!$article) {
            return response()->json(['success' => false, 'message' => Lang::get('lang.sorry_not_processed')]);
        }

        $comment->article_id = $article->id;

        if (Auth::check()) {
            // Associate the comment with the authenticated user
            $comment->article_id = Auth::id();
            $comment->name = Auth::user()->first_name.' '.Auth::user()->last_name;
        } else {
            // Set default values for non-authenticated user
            $comment->name = $request->input('name');
            $comment->email = $request->input('email');
            $comment->website = $request->input('website');
        }

        if ($comment->fill($request->input())->save()) {
            return Redirect::back()->with('success', Lang::get('lang.your_comment_posted'));
        } else {
            return Redirect::back()->with('fails', Lang::get('lang.sorry_not_processed'));
        }
    }

    public function getPage($name, Page $page)
    {
        $page = $page->where('slug', $name);

        if (!Auth::check() || \Auth::user()->role == 'user') {
            $page = $page
                    ->where(['status' => 1, 'visibility'=>1])
                    ->first();
        } else {
            $page = $page->where('status', 1)->first();
        }

        if ($page) {
            return view('themes.default1.client.kb.article-list.pages', compact('page'));
        } else {
            return Redirect::back()->with('fails', Lang::get('lang.sorry_not_processed'));
        }
    }

    public static function port()
    {
        $setting = Settings::whereId('1')->first();
        Config::set('mail.port', $setting->port);
    }

    public static function host()
    {
        $setting = Settings::whereId('1')->first();
        Config::set('mail.host', $setting->host);
    }

    public static function encryption()
    {
        $setting = Settings::whereId('1')->first();
        Config::set(['mail.encryption' => $setting->encryption, 'mail.username' => $setting->email]);
    }

    public static function email()
    {
        $setting = Settings::whereId('1')->first();
        Config::set(['mail.from' => ['address' => $setting->email, 'name' => 'asd']]);
        //dd(Config::get('mail'));
    }

    public static function password()
    {
        $setting = Settings::whereId('1')->first();
        Config::set(['mail.password' => $setting->password, 'mail.sendmail' => $setting->email]);
    }

    public function getCategoryList(Article $article, Category $category, Relationship $relation)
    {
        //$categorys = $category->get();
        $categorys = $category->get();
        // $categorys->setPath('home');
        /* direct to view with $article_id */
        $page = Relationship::where('category_id', '=', $category->id)->get();
        /* from whole attribute pick the article_id */
        $articles_id = $page->pluck('article_id');

        return view('themes.default1.client.kb.article-list.categoryList', compact('categorys', 'articles_id'));
    }

    // static function timezone($utc) {
    // 	$set = Settings::whereId('1')->first();
    // 	$tz = $set->timezone;
    // 	$format = $set->dateformat;
    // 	//$utc = date('M d Y h:i:s A');
    // 	//echo 'UTC : ' . $utc;
    // 	date_default_timezone_set($tz);
    // 	$offset = date('Z', strtotime($utc));
    // 	//print "offset: $offset \n";
    // 	$date = date($format, strtotime($utc) + $offset);
    // 	return $date;
    // 	//return substr($date, 0, -6);
    // }
}
