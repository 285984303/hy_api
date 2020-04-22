<?php namespace App\Models;

/**
 * Created by PhpStorm.
 * User: Will
 * Date: 2016/7/5
 * Time: 10:03
 */

use App\Library\OSS;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Validator;
use DB;

/**
 * Class BaseModel
 *
 * @package App\Models
 *
 * @property integer $id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @mixin \Eloquent
 */
class BaseModel extends \Eloquent
{
    protected $guarded = ['id'];
    protected $rules = [];
    protected $message = [];
    protected $customAttributes = [];

    protected $perPage = 10;

    public function getCustomAttributeName($key)
    {
        return $this->customAttributes[$key] ? $this->customAttributes[$key] : $key;
    }

    public static function getAttrName($key)
    {
        return self::getCustomAttributeName($key);
    }

    /**
     * @param array $options
     *
     * @return bool
     * @throws ParameterError
     * @throws StoreError
     * @throws \Exception
     */
    public function save(array $options = [])
    {
        $this->validator();
        if (!parent::save($options)) {
            throw new StoreError(static::class);
        }

        return TRUE;
    }

    /**
     * @throws ParameterError
     */
    protected function validator()
    {
        if (!empty($this->original)) {
            // 数据修改的时候校验
            // 修改的属性
            $update_attributes = array_diff($this->attributes, $this->original);
            // 修改的key
            $diff_key = array_keys(array_diff($this->attributes, $this->original));
            $rules = array_only($this->rules, $diff_key);
            $validator = Validator::make($update_attributes, $rules, $this->message, $this->customAttributes);
        } else {
            // 数据添加的时候校验
            $validator = Validator::make($this->attributes, $this->rules, $this->message, $this->customAttributes);
        }

        if ($validator->fails()) {
            throw new ParameterError($validator->errors()->first());
        }
    }

    public function fill(array $attributes)
    {
        if (empty($attributes))
            return $this;
        $columns = $this->columns();
        foreach ($attributes as $key => $value) {
            if (!in_array($key, $columns) || in_array($key, $this->guarded)) {
                unset($attributes[$key]);
            }
        }

        return parent::fill($attributes);
    }

    protected function columns()
    {
        return \Cache::remember($this->table . '_columns', 60, function () {
            return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
        });
    }

    /**
     * Execute a query for a single record by ID.
     *
     * @param       $id
     * @param array $columns
     *
     * @return static
     * @throws NotFound
     */
    public static function find($id, $columns = ['*'])
    {
        $keyName = (new static)->getKeyName();
        if (is_array($keyName)) {
            $row = static::where($id)->first($columns);
        } else {
            $row = static::where($keyName, '=', $id)->first($columns);
        }

//        if (!$row) {
//            throw new NotFound('Cannot found model '.static::class);
//        }
        return $row;
    }

    public static function send_record($type, $url, $data, $img)
    {
        switch ($type) {
            case 1:
                $result = Http::file($url, $data);
                $file = file_get_contents($img);
                $file = base64_encode($file);
                $file_data = json_encode($data);
                self::insert_send($url, $result, $method = "file", $file_data, $file, $file_data);
                break;
            case 2:

                break;
        }
    }

    public static function insert_send($url, $result, $method, $data, $file = "", $file_data = "")
    {
        $insert_data['url'] = $url;
        $insert_data['message'] = empty($result->result_array['message']) ? '' : $result->result_array['message'];
        $insert_data['errorcode'] = empty($result->result_array['errorcode']) ? 0 : $result->result_array['errorcode'];
        $insert_data['method'] = $method;
        $insert_data['send_time'] = date("Y-m-d H:i:s", time());
        $insert_data['data'] = $data;
        $insert_data['file_content'] = $file;
        $insert_data['file_data'] = $file_data;
        $id = DB::table("send_record")->insertGetId($insert_data);
    }

    /*
    *功能：下载远程图片保存到本地
    *参数：文件url,保存文件目录,保存文件名称，使用的下载方式
    *当保存文件名称为空时则使用远程文件原来的名称
    */
    public static function getImage($url, $save_dir = '', $filename = '', $type = 0)
    {
        if (trim($url) == '') {
            return array('file_name' => '', 'save_path' => '', 'error' => 1);
        }
        if (trim($save_dir) == '') {
            $save_dir = './';
        }
        $ext = strrchr($url, '.');
        $filename = time() . $ext;

        if (0 !== strrpos($save_dir, '/')) {
            $save_dir .= '/';
        }
        //创建保存目录
        if (!file_exists($save_dir) && !mkdir($save_dir, 0777, true)) {
            return array('file_name' => '', 'save_path' => '', 'ext' => '', 'error' => 5);
        }

        //获取远程文件所采用的方法
        if ($type) {
            $ch = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $img = curl_exec($ch);
            curl_close($ch);
        } else {
            ob_start();
            readfile($url);
            $img = ob_get_contents();
            ob_end_clean();
        }
        //$size=strlen($img);
        //文件大小
        $fp2 = @fopen($save_dir . $filename, 'a');
        fwrite($fp2, $img);
        fclose($fp2);
        unset($img, $url);
        return array('file_name' => $filename, 'save_path' => $save_dir . $filename, 'ext' => $ext, 'error' => 0);
    }

    //上传头像
    public static function uploadAvatar($path = '')
    {
        try {
            $file = request()->file('file');

            //判断文件是否上传成功
            if ($file->isValid()) {
                //获取原文件名
                $originalName = $file->getClientOriginalName();
                //扩展名
                $ext = $file->getClientOriginalExtension();
//                if ($ext != 'log') {
//                    return response()->json(['result' => 'fail', 'error_msg' => '文件格式有误!']);
//                }
                //文件类型
                $type = $file->getClientMimeType();
                //临时绝对路径
                $realPath = $file->getRealPath();

                $filename = date('Y-m-d-H-i-S') . '-' . uniqid() . '.' . $ext;
                if (!is_dir(public_path('upload') . '/avatar')) {
                    mkdir(public_path('upload') . '/avatar', 0777, true);
                }

                $upLocal = $file->move(public_path('upload') . '/avatar', $filename);
                //上传至阿里云
                if ($upLocal) {
                    $pathDefault = \Config::get('oss.images.avatar') . date("Y-m-d");
                    $path = $path ? $path : $pathDefault;
                    OSS::publicUpload(\Config::get('oss.CONFIG.BUCKET'), $path . '/' . $filename, public_path('upload') . '/avatar/' . $filename);
                    unlink(public_path('upload') . '/avatar/' . $filename);
                }

                if (!$file->getError()) {
                    return OSS::getPublicObjectURL(\Config::get('oss.CONFIG.BUCKET'), $path . '/' . $filename);
                }
            }
            return false;

        } catch (\Exception $e) {

            return $e->getMessage();
        }
    }

    /**
     * Delete the model from the database.
     *
     * @return bool
     * @throws \App\Models\StoreError
     */
    public function delete()
    {
        if (!parent::delete()) {
            throw new StoreError(static::class);
        }

        return TRUE;
    }

    /**
     * Set the keys for a save update query. -- fix for multi-primary key
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery(Builder $query)
    {
        $keys = $this->getKeyName();
        if (!is_array($keys)) {
            return parent::setKeysForSaveQuery($query);
        }

        foreach ($keys as $keyName) {
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    /**
     * Get the primary key value for a save query. -- fix for multi-primary key
     *
     * @param mixed $keyName
     *
     * @return mixed
     */
    protected function getKeyForSaveQuery($keyName = NULL)
    {
        if (is_null($keyName)) {
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }
}
