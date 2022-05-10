<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Events\SendMessage;
use App\Models\file_uploads;
use App\Jobs\CsvFormaterJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;


class FileUploader extends Controller
{
    public function index()
    {
        $file = DB::select('select * from file_uploads');
        return view('uploadfile', ['dataFile' => $file ]);
    }

    public function t()
    {
        // broadcast(new \App\Events\SendMessage('hehe'));
        return 'test';
    }
 
    public function uploadToServer(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv',
        ]);
        $name = request()->file->getClientOriginalName();
        $fileExist = DB::table('file_uploads')
                ->where('name', '=', $name)
                ->first();
        // print_r($fileExist->id);
        if ($fileExist) {
            DB::table('file_uploads')
            ->where('id', $fileExist->id)
            ->update(['upload_at' => date('Y-m-d H:i:s')]);
            $id = $fileExist->id;
        } else {
            $file = new file_uploads;
            $file->name = $name;
            $file->status = 'pending';
            $file->upload_at = date('Y-m-d H:i:s');
            $file->save();
            $id = $file->id;
        }
        $request->file->move(public_path('uploads'), $name);
        $msg = [];
        $msg['id'] = $id;
        $msg['name'] = $name;
        $msg['time'] = date('Y-m-d H:i:s');
        $msg['status'] = 'pending';
        SendMessage::dispatch($msg);
        CsvFormaterJob::dispatch(['id' =>$id, 'name' => $name]);
        return response()->json(['success'=>'Successfully uploaded.']);
        // $request->validate([
        //     'file' => 'required|mimes:csv',
        // ]);
 
        // $name = request()->file->getClientOriginalName();
        // $fileExist = DB::table('file_uploads')
        //         ->where('name', '=', $name)
        //         ->first();
        // // print_r($fileExist->id);
        // if ($fileExist) {
        //     DB::table('file_uploads')
        //     ->where('id', $fileExist->id)
        //     ->update(['upload_at' => date('Y-m-d H:i:s')]);
        //     $id = $fileExist->id;
        //     print_r('aaaaaaaaaaaa');
        // } else {
        //     $file = new FileUpload;
        //     $file->name = $name;
        //     $file->status = 'pending';
        //     $file->upload_at = date('Y-m-d H:i:s');
        //     $file->save();
        //     $id = $file->id;
        //     print_r('bbbbbbbbbbbbb');
        // }

        // $request->file->move(public_path('uploads'), $name);
        // $msg = [];
        // $msg['id'] = $id;
        // $msg['name'] = $name;
        // $msg['time'] = date('Y-m-d H:i:s');
        // $msg['status'] = 'pending';
        // SendMessage::dispatch($msg);
        // ProcessCsv::dispatch(['id' =>$id, 'name' => $name]);
        // Excel::import(new ProductsImport(['id' =>$id, 'name' => $name]), public_path('uploads').'/'.$name, null, \Maatwebsite\Excel\Excel::CSV);
        // return response()->json(['success'=>'Successfully uploaded.']);
    }
}
