<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use App\RegistryTool;
use App\InventoryTools;
use App\Category;
use App\Staging;
use DB;


class CartController extends Controller
{
    //

    public function store(Request $request, $registry_tool_id)
    {
        
    	$find = $registry_tool_id;

    	

        //join table resulting table will only reflect all items of the registry_tools
        $tools = DB::table('categories')
            ->join('registry_tools','categories.id','=','registry_tools.category_id')
            ->select('categories.*','registry_tools.*')
            ->where('registry_tools.id','=', $find )
            ->get();

        $counts = DB::table('inventory_tools')
            ->join('registry_tools','inventory_tools.registry_tool_id','=','registry_tools.id')
            ->leftJoin('borrows__inventory_tools','inventory_tools.id','=','borrows__inventory_tools.inventory_tool_id')
            ->select('inventory_tools.*','registry_tools.*','borrows__inventory_tools.*')
            ->where('inventory_tools.status','=','functioning')
            ->get();
   
      
        $stage = Staging::all();

        


       	//loop thru until we have one item that matches the registrytool_id 

       	$found = 0;

        foreach($tools as $tool) 
        {
        	foreach($counts as $count) 
        	{
        		if($count->registry_tool_id == $tool->id)
        		{
                    if($stage->containsStrict('tool_serial',$count->tool_serial) || $found > 0 )
                    {
                        $found = 300;
                    }
                    else
                    {
                        $found = 1;
                        $addtostaging = new Staging;

                        $addtostaging->tool_serial = $count->tool_serial;
                        $addtostaging->inCart = "yes";

                        $addtostaging->save();

                        $cart = Session::get('cart');

                        if(array_key_exists($count->tool_serial, $cart)){
                            $cart = 0;
                        } else {
                            $cart[$count->tool_serial] = 1;
                        }

                        Session::put('cart', $cart);




                    }
                    
                   
        				
        		}
        	}
        }

        dd($cart);
         
       
    	//when one item is found put in a session cart [tool_serial, user]


    	//return back

        
                 


        

        if(array_key_exists($find, $cart)) {
            $cart[$find] += (int)$request->quantity; 
        } else {
            $cart[$find] = (int)$request->quantity;
        }
 
        Session::put('cart', $cart);

        
        
        // return redirect ('/catalogue');
        
    }
}