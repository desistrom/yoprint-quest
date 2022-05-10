<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use DB;
use App\Events\SendMessage;

class CsvFormaterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::table('file_uploads')
            ->where('id', $this->data['id'])
            ->update(['status' => 'processing']);
        $msg = [];
        $msg['id'] = $this->data['id'];
        $msg['name'] = $this->data['name'];
        $msg['time'] = date('Y-m-d H:i:s');
        $msg['status'] = 'processing';
        SendMessage::dispatch($msg);
        $files = public_path('uploads').'/'.$this->data['name'];
        $file = fopen($files, "r");
        while ( ($data = fgetcsv($file, 0, ",")) !==FALSE ){
            $newStr = [];
            foreach ($data as $key => $value) {
               $newStr[] = utf8_encode($value);
            }
            Cache::add('file_'.$this->data['name'].'_'.$data[0], json_encode($newStr));
            Redis::set('file_'.$this->data['name'].'_'.$data[0], json_encode($newStr));
        }
        fclose($file);
        DB::table('file_uploads')
            ->where('id', $this->data['id'])
            ->update(['status' => 'completed']);
        $msg = [];
        $msg['id'] = $this->data['id'];
        $msg['name'] = $this->data['name'];
        $msg['time'] = date('Y-m-d H:i:s');
        $msg['status'] = 'completed';
        SendMessage::dispatch($msg);
    }

    public function failed()
    {
        DB::table('file_uploads')
            ->where('id', $this->data['id'])
            ->update(['status' => 'failed']);
        $msg = [];
        $msg['id'] = $this->data['id'];
        $msg['name'] = $this->data['name'];
        $msg['time'] = date('Y-m-d H:i:s');
        $msg['status'] = 'failed';
        SendMessage::dispatch($msg);
    }
}
