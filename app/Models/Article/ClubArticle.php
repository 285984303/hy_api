<?php

namespace App\Models\Article;

use Illuminate\Database\Eloquent\Model;

class ClubArticle extends Model {
	protected $table = 'club_article';
	protected $guarded = ['id'];
	public $timestamps = false;
	const TYPE_CERTIFICATE = 0;
	const TYPE_SCENE = 1;
	
	/*
	 * @Des: 数据列表分页查询
	 */
	public static function costSelectPage( $parms = array(), $orderfileds = 'id', $orderby = 'DESC') {
		$query = self::select ( [ 
				'*' 
		] );
		foreach ( $parms as $key => $value ) {
			switch ($key) {
				case 'is_paid' :
					$query->whereIn ( $key, $value );
					break;
				case 'name' :
					$user_ids = User::where ( 'user_truename', 'like', "%$value%" )->pluck ( 'id' );
					$query->whereIn ( 'student_id', $user_ids );
					break;
				case 'id_card' :
					$user_ids = User::where ( 'id_card', 'like', "%$value%" )->pluck ( 'id' );
					$query->whereIn ( 'student_id', $user_ids );
					break;
				case 'type' :
					$query->where ( 'type', '=', $value );
					break;
				case 'date' :
					$query->where ( 'arrears_time', '>=', $value );
					break;
				case 'paydate' :
					$query->where ( 'updated_at', '>=', $value );
					break;
				case 'income_type_id' :
					$query->where ( 'expense_type', '=', $value );
					break;
				case 'start_date' :
					$query->where ( 'updated_at', '>=', "$value 00:00:00" );
					break;
				case 'finish_date' :
					$query->where ( 'updated_at', '<=', "$value 23:59:59" );
					break;
				default :
					break;
			}
		}
		$query->orderby ( $orderfileds, $orderby );
		return $query;
	}
	
	/*
	 * @Des: 数据添加 返回插入ID
	 */
	public static function costAdd($parms = array()) {
		return self::insertGetId ( $parms );
	}
	
	/*
	 * @Des: 数据添加 返回对象及执行成功数据
	 */
	public static function costCreate($parms = array()) {
		return self::create ( $parms );
	}
	
	/*
	 * @Des: 数据删除
	 */
	public static function costDelete($id) {
		return self::where ( array (
				'id' => $id 
		) )->delete ();
	}
	
	/*
	 * @Des: 数据修改
	 */
	public static function costUpdate($id, $parms = array()) {
		return self::where ( array (
				'id' => $id 
		) )->update ( $parms );
	}
	
	/*
	 * @Des:关联学员模型
	 */
	public function user() {
		return $this->belongsTo ( User::class, 'user_id' );
	}
}