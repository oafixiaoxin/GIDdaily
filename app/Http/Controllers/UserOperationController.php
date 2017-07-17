<?php
	namespace App\Http\Controllers;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Http\Request;
	use App\Response;

	class UserOperationController extends Controller
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
		 * 用户登陆
		 * userName: 帐号
		 * password: 密码
		 */
	    public function userLogin($userName, $password)
	    {
	    	$judgeUserName = DB::select('select * from unierp_user where loginName="'.$userName.'"');
	    	if (!empty($judgeUserName))
	    	{
	    		$judgePassword = DB::select('select userId,shopId from unierp_user where loginName="'.$userName.'" and password="'.$password.'"');
	    		if (!empty($judgePassword))
	    		{
	    			return $this->output(Response::SUCCESS, $judgePassword);
	    		}
	    		else
	    		{
	    			return $this->output(Response::PASSWORD_INCORRECT);
	    		}
	    	}
	    	else
	    	{
	    		return $this->output(Response::USER_NOT_FOUND);
	    	}
	    }
	    
	    
	    /*
	     * 获取用户信息
	     * userId: 用户ID
	     */
	    public function getUserInfo($userId)
	    {
	    	$userInfo = DB::select('SELECT ta.`userId`,ta.`nickname`,ta.`phone`,ta.`age`,ta.`sex`,td.`fileName`,tb.`shopName`,tb.`shopScope`,tc.jobName FROM unierp_user ta 
LEFT JOIN unierp_shop tb ON ta.`shopId`=tb.`id`
LEFT JOIN unierp_job tc ON ta.`jobId`=tc.jobId
LEFT JOIN unierp_images td ON ta.`imageId`=td.id
WHERE userId="'.$userId.'"');
	    	if (!empty($userInfo))
	    	{
	    		return $this->output(Response::SUCCESS, $userInfo);
	    	}
	    	else
	    	{
	    		return $this->output(Response::USER_NOT_FOUND);
	    	}
	    }
	    
	    
	    /*
	     * 修改用户信息
	     * post参数
	     */
	    public function modifyUserInfo(Request $request)
	    {
	    	$nickname = $request->input('nickname');
	    	$age = $request->input('age');
	    	$phone = $request->input('phone');
	    	$shopName = $request->input('shopName');
	    	$areaStyle = $request->input('areaStyle');
	    	$shopStyle = $request->input('shopStyle');
	    	$shopArea = $request->input('shopArea');
	    	$userSex = $request->input('userSex');
	    	$userId = $request->input('userId');
	    	$sqlStr = 'update unierp_user set nickname="'.$nickname.'",sex='.$userSex.',age='.$age.',phone='.$phone.' where 1=1 and userId="'.$userId.'"';
	    	$ary = DB::update($sqlStr);
//	    	$ary = DB::update('update unierp_user set nickname=?,sex=?,age=?,phone=? where userId=?',[$nickname, $userSex, $age, $phone, $userId]);
//			return $sqlStr;die;
	    	if ( $ary == 1 )
	    	{
	    		return $this->output(Response::SUCCESS);
	    	}
	    	else
	    	{
	    		return $this->output(Response::MODIFY_USER_INFO_FAILED);
	    	}
	    }
	    
	    /*
	     * 获取用户信息列表
	     */
	    public function getUserList ($shopId)
	    {
	    	$userlist = DB::select('SELECT ta.`userId`,ta.`nickname`,ta.`sex`,ta.`age`,ta.`phone`,tb.jobName,tc.shopName,td.`fileName`,IFNULL((SELECT upperLimit FROM unierp_user_target WHERE utId=ta.`userTargetId`), 0) AS userTarget,ta.active  FROM unierp_user ta
LEFT JOIN unierp_job tb ON ta.`jobId`=tb.jobId
LEFT JOIN unierp_shop tc ON ta.`shopId`=tc.id
LEFT JOIN unierp_images td ON ta.`imageId`=td.id
WHERE 1=1 AND ta.`shopId`='.$shopId);
			if ( !empty($userlist) )
			{
				return $this->output(Response::SUCCESS, $userlist);
			}
			else
			{
				return $this->output(Response::NO_MORE_INFO);
			}
	    }
	    
	    /*
	     * 获取用户信息列表,需要传时间year | month | day
	     */
	    public function getUserListByTime ($shopId, $time)
	    {
	    	$selStr = ',IFNULL((SELECT upperLimit FROM unierp_user_target WHERE utId=ta.`userTargetId` AND `month`='.$time.'),0) AS userTarget ';
	    	$userlist = DB::select('SELECT ta.`userId`,ta.`nickname`,ta.`sex`,ta.`age`,ta.`phone`,tb.jobName,tc.shopName,td.`fileName`'.$selStr.'FROM unierp_user ta
LEFT JOIN unierp_job tb ON ta.`jobId`=tb.jobId
LEFT JOIN unierp_shop tc ON ta.`shopId`=tc.id
LEFT JOIN unierp_images td ON ta.`imageId`=td.id
WHERE 1=1 AND ta.`shopId`='.$shopId);
			if ( !empty($userlist))
			{
				return $this->output(Response::SUCCESS, $userlist);
			}
			else
			{
				return $this->output(Response::NO_MORE_INFO);
			}
	    }
	    
	    /*
	     * rank页调用
	     * params:	userId,year,month
	     */
	    public function getUserInfoForRank ($userId, $year, $month)
	    {
	    	$userInfo = DB::select('SELECT ta.*,tb.filename,shopName,
(SELECT COUNT(*) FROM unierp_facts a LEFT JOIN unierp_time b ON a.timeId=b.id WHERE 1=1 AND userId=ta.`userId` AND b.year='.$year.' AND b.month='.$month.') AS totalorder,
(SELECT SUM(`count`) FROM unierp_facts a LEFT JOIN unierp_time b ON a.timeId=b.id WHERE 1=1 AND userId=ta.`userId` AND b.year='.$year.' AND b.month='.$month.') AS totalgoods,
IFNULL((SELECT upperLimit FROM unierp_user_target WHERE 1=1 AND userId=ta.`userId` AND `year`='.$year.' AND `month`='.$month.' AND `day`=0), 0) AS upperLimit,
(SELECT IFNULL(SUM(a.turnover), 0) FROM unierp_facts a LEFT JOIN unierp_time b ON a.timeId=b.id WHERE 1=1 AND userId=ta.`userId` AND b.year='.$year.' AND b.month='.$month.') AS ctturnover
FROM unierp_user ta
LEFT JOIN unierp_images tb ON ta.`imageId`=tb.id
LEFT JOIN unierp_shop tc ON ta.`shopId`=tc.`id`
WHERE 1=1 AND userId="'.$userId.'"');
			if ( !empty($userInfo))
			{
				return $this->output(Response::SUCCESS, $userInfo);
			}
			else
			{
				return $this->output(Response::NO_MORE_INFO);
			}
	    }
	    
	    /*
	     * rank页调用
	     * params:	year,month,shopId,jobType,dataType
	     */
	    public function getUserListForRank ($year, $month, $shopId, $jobType, $dataType)
	    {
	    	//根据jobType职业类型筛选
	    	if ( $jobType == 0 )
	    	{
	    		$jobWhereStr = "";
	    	}
	    	else 
	    	{
	    		$jobWhereStr = ' and ta.`jobId`="J002"';
	    	}
	    	
	    	$queryStr = '';
	    	//根据dataType数据查询类型筛选
	    	switch ( $dataType)
	    	{
	    		//月完成率
	    		case 0:
	    			$queryStr = ',
IFNULL((SELECT upperLimit FROM unierp_user_target WHERE 1=1 AND userId=ta.`userId` AND `year`='.$year.' AND `month`='.$month.' AND `day`=0), 0) AS upperLimit,
(SELECT IFNULL(SUM(a.turnover), 0) FROM unierp_facts a LEFT JOIN unierp_time b ON a.timeId=b.id WHERE 1=1 AND userId=ta.`userId` AND b.year='.$year.' AND b.month='.$month.') AS ctturnover';
	    			break;
	    		//客单价
	    		case 1:
	    			$queryStr = ',
(SELECT COUNT(*) FROM unierp_facts a LEFT JOIN unierp_time b ON a.timeId=b.id WHERE 1=1 AND userId=ta.`userId` AND b.year='.$year.' AND b.month='.$month.') AS totalorder,
(SELECT IFNULL(SUM(a.turnover), 0) FROM unierp_facts a LEFT JOIN unierp_time b ON a.timeId=b.id WHERE 1=1 AND userId=ta.`userId` AND b.year='.$year.' AND b.month='.$month.') AS ctturnover';
	    			break;
	    		//件单价
	    		case 2:
	    			break;
	    		//连带率
	    		case 3:
	    			$queryStr = ',
(SELECT COUNT(*) FROM unierp_facts a LEFT JOIN unierp_time b ON a.timeId=b.id WHERE 1=1 AND userId=ta.`userId` AND b.year='.$year.' AND b.month='.$month.') AS totalorder,
(SELECT SUM(`count`) FROM unierp_facts a LEFT JOIN unierp_time b ON a.timeId=b.id WHERE 1=1 AND userId=ta.`userId` AND b.year='.$year.' AND b.month='.$month.') AS totalgoods';
	    			break;
	    		//折扣率
	    		case 4:
	    			break;
	    		//vip销售占比
	    		case 5:
	    			break;
	    		default:
	    			break;
	    	}
	    	
	    	$userList = DB::select('SELECT ta.*,tb.`fileName`,tc.`shopName`'.$queryStr.'
FROM unierp_user ta
LEFT JOIN unierp_images tb ON ta.`imageId`=tb.`id`
LEFT JOIN unierp_shop tc ON ta.`shopId`=tc.`id`
WHERE 1=1 AND ta.`shopId`='.$shopId.$jobWhereStr);

			if ( !empty($userList))
			{
				return $this->output(Response::SUCCESS, $userList);
			}
			else
			{
				return $this->output(Response::NO_MORE_INFO);
			}
	    }
	    
	    
	    /*
	     * Analyze页调用
	     * 
	     */
	    
	    public function getUserInfoForAnalyze ( $userId , $year, $month, $day )
	    {
	    	$sqlStr = 'SELECT ta.`nickname`,ta.`userId`,tb.`shopName`,tc.`fileName`,
(SELECT upperLimit FROM unierp_user_target WHERE 1=1 AND userId="'.$userId.'" AND `year`='.$year.' AND `month`='.$month.' AND `day`='.$day.') AS dayTarget,
(SELECT upperLimit FROM unierp_user_target WHERE 1=1 AND userId="'.$userId.'" AND `year`='.$year.' AND `month`='.$month.' AND `day`=0) AS monthTarget,
(SELECT SUM(ta_1.turnover) FROM unierp_facts ta_1
LEFT JOIN unierp_time tb_1 ON ta_1.timeId=tb_1.id
WHERE 1=1 AND ta_1.userId="'.$userId.'" AND tb_1.year='.$year.' AND tb_1.month='.$month.') AS monthFactsTotal,
(SELECT SUM(ta_1.turnover) FROM unierp_facts ta_1
LEFT JOIN unierp_time tb_1 ON ta_1.timeId=tb_1.id
WHERE 1=1 AND ta_1.userId="'.$userId.'" AND tb_1.year='.$year.' AND tb_1.month='.$month.' AND tb_1.day='.$day.') AS dayFactsTotal,
IFNULL((SELECT SUM(ta.count) FROM unierp_facts ta
LEFT JOIN unierp_time tb ON ta.timeId=tb.id
WHERE 1=1 AND userId="'.$userId.'" AND tb.year='.$year.' AND tb.month='.$month.'), 0) AS monthSale,
(SELECT COUNT(*) FROM unierp_facts ta
LEFT JOIN unierp_time tb ON ta.timeId=tb.id
WHERE 1=1 AND userId="'.$userId.'" AND tb.year='.$year.' AND tb.month='.$month.') AS monthOrderCount,
(SELECT COUNT(*) FROM unierp_facts) AS totalOrderCount
FROM unierp_user ta
LEFT JOIN unierp_shop tb ON ta.`shopId`=tb.`id`
LEFT JOIN unierp_images tc ON ta.`imageId`=tc.`id`
WHERE 1=1 AND ta.`userId`="'.$userId.'"';

			$userInfo = DB::select($sqlStr);
			
			if ( !empty($userInfo) )
			{
				return $this->output(Response::SUCCESS, $userInfo);
			}
			else
			{
				return $this->output(Response::NO_MORE_INFO);	
			}
	    }
	    
	    
	    /*
	     * personalRank.vue
	     */
	    public function getUserInfoForPersonalRank ( $userId, $year, $month, $day, $type )
	    {
	    	//获取每个月一共有多少天
	    	$dateStr = $year.'-'.$month.'-'.$day;
	    	$d = strtotime($dateStr);
	    	$dateCount = date('t',$d);
	    	
	    	$tempAry = array();
	    	for ( $i = 0 ; $i < $dateCount ; $i++ )
	    	{
	    		array_push($tempAry, 0);		
	    	}
	    	
	    	$retAry = array();
	    	$get = DB::select('SELECT ta.userId,tb.`year`,tb.`month`,tb.`day`,SUM(ta.turnover) AS turnover FROM unierp_facts ta
LEFT JOIN unierp_time tb ON ta.timeId=tb.id
WHERE 1=1 AND ta.userId=? AND tb.`year`=? AND tb.`month`=?
GROUP BY tb.`day`', [$userId, $year, $month]);
			
			foreach ( $get as $g )
			{
				$tempAry[$g->day] = $g->turnover;
			}
			
	    	return $this->output(Response::SUCCESS, $tempAry);
	    }
	}