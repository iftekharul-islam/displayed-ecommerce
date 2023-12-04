<?php
namespace App\Http\Controllers\Admin\Plugin;



use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Plugin;
use AWS\CRT\HTTP\Request;
use Brian2694\Toastr\Facades\Toastr;


class PluginController extends Controller
{
    public function index(){

        $plugins = Plugin::latest()->paginate(get_pagination('pagination'));;

        return view('admin.plugins.index',compact('plugins'));
    }

    public function activatePlugin($baseName){


        DB::beginTransaction();
        try {
            $pluginManager = app('pluginManager');
            dd($pluginManager);
            $status = $pluginManager->activatePlugin($baseName);
            $response['message'] = __('Updated Successfully');
            $response['title']   = __('Success');
            $response['type']    ='addon';
            $response['status']   = 'success';
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }
    public function deactivatePlugin($baseName){

        DB::beginTransaction();
        try {
            $pluginManager = app('pluginManager');
            $status = $pluginManager->deactivatePlugin($baseName);
            $response['message'] = __('Updated Successfully');
            $response['title']   = __('Success');
            $response['type']    ='addon';
            $response['status']   = 'success';
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

}
