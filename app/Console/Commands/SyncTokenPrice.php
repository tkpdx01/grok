<?php
namespace App\Console\Commands;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Config;
use App\Models\TicketCurrency;

class SyncTokenPrice extends Command
{

    // 自定义脚本命令签名
    protected $signature = 'sync:tokenprice';

    // 自定义脚本命令描述
    protected $description = '同步薄饼代币价格';


    // 创建一个新的命令实例
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try
        {
            $list = TicketCurrency::query()
                ->where('contract_address', '<>', '0x55d398326f99059ff775485246999027b3197955')
                ->where('is_del', '=', '0')
                ->where('is_sync', '=', 1)
                ->get(['id','contract_address','contract_address_lp','pancake_cate','is_fan'])
                ->toArray();
            if ($list) 
            {
                $client = new Client();
                $usdtContractAddress = '0x55d398326f99059ff775485246999027b3197955';
                $busdContractAddress = '0xe9e7cea3dedca5984780bafc599bd69add087d56';
                
                foreach ($list as $val) 
                {
                    if ($val['pancake_cate']==1)
                    {
                        try
                        {
                            $contract_address = $val['contract_address_lp'];
                            $response = $client->post('http://127.0.0.1:9090/v1/bnb/lpInfo',[
                                'form_params' => [
                                    'contract_address' => $contract_address
                                ]
                            ]);
                            $result = $response->getBody()->getContents();
                            if (!is_array($result)) {
                                $result = json_decode($result, true);
                            }
                            
                            if (!is_array($result) || !$result || !isset($result['code']) || $result['code']!=200 ||
                                !isset($result['data']) || !isset($result['data']['reserve0']) || !isset($result['data']['reserve1']) ||
                                !isset($result['data']['token0']) || !isset($result['data']['token1']))
                            {
                                Log::channel('lp_info')->info('查询LP信息V2失败');
                            }
                            else
                            {
                                $token0 = strtolower($result['data']['token0']);
                                $token1 = strtolower($result['data']['token1']);
                                if ($token1==$usdtContractAddress || $token1==$busdContractAddress) {
                                    $coin_price = @bcdiv($result['data']['reserve1'], $result['data']['reserve0'], 10);
                                } else {
                                    $coin_price = @bcdiv($result['data']['reserve0'], $result['data']['reserve1'], 10);
                                }
                                
                                if (bccomp($coin_price, '0', 6)>0) {
                                    TicketCurrency::query()->where('id', $val['id'])->update(['price'=>$coin_price]);
                                }
//                                 Log::channel('lp_info')->info('查询LP信息V2成功', $result);
                            }
                            
                        }
                        catch (\Exception $e)
                        {
                            Log::channel('lp_info')->info('查询LP信息V2失败', ['error_msg'=>$e->getMessage().$e->getLine()]);
                        }
                    }
                    else
                    {
                        try
                        {
                            $contract_address = $val['contract_address_lp'];
                            $response = $client->post('http://127.0.0.1:9090/v1/bnb/lp3Info',[
                                'form_params' => [
                                    'contract_address' => $contract_address,
                                    'is_fan' => $val['is_fan']
                                ]
                            ]);
                            $result = $response->getBody()->getContents();
                            if (!is_array($result)) {
                                $result = json_decode($result, true);
                            }
                            
                            if (!is_array($result) || !$result || !isset($result['code']) || $result['code']!=200 ||
                                !isset($result['data']) || !isset($result['data']['amountOut']) ||
                                !isset($result['data']['token0']) || !isset($result['data']['token1']))
                            {
                                Log::channel('lp_info')->info('查询LP信息V3失败');
                            }
                            else
                            {
                                $coin_price = @bcadd($result['data']['amountOut'], '0', 6);
                                if (bccomp($coin_price, '0', 6)>0) {
                                    TicketCurrency::query()->where('id', $val['id'])->update(['price'=>$coin_price]);
                                }
//                                 Log::channel('lp_info')->info('查询LP信息V3成功', $result);
                            }
                            
                        }
                        catch (\Exception $e)
                        {
                            Log::channel('lp_info')->info('查询LP信息V3失败', ['error_msg'=>$e->getMessage().$e->getLine()]);
                        }
                    }
                }
            }
        }
        catch (\Exception $e)
        {
            Log::channel('lp_info')->info('查询LP信息失败', ['error_msg'=>$e->getMessage().$e->getLine()]);
        }
    }
}
