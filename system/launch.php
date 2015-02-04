<?php


/**
 * Manager Page(s)
 * ---------------
 */

if(Guardian::happy() && $deck = File::exist(DECK . DS . 'launch.php')) {
    include $deck;
}


/**
 * Login Page
 * ----------
 *
 * [1]. manager/login
 *
 */

Route::accept($config->manager->slug . '/login', function() use($config, $speak) {

    Config::set(array(
        'page_type' => 'manager',
        'page_title' => $speak->log_in . $config->title_separator . $config->manager->title,
        'cargo' => DECK . DS . 'workers' . DS . 'login.php'
    ));

    if(Request::post()) {
        Guardian::authorize()->kick($config->manager->slug . '/article');
    } else {
        Guardian::reject();
    }

    Shield::attach('manager');

}, 20);


/**
 * Logout Page
 * -----------
 *
 * [1]. manager/logout
 *
 */

Route::accept($config->manager->slug . '/logout', function() use($config, $speak) {
    Notify::success(ucfirst(strtolower($speak->logged_out)) . '.');
    Guardian::reject()->kick($config->manager->slug . '/login');
}, 21);


/**
 * Index Page
 * ----------
 *
 * [1]. article
 * [2]. article/1
 *
 */

Route::accept(array($config->index->slug, $config->index->slug . '/(:num)'), function($offset = 1) use($config) {

    $articles = array();
    $offset = (int) $offset;

    if($files = Mecha::eat(Get::articles())->chunk($offset, $config->index->per_page)->vomit()) {
        foreach($files as $file) {
            $articles[] = Get::article($file, array('content', 'tags', 'css', 'js', 'comments'));
        }
    } else {
        if($offset !== 1) {
            Shield::abort('404-index');
        } else {
            $articles = false;
        }
    }

    Config::set(array(
        'page_type' => 'index',
        'page_title' => $config->index->title . $config->title_separator . $config->title,
        'offset' => $offset,
        'articles' => $articles,
        'pagination' => Navigator::extract(Get::articles(), $offset, $config->index->per_page, $config->index->slug)
    ));

    Shield::attach('index');

}, 30);


/**
 * Archive Page
 * ------------
 *
 * [1]. archive/2014
 * [2]. archive/2014/1
 *
 */

Route::accept(array($config->archive->slug . '/(:num)', $config->archive->slug . '/(:num)/(:num)'), function($slug = "", $offset = 1) use($config) {

    $articles = array();
    $offset = (int) $offset;

    if($files = Mecha::eat(Get::articles('DESC', 'time:' . $slug))->chunk($offset, $config->archive->per_page)->vomit()) {
        foreach($files as $file) {
            $articles[] = Get::article($file, array('content', 'tags', 'css', 'js', 'comments'));
        }
    } else {
        Shield::abort('404-archive');
    }

    Config::set(array(
        'page_type' => 'archive',
        'page_title' => $config->archive->title . ' ' . $slug . $config->title_separator . $config->title,
        'offset' => $offset,
        'archive_query' => $slug,
        'articles' => $articles,
        'pagination' => Navigator::extract(Get::articles('DESC', 'time:' . $slug), $offset, $config->archive->per_page, $config->archive->slug . '/' . $slug)
    ));

    Shield::attach('index');

}, 40);


/**
 * Archive Page
 * ------------
 *
 * [1]. archive/2014-04
 * [2]. archive/2014-04/1
 *
 */

Route::accept(array($config->archive->slug . '/(:num)-(:num)', $config->archive->slug . '/(:num)-(:num)/(:num)'), function($year = "", $month = "", $offset = 1) use($config, $speak) {

    $months = explode(',', $speak->months);
    $slug = $year . '-' . $month;
    $articles = array();
    $offset = (int) $offset;

    if($files = Mecha::eat(Get::articles('DESC', 'time:' . $slug))->chunk($offset, $config->archive->per_page)->vomit()) {
        foreach($files as $file) {
            $articles[] = Get::article($file, array('content', 'tags', 'css', 'js', 'comments'));
        }
    } else {
        Shield::abort('404-archive');
    }

    Config::set(array(
        'page_type' => 'archive',
        'page_title' => $config->archive->title . ' ' . ($config->widget_year_first ? $year . ', ' . $months[(int) $month - 1] : $months[(int) $month - 1] . ' ' . $year) . $config->title_separator . $config->title,
        'offset' => $offset,
        'archive_query' => $slug,
        'articles' => $articles,
        'pagination' => Navigator::extract(Get::articles('DESC', 'time:' . $slug), $offset, $config->archive->per_page, $config->archive->slug . '/' . $slug)
    ));

    Shield::attach('index');

}, 41);


/**
 * Tag Page
 * --------
 *
 * [1]. tagged/tag-slug
 * [2]. tagged/tag-slug/1
 *
 */

Route::accept(array($config->tag->slug . '/(:any)', $config->tag->slug . '/(:any)/(:num)'), function($slug = "", $offset = 1) use($config) {

    if( ! $tag = Get::tagsBy($slug)) {
        Shield::abort('404-tag');
    }

    $articles = array();
    $offset = (int) $offset;

    if($files = Mecha::eat(Get::articles('DESC', 'kind:' . $tag->id))->chunk($offset, $config->tag->per_page)->vomit()) {
        foreach($files as $file) {
            $articles[] = Get::article($file, array('content', 'tags', 'css', 'js', 'comments'));
        }
    } else {
        Shield::abort('404-tag');
    }

    Config::set(array(
        'page_type' => 'tag',
        'page_title' => $config->tag->title . ' ' . $tag->name . $config->title_separator . $config->title,
        'offset' => $offset,
        'tag_query' => $slug,
        'articles' => $articles,
        'pagination' => Navigator::extract(Get::articles('DESC', 'kind:' . $tag->id), $offset, $config->tag->per_page, $config->tag->slug . '/' . $slug)
    ));

    Shield::attach('index');

}, 50);


/**
 * Search Page
 * -----------
 *
 * [1]. search/search+query
 * [2]. search/search+query/1
 *
 */

Route::accept(array($config->search->slug . '/(:any)', $config->search->slug . '/(:any)/(:num)'), function($query = "", $offset = 1) use($config) {

    $articles = array();
    $offset = (int) $offset;
    $query = Text::parse($query)->to_decoded_url;
    $keywords = Text::parse($query)->to_slug;

    if(Session::get('search_query') == $query) {

        $articles = Session::get('search_results');

    } else {

        // Matched with all keywords combined
        if($files = Get::articles('DESC', 'keyword:' . $keywords)) {
            foreach($files as $file) {
                $articles[] = $file;
                $anchor = Get::articleAnchor($file);
                if(strpos(strtolower($anchor->title), str_replace('-', ' ', $keywords)) !== false) {
                    $articles[] = $file;
                }
            }
        }

        // Matched with a keyword
        $keywords = explode('-', $keywords);
        foreach($keywords as $keyword) {
            if($files = Get::articles('DESC', 'keyword:' . $keyword)) {
                foreach($files as $file) {
                    $articles[] = $file;
                    $anchor = Get::articleAnchor($file);
                    if(strpos(strtolower($anchor->title), $keyword) !== false) {
                        $articles[] = $file;
                    }
                }
            }
        }

        $articles = array_unique($articles); // Remove search results duplicates

        Session::set('search_query', $query);
        Session::set('search_results', $articles);

    }

    if( ! empty($articles)) {
        $_articles = array();
        foreach(Mecha::eat($articles)->chunk($offset, $config->search->per_page)->vomit() as $file) {
            $_articles[] = Get::article($file, array('content', 'tags', 'css', 'js', 'comments'));
        }
        Config::set(array(
            'page_type' => 'search',
            'page_title' => $config->search->title . ' &ldquo;' . $query . '&rdquo;' . $config->title_separator . $config->title,
            'offset' => $offset,
            'search_query' => $query,
            'articles' => $_articles,
            'pagination' => Navigator::extract($articles, $offset, $config->search->per_page, $config->search->slug . '/' . Text::parse($query)->to_encoded_url)
        ));
        Shield::attach('index');
    } else {
        Config::set(array(
            'page_title' => $config->search->title . ' &ldquo;' . $query . '&rdquo;' . $config->title_separator . $config->title,
            'search_query' => $query
        ));
        Session::kill('search_query');
        Session::kill('search_results');
        Shield::abort('404-search');
    }

}, 60);


/**
 * Ignite Search ...
 * -----------------
 */

Route::accept($config->search->slug, function() use($config) {
    if(Request::post('q') !== false) {
        Guardian::kick($config->search->slug . '/' . strip_tags(Text::parse(Request::post('q'))->to_encoded_url));
    } else {
        Guardian::kick();
    }
}, 61);


/**
 * Article Page
 * ------------
 *
 * [1]. article/article-slug
 *
 */

Route::accept($config->index->slug . '/(:any)', function($slug = "") use($config, $speak) {

    if( ! $article = Get::article($slug)) {
        Shield::abort('404-article');
    }

    if(isset($_GET['repair']) && Guardian::happy()) {
        Guardian::kick($config->manager->slug . '/article/repair/id:' . $article->id);
    }

    if($article->state == 'draft') {
        Shield::abort('404-article');
    }

    Config::set(array(
        'page_type' => 'article',
        'page_title' => $article->title . $config->title_separator . $config->index->title . $config->title_separator . $config->title,
        'article' => $article,
        'pagination' => Navigator::extract(Get::articles(), $article->path, 1, $config->index->slug)
    ));

    Weapon::add('shell_after', function() use($article) {
        if(isset($article->css) && trim($article->css) !== "") echo O_BEGIN . $article->css . O_END;
    });

    Weapon::add('sword_after', function() use($article) {
        if(isset($article->js) && trim($article->js) !== "") echo O_BEGIN . $article->js . O_END;
    });

    // Submitting a comment ...
    if($request = Request::post()) {

        Guardian::checkToken($request['token'], $config->url_current . '#comment-form');

        $extension = $config->comment_moderation && ! Guardian::happy() ? '.hold' : '.txt';

        if(trim($request['name']) === "") {
            Notify::error(Config::speak('notify_error_empty_field', array($speak->comment_name)));
        }

        if(trim($request['email']) !== "") {

            if( ! Guardian::check($request['email'])->this_is_email) {
                Notify::error($speak->notify_invalid_email);
            } else {
                // Disallow passengers from entering your email address in the comment email field
                if( ! Guardian::happy() && $request['email'] == $config->author_email) {
                    Notify::warning(Config::speak('notify_warning_forbidden_input', array('<em>' . $request['email'] . '</em>', strtolower($speak->email))));
                }
            }

        } else {
            Notify::error(Config::speak('notify_error_empty_field', array($speak->email)));
        }

        if(trim($request['url']) !== "" && ! Guardian::check($request['url'])->this_is_URL) {
            Notify::error($speak->notify_invalid_url);
        }

        if(trim($request['message']) === "") {
            Notify::error(Config::speak('notify_error_empty_field', array($speak->comment_message)));
        }

        if( ! Guardian::checkMath($request['math'])) {
            Notify::error($speak->notify_invalid_math_answer);
        }

        if(Guardian::check($request['name'], 100)->this_is_too_long) {
            Notify::error(Config::speak('notify_error_too_long', array($speak->comment_name)));
        }

        if(Guardian::check($request['email'], 100)->this_is_too_long) {
            Notify::error(Config::speak('notify_error_too_long', array($speak->comment_email)));
        }

        if(Guardian::check($request['url'], 100)->this_is_too_long) {
            Notify::error(Config::speak('notify_error_too_long', array($speak->comment_url)));
        }

        if(Guardian::check($request['message'], 1700)->this_is_too_long) {
            Notify::error(Config::speak('notify_error_too_long', array($speak->comment_message)));
        }

        // Check for spam keywords in comment
        $fucking_words = explode(',', $config->spam_keywords);
        foreach($fucking_words as $spam) {
            $fuck = trim($spam);
            if($fuck !== "") {
                if(
                    $request['email'] == $fuck || // Block by email address
                    Get::IP() != 'N/A' && Get::IP() == $fuck || // Block by IP address
                    strpos(strtolower($request['message']), strtolower($fuck)) !== false // Block by message word(s)
                ) {
                    Notify::warning($speak->notify_warning_intruder_detected . ' <strong class="text-error pull-right">' . $fuck . '</strong>');
                    break;
                }
            }
        }

        if( ! Notify::errors()) {

            $post = Date::format($article->time, 'Y-m-d-H-i-s');
            $id = (int) time();
            $parent = Request::post('parent');

            $P = array('data' => $request);

            // Restrict users from inputting the `SEPARATOR` constant
            // to prevent mistakes in parsing the file content
            $s = Text::parse(SEPARATOR)->to_ascii;
            $name = str_replace(SEPARATOR, $s, strip_tags($request['name']));
            $email = Text::parse($request['email'])->to_ascii;
            $url = str_replace(SEPARATOR, $s, Request::post('url', '#'));
            $parser = strip_tags(Request::post('content_type', $config->html_parser));
            $message = str_replace(SEPARATOR, $s, $request['message']);

            // Temporarily disallow images in comment to prevent XSS
            $message = strip_tags($message, '<br><img>' . ($parser == 'HTML' ? '<a><abbr><b><blockquote><code><del><dfn><em><i><ins><p><pre><span><strong><sub><sup><time><u><var>' : ""));
            $message = preg_replace('#(\!\[.*?\]\(.*?\)|<img (.*?)' . preg_quote(ES, '/') . ')#','`$1`', $message);
            $message = str_replace('<img ', '&lt;img ', $message);

            $data  = 'Name: ' . $name . "\n";
            $data .= 'Email: ' . $email . "\n";
            $data .= 'URL: ' . $url . "\n";
            $data .= 'Status: ' . (Guardian::happy() ? 'pilot' : 'passenger') . "\n";
            $data .= 'Content Type: ' . $parser . "\n";
            $data .= 'UA: ' . Get::UA() . "\n";
            $data .= 'IP: ' . Get::IP() . "\n";
            $data .= "\n" . SEPARATOR . "\n\n" . $message;

            File::write($data)->saveTo(RESPONSE . DS . $post . '_' . Date::format($id, 'Y-m-d-H-i-s') . '_' . ($parent ? Date::format($parent, 'Y-m-d-H-i-s') : '0000-00-00-00-00-00') . $extension, 0600);

            Notify::success(Config::speak('notify_success_submitted', array($speak->comment)));

            Weapon::fire('on_comment_update', array($P, $P));
            Weapon::fire('on_comment_construct', array($P, $P));

            if($config->email_notification) {
                $mail  = '<p>' . Config::speak('comment_notification', array($article->url . '#comment-' . Date::format($id, 'U'))) . '</p>';
                $mail .= Text::parse('**' . $name . ':** ' . $message)->to_html;
                $mail .= '<p>' . Date::format($id, 'Y/m/d H:i:s') . '</p>';
                // Sending email notification ...
                if( ! Guardian::happy()) {
                    if(Notify::send($request['email'], $config->author_email, $speak->comment_notification_subject, $mail, 'comment:')) {
                        Weapon::fire('on_comment_notification_construct', array($request, $config->author_email, $speak->comment_notification_subject, $mail));
                    }
                }
            }

            Guardian::kick($config->url_current . ( ! Guardian::happy() && $config->comment_moderation ? '#comment-form' : '#comment-' . Date::format($id, 'U')));

        } else {

            Guardian::kick($config->url_current . '#comment-form');

        }

    }

    Shield::attach('article-' . $slug);

}, 70);


/**
 * XML Sitemap
 * -----------
 *
 * [1]. sitemap
 *
 */

Route::accept('sitemap', function() {
    header('Content-Type: text/xml; charset=UTF-8');
    Shield::attach(SHIELD . DS . 'sitemap', true, true);
}, 80);


/**
 * RSS Feed
 * --------
 *
 * [1]. feeds
 * [2]. feeds/rss
 * [3]. feeds/rss/1
 *
 */

Route::accept(array('feeds', 'feeds/rss', 'feeds/rss/(:num)'), function($offset = 1) {
    Config::set('offset', (int) $offset);
    header('Content-Type: text/xml; charset=UTF-8');
    Shield::attach(SHIELD . DS . 'rss', true, true);
}, 81);


/**
 * JSON Feed
 * ---------
 *
 * [1]. feeds/json
 * [2]. feeds/json/1
 * [3]. feeds/json?callback=theFunction
 * [4]. feeds/json/1?callback=theFunction
 *
 */

Route::accept(array('feeds/json', 'feeds/json/(:num)'), function($offset = 1) {
    Config::set('offset', (int) $offset);
    header('Content-Type: application/json');
    Shield::attach(SHIELD . DS . 'json', true, true);
}, 82);


/**
 * Captcha Image
 * -------------
 *
 * [1]. captcha.png
 *
 */

Route::accept('captcha.png', function() {

    header('Content-Type: image/png');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');

    $bg = Request::get('bg', '333333');
    $color = Request::get('color', 'FFFFAA');
    $width = (int) Request::get('width', 100);
    $height = (int) Request::get('height', 30);
    $padding = (int) Request::get('padding', 7);
    $size = (int) Request::get('size', 16);
    $length = (int) Request::get('length', 7);
    $font = Request::get('font', 'special-elite-regular.ttf');
    $text = Session::get(Guardian::$captcha, "");

    if($bg !== false && $bg = Converter::HEX2RGB($bg)) {
        $bg = array($bg['r'], $bg['g'], $bg['b'], $bg['a']);
    } else {
        $bg = $bg !== false ? array(51, 51, 51, 1) : array(0, 0, 0, 0);
    }

    if($color = Converter::HEX2RGB($color)) {
        $color = array($color['r'], $color['g'], $color['b'], $color['a']);
    } else {
        $color = array(255, 255, 170, 1);
    }

    $image = imagecreatetruecolor($width, $height);

    imagefill($image, 0, 0, 0x7fff0000);
    imagealphablending($image, true);
    imagesavealpha($image, true);

    $bg = imagecolorallocatealpha($image, $bg[0], $bg[1], $bg[2], 127 - ($bg[3] * 127));
    $color = imagecolorallocatealpha($image, $color[0], $color[1], $color[2], 127 - ($color[3] * 127));

    imagefilledrectangle($image, 0, 0, $width, $height, $bg);
    imagettftext($image, $size, 0, $padding, $size + $padding, $color, ASSET . DS . '__captcha' . DS . $font, $text);
    imagepng($image);
    imagedestroy($image);

}, 100);


/**
 * Static Page
 * -----------
 *
 * [1]. page-slug
 *
 */

Route::accept('(:any)', function($slug = "") use($config) {

    if( ! $page = Get::page($slug)) {
        Shield::abort('404-page');
    }

    if(isset($_GET['repair']) && Guardian::happy()) {
        Guardian::kick($config->manager->slug . '/page/repair/id:' . $page->id);
    }

    if($page->state == 'draft') {
        Shield::abort('404-page');
    }

    Weapon::add('shell_after', function() use($page) {
        if(isset($page->css) && trim($page->css) !== "") echo O_BEGIN . $page->css . O_END;
    });

    Weapon::add('sword_after', function() use($page) {
        if(isset($page->js) && trim($page->js) !== "") echo O_BEGIN . $page->js . O_END;
    });

    Config::set(array(
        'page_type' => 'page',
        'page_title' => $page->title . $config->title_separator . $config->title,
        'page' => $page
    ));

    Shield::attach('page-' . $slug);

}, 110);


/**
 * Home Page
 * ---------
 *
 * [1]. /
 *
 */

Route::accept("", function() use($config) {

    Session::kill('search_query');
    Session::kill('search_results');

    $articles = array();

    if($files = Mecha::eat(Get::articles())->chunk(1, $config->index->per_page)->vomit()) {
        foreach($files as $file) {
            $articles[] = Get::article($file, array('content', 'tags', 'css', 'js', 'comments'));
        }
    } else {
        $articles = false;
    }

    Config::set(array(
        'page_type' => 'home',
        'articles' => $articles,
        'pagination' => Navigator::extract(Get::articles(), 1, $config->index->per_page, $config->index->slug)
    ));

    Shield::attach('page-home');

}, 120);


/**
 * Do Routing
 * ----------
 */

Route::execute();


/**
 * 404 Page
 * --------
 *
 * Fallback to 404 page if nothing matched.
 *
 */

Shield::abort();