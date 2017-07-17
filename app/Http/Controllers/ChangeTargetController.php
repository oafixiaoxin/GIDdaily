<?php
	namespace App\Http\Controllers;
	use Illuminate\Support\Facades\DB;
//	use Illuminate\Database\Eloquent\ModelNotFoundException;
//	use Illuminate\Database\MySqlConnection;
	use Illuminate\Http\Request;
	use App\Response;

	class ChangeTargetController extends Controller
	{
	    /**
	     * Create a new controller instance.
	     *
	     * @return void
	     */
	    public function __construct()
	    {
	        //
	    }
	    
	    /*
	     * type:1->店长以上角色，0->店员
	     * shopId: 店铺id
	     * year: 年份
	     * month: 月份
	     * day: 几号
	     */
		public function getTarget( $type, $shopId, $year, $month, $day )
		{
			$retAry = array();
			$shopTarget;
			if ( $type == 0 )
			{
				//店员角色不做处理
				$shopTarget = '';
			}
			else
			{
				$shopTarget = DB::select('SELECT upperLimit AS shopMonthTarget,
(SELECT upperLimit FROM unierp_user_target WHERE 1=1 AND userId='.$shopId.' AND `year`='.$year.' AND `month`='.$month.' AND `day`='.$day.') AS shopDayTarget 
FROM unierp_user_target WHERE 1=1 AND userId='.$shopId.' AND `year`='.$year.' AND `month`='.$month.' AND `day`=0');
			}
			
			//个人月目标
			$userTargetList = DB::select('SELECT ta.userId,ta.nickname,
IFNULL((SELECT upperLimit FROM unierp_user_target WHERE 1=1 AND userId=ta.`userId` AND `year`='.$year.' AND `month`='.$month.' AND `day`=0), 0) AS upperLimit,
tb.`fileName`
FROM unierp_user ta
LEFT JOIN unierp_images tb ON ta.`imageId`=tb.`id`');

			$retAry['shopTarget'] = $shopTarget;
			$retAry['userTargetList'] = $userTargetList;
			
			if ( !empty($userTargetList) )
			{
				return $this->output(Response::SUCCESS, $retAry);
			}
			else
			{
				return $this->output(Response::NO_MORE_INFO);
			}
			
		}
		
		/*
		 * shopId: 店铺id
		 * year: 年份
		 * month: 月份
		 * day: 几号(day=0时，查询月份)
		 * target： 目标数值
		 */
		public function updateShopTarget( Request $request )
		{
			$shopId = $request->input('shopId');
			$year = $request->input('year');
			$month = $request->input('month');
			$day = $request->input('day');
			$target = $request->input('target');
			//先查询该店铺在该日期有无目标
			$haveTarget = DB::select('SELECT id FROM unierp_user_target WHERE 1=1 AND userId='.$shopId.' AND `year`='.$year.' AND `month`='.$month.' AND `day`='.$day);
			
			//店铺存在有目标，则更新
			if ( !empty($haveTarget) )
			{
				$sqlStr = 'update unierp_user_target set `upperLimit`='.$target.' where 1=1 and userId='.$shopId.' and `year`='.$year.' and `month`='.$month.' and `day`='.$day;
				$result = DB::update($sqlStr);
			}
			//反之新增目标
			else
			{
				DB::insert('insert into unierp_user_target (`year`,`month`,`day`,`upperLimit`,`userId`) values (?,?,?,?,?)', [$year, $month, $day, $target, $shopId]);
				$haveTarget1 = DB::select('SELECT id FROM unierp_user_target WHERE 1=1 AND userId='.$shopId.' AND `year`='.$year.' AND `month`='.$month.' AND `day`='.$day);
				if ( !empty($haveTarget1) )
				{
					$result = 1;
				}
				else
				{
					$result = 0;
				}
			}
			
			if ( $result == 1 )
			{
				return $this->output(Response::SUCCESS);
			}
			else
			{
				return $this->output(Response::WRONG_OPERATION);
			}
			
		}
		
		
		/*
		 * 
		 */
		public function updateUsersTarget( Request $request )
		{
			$ary = $request->input('ary');
			$year = $request->input('year');
			$month = $request->input('month');
			
			$len = count($ary);
			if ( $len != 0 )
			{
				$temp = 0;
				for ( $i = 0 ; $i < $len ; $i++ )
				{
					if ( $ary[$i]['upperLimit'] != 0 )
					{
						$result = DB::select('select id from unierp_user_target where 1=1 and `userId` = :userId and `year` = :year and `month` = :month and `day` = :day', ['userId'=>$ary[$i]['userId'], 'year'=>$year, 'month'=>$month, 'day'=>0]);
						if ( empty($result) )
						{
							//insert
							$id = DB::table('unierp_user_target')->insertGetId(
								['year' => $year, 'month' => $month, 'day' => 0, 'upperLimit' => $ary[$i]['upperLimit'], 'userId' => $ary[$i]['userId']]
							);
							if ( $id != null )
							{
								$temp++;
							}
						}
						else
						{
							//update
							$affected = DB::update('update unierp_user_target set `upperLimit`=? where 1=1 and `userId`=? and `year`=? and `month`=? and `day`=?', [$ary[$i]['upperLimit'], $ary[$i]['userId'], $year, $month, 0]);
							if ( isset($affected) )
							{
								$temp ++;
							}
						}
					}
				}
				
				if ( $temp != $len )
				{
//					DB::rollback();
					return $this->output(Response::WRONG_OPERATION);
				}
				else
				{
//					DB::commit();
					return $this->output(Response::SUCCESS);
				}
			}
			else
			{
				
			}
		}
	}