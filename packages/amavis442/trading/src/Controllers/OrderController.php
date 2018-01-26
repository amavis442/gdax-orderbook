<?php

namespace Amavis442\Trading\Controllers;

use Amavis442\Trading\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $page = 1)
    {
        $orders = Order::select('*')
            ->orderBy('created_at', 'desc')
            ->paginate();

        return $orders;
    }


    public function create(Request $request)
    {
        $side = $request->get('orderside');
        $pair = $request->get('orderpair');
        $size = $request->get('ordersize');
        $price = $request->get('orderprice');
        $orderType = $request->get('ordertype');
        $stop = $request->get('ordertype');
        $limitprice = $request->get('orderlimitprice');
    }
}
