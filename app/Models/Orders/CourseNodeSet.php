<?php

namespace App\Models\Course;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseNodeSet extends Model
{
    use SoftDeletes;
    protected $table = 'c_course_node_set';
    protected $dates = ['deleted_at'];//软删除

    /*
     * @Des:节点ID生成
     * */
    public static function createNodeId($parms=array()){
        $count = self::where(array('packages_id'=>$parms['packages_id'],'subject_id'=>$parms['subject_id']))->count();
        return $count+1;
    }

    /*
     * @Des:获取上个节点配置数据
     * */
    public static function getUpNodeInfo($parms=array(),$orderby="DESC"){
        return self::where(array('packages_id'=>$parms['packages_id'],'subject_id'=>$parms['subject_id']))->orderBy("start",$orderby)->first();
    }

    /*
     * @Des:取得当前条件下 相邻的数据
     * */
    public static function getAdjacentNodeInfo($parms=array(),$order='DESC'){
        $query = self::where(array('packages_id'=>$parms['packages_id'],'subject_id'=>$parms['subject_id']));
        if($parms['datamethod']=='up'){
            $query = $query->where('start','<=',$parms['start']);
        }else{
            $query = $query->where('start','>',$parms['start']);
        }
        if($parms['expect_id']){
            $query = $query->where('id','!=',$parms['expect_id']);
        }
        $query = $query->orderBy("start",$order)->first();
        return $query;
    }

    /*
     * @Des:获取符合条件的第一个节点数据
     * */
    public static function getNodeInfo($parms=array()){
        return self::where(array('packages_id'=>$parms['packages_id'],'subject_id'=>$parms['subject_id']))->where('start','<=',$parms['start'])->where('end','>=',$parms['start'])->orderBy("start","ASC")->first();
    }

}
