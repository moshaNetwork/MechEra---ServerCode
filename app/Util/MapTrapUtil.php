<?php
namespace App\Util;
use App\Http\Requests;
use App\MapTrapRelationMst;
use App\MapModel;
use App\TrapMstModel;
use App\EffectionMstModel;
use Exception;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Redis;

class MapTrapUtil
{
	//show the quantity and icon for every item in the baggage
	function getTrapEff($map_id,$x1,$x2,$x3,$y)
	{ 
		$mapRelation=new MapTrapRelationMst();
		$effect=new EffectionMstModel();
		$trap=new TrapMstModel();
		$mapEff=[];
		$grass=$mapRelation->where(function($query){
        				$query->Where('map_id',$map_id)->where('trap_id',1)
        				->where('trap_x_from','>=',abs($x1))
        				->where('trap_x_to','<=',abs($x2))->where('trap_y_from','<=',abs($y))->where('trap_y_to','>',abs($y))
            				->orWhere(function($query){
                				->Where('map_id',$map_id)->where('trap_id',1)
                				->where('trap_x_from','<=',abs($x2))
                				->where('trap_x_to','>=',abs($x3))->where('trap_y_from','<=',abs($y))->where('trap_y_to','>',abs($y))
           				});
   					})->first();

         if(!$grass){
         	$brambles=$mapRelation
        				->Where('map_id',$map_id)->where('trap_id',1)
        				->where('trap_x_from','>=',abs($x1))
        				->where('trap_x_to','<=',abs($x3))->where('trap_y_from','<=',abs($y))->where('trap_y_to','>',abs($y))
   					->first();
         
        	if($brambles){
         		$trapData=$trap->where('trap_id',$brambles['trap_id'])->first();
        		$mapEff=$effect->where('eff_id',$brambles['eff_id'])->first();
				$mapEff['trap_id']=$mapData['trap_id'];
         	}
     	else{
				$trapData=$trap->where('trap_id',$grass['trap_id'])->first();
				$mapEff=$effect->where('eff_id',$grass['eff_id'])->first();
				$mapEff['trap_id']=$mapData['trap_id'];
     	}
     }

		return $mapEff;
	}


	// function nearStone($map_id,$userX1,$userX2,$effectXfrom,$effectX,$effectY)
	// {	
 //        $mapRelation=new MapTrapRelationMst();
	// 	$effect=new EffectionMstModel();
	// 	$trap=new TrapMstModel();
 //        if($direction==1){
	// 	      $mapData=$mapRelation->where(function($query){
 //        				$query->Where('map_id',$map_id)->where('trap_id',1)->where('trap_x_to',abs($userX1)+1)->where('trap_y_from','<=',abs($y)->where('trap_y_to','>',abs($y)-1)
 //            				->orWhere(function($query){
 //                				->Where('map_id',$map_id)->where('trap_id',1)->where('trap_x_from',abs($userX3)-1)->where('trap_y_from','<=',abs($y)->where('trap_y_to','>',abs($y)-1)
 //           				});
 //   					})->first();
 //                }

 //        if($mapData){
 //        	if($mapData['trap_x_from']==abs($effectXfrom)||$mapData['trap_x_from']>=abs($effectX)||$mapData['trap_x_to']==abs($effectXfrom)||$mapData['trap_x_to']==abs($effectX)){
 //        		return true;
 //        		}
 //        	else {
 //        		return false;
 //        	}
 //        }
 //        return false;
 //    }

        function checkEffstone($map_id,$effectXfrom,$effectXto,$effectYfrom,$effectYto)
    {       $mapRelation=new MapTrapRelationMst();
            $mapData=$mapRelation->where(function($query){
                     $query->Where('map_id',$map_id)->where('trap_id',1)->where('trap_x_from','<=',$effectXto)->where('trap_y_to','>=',$effectYto->where('trap_y_from','>=',$effectYfrom)


    }

}