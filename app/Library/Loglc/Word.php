<?php
/**
 * 文字处理
 * Created by PhpStorm.
 * User: link
 * Date: 16/9/14
 * Time: 10:45
 */
namespace App\Library\Loglc;

class Word
{


    /**
     * 获取汉字的首字母
     * @param $string
     * @param int $number [获取个数]
     * @return string
     */
    public static function getFirstCharters($string, $number = 1)
    {
        $initials = "";
        for ($i = 0; $i < $number; $i++) {
            $str = mb_substr($string, $i, 1, 'utf-8');
            $initials .= self::getFirstCharter($str);
        }
        return $initials;
    }

    private static function getFirstCharter($str)
    {

        if (empty($str)) {
            return 'J';
        }
        $fchar = ord($str{0});
        if ($fchar >= ord('A') && $fchar <= ord('z'))
            return strtoupper($str{0});

        if ($fchar >= ord('0') && $fchar <= ord('9'))
            switch ($str{0}){
                case 0: return "L"; break;
                case 1: return "Y"; break;
                case 2: return "R"; break;
                case 3: return "S"; break;
                case 4: return "S"; break;
                case 5: return "W"; break;
                case 6: return "L"; break;
                case 7: return "Q"; break;
                case 8: return "B"; break;
                case 9: return "J"; break;
                default:
                    return "L";
            }

        $s1 = iconv('UTF-8', 'gb2312', $str);
        $s2 = iconv('gb2312', 'UTF-8', $s1);
        $s = $s2 == $str ? $s1 : $str;
        $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
        if ($asc >= -20319 && $asc <= -20284) return 'A';
        if ($asc >= -20283 && $asc <= -19776) return 'B';
        if ($asc >= -19775 && $asc <= -19219) return 'C';
        if ($asc >= -19218 && $asc <= -18711) return 'D';
        if ($asc >= -18710 && $asc <= -18527) return 'E';
        if ($asc >= -18526 && $asc <= -18240) return 'F';
        if ($asc >= -18239 && $asc <= -17923) return 'G';
        if ($asc >= -17922 && $asc <= -17418) return 'H';
        if ($asc >= -17417 && $asc <= -16475) return 'J';
        if ($asc >= -16474 && $asc <= -16213) return 'K';
        if ($asc >= -16212 && $asc <= -15641) return 'L';
        if ($asc >= -15640 && $asc <= -15166) return 'M';
        if ($asc >= -15165 && $asc <= -14923) return 'N';
        if ($asc >= -14922 && $asc <= -14915) return 'O';
        if ($asc >= -14914 && $asc <= -14631) return 'P';
        if ($asc >= -14630 && $asc <= -14150) return 'Q';
        if ($asc >= -14149 && $asc <= -14091) return 'R';
        if ($asc >= -14090 && $asc <= -13319) return 'S';
        if ($asc >= -13318 && $asc <= -12839) return 'T';
        if ($asc >= -12838 && $asc <= -12557) return 'W';
        if ($asc >= -12556 && $asc <= -11848) return 'X';
        if ($asc >= -11847 && $asc <= -11056) return 'Y';
        if ($asc >= -11055 && $asc <= -10247) return 'Z';
        return 'JX';
    }
}