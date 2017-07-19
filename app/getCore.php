<?php

use App\UserBaggageEqModel;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Exceptions\Handler;
use Illuminate\Http\Response;
use Carbon\Carbon;
use DateTime;

	function getCore ($baggage_u_id)
	{
		if(isset($baggage_u_id))
		{
			$UserBaggageEqModel=new UserBaggageEqModel();
			$result=[];

			$baggageCore=$UserBaggageEqModel->where('u_id','=',$baggage_u_id)->where('status','=',0)->where('b_equ_type','=',3)->get();
			$result['Baggage_data']['Baggage_Core']=$baggageCore;

			$response=json_encode($result,TRUE);
		}else{
			throw new Exception("No User ID");
			$response=[
			'status' => 'Wrong',
			'error' => "please check u_id",
			];
		}
		return $response;
	}