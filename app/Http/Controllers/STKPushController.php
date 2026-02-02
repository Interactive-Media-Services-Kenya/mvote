<?php

namespace App\Http\Controllers;

use App\Models\MpesaStk;
use Illuminate\Http\Request;

class STKPushController extends Controller
{

    private $consumer_key = 'UOmHN9mEAxQSj8GN2EqrIyTfN7Bmd0aw';
    private $consumer_secret = "4PuCL5TRr9eOKuV2";


    public function initiateSTK(Request $request)
    {

        $data = $request->json()->all();

        $mobileNumber = $data["mobileNumber"];
        $account_number = $data["account_number"];
        $betID = $data["betID"];
        $Amount = $data["Amount"];
        $uid = $data["uid"];

        $access_token = $this->getAccessToken();
        if (!$access_token) {
            throw new \Exception("Failed to retrieve access token");
        }
        // Prepare STK Push request
        $initiate_url = "https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest";
        $stkHeader = [
            "Content-Type: application/json",
            "Authorization: Bearer " . $access_token,
        ];
        $BusinessShortCode = "292222";
        $Timestamp = date("YmdHis");
        $PartyA = $mobileNumber;
        $Amount = $Amount;
        $CallBackURL = "https://lottery.ims.co.ke/api/v1/payment/callback-url";
        $AccountReference = $account_number;
        $TransactionDesc = "Subscription";
        $Passkey = "d8d442fbf4e75b4d820ce3d8f6c33d4afcecbe3b1bfd70091213782cff229785";
        $Password = base64_encode($BusinessShortCode . $Passkey . $Timestamp);
        $curl_post_data = [
            "BusinessShortCode" => $BusinessShortCode,
            "Password" => $Password,
            "Timestamp" => $Timestamp,
            "TransactionType" => "CustomerPayBillOnline",
            "Amount" => $Amount,
            "PartyA" => $PartyA,
            "PartyB" => $BusinessShortCode,
            "PhoneNumber" => $PartyA,
            "CallBackURL" => $CallBackURL,
            "AccountReference" => $AccountReference,
            "TransactionDesc" => $TransactionDesc,
        ];

        return $this->sendRequest($initiate_url, $stkHeader, $curl_post_data, $betID, $uid, $mobileNumber);
    }
    private function getAccessToken()
    {
        $access_token_url = "https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";
        $curl = curl_init();
        $credentials = base64_encode($this->consumer_key . ":" . $this->consumer_secret);
        curl_setopt($curl, CURLOPT_URL, $access_token_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "Authorization: Basic " . $credentials,
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $curl_response = curl_exec($curl);
        curl_close($curl);
        $response_data = json_decode($curl_response);

        return isset($response_data->access_token) ? $response_data->access_token : null;
    }

    private function sendRequest($url, $headers, $data, $betID, $uid, $mobileNumber)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        $curl_response = curl_exec($curl);
        curl_close($curl);


        // $resp_data = json_decode($curl_response);

        return json_decode($curl_response);


        // $MerchantRequestID = $resp_data->MerchantRequestID;
        // $CheckoutRequestID = $resp_data->CheckoutRequestID;
        // $ResponseCode = $resp_data->ResponseCode;
        // $ResponseDescription = $resp_data->ResponseDescription;

        // info($ResponseCode);
        // $mpesa_stk = MpesaStk::create([
        //     "merchant_request_id" => $MerchantRequestID,
        //     "checkout_request_id" => $CheckoutRequestID,
        //     "bet_id" => $betID,
        //     "phone_number" => $mobileNumber,
        //     "resultDescription" => $ResponseDescription ?? 'No Reponse',
        //     "resultCode" => $ResponseCode ?? '13',
        //     "user_id" => $uid,
        //     "status" => 0,
        // ]);
    }
}
