<?php

namespace App\Http\Controllers;

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Customer;

class ExampleController extends Controller
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

//        dd($this->quickbooks);
    }

    public function __invoke()
    {

        $data = [
            "DisplayName" => "Cheburek",
            "Title"=> "HerVoRtu",
        ];

        $customerToCreate = Customer::create($data);

        $this->quickbooks->Add($customerToCreate);

        $resultObj = $this->quickbooks->query("SELECT * FROM Customer");
;
        $error = $this->quickbooks->getLastError();
        if ($error) {
            echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
            echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
            echo "The Response message is: " . $error->getResponseBody() . "\n";
        }

        foreach ($resultObj as $result){
            echo "{$result->DisplayName} - {$result->Id}</br> ";
        }
    }


}
