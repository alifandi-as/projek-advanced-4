<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Order;
use App\Models\UserData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $check = auth()->check();
        if($check){

                return $next($request);

            return "You must select your own order";
        }
        // $user = UserData::query()
        // ->where("remember_token", "=", $request->token)
        // ->get()
        // ->pluck("remember_token")
        // ->toArray()[0];
        // if(isset($user)){
            
        // }
        // return "Login to delete order";
    }
}
