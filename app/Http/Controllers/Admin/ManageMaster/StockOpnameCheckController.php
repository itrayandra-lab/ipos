<?php

namespace App\Http\Controllers\Admin\ManageMaster;

use App\Http\Controllers\Controller;
use App\Services\StockOpnameCheckService;
use Illuminate\Http\Request;

class StockOpnameCheckController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->hasPermission('access_stock_opname')) {
                abort(403, 'Anda tidak memiliki izin untuk membuka pengecekan SO');
            }
            return $next($request);
        });
    }

    public function index()
    {
        return view('admin.manage_master.stock_opname.check', ['result' => null, 'fileName' => null])
            ->with('sb', 'StockOpnameCheck');
    }

    public function process(Request $request, StockOpnameCheckService $service)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:10240']);

        $file = $request->file('file');
        $result = $service->check($file->getRealPath());

        return view('admin.manage_master.stock_opname.check', [
            'result' => $result,
            'fileName' => $file->getClientOriginalName(),
        ])->with('sb', 'StockOpnameCheck');
    }
}
