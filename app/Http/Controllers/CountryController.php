<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;

class CountryController extends BaseController
{
    // List all countries to the admin
    public function index()
    {
        if (Auth::user()->type != 'admin') {
            abort(405);
        }

        return view('countries.index');
    }

    // Get all leagues for a country
    public function get_leagues(Request $request)
    {
        // Validate data

        if(!($cnt = Country::find($request->input('country_id')))) {
            return $this->error('The country is not valid');
        }

        // Get the leagues

        $leagues = $cnt->leagues()->select('id', 'name')->get();

        return $this->success(compact('leagues'));
    }

    // Empty the country
    public function empty(Request $request, $id)
    {
        // Only an admin can empty a country

        if (Auth::user()->type != 'admin') {
            abort(405);
        }

        // Validate data

        if(!($cnt = Country::find($id))) {
            return $this->error('The country is not valid');
        }

        // Empty the country

        $cnt->admin_file_matches()->delete();
        $cnt->admin_files()->delete();
        $cnt->leagues()->delete();

        return $this->success("{$cnt->name} has been emptied!");
    }

    // Lists

    public function cnt_list(Request $request)
    {
        // Only an admin can get the country list

        if (Auth::user()->type != 'admin') {
            abort(405);
        }

        $params = new stdClass;

        $params->searchColumns = ['name'];
        $params->orderColumns = ['id', 'name'];

        $subquery = DB::table('countries')
        ->selectRaw('
            id,
            name,
            code
        ');

        $params->builder = DB::query()->fromSub($subquery, 'sub');

        $params->rowsCallback = function ($row) {        
            $r['id'] = $row->id;
            $r['__no__'] = $row->__no__;
            $r['name'] = $row->name;
            $r['code'] = $row->code;

            $r['flag_name'] = '
                <span>
                    <img style="display: inline;"
                        src="https://flagcdn.com/16x12/'.$row->code.'.png"
                        srcset="https://flagcdn.com/32x24/'.$row->code.'.png 2x,
                        https://flagcdn.com/48x36/'.$row->code.'.png 3x"
                        width="16"
                        height="12"
                        alt="'.$row->code.'">
                </span>
                <span>'.$row->name.'</span>
            ';

            $r['actions'] = '
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill leagues" title="Leagues"><i class="fas fa-trophy"></i></button>
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill empty" title="Empty"><i class="fas fa-trash"></i></button>
            ';
            
            return $r;
        };

        return $this->fetch_list_data($request, $params);
    }
}
