<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use stdClass;

class BaseController extends Controller
{
    protected function exit($message, $status, $data) {
        if(is_array($message)) {
            $data = array_merge($message, $data);
            $message = '';
        }

        return response()->json(array_merge(
            [
                'status' => $status,
                'message' => $message
            ],
            $data
        ));
    }

    protected function success($message = '', $data = []) {
        return $this->exit($message, 'success', $data);
    }

    protected function error($message = '', $data = []) {
        return $this->exit($message, 'error', $data);
    }

    private function get_list_inputs($request)
    {
        $inputs = new stdClass;

        $inputs->search = $request->input('search')['value'] ?? '';

        $inputs->order = null;

        if($request->has('order')) {
            $inputs->order = new stdClass;
            $inputs->order->column = $request->input('order')['0']['column'];
            $inputs->order->dir = $request->input('order')['0']['dir'];
        }

        if($request->input('length') !== -1) {
            $inputs->offset = $request->input('start');
            $inputs->limit = $request->input('length');
        }

        $inputs->draw = intval($request->input('draw'));

        return $inputs;
    }

    public function fetch_list_data(Request $request, $params)
    {        
        if(isset($params->columns)) {
            if(!isset($params->searchColumns)) {
                $params->searchColumns = $params->columns;
            }
            
            if(!isset($params->orderColumns)) {
                $params->orderColumns = $params->columns;
            }
        }

        $inputs = $this->get_list_inputs($request);

        // Count
        $recordsFiltered = $recordsTotal = with(clone $params->builder)->count();

        // Search
        if($inputs->search != '') {
            $params->builder->where(function($q) use($params, $inputs) {
                $q->whereRaw('0');

                foreach ($params->searchColumns as $colname) {
                    $q->orWhere($colname, 'LIKE', '%'.$inputs->search.'%');
                }
            });
        
            $recordsFiltered = with(clone $params->builder)->count();
        }

        // Order
        if($inputs->order) {
            $params->builder->orderBy($params->orderColumns[$inputs->order->column], $inputs->order->dir);
        }
        
        // Limit
        if($inputs->limit != -1) {
            $params->builder->offset($inputs->offset)->limit($inputs->limit);
        }

        $data = $params->builder->get();

        // Add line number

        foreach ($data as $key => $value) {
            $value->__no__ = $inputs->offset + $key + 1;
        }

        // Apply callback
        
        if(isset($params->rowsCallback)) {
            $data = $data->map($params->rowsCallback);
        }

        return response()->json([
            'draw' => $inputs->draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
    }
}
