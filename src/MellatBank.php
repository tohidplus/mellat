<?php

namespace Tohidplus\Mellat;


use Illuminate\Http\Request;
use SoapClient;
use Tohidplus\Mellat\Models\MellatLog;

class MellatBank
{

    CONST NAMESPACE = 'http://interfaces.core.sw.bps.com/';
    /**
     * @var SoapClient
     */
    protected $client;
    protected $orderId;
    protected $amount;
    protected $date;
    protected $time;
    protected $additionalData;
    protected $callBackUrl;
    protected $payerId;

    //

    public function __construct(SoapClient $client)
    {
        $this->client = $client;
    }

    public function set($amount, $orderId=null, $payerId=null, $additionalData = null)
    {
        $this->amount = $amount;
        $this->orderId = $orderId?:rand(1000,9999999);
        $this->payerId = $payerId?:0;
        $this->additionalData = $additionalData;
        $this->time = \Carbon\Carbon::now()->format('His');
        $this->date = \Carbon\Carbon::now()->format('Ymd');
    }

    public function redirect($error)
    {
        $this->validate();
        $response = $this->client->bpPayRequest($this->redirectionData(), self::NAMESPACE)->return;
        $response = explode(',', $response);
        if ($response[0] == 0) {
            $refId = $response[1];
            $this->createLog($refId);
            return view('mellat::mellat', compact('refId'));
        } else {
            return $error((new MellatException())->getMellatErrorMessage($response[0]));
        }
    }

    public function verify($success, $error)
    {
        $request=\request();
        $resCode = $request->get('ResCode');
        if ($resCode == 0) {
            $result = $this->client->bpVerifyRequest($this->verifyData($request), self::NAMESPACE);
            $result = $result->return;
            $result = explode(',', $result);
            if ($result[0] == 0) {
                $settleResult = $this->client->bpSettleRequest($this->verifyData($request), self::NAMESPACE);
                $settleResult = $settleResult->return;
                $settleResult = explode(',', $settleResult);
                if ($settleResult[0] == 0) {
                    $request->merge(['ResCode' => 0]);
                    return $success($this->updateLog($request));
                } else {
                    $request->merge(['ResCode' => $settleResult[0]]);
                    $this->updateLog($request);
                    return $error($this->updateLog($request));
                }
            } else {
                $request->merge(['ResCode' => $result[0]]);
                $this->updateLog($request);
                return $error($this->updateLog($request));
            }
        } else {
            return $error($this->updateLog($request));
        }
    }

    public function setCallBackUrl($callBackUrl)
    {
        $this->callBackUrl = $callBackUrl;
    }

    #-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#
    # Protected functions
    #-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#

    /**:::::::::::::::**| Getters |**:::::::::::::::**/
    protected function getCallBackUrl()
    {
        return $this->callBackUrl ?: config('mellat.callBackUrl');
    }

    protected function getOrderId()
    {
        return $this->orderId;
    }

    protected function getAmount()
    {
        return config('mellat.convertToRial') ? $this->amount * 10 : $this->amount;
    }

    protected function getDate()
    {
        return $this->date;
    }

    protected function getTime()
    {
        return $this->time;
    }

    protected function getAdditionalData()
    {
        return $this->additionalData;
    }

    protected function getUsername()
    {
        return config('mellat.username');
    }

    protected function getPassword()
    {
        return config('mellat.password');
    }

    protected function getTerminalId()
    {
        return config('mellat.terminalId');
    }

    protected function getPayerId()
    {
        return $this->payerId;
    }

    /**:::::::::::::::**| Helpers |**:::::::::::::::**/

    protected function validate()
    {
        if (!$this->getAmount() || !$this->getTerminalId() || !$this->getCallBackUrl() || !$this->getUsername()
            || !$this->getPassword()) {
            throw new MellatException('Essential fields cannot be null', 500);
        }
    }

    protected function baseData(){
        return [
            'terminalId' => $this->getTerminalId(),
            'userName' => $this->getUsername(),
            'userPassword' => $this->getPassword(),
        ];
    }

    protected function redirectionData()
    {
        return array_merge($this->baseData(), [
            'orderId' => $this->getOrderId(),
            'amount' => $this->getAmount(),
            'localDate' => $this->getDate(),
            'localTime' => $this->getTime(),
            'additionalData' => $this->getAdditionalData(),
            'callBackUrl' => $this->getCallBackUrl(),
            'payerId' => $this->getPayerId()
        ]);
    }

    protected function verifyData(Request $request)
    {
        return array_merge($this->baseData(), [
            'orderId' => $request->get('SaleOrderId'),
            'saleOrderId' => $request->get('SaleOrderId'),
            'saleReferenceId' => $request->get('SaleReferenceId')
        ]);
    }

    protected function createLog($refId)
    {
        MellatLog::create([
            'ref_id' => $refId,
            'amount' => $this->getAmount(),
            'order_id' => $this->getOrderId(),
            'payer_id' => $this->getPayerId()
        ]);
    }

    protected function updateLog(Request $request)
    {
        $log = MellatLog::where('ref_id', $request->get('RefId'))->first();
        if ($log) {
            $log->update([
                'res_code' => $request->get('ResCode'),
                'sale_order_id' => $request->get('SaleOrderId'),
                'sale_reference_id' => $request->get('SaleReferenceId'),
                'status' => $request->get('ResCode') == 0 ? 'successful' : 'unsuccessful',
                'message' => (new MellatException())->getMellatErrorMessage($request->get('ResCode'))
            ]);
        }
        return $log;
    }
}
