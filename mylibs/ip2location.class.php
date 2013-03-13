<?
//===================================
//
// 功能：IP地址获取真实地址函数
// 参数：$ip - IP地址
// 作者：[Discuz!] (C) Comsenz Inc.
//
//===================================
class ip2location{
        
        var $dat_path = '/dev/shm/qqwry.dat';
        var $origin_path = '/data/vhosts/app766/mylibs/ipdata/qqwry.dat';
        var $fd = '';
        var $province = array("北京", "上海", "重庆", "安徽", "福建", "甘肃", "广东", "广西", "贵州", "海南", "河北", "黑龙江", "河南",
        "香港", "湖北", "湖南", "江苏", "江西", "吉林", "辽宁", "澳门", "内蒙古", "宁夏", "青海", "山东",
        "山西", "陕西", "四川", "台湾", "天津", "新疆", "西藏", "云南", "浙江");

        function ip2location(){
                
                if(!is_file($this->dat_path)){
                        copy($this->origin_path, $this->dat_path);
                }
                
                $this->fd = @fopen($this->dat_path, 'rb');
        }


        function location($ip) {
                //IP数据文件路径
                if(!$this->fd)return false;

                //检查IP地址
                if(!preg_match("/^\d{1,3}.\d{1,3}.\d{1,3}.\d{1,3}$/", $ip)) {
                        return false;
                }
                //打开IP数据文件


                //分解IP进行运算，得出整形数
                $ip = explode('.', $ip);
                $ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];

                //获取IP数据索引开始和结束位置
                $DataBegin = fread($this->fd, 4);
                $DataEnd = fread($this->fd, 4);
                $ipbegin = implode('', unpack('L', $DataBegin));
                if($ipbegin < 0) $ipbegin += pow(2, 32);
                $ipend = implode('', unpack('L', $DataEnd));
                if($ipend < 0) $ipend += pow(2, 32);
                $ipAllNum = ($ipend - $ipbegin) / 7 + 1;
                   
                $BeginNum = 0;
                $EndNum = $ipAllNum;
                $ip1num = 0;
                $ip2num = 0;
                $ipAddr1 = '';
                //使用二分查找法从索引记录中搜索匹配的IP记录
                while($ip1num>$ipNum || $ip2num<$ipNum) {
                        
                        $Middle= intval(($EndNum + $BeginNum) / 2);

                        //偏移指针到索引位置读取4个字节
                        fseek($this->fd, $ipbegin + 7 * $Middle);
                        $ipData1 = fread($this->fd, 4);
                        if(strlen($ipData1) < 4) {
                                fclose($this->fd);
                                return 'System Error';
                        }
                        //提取出来的数据转换成长整形，如果数据是负数则加上2的32次幂
                        $ip1num = implode('', unpack('L', $ipData1));
                        if($ip1num < 0) $ip1num += pow(2, 32);
                           
                        //提取的长整型数大于我们IP地址则修改结束位置进行下一次循环
                        if($ip1num > $ipNum) {
                                $EndNum = $Middle;
                                continue;
                        }
                           
                        //取完上一个索引后取下一个索引
                        $DataSeek = fread($this->fd, 3);
                        if(strlen($DataSeek) < 3) {
                                fclose($this->fd);
                                return 'System Error';
                        }
                        $DataSeek = implode('', unpack('L', $DataSeek.chr(0)));
                        fseek($this->fd, $DataSeek);
                        $ipData2 = fread($this->fd, 4);
                        if(strlen($ipData2) < 4) {
                                fclose($this->fd);
                                return 'System Error';
                        }
                        $ip2num = implode('', unpack('L', $ipData2));
                        if($ip2num < 0) $ip2num += pow(2, 32);

                        //没找到提示未知
                        if($ip2num < $ipNum) {
                                if($Middle == $BeginNum) {
                                        fclose($this->fd);
                                        return 'Unknown';
                                }
                                $BeginNum = $Middle;
                        }
                }

                //下面的代码读晕了，没读明白，有兴趣的慢慢读
                $ipFlag = fread($this->fd, 1);
                if($ipFlag == chr(1)) {
                        $ipSeek = fread($this->fd, 3);
                        if(strlen($ipSeek) < 3) {
                                fclose($this->fd);
                                return 'System Error';
                        }
                        $ipSeek = implode('', unpack('L', $ipSeek.chr(0)));
                        fseek($this->fd, $ipSeek);
                        $ipFlag = fread($this->fd, 1);
                }

                if($ipFlag == chr(2)) {
                        $AddrSeek = fread($this->fd, 3);
                        if(strlen($AddrSeek) < 3) {
                                fclose($this->fd);
                                return 'System Error';
                        }

                        $AddrSeek = implode('', unpack('L', $AddrSeek.chr(0)));
                        fseek($this->fd, $AddrSeek);

                        while(($char = fread($this->fd, 1)) != chr(0))
                        $ipAddr1 .= $char;
                } else {
                        fseek($this->fd, -1, SEEK_CUR);
                        while(($char = fread($this->fd, 1)) != chr(0))
                        $ipAddr1 .= $char;
                }

                rewind($this->fd);

                //最后做相应的替换操作后返回结果
                $ipaddr = unmb($ipAddr1);
                $ipaddr = str_ireplace('CZ88.Net', '', $ipaddr);
                $ipaddr = trim($ipaddr);
                $ipaddr = str_replace('地区', '', $ipaddr);
                $ipaddr = str_replace('局域网', '', $ipaddr);
                if(stripos($ipaddr, '共和')===false)$ipaddr = str_replace('和', '/', $ipaddr);
                if(stripos('http', $ipaddr)!==false || !$ipaddr) {
                        $ipaddr = false;
                }

                return $ipaddr;
        }
        
        function province($ip){
                
                $location = $this->location($ip);
                if($location){
                        foreach($this->province as $province){
                                if(strpos($location, $province)!==false)
                                        return $province;
                        }
                }
                
                return $location;
        }

        function __destruct(){
                fclose($this->fd);
        }
}
?>
