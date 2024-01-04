<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;

class MainCurrency extends Model
{
	use HasDateTimeFormatter;


    protected $table = 'main_currency';

    /**
     * 获取BHB兑U汇率
     * @return mixed
     */
    public static function getBhbRate(){
        return self::query()->where('name','SCC')->value('rate');
    }


    public static function getLpPowerByInvest($invest,$num){
        $bhbPrice = self::query()->where('name','LP')->value('rate');
        $client = new Client();
        $response = $client->post('http://127.0.0.1:9090/api/wallet/pro/getSwapInfo',[
            'form_params' => [
                'mainChain' => 'BNB',
                'contractAddress' => MainCurrency::query()->where('name','LP')->value('contract_address'),
            ]
        ]);
        $lpResponse = json_decode($response->getBody()->getContents(),true);

        $a= number_format($lpResponse['obj']['reserve1']/$lpResponse['obj']['totalSupply'], 9, '.', '');
        $b = bcmul($num,2,9);
        $power = bcdiv(bcmul($b,$a,9),$bhbPrice,9);
        $acPower = bcmul($power,$invest->rate/100,9);
        $bhbNum = $num;
        return compact('bhbNum','power','acPower');
    }

}
