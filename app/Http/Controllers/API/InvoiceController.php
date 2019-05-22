<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Facades\Item;

class InvoiceController extends Controller
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

        $user = $this->quickbooks->query("SELECT * FROM Customer WHERE DisplayName = '$request->customer_name'");

        if(!isset($user[0])){
            $data = [
                "DisplayName" => $request->customer_name,
                "Title"=> "Customer",
            ];

            $customerToCreate = Customer::create($data);
            $user = $this->quickbooks->Add($customerToCreate);
        }else{
            $user = $user[0];
        }

        $product = $this->quickbooks->query("SELECT * FROM Item WHERE Name = '$request->product_name'");

        if(!isset($product[0])){
            $asset_account = $this->quickbooks->query("SELECT * FROM Account WHERE AccountType = 'Other Current Asset' AND AccountSubType = 'Inventory'");
            $income_account = $this->quickbooks->query("SELECT * FROM Account WHERE AccountType = 'Income' AND AccountSubType = 'SalesOfProductIncome'");
            $expense_account = $this->quickbooks->query("SELECT * FROM Account WHERE AccountType = 'Cost of Goods Sold'");

            $data = [
                "Name" => $request->product_name,
                "TrackQtyOnHand" => true,
                "Type" => "Inventory",
                "QtyOnHand" => 1,
                "ExpenseAccountRef" => [
                    "name" => "Cost of Goods Sold",
                    "value" => $expense_account[0]->Id
                ],
                "IncomeAccountRef" => [
                    "name" => "Sales of Product Income",
                    "value" => $income_account[0]->Id
                ],
                "AssetAccountRef" => [
                    "name" => "Inventory Asset",
                    "value" => $asset_account[0]->Id
                ],
                "InvStartDate"=> "2015-01-01"
            ];

            $itemToCreate = Item::create($data);
            $product = $this->quickbooks->Add($itemToCreate);

        }else{
            $product = $product[0];
        }

        $error = $this->quickbooks->getLastError();
        if ($error) {
            $message =  "The Status code is: " . $error->getHttpStatusCode() ;
            $message .= "The Helper message is: " . $error->getOAuthHelperError();
            $message .= "The Response message is: " . $error->getResponseBody();
            return response()->json($message);
        }

        $data = [
            "Line" => [
                [
                    "DetailType" => "SalesItemLineDetail",
                    "Amount" => 100.0,
                    "SalesItemLineDetail" => [
                        "Qty" => $request->qty,
                        "ItemRef" => [
                            "name" => "Services",
                            "value" => $product->Id
                        ]
                    ]
                ]
            ],
            "CustomerRef" => [
                "value" => $user->Id
            ]
        ];

        $invoiceToCreate = Invoice::create($data);
        $this->quickbooks->Add($invoiceToCreate);
        $error = $this->quickbooks->getLastError();
        if ($error) {
            $message =  "The Status code is: " . $error->getHttpStatusCode() ;
            $message .= "The Helper message is: " . $error->getOAuthHelperError();
            $message .= "The Response message is: " . $error->getResponseBody();
            return response()->json($message);
        }

        return response()->json(['message' => 'Invoice added']);

    }


}
