<?php namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Guard;

class sitemap {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        {
            $aSiteMap = \Cache::get('sitemap', []);
            $changefreq = 'always';
            $fullUrl = explode("?", $request->fullUrl())[0];
            if ( !empty( $aSiteMap[$fullUrl]['added'] ) ) {
                $aDateDiff = Carbon::createFromTimestamp( $aSiteMap[$fullUrl]['added'] )->diff( Carbon::now() );
                if ( $aDateDiff->y > 0 ) {
                    $changefreq = 'yearly';
                } else if ( $aDateDiff->m > 0) {
                    $changefreq = 'monthly';
                } else if ( $aDateDiff->d > 6 ) {
                    $changefreq = 'weekly';
                } else if ( $aDateDiff->d > 0 && $aDateDiff->d < 7 ) {
                    $changefreq = 'daily';
                } else if ( $aDateDiff->h > 0 ) {
                    $changefreq = 'hourly';
                } else {
                    $changefreq = 'always';
                }
            }
            $aSiteMap[$fullUrl] = [
                'added' => time(),
                'lastmod' => Carbon::now()->toIso8601String(),
                'priority' => 1 - substr_count($request->getPathInfo(), '/') / 10,
                'changefreq' => $changefreq
            ];
            \Cache::put('sitemap', $aSiteMap, 2880);
        }
        return $next($request);
    }
}