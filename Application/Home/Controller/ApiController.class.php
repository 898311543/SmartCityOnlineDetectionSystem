<?php
namespace Home\Controller;
use Think\Controller;
class ApiController extends Controller {
    public function index(){
	$this->show('This is api server');
    }
    public function present_data(){
        $date_ = date('Ymd');
        $tableName = 'date-'.$date_;
        try{
            $model = M($tableName);
            $data = $model->query("select `$tableName`.id,`$tableName`.time,temp,humidity,`pm2.5` pm25,illumination,weather,field1 pm10,field2 jiaquan,`$tableName`.location_x,`$tableName`.location_y,device_id,detail from `$tableName` left join machine on `$tableName`.device_id = machine.hard_id where (`$tableName`.id IN (select MAX(`$tableName`.id) from `$tableName` group by `device_id`) AND device_id IN (SELECT hard_id from `machine` WHERE `status` = 0))");
	    $return_data['status'] = 0;
	    for($i=0;$i<count($data);$i++){
            $data[$i]['location_x'] = (string)(($data[$i]['location_x'])/100);
            $data[$i]['location_y'] = (string)(($data[$i]['location_y'])/100);
        }

        }catch(\Exception $e){
            $data = '无今日数据!';
            $return_data['status'] = 1;
	}
        $return_data['data'] = $data;
        $this->ajaxReturn($return_data,'JSON');
}
    public function weely_average_temp() {
	$Dao = M();
	$return_data = array();
	for($j=7;$j>0;$j--){
	    $date = $this->get_before_date($j);
	    $one_day['date'] = $date;
	    $tableName = 'date-'.$date;
	    try{
		$data = $Dao->query("select AVG(temp) data from `$tableName`");
		$one_day['value'] = $data[0]['data'];
		if($one_day['value'] == NULL)
		    $one_day['value'] = '-1';
	}catch(\Exception $e){
		$one_day['value'] = '-1';
        	}
	$return_data[$j-1] = $one_day;
	}
	$return_data_['status'] = 0;
	$return_data_['data'] = $return_data;
	$this->ajaxReturn($return_data_,'JSON');
}
    public function weely_average_humid() {
        $Dao = M();
        $return_data = array();
        for($j=7;$j>0;$j--){
            $date = $this->get_before_date($j);
            $one_day['date'] = $date;
            $tableName = 'date-'.$date;
            try{
                $data = $Dao->query("select AVG(humidity) data from `$tableName`");
                $one_day['value'] = $data[0]['data'];
                if($one_day['value'] == NULL)
                    $one_day['value'] = '-1';
        }catch(\Exception $e){
                $one_day['value'] = '-1';
                }
        $return_data[$j-1] = $one_day;
        }
        $return_data_['status'] = 0;
        $return_data_['data'] = $return_data;
        $this->ajaxReturn($return_data_,'JSON');
}
    public function weely_average_pm25() {
        $Dao = M();
        $return_data = array();
        for($j=7;$j>0;$j--){
            $date = $this->get_before_date($j);
            $one_day['date'] = $date;
            $tableName = 'date-'.$date;
            try{
                $data = $Dao->query("select AVG(`pm2.5`) data from `$tableName`");
                $one_day['value'] = $data[0]['data'];
                if($one_day['value'] == NULL)
                    $one_day['value'] = '-1';
        }catch(\Exception $e){
                $one_day['value'] = '-1';
                }
        $return_data[$j-1] = $one_day;
        }
        $return_data_['status'] = 0;
        $return_data_['data'] = $return_data;
        $this->ajaxReturn($return_data_,'JSON');
}
    public function weely_average_pm10() {
        $Dao = M();
        $return_data = array();
        for($j=7;$j>0;$j--){
            $date = $this->get_before_date($j);
            $one_day['date'] = $date;
            $tableName = 'date-'.$date;
            try{
                $data = $Dao->query("select AVG(`field1`) data from `$tableName`");
                $one_day['value'] = $data[0]['data'];
                if($one_day['value'] == NULL)
                    $one_day['value'] = '-1';
            }catch(\Exception $e){
                $one_day['value'] = '-1';
            }
            $return_data[$j-1] = $one_day;
        }
        $return_data_['status'] = 0;
        $return_data_['data'] = $return_data;
        $this->ajaxReturn($return_data_,'JSON');
    }


    private function get_before_date($i){
	    return date("Ymd",strtotime("-$i day"));
	}
	private function get_after_date($i,$time_){
        return date("Ymd",strtotime("$i day",$time_));
    }
    private function get_after_hour($i,$time_){
        return date("Y-m-d H:m:s",strtotime("$i hour",$time_));
    }
	public function close_sys(){
        error_reporting(E_ALL);
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if($socket == false) {
            echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
        }else {
            echo "OK.\n";
        }
        echo( "Attempting to connect to '118.89.244.53' on port '8899'...");
        $result = socket_connect($socket, getenv("SERVER_PORT_8899_TCP_ADDR"), 8899);
        if ($result === false) {
            echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
        } else {
            echo "OK.\n";
        }
        echo "Sending  request...";
        $send = '1';
        socket_write($socket, $send, strlen($send));
        echo "Reading response:\n\n";
       // $out = socket_read($socket, 2048);
      //      echo $out;
        echo "Closing socket...";
        socket_close($socket);
        echo "OK.\n\n";
    }
    public function open_sys(){
        error_reporting(E_ALL);
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if($socket == false) {
            echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
        }else {
            echo "OK.\n";
        }
        echo( "Attempting to connect to '118.89.244.53' on port '8899'...");
        $result = socket_connect($socket, getenv("SERVER_PORT_8899_TCP_ADDR"), 8899);
        if ($result === false) {
            echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
        } else {
            echo "OK.\n";
        }
        echo "Sending  request...";
        $send = '2';
        socket_write($socket, $send, strlen($send));
        echo "Reading response:\n\n";
        // $out = socket_read($socket, 2048);
        //      echo $out;
        echo "Closing socket...";
        socket_close($socket);
        echo "OK.\n\n";
    }
    private function curl_file_get_contents($durl){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $durl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }
    public function get_img_url(){
        $this->show($this->curl_file_get_contents('http://guolin.tech/api/bing_pic'));
    }
    public function login(){
        if(I('post.username') == 'admin' && I('post.password') == 'admin'){
            $_COOKIE['username'] = 'admin';
            session(array("username"=>'admin'));
            $this->ajaxReturn(array('status'=>'success','username'=>'admin'),'JSON');
        }
        $this->ajaxReturn(array('status'=>'fail'),'JSON');
    }
    public function get_history_data(){
        if(I('post.method') == "GETDETAIL"){
            if(I('post.type') == "DAY"){
                $startTime = strtotime(I('post.startTime'));
                $endTime = strtotime(I('post.endTime'));
                $location_id = I("post.location_id");
                $Dao = M();
                $return_data = array();
                $j = 0;
                while (strtotime("$j day",$startTime)< $endTime){
                    $date = $this->get_after_date($j,$startTime);
                    $one_day['time'] = date('Y-m-d',strtotime($date));
                    $tableName = 'date-'.$date;
                    try{
                        $data = $Dao->query("select temp,humidity,`pm2.5` pm25,field1 pm10 from `daily_everage` where `date` = '$tableName' AND device_id=$location_id");
                        $one_day['pm10'] = $data[0]['pm10'];
                        $one_day['pm2_5'] = $data[0]['pm25'];
                        $one_day['temp'] = $data[0]['temp'];
                        $one_day['humi'] = $data[0]['humidity'];
                    }catch(\Exception $e){
                        echo $e;
                        $value = array("pm10","pm2_5","temp","humi");
                        foreach ($value as $one){
                            if($one_day[$one] == null){
                                $one_day[$one] = -1;
                            }
                        }
                    }
                    $return_data[$j] = $one_day;
                    $j++;
                    }
                $return_data_['success'] = true;
                $return_data_['data'] = array(
                    "total"=>"$j",
                    "rows"=>$return_data,
                    "time"=>time(),
                );
                $this->ajaxReturn($return_data_,'JSON');
                }
            if(I('post.type') == 'HOUR'){
                $startTime = strtotime(I('post.startTime'));
                $endTime = strtotime(I('post.endTime'));
                $location_id = I("post.location_id");
                $Dao = M();
                $return_data = array();
                $j = 0;
                while (strtotime("$j hour",$startTime)< $endTime){
                    $date = $this->get_after_hour($j,$startTime);
                    $one_day['time'] = date('Y-m-d H:00:00',strtotime($date));
                    $increTime = strtotime($date);
                    try{
                        $data = $Dao->query("select temp,humidity,`pm2.5` pm25,field1 pm10 from `hourly_average` where `time` between '$date' and date_add('$date',interval 1 hour) AND device_id=$location_id");
                        $one_day['pm10'] = $data[0]['pm10'];
                        $one_day['pm2_5'] = $data[0]['pm25'];
                        $one_day['temp'] = $data[0]['temp'];
                        $one_day['humi'] = $data[0]['humidity'];
                    }catch(\Exception $e){
                        $value = array("pm10","pm2_5","temp","humi");
                        foreach ($value as $one){
                            if($one_day[$one] == null){
                                $one_day[$one] = -1;
                            }
                        }
                    }
                    $return_data[$j] = $one_day;
                    $j++;
                }
                $return_data_['success'] = true;
                $return_data_['data'] = array(
                    "total"=>"$j",
                    "rows"=>$return_data,
                    "time"=>time(),
                );
                $this->ajaxReturn($return_data_,'JSON');
            }
            }
        }
        public function add_machine(){
            if(I('post.id')  && I('post.detail')  && I('post.id_name') ) {
                $data_['id'] = I('post.id');
                $data_['detail'] = I('post.detail');
                $data_['id_name'] = I('post.id_name');
                $data_['operator'] = 'admin';
                $data_['time'] = date('Y-m-d H:i:s',time());
                $Dao = M('machine');
                if ($Dao->add($data_)) {
                    echo '<script type="text/javascript">alert("添加成功");</script>';
                } else {
                    echo '<script type="text/javascript">alert("添加失败");</script>';
                }
            }
            else{
                echo '<script type="text/javascript">alert("添加失败");</script>';
            }
        }

        public function del_machine(){
            $return_data_ = array();
            if(I('post.id')){
                $id = I('post.id');
                $Dao = M('machine');
                $result = $Dao->where("id = $id")->delete();
                if($result)
                    $return_data_['status'] = "success";
                else
                    $return_data_['status'] = "fail";
                $this->ajaxReturn($return_data_,'JSON');
            }
        }
        public function modify_machine(){
            //获得设备号
            $id = I("device_id");
            //获得标记
            $id_name = I("id_name");
            //获得设备名称
            $detail = I("detail");
            $machine = M("machine");
            $machine->id_name = $id_name;
            $machine->detail = $detail;
            $status = $machine->where("hard_id = $id")->save();
            $this->ajaxReturn($status,"JSON");
        }





}
