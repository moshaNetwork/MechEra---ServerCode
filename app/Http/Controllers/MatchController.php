<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\UserModel;
use App\MatchRangeModel;
use App\CharacterModel;
use Illuminate\Support\Facades\Redis;
use DateTime;
class MatchController extends Controller
{
    public function match(Request $request)
    {
    	$req=$request->getContent();
		$json=base64_decode($req);
	 	//dd($json);
		$data=json_decode($json,TRUE);
		$u_id=$data['u_id'];
		//push to match list
		$now   = new DateTime;;
		$dmy=$now->format( 'Ymd' );
		$data=json_decode($json,TRUE);
		$loginToday=Redis::HGET('login_data',$dmy.$data['u_id']);
		$loginTodayArr=json_decode($loginToday);
		// $access_token=$loginTodayArr->access_token;
		// if($access_token==$data['access_token'])
		// {
     		$usermodel=new UserModel();
     		$matchrange=new MatchRangeModel();
     		$characterModel=new CharacterModel();
     		$chardata=$characterModel->where('u_id',$u_id)->first();
     		if(isset($chardata)){
     		$maxLv=$matchrange->max('user_lv_to');
     		$maxStar=$matchrange->max('star_from');
		 	$match=$matchrange->where('star_from','<=',$chardata['ch_star'])->where('star_to','>=',$chardata['ch_star'])->first();
			if($chardata['ch_lv']<$maxLv){
			$matchKey='match_below_maxlv_star'.$match['star_from'].'to'.$match['star_to'];
			}
			else{
				$matchKey='match_maxlv_star'.$match['star_from'].'star'.$match['star_to'];
			}
			$matchList=Redis::LLEN($matchKey);

			if($matchList==0||!$matchList){
				Redis::LPUSH($matchKey,$u_id);
				return "wait in list";
			}
			else {
				$match_uid=Redis::LRANGE($matchKey,0,1);
				if($matchList==1&&$match_uid[0]==$u_id){
					return "wait in list";
				}
				else{
					$match_uid=Redis::LPOP($matchKey);
					$result['match_result']=$match_uid;
					$enmeydata=$usermodel->where('u_id',$match_uid)->first();
					
					$match=[$u_id,$match_uid];
					$match_id='m'.time();
					Redis::HSET('match_list',$match_id,$match);
					$enmeydata['match_id']=$match_id;
					$response=json_encode($enmeydata,TRUE);
					return base64_encode($response);
				}
			}
		}
		 else{
 	    		throw new Exception("no exist character of this user");
 	    }
        
    	// }
    	// else{
 	   //  		throw new Exception("there have some error of you access_token");
 	   //  }
 	    	
 }
}
