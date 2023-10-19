<?php

use App\Models\ShortUrl;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('domain/transfer', function () {
    try {

        $transferDomains = [

            "5facebook.com",

            "betffed.com",

            "betrred.com",

            "bettred.com",

            "bhk01.com",

            "bingng.com",

            "binwnce.com",

            "bjnance.com",

            "btefair.com",

            "cam4.asia",

            "cricbuyzz.com",

            "dafabeet.com",

            "dafabert.com",

            "dafabetr.com",

            "dafabety.com",

            "dcinsife.com",

            "dcinsise.com",

            "ddoeda.com",

            "dlsitr.com",

            "doeea.com",

            "dpuban.com",

            "eaatmoney.com",

            "eastminey.com",

            "eoeda.com",

            "fdouyu.com",

            "hkjcc.com",

            "instadgram.com",

            "instakgram.com",

            "kivenation.com",

            "livesclre.com",

            "livwnation.com",

            "mmegamillions.com",

            "ooblox.com",

            "openqai.com",

            "ppixi.com",

            "ppornhu.com",

            "sdouyu.com",

            "sepankbang.com",

            "spankbang0.com",

            "taobzo.com",

            "tataobao.com",

            "twitter8.com",

            "vetfred.com",

            "weibho.com",

            "weijbo.com",

            "wzhihu.com",

            "zhihh.com",

            "zhjihu.com"
        ];

        foreach ($transferDomains as $domain) {
            $domain = ShortUrl::query()
                ->where('original_domain', $domain)
                ->first();
            if ($domain) {
                $domain->update([
                    'campaign_id' => 9
                ]);
            }
        }

        return 'done';
    } catch (\Throwable $th) {
        info($th);
    }
});

// auth routes
require __DIR__ . '/auth/auth.php';

// dashboard routes
require __DIR__ . '/dashboard/dashboard.php';

// role routes
require __DIR__ . '/role/role.php';

// role modules
require __DIR__ . '/module/module.php';

// user routes
require __DIR__ . '/user/user.php';

// tld routes
require __DIR__ . '/tld/tld.php';

// campaign routes
require __DIR__ . '/campaign/campaign.php';

// short url type routes
require __DIR__ . '/short-url-type/short_url_type.php';

// short url routes
require __DIR__ . '/short-url/short_url.php';

// excluded domain routes
require __DIR__ . '/excluded-domain/excluded_domain.php';

// report download routes
require __DIR__ . '/report-download/report-download.php';
