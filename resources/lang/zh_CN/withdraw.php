<?php 
return [
    'labels' => [
        'Withdraw' => '提币订单',
        'withdraw' => '提币订单',
    ],
    'fields' => [
        'p_id' => '父订单ID',
        'p_ordernum' => '父订单号',
        'ordernum' => '子订单号',
        'user_id' => '用户ID',
        'receive_address' => '接收方地址',
        'coin_type' => '提现币种',
        'w_type' => '提现类型',
        'usdt' => 'USDT价值',
        'num' => '出金数量',
        'fee' => '手续费比例',
        'fee_amount' => '手续费金额',
        'ac_amount' => '实际到账金额',
        'status' => '状态',
        'finsh_time' => '到账时间',
        'hash' => '哈希',
        'active_num' => '直推活跃用户数量',
        'withdraw_destroy_usdt' => '销毁USDT价值',
        'withdraw_destroy_xhy' => '销毁XHY数量',
        'xhy_price' => 'XHY价格',
    ],
    'options' => [
    ],
];
