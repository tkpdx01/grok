<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Spatie\ShortSchedule\ShortSchedule;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //用户组团 不在这里执行 shell脚本20秒一次
        //* * * * * /www/wwwroot/xhy_admin/syncgamejoin.sh
//         $schedule->command('sync:SyncGameJoin')->cron('* * * * *');
        //查询价格
//        $schedule->command('sync:MineRelease')->daily();
        $schedule->command('sync:MineRelease')->everyMinute()->between('00:00','00:10')->withoutOverlapping();
        //释放矿机收益

    }


    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
