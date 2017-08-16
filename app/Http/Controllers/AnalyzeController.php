<?php
	namespace App\Http\Controllers;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Http\Request;
	use App\Response;
	
	class AnalyzeController extends Controller
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
	     * type: 年、月、周的查询类型
	     */
	    public function getDataForAnalyze( $year, $month, $day, $shopId, $type )
	    {
	    	//获取每个月一共有多少天
	    	$dateStr = $year.'-'.$month.'-'.$day;
	    	$d = strtotime($dateStr);
	    	if ( $type == 'date' )
	    	{
//	    		$dateCount = date('t',$d);
				$dateCount = $day;
	    	}
	    	else if ( $type == 'week' )
	    	{
	    		$dateCount = 4;
	    	}
	    	else if ( $type == 'year' )
	    	{
	    		$dateCount = 12;
	    	}
	    	
	    	$tempAry = array();
	    	for ( $i = 0 ; $i < $dateCount ; $i++ )
	    	{
	    		array_push($tempAry, 0);		
	    	}
	    	
	    	if ( $type == 'year')
	    	{
	    		$res = DB::select('SELECT IFNULL(SUM(ta.`turnover`), 0) AS dayTurnover,tb.`year`,tb.`month`,tb.`day` FROM unierp_facts ta
LEFT JOIN unierp_time tb ON ta.timeId=tb.`id`
WHERE 1=1 AND ta.shopId=:shopId AND tb.`year`=:year
GROUP BY tb.`month`', ['shopId' => $shopId, 'year' => $year]);
	    	}
	    	else if ( $type == 'week' )
	    	{
	    		$res = DB::select('SELECT IFNULL(SUM(ta.`turnover`), 0) AS dayTurnover,tb.`year`,tb.`month`,tb.`day`,tb.`week` FROM unierp_facts ta
LEFT JOIN unierp_time tb ON ta.timeId=tb.`id`
WHERE 1=1 AND ta.shopId=:shopId AND tb.`year`=:year AND tb.`month`=:month
GROUP BY tb.`week`', ['shopId' => $shopId, 'year' => $year, 'month' => $month]);
	    	}
	    	else if ( $type == 'date' )
	    	{
	    		$res = DB::select('SELECT IFNULL(SUM(ta.`turnover`), 0) AS dayTurnover,tb.`year`,tb.`month`,tb.`day` FROM unierp_facts ta
LEFT JOIN unierp_time tb ON ta.timeId=tb.`id`
WHERE 1=1 AND ta.shopId=:shopId AND tb.`year`=:year AND tb.`month`=:month
GROUP BY tb.`day`', ['shopId' => $shopId, 'year' => $year, 'month' => $month]);                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     
	    	}
	    	
	    	foreach ( $res as $r )
	    	{
	    		if ( $type == 'year' )
	    		{
	    			$tempAry[$r->month-1] = $r->dayTurnover;
	    		}
	    		else if ( $type == 'week' )
	    		{
	    			$tempAry[$r->week-1] = $r->dayTurnover;
	    		}
	    		else if ( $type == 'date' )
	    		{
	    			$tempAry[$r->day-1] = $r->dayTurnover;
	    		}
	    	}
	    	
	    	return $this->output(Response::SUCCESS, $tempAry);
	    }
	    
	    
	}
