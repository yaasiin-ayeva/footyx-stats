<?php

namespace App\Http\Controllers;

use App\Models\AdminFileMatch;
use App\Models\PlayerFile;
use App\Models\PlayerFileMatch;
use App\Models\VarGroup;
use App\Models\Variable;
use App\Models\VarLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Shuchkin\SimpleXLSX;
use stdClass;

class PlayerFileController extends BaseController
{
    // List all files loaded by the player
    public function index()
    {
        if (Auth::user()->type != 'player') {
            abort(405);
        }

        return view('plyf.index');
    }

    // List all matches for a file
    public function get_matches(Request $request)
    {
        if (Auth::user()->type != 'player') {
            abort(405);
        }

        // Validate the file

        $player = Auth::user()->player;

        $plyf = PlayerFile::find($request->input('plyf'));

        if (!$plyf) {
            abort(404);
        }

        if($plyf->player_id != $player->id) {
            abort(405);
        }

        return view('plyf.matches', compact('plyf'));
    }

    // Select Home and Away teams
    public function get_select_teams(Request $request)
    {
        if (Auth::user()->type != 'player') {
            abort(405);
        }

        $player = Auth::user()->player;

        $plyf = PlayerFile::find($request->input('plyf'));

        if (!$plyf) {
            abort(404);
        }

        if($plyf->player_id != $player->id) {
            abort(405);
        }

        $teams = $plyf->get_teams();

        return view('plyf.select_teams', compact('plyf', 'teams'));
    }

    // Predict the result of a match
    public function get_predict(Request $request)
    {
        if (Auth::user()->type != 'player') {
            abort(405);
        }

        $player = Auth::user()->player;

        // Validate player file

        $plyf = PlayerFile::find($request->input('plyf'));

        if (!$plyf) {
            abort(404);
        }

        if($plyf->player_id != $player->id) {
            abort(405);
        }

        // Validate teams

        $teams = $plyf->get_teams();

        $home = $request->input('home');
        
        if(!$home || $teams->where('name', $home)->isEmpty()) {
            abort(404);
        }
        
        $away = $request->input('away');

        if(!$away || $teams->where('name', $away)->isEmpty()) {
            abort(404);
        }

        // Get matches

        $matches = $plyf->player_file_matches()->orderBy('id')->get();

        // Generate variables

        $row = new stdClass;

        $row->home = $home;
        $row->away = $away;
        $row->score = '-';
        $row->date = '-';

        // Get all variables

        $vars = Variable::get()->keyBy('code');

        (new VariableGenerator($vars))->fill_row($row, $matches, $teams);

        // Get var groups

        $var_groups = DB::table('var_groups')->get()->keyBy('id');

        foreach (VarLink::get() as $vl) {
            $var_groups[$vl->var_group_id]->vars[] = $vl->var;
        }

        $vars = $vars->keyBy('id');

        return view('plyf.predict', compact('plyf', 'row', 'vars', 'var_groups'));
    }

    // Save the player file
    public function save_file($plyf, $file) {
        // Generate paths

        $xlsxPath = storage_path('app/' . Storage::putFileAs('tmp', $file, uniqid(rand(), true).'.xlsx'));

        // Open XLSX File

        if(!($xlsx = SimpleXLSX::parse($xlsxPath))) {
            return 'Unable to parse the XLSX File: ' . SimpleXLSX::parseError();
        }

        $matches = [];

        $var_gen = new VariableGenerator(null);

        foreach ($xlsx->rows() as $key => $row) {
            if($key == 0) {
                continue;
            }

            $match = (object)$var_gen->map_row($row);

            $match->player_id = $plyf->player_id;
            $match->player_file_id = $plyf->id;

            $matches[] = (array)$match;
        }

        PlayerFileMatch::insert($matches);

        // Delete temp files
        // unlink($xlsxPath);

        return '';
    }

    protected function getLoadValidator($data) {
        $rules = [
            'file' => 'bail|required|file'
        ];

        $messages = [

        ];

        return Validator::make($data, $rules, $messages);
    }

    public function load(Request $request)
    {
        if (!in_array(Auth::user()->type, ['player'])) {
            abort(405);
        }

        // Retrieve the data

        $data = [
            'file' => $request->file('file'),
        ];

        // Validate the data

        $validator = $this->getLoadValidator($data);

        if ($validator->fails()) {
            return $this->error(['errors' => $validator->getMessageBag()->toArray()]);
        }

        // Check the extension of the file

        $ext = strtolower($data['file']->getClientOriginalExtension());

        if(!in_array($ext, ['xlsx'])) {
            return $this->error('XLSX file expected');
        }

        // Create plyf

        $plyf = PlayerFile::create([
            'name' => $data['file']->getClientOriginalName(),
            'player_id' => Auth::user()->player->id,
        ]);

        // Save the file content

        if($feedback = $this->save_file($plyf, $data['file'])) {
            return $this->error($feedback);
        }

        return $this->success('File loaded successfully');
    }

    // Search matches from admin files
    public function search(Request $request)
    {
        if (!in_array(Auth::user()->type, ['player'])) {
            abort(405);
        }

        // Retrieve the data

        $vars = $request->input('vars');

        // Validate the data

        if(!$vars) {
            return $this->error('No variable selected');
        }

        $var_ids = array_keys($vars);

        if(Variable::whereIn('id', $var_ids)->count() != count($var_ids)) {
            return $this->error('An error occured');
        }

        // Perform the search

        $matches = AdminFileMatch::where($vars)->get();

        return $this->success("{$matches->count()} result(s) matched", compact('matches'));
    }

    // Delete a player file
    public function delete(Request $request, $id)
    {
        if (Auth::user()->type != 'player') {
            abort(405);
        }

        // Validate data

        if(!($plyf = PlayerFile::find($id))) {
            return $this->error('The file is not valid');
        }

        // Delete the file

        $plyf->player_file_matches()->delete();
        $plyf->delete();

        return $this->success("The file has been deleted!");
    }

    // List all schedules
    public function get_schedules()
    {
        if (Auth::user()->type != 'player') {
            abort(405);
        }

        return view('plyf.schedules');
    }

    // Lists

    public function plyf_list(Request $request)
    {
        if (Auth::user()->type != 'player') {
            abort(405);
        }

        $params = new stdClass;

        $params->searchColumns = ['name', 'created_at'];
        $params->orderColumns = ['id', 'name', 'created_at'];

        $subquery = DB::table('player_files')
        ->selectRaw('
            id,
            name,
            created_at
        ');

        $params->builder = DB::query()->fromSub($subquery, 'sub');

        $params->rowsCallback = function ($row) {        
            $r['id'] = $row->id;
            $r['__no__'] = $row->__no__;
            $r['name'] = $row->name;
            $r['name_link'] = '<a href="'.route('plyf.get_matches', ['plyf' => $row->id]).'">'.$row->name.'</a>';

            $r['created_at'] = $row->created_at;

            $r['actions'] = '
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill select_teams" title="Pick teams"><i class="fas fa-mouse-pointer"></i></button>
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill delete" title="Delete"><i class="fas fa-trash"></i></button>
            ';
            
            return $r;
        };

        return $this->fetch_list_data($request, $params);
    }

    public function matches_list(Request $request)
    {
        if (Auth::user()->type != 'player') {
            abort(405);
        }

        // Validate the file

        $player = Auth::user()->player;

        $plyf = PlayerFile::find($request->input('plyf'));

        if (!$plyf) {
            abort(404);
        }

        if($plyf->player_id != $player->id) {
            abort(405);
        }

        $params = new stdClass;
        
        $params->searchColumns = ['year', 'time', 'home', 'away', 'score'];
        $params->orderColumns = ['id', 'year', 'time', 'home', 'away', 'score'];

        $subquery = DB::table('player_file_matches')
        ->selectRaw('
            id,
            date,
            
            year,
            time,
            home,
            away,
            score
        ')
        ->where('player_file_id', $plyf->id);

        $params->builder = DB::query()->fromSub($subquery, 'sub');

        $params->rowsCallback = function ($row) {        
            $r['id'] = $row->id;
            $r['__no__'] = $row->__no__;
            $r['date'] = $row->date;
            
            $r['year'] = $row->year;
            $r['time'] = $row->time;
            $r['home'] = $row->home;
            $r['away'] = $row->away;
            $r['score'] = $row->score;

            return $r;
        };

        return $this->fetch_list_data($request, $params);
    }
}
