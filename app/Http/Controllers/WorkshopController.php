<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\UserModel;
use App\CharacterModel;
use App\EquipmentMstModel;
use App\UserBaggageEqModel;
use App\SkillMstModel;
use App\EffectionMstModel;
use App\ImgMstModel;
use App\Util\ItemInfoUtil;
use Exception;
use App\Util\CharSkillEffUtil;
use App\Exceptions\Handler;
use Illuminate\Http\Response;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Redis;
use App\Util\CharSkillEffUtil;
class WorkshopController extends Controller
{
	public function workshop(Request $request)
	{
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);

		$CharacterModel=new CharacterModel();
		$EquipmentMstModel=new EquipmentMstModel();
		$SkillMstModel=new SkillMstModel();
		$result=[];
		$weaponData=[];
		$movementData=[];
		$coreData=[];

		$u_id=$data['u_id'];
		if(isset($u_id))
		{
			$characterDetail=$CharacterModel->where('u_id',$u_id)->first();
			$characterInfo=$CharacterModel->where('u_id',$u_id)->first();
			$result['Workshop_Data']['Character_info']=$characterInfo;

			$WeaponId=$characterDetail['w_id'];
			$weaponDetail=$EquipmentMstModel->select('skill_id','icon_path')->where('equ_id',$WeaponId)->first();
			$MovementId=$characterDetail['m_id'];
			$movementDetail=$EquipmentMstModel->select('skill_id','icon_path')->where('equ_id',$MovementId)->first();
			$CoreId=$characterDetail['core_id'];
			$coreDetail=$EquipmentMstModel->select('skill_id','icon_path')->where('equ_id',$CoreId)->first();
			$WeaSkillId=$weaponDetail['skill_id'];
			$weaSkillDetail=$SkillMstModel->select('skill_icon')->where('skill_id',$WeaSkillId)->first();
			$MoveSkillId=$movementDetail['skill_id'];
			$moveSkillDetail=$SkillMstModel->select('skill_icon')->where('skill_id',$MoveSkillId)->first();
			$CoreSkillId=$coreDetail['skill_id'];
			$coreSkillDetail=$SkillMstModel->select('skill_icon')->where('skill_id',$CoreSkillId)->first();

			$weaponData['equ_id']=$characterDetail['w_id'];
			$weaponData['equ_icon']=$weaponDetail['icon_path'];
			$weaponData['skill_id']=$weaponDetail['skill_id'];
			$weaponData['skill_icon']=$weaSkillDetail['skill_icon'];
			$result['Workshop_Data']['Weapon_Data']=$weaponData;

			$movementData['equ_id']=$characterDetail['m_id'];
			$movementData['equ_icon']=$movementDetail['icon_path'];
			$movementData['skill_id']=$movementDetail['skill_id'];
			$movementData['skill_icon']=$moveSkillDetail['skill_icon'];
			$result['Workshop_Data']['Movement_Data']=$movementData;

			$coreData['equ_id']=$characterDetail['core_id'];
			$coreData['equ_icon']=$coreDetail['icon_path'];
			$coreData['skill_id']=$coreDetail['skill_id'];
			$coreData['skill_icon']=$coreSkillDetail['skill_icon'];
			$result['Workshop_Data']['Core_Data']=$coreData;

			$response=json_encode($result,TRUE);
		}else
		{
			throw new Exception("there have some error of you access_token");
			$response=[
			'status' => 'Wrong',
			'error' => "please check u_id",
			];
		}
		return base64_encode($response);
	}

	public function showEquipmentInfo (Request $request)
	{
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);

		$EquipmentMstModel=new EquipmentMstModel();
		$ItemInfoUtil=new ItemInfoUtil();
		$result=[];

		$Item_Id=$data['equ_id'];
		if(isset($Item_Id))
		{
			$EquipmentDetail = $ItemInfoUtil->getEquipmentInfo($Item_Id);
			$response=$EquipmentDetail;
		}else
		{
			throw new Exception("Wrong Equipment ID data");
			$response=[
			'status' => 'Wrong',
			'error' => "please check Equipment ID data",
			];
		}
		return base64_encode($response);
	}

	public function showSkillInfo (Request $request)
	{
		$req=$request->getContent();
		$json=base64_decode($req);
		$data=json_decode($json,TRUE);

		$SkillMstModel=new SkillMstModel();
		$ItemInfoUtil=new ItemInfoUtil();
		$result=[];

		$skill_id=$data['skill_id'];
		if(isset($skill_id))
		{
			$SkillDetail = $ItemInfoUtil->getSkillInfo($skill_id);
			$response=$SkillDetail;
		}else
		{
			throw new Exception("Wrong Skill ID data");
			$response=[
			'status' => 'Wrong',
			'error' => "please check Skill ID data",
			];
		}
		return base64_encode($response);
	}

	//compare two equipments in the workshop. show the details of equipments and the skills.
	public function compareEquipment (Request $request)
	{
		$req=$request->getContent();
		$data=json_decode($req,TRUE);

		$EquipmentMstModel=new EquipmentMstModel();
		$CharacterModel=new CharacterModel();
		$SkillMstModel=new SkillMstModel();
		$ItemInfoUtil=new ItemInfoUtil();
		$result=[];

		$u_id=$data['u_id'];
		$equ_id=$data['equ_id'];

		if(isset($u_id))
		{
			$Equ_click_detail=$ItemInfoUtil->getEquipmentInfo($equ_id);
			$Skill_click_id=$EquipmentMstModel->where('equ_id',$equ_id)->pluck('skill_id');
			$Skill_click_detail=$ItemInfoUtil->getSkillInfo($Skill_click_id);
			$result['equ_click_data']=$Equ_click_detail;
			$result['equ_click_data']=$Skill_click_detail;

			$Equ=$EquipmentMstModel->where('equ_id',$equ_id)->first();
			$Equ_part=$Equ['equ_part'];

			if($Equ_part == 1)
			{
				$Equ_now_id=$CharacterModel->where('u_id',$u_id)->pluck('w_id');
				$Equ_now_detail=$ItemInfoUtil->getEquipmentInfo($Equ_now_id);
				$Skill_now_id=$EquipmentMstModel->where('equ_id',$Equ_now_id)->pluck('skill_id');
				$Skill_now_detail=$ItemInfoUtil->getSkillInfo($Skill_now_id);
				$result['equ_now_data']=$Equ_now_detail;
				$result['equ_now_data']=$Skill_now_detail;
			}else if($Equ_part == 2)
			{
				$Equ_now_id=$CharacterModel->where('u_id',$u_id)->pluck('m_id');
				$Equ_now_detail=$ItemInfoUtil->getEquipmentInfo($Equ_now_id);
				$Skill_now_id=$EquipmentMstModel->where('equ_id',$Equ_now_id)->pluck('skill_id');
				$Skill_now_detail=$ItemInfoUtil->getSkillInfo($Skill_now_id);
				$result['equ_now_data']=$Equ_now_detail;
				$result['equ_now_data']=$Skill_now_detail;
			}else if($Equ_part == 3)
			{
				$Equ_now_id=$CharacterModel->where('u_id',$u_id)->pluck('core_id');
				$Equ_now_detail=$ItemInfoUtil->getEquipmentInfo($Equ_now_id);
				$Skill_now_id=$EquipmentMstModel->where('equ_id',$Equ_now_id)->pluck('skill_id');
				$Skill_now_detail=$ItemInfoUtil->getSkillInfo($Skill_now_id);
				$result['equ_now_data']=$Equ_now_detail;
				$result['equ_now_data']=$Skill_now_detail;
			}
			$response=json_encode($result,TRUE);			
		}else{
			throw new Exception("there have some error of you access_token");
			$response=[
			'status' => 'Wrong',
			'error' => "please check u_id",
			];
		}
		return $response;
	}

	//after user change equipment, adjust the attributes of chararcter and change the character image
	public function equipEquipment (Request $request)
	{
		$req=$request->getContent();
		$data=json_decode($req,TRUE);
		$now=new DateTime;
		$datetime=$now->format( 'Y-m-d h:m:s' );
		$dmy=$now->format( 'Ymd' );
		
		$CharacterModel=new CharacterModel();
		$EquipmentMstModel=new EquipmentMstModel();
		$ImgMstModel=new ImgMstModel();
		$charUtil=new CharSkillEffUtil();
		$UserBaggageEqModel=new UserBaggageEqModel();
		$result=[];

		$u_id=$data['u_id'];
		$equ_id=$data['equ_id'];

		if(isset($u_id))
		{
			$characterDetail=$CharacterModel->where('u_id',$u_id)->first();
			$w_id=$characterDetail['w_id'];
			$m_id=$characterDetail['m_id'];
			$core_id=$characterDetail['core_id'];
			$hp=$characterDetail['ch_hp_max'];
			$atk=$characterDetail['ch_atk'];
			$def=$characterDetail['ch_def'];
			$crit=$characterDetail['ch_crit'];
			$cd=$characterDetail['ch_cd'];

			$EquNew=$EquipmentMstModel->where('equ_id',$equ_id)->first();
			$Equ_part=$EquNew['equ_part'];

			$UserBaggageEqModel->equipNewWeapon($u_id,$equ_id,$Equ_part);
			if($Equ_part==1){
				$CharacterModel->update(['w_id'=>$equ_id,'update_at'=>$datetime])->where('u_id',$u_id);
			}
			else if($Equ_part==2){
				$CharacterModel->update(['m_id'=>$equ_id,'update_at'=>$datetime])->where('u_id',$u_id);

			}
			else if($Equ_part==2){
				$CharacterModel->update(['core_id'=>$equ_id,'update_at'=>$datetime])->where('u_id',$u_id);
			}
			$newchar=$charUtil->calculatCharEq($u_id);
			$result['change_part']=$Equ_part;
			$result['character']=$newchar;

			}else{
				throw new Exception("Change equipment error");
				$response=[
				'status' => 'Wrong',
				'error' => "please check workshop controller",
				];
			}
		$response=json_encode($result,TRUE);	
		return $response;
	}
}