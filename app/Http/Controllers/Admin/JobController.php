<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductImportFail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    public function product_queue_record($id){
        $progress=DB::table('job_progress')->select(['job_id','total_jobs','completed_jobs'])->where('job_id',$id)->first();
        if($progress){
            $percentage = ($progress->completed_jobs / $progress->total_jobs) * 100;
            return response()->json([
                'total_jobs' => $progress->total_jobs,
                'completed_jobs' => $progress->completed_jobs,
                'percentage' => $percentage,
            ]);
        }
        else{
            return response()->json(['no job available'],200);
        }
    }

    public function product_import_report($id){
       $res= ProductImportFail::where('job_id',$id)->get();
       return view('admin-views.product.bulk-import-report',compact('res'));
    }
}
