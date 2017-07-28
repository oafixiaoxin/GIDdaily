<?php
	use Illuminate\Http\Request;
	use Dingo\Api\Routing\Router;
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// options请求就只需要输出头部信息就OK了。
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit; // finish preflight CORS requests here
}

//$app->get('/', function () use ($app) {
//  return $app->version();
//});

$app->group(['prefix' => 'api/v1'], function($app)
{
	$app->put('/modifyUserInfo', 'UserOperationController@modifyUserInfo');
	$app->get('/getUserInfo/{userId}', 'UserOperationController@getUserInfo');
	$app->get('/userLogin/{userName}/{password}', 'UserOperationController@userLogin');
	$app->get('/getUserList/{shopId}', 'UserOperationController@getUserList');
	$app->get('/getUserListByTime/{shopId}/{time}', 'UserOperationController@getUserListByTime');
	$app->get('/getUserInfoForRank/{userId}/{year}/{month}', 'UserOperationController@getUserInfoForRank');
	$app->get('/getUserListForRank/{year}/{month}/{shopId}/{jobType}/{dataType}', 'UserOperationController@getUserListForRank');
	$app->get('/getUserInfoForAnalyze/{userId}/{year}/{month}/{day}', 'UserOperationController@getUserInfoForAnalyze');
	$app->get('/getUserInfoForPersonalRank/{userId}/{year}/{month}/{day}/{type}', 'UserOperationController@getUserInfoForPersonalRank');
	
	$app->get('/getTarget/{type}/{shopId}/{year}/{month}/{day}', 'ChangeTargetController@getTarget');
	$app->put('/updateShopTarget', 'ChangeTargetController@updateShopTarget');
	$app->put('/updateUsersTarget', 'ChangeTargetController@updateUsersTarget');
	
	$app->get('/getDataForAnalyze/{year}/{month}/{day}/{shopId}/{type}', 'AnalyzeController@getDataForAnalyze');
	
	$app->get('/getEmptyTables', 'TableController@getEmptyTables');
	
//	$app->get('/ysxTest', 'ExampleController@ysxTest');
});

$app->group(['prefix' => 'api2/v1'], function($app)
{
	$app->get('/getEmptyTables', 'TableController@getEmptyTables');
});
