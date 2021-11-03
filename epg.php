<?php
if(date_default_timezone_get() != "Asia/Shanghai") date_default_timezone_set("Asia/Shanghai");
 
$cgname = array(
"CCTV-1"=>"b3666b9d",//频道ID
"CCTV-2"=>"c5717c2d",
"CCTV-3"=>"53eda06f",
"CCTV-4"=>"0ccc41bf",
"CCTV-5"=>"6b26bee1",
"CCTV-5+"=>"e4e3801d",
"CCTV-6"=>"ddb707c0",
"CCTV-7"=>"f2d13f2a",
"CCTV-8"=>"13e8f054",
"CCTV-9"=>"8f932b7b",
"CCTV-10"=>"7651a0a2",
"CCTV-11"=>"0a2de840",
"CCTV-12"=>"1e983148",
"CCTV-13"=>"f5b1a323",
"CCTV-14"=>"6fff4f43",
"CCTV-15"=>"3201ff16",
"CCTV-17"=>"d3d48ldf",
"湖南卫视"=>"7d4daf1f",
""=>"",
);
 
         
function compress_html($string) {
        $string = str_replace("\r", '', $string); //清除换行符
        $string = str_replace("\n", '', $string); //清除换行符
        $string = str_replace("\t", '', $string); //清除制表符
        return $string;
}
 
 
$cname = !empty($_GET["ch"]) ? $_GET["ch"] : exit(json_encode(["code" => 500, "msg" => "EPG频道参数不能为空！", "name" => $name, "date" => null, "data" => null], JSON_UNESCAPED_UNICODE));
$dt1=$_GET['date'];
$dt2=date('Y-m-d',strtotime($dt1)+86400);
$w1=date("w",strtotime($dt1));
if ($w1<'1') {$w1=7;}
$w0=$w1-1;
if ($w0<'1') {$w0=7;}
 
if (empty($cgname[$cname])) {
    exit(json_encode(["code" => 500, "msg" => "未定义频道ID！", "name" => $name, "date" => null, "data" => null], JSON_UNESCAPED_UNICODE));
} else {
    if ((strtotime($dt1) < time() && $w1 > date("w")) || date("Ymd", strtotime($dt1)) > (date("Ymd") - date("w") + '7')) {
        exit(json_encode(["code" => 500, "msg" => "超出搜视网时间范围！", "name" => $name, "date" => null, "data" => null], JSON_UNESCAPED_UNICODE));
    } else {
        $url0 = "https://www.tvsou.com/epg/";
        $t0 = array();
        $t1 = array();
        $nm = array();
        //获取前一天的最后一个节目名，作为当天第一个节目
        $url = $url0 . $cgname[$cname] . '/w' . $w0;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $re = curl_exec($ch);
        curl_close($ch);
        $re = compress_html($re);
        preg_match('|<table class="layui-table c_table"(.*?)layui-tab-item|i', $re, $u);
        preg_match_all('|<tr>([\s\S]+?)<\/tr>|', trim($u[1]), $u);
        $u = $u[1];
        $ysdnum = sizeof($u);
        preg_match_all('|_blank\'>(.*?)<\/a>|', $u[$ysdnum - 1], $tr);
        $lstnm = $tr[1][1];
        //获取当天节目表
        $url = $url0 . $cgname[$cname] . '/w' . $w1;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $re = curl_exec($ch);
        curl_close($ch);
        $re = compress_html($re);
        preg_match('|<table class="layui-table c_table"(.*?)layui-tab-item|i', $re, $u);
        preg_match_all('|<tr>([\s\S]+?)<\/tr>|', trim($u[1]), $u);
        $u = $u[1];
        $num = sizeof($u);
        for ($i = 0;$i < $num;$i++) {
            preg_match_all('|_blank\'>(.*?)<\/a>|', $u[$i], $tr);
            $t0[] = $tr[1][0];
            $nm[] = $tr[1][1];
        }
        //转码节目表
        for ($i = 1;$i < $num;$i++) {
            $t1[] = $t0[$i];
        }
        $t1[] = '00:00'; //当日最后一个节目设定结束时间，避免冲突
        $chn.= "{\"channel_name\":\"" . $cname . "\",\"date\":\"" . $dt1 . "\",\"epg_data\":[";
        $chn.= "{\"title\":\"" . $lstnm . "\",\"start\":\"00:00\",\"end\":\"" . $t0[0] . "\"},"; //前一天的最后一个节目名作为当天第一个节目，开始时间为00:00
        for ($i = 0;$i < $num;$i++) {
            $chn.= "{\"title\":\"" . $nm[$i] . "\",\"start\":\"" . $t0[$i] . "\",\"end\":\"" . $t1[$i] . "\"},";
        }
        $chn = substr($chn, 0, -1);
        $chn.= "]}";
    }
}
echo $chn;
?>
