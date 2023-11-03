<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;

class PlayerController extends BaseController
{
    // Get player dashboard
    public function get_dashboard(Request $request)
    {
        if (Auth::user()->type != 'player') {
            abort(405);
        }

        $player = Auth::user()->player;

        $files = $player->player_files()->count();
		$matches = $player->player_file_matches()->count();

        return view('dashboards.player', compact('files', 'matches'));
    }

    // List all players to the admin
    public function index()
    {
        if (Auth::user()->type != 'admin') {
            abort(405);
        }

        return view('players.index');
    }

    // Delete player data
    public function delete(Request $request, $id)
    {
        if (!in_array(Auth::user()->type, ['admin'])) {
            abort(405);
        }

        // Validate data

        if (!($ply = Player::find($id))) {
            return $this->error('The player is not valid');
        }

        // Delete the player

        $ply->player_file_matches()->delete();
        $ply->player_files()->delete();
        $ply->user()->delete();
        $ply->delete();

        return $this->success('The player has been deleted!');
    }

    // Lists

    public function ply_list(Request $request)
    {
        if (Auth::user()->type != 'admin') {
            abort(405);
        }

        $params = new stdClass;

        $params->searchColumns = ['name', 'email'];

        $params->orderColumns = ['id', 'name', 'email'];

        $subquery = DB::table('players', 'ply')
        ->leftJoin('users AS us', 'ply.user_id', 'us.id')
        ->selectRaw('
            ply.id,
            name,
            email,
            image
        ');

        $params->builder = DB::query()->fromSub($subquery, 'sub');

        $params->rowsCallback = function ($row) {
            $r['id'] = $row->id;
            $r['__no__'] = $row->__no__;
            $r['name'] = $row->name;
            $r['email'] = $row->email;

            $r['image'] = '<img class="rounded-circle me-2" width="33" src="/storage/thumbnails/' . $row->image . '">' . $row->name;

            $r['actions'] = '
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill delete" title="Delete"><i class="fas fa-trash"></i></button>
            ';

            return $r;
        };

        return $this->fetch_list_data($request, $params);
    }
}
