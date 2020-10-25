<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\EventRequest;
use App\Models\{Order,Event};

class EventController extends Controller
{
    public function index(EventRequest $request){
        $order = new Order();
        $order->amount = $request->amount;
        $order->save();

        $event = new Event();
        $event->id = $order->id;
        $event->email = $request->email;
        $event->environment = env('APP_ENV');
        $event->component = $request->component;
        $event->message = $request->message;
        $event->data = json_encode($order->getAttributes('original'));
        $event->save();

        return response()->json(['status'=> 200, 'message' => 'Event completed successfully']);
    }

    public function get(Request $request){
    	$search = '';
    	if($request->has('message')){
    		$search = $request->message;
    		unset($request['message']);
    	}

    	if($request->has('created_at')){
    	    $from = date('Y-m-d', strtotime($request->created_at));
    	    unset($request['created_at']);
    	    $event=Event::where($request->all())->whereBetween('created_at', [$from." 00:00:00", date('Y/m/d H:i:s')]);
    	    
    	}else{
    		$event=Event::where($request->all());
    	}
    	
		if($search !=''){
			$event=$event->where('message', 'like', '%' . $search . '%')->get();
		}else{
            $event=$event->get();
        }
		$event->map(function($data){
			$data->data = json_decode($data->data);
		});
    	if($event){
    		return response()->json(['status'=> 200, 'data' => $event]);
    	}else{
    		return response()->json(['status'=> 204, 'message' => 'Data not found']);
    	}
    }
    
}
