<?php

namespace App\Http\ControllerAPI;

use Illuminate\Http\Request;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Customer;

class InvoceController extends Controller
{
    /**
     * @var Quickbooks
     */
    private $quickbooks;

    /**
     * Create a new controller instance.
     *
     * @param Quickbooks $quickbooks
     */
    public function __construct(DataService $quickbooks)
    {
        $this->quickbooks = $quickbooks;
    }

    public function __invoke(Request $request)
    {
        return response()->json($request->all());
    }


}
