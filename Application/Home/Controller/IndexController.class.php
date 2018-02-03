<?php
namespace Home\Controller;
use Think\Controller;
use Think\Exception;

class IndexController extends Controller {

    public function _initialize() {
        $allow_actions = explode(',','Home.Index.login,Home.Index.index_old,Home.Index.present_data_display,Home.Index.history_data'); //配置哪些操作无需登录即可访问,比如登录，验证登录
        $curr_action = MODULE_NAME . '.' . CONTROLLER_NAME . '.' . ACTION_NAME;
        if(!in_array($curr_action,$allow_actions) && !session("?username")) { //未登录且是需要登录后访问的
            $this->redirect('index/login');
        }
        else{
            $this->assign("username",session("username"));
        }
    }



    public function index(){
        $this->redirect("index/mine_index");
    }

    public function index_old(){
        if($this->test())
            $this->display('index_mobile');
        else
            $this->display("index");
    }
    public function mine_index(){
        $devices = M('machine');
        $num_all = $devices->count();
        $num_online = $devices->where("status = 0")->count();
        $this->assign("num_all",$num_all);
        $this->assign("num_online",$num_online);
        $this->assign("num_offline",$num_all-$num_online);
        //获得table的值
        $date_ = date('Ymd');
        $tableName = 'date-'.$date_;
        try {
            $model = M("$tableName");
            $data = $model->query("select `$tableName`.id,`$tableName`.time,`$tableName`.temp,humidity,`pm2.5` pm25,field1 pm10,detail from `$tableName` LEFT JOIN `machine` ON `$tableName`.device_id = machine.hard_id WHERE `$tableName`.id IN (SELECT MAX(`$tableName`.id) from `$tableName` GROUP by device_id) ");
        }catch (Exception $e){
            $model = M();
        }
        $date_num = $model->query("select sum(table_rows) sum from information_schema.tables where table_name like 'date%'");
        $date_num = sprintf("%.2d",$date_num[0][sum]/10000);
        $this->assign("data_num",$date_num);
        $this->assign("data",$data);
        $this->show();
    }
    public function mine_devices(){
        $devices = M('machine');
        $list = $devices->select();
        $this->assign('list',$list);
        $this->show();
    }
    public function device_manager(){
        //未经过处理可能会出现SQL注入问题
        $id = I("get.id","int");
        //header介绍的显示
        $devices_information = M('machine');
        $devices_information_i = $devices_information->where("hard_id=$id")->select();
        $this->assign('device_information',$devices_information_i[0]);
        $this->show();
    }
    public function mine_chart(){
        $this->show();
    }
    public function abnormallog(){
        $devices = M('machine');
        $list = $devices->select();
        $this->assign('list',$list);
        $this->show();
    }
    public function mine_grade(){
        $warning = M('warning');
        $list = $warning->select();
        foreach ($list as $each){
            $array_max_min[$each["name"]]["max"] = $each["maximum"];
            $array_max_min[$each["name"]]["min"] = $each["minimum"];
            $array_max_min[$each["name"]]["id"] = $each["id"];
        }
        $this->assign("old_data",$array_max_min);
        $this->show();
    }
    //环境排行榜
    public function mine_environment_rank(){
        $daily_everage = M("daily_everage");
        $daily_everage->join("machine ON machine.hard_id = daily_everage.device_id");
        $daily_everage = ($daily_everage->field(["device_id","avg(temp)"=>"temp","avg(humidity)"=>"humidity","avg(`pm2.5`)"=>"pm25","avg(field1)"=>"pm10","detail"])->group("device_id")->order("temp")->select());
        $this->assign("list",$daily_everage);
        $this->show();
    }
    public function rank_ajax(){
        $rank_type = I("post.rank_type");
        $start_time = I("post.start_time");
        $end_time = I("post.end_time");
        $daily_everage = M("daily_everage");
        $daily_everage = $daily_everage->query("select device_id,avg(temp) temp,avg(humidity) humidity,avg(`pm2.5`) pm25,avg(field1) pm10,detail from daily_everage JOIN machine ON machine.hard_id = daily_everage.device_id WHERE str_to_date(date,'date-%Y%m%d') > '$start_time' AND str_to_date(date,'date-%Y%m%d') < '$end_time' group BY  device_id ORDER BY avg($rank_type)");
        $this->ajaxReturn($daily_everage,'JSON');
    }
    //异常日志详细信息
    public function abnormal_details(){
        //未经过处理可能会出现SQL注入问题
        $id = I("get.id","int");
        //异常日志的显示
        $devices = M('alert_log');
        $list = $devices->where("device_id=$id")->select();
        $this->assign('list',$list);
        //header介绍的显示
        $devices_information = M('machine');
        $devices_information_i = $devices_information->where("hard_id=$id")->select();
        $this->assign('device_information',$devices_information_i[0]);
        $this->show();
    }
    public function delete_log(){
        //获得log的id
        $id = I("get.id","int");
        $devices = M('alert_log');
        $devices->where("id=$id")->delete();
        $return_data['status'] = "success";
        $return_data['id'] = $id;
        $this->ajaxReturn($return_data,'JSON');
    }
    public function delete_run_log(){
        //获得log的id
        $id = I("get.id","int");
        $devices = M('run_log');
        $devices->where("id=$id")->delete();
        $return_data['status'] = "success";
        $return_data['id'] = $id;
        $this->ajaxReturn($return_data,'JSON');
    }
    public function delete_rawdata(){
        //获得log的id
        $date_ = date('Ymd');
        $tableName = 'date-'.$date_;
        //设备ID号
        $id = I("get.id","int");
        $devices = M("$tableName");
        $devices->where("id=$id")->delete();
        $return_data['status'] = "success";
        $return_data['id'] = $id;
        $this->ajaxReturn($return_data,'JSON');
    }
    public function delete_device(){
        $device_id = I("get.device_id","int");
        $devices = M('machine');
        if($devices->where("hard_id=$device_id")->delete()){
            $this->success('删除成功！', 'index/index');
        }else{
            $this->success('删除成功！', 'index/device_manager');
        }

    }
    public function change_warning_range(){
        //获得参数
        //报警类型的ID
        $id = I("post.id","int");
        //报警类型的最小值
        $min_val = I("post.min_val","int");
        //报警类型的最大值
        $max_val = I("post.max_val","int");
        $warning = M("warning");
        $warning->minimum = $min_val;
        $warning->maximum = $max_val;
        $warning->where("id=$id")->save();
        if($warning){
            $return_data['status'] = "success";
        }
        else{
            $return_data['status'] = "fail";
        }
        $this->ajaxReturn($return_data,'JSON');
    }
    public function get_abnormal_log(){
        //获得参数
        //获得异常的开始时间
        $start_time = I("post.start_time","int");
        //结束时间
        $end_time = I("post.end_time","int");
        //类型
        $type = I("post.type");
        $warning = M('alert_log');
        $warning = $warning->where("`begin_time` > '$start_time' AND `begin_time` < '$end_time' AND `alert_message` = '$type'")->select();
        $this->ajaxReturn($warning,'JSON');
    }
    public function mine_runlog(){
        $devices = M('machine');
        $list = $devices->select();
        $this->assign('list',$list);
        $this->show();
    }
    public function mine_rawdata(){
        $devices = M('machine');
        $list = $devices->select();
        $this->assign('list',$list);
        $this->show();
    }
    public function mine_raw_data_details(){
        $date_ = date('Ymd');
        $tableName = 'date-'.$date_;
        //设备ID号
        $id = I("get.id");
        $device_infom = M("machine")->where("hard_id=$id")->find();
        try{
            $today_data_model = M("$tableName");
            $today_data = $today_data_model->where("`device_id`=$id")->count();
        }catch (Exception $exception){
            $this->error('暂无数据');
        }
        $Page = new \Think\Page($today_data,20);// 实例化分页类 传入总记录数和每页显示的记录数
        $list = $today_data_model->where("`device_id`=$id")->order('id')->limit($Page->firstRow.','.$Page->listRows)->select();
        $show = $Page->show();// 分页显示输出
        for($i=0;$i<count($list);$i++)
            $list[$i]['pm25'] = $list[$i]['pm2.5'];
        $this->assign('list',$list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('device_inform',$device_infom);
        $this->show();

    }
    public function mine_runlog_detail(){
        //设备ID号
        $id = I("get.device_id");
        $runlog_model = M("run_log");
        $runlog = $runlog_model->where("device_id=$id")->select();
        $this->assign("list",$runlog);
        //header介绍的显示
        $devices_information = M('machine');
        $devices_information_i = $devices_information->where("hard_id=$id")->select();
        $this->assign('device_information',$devices_information_i[0]);
        $this->show();
    }
    public function mysql(){
        $date_ = date('Ymd');
        $tableName = 'date-'.$date_;
        try{
            $model = M($tableName);
            $data = $model->query("select id,time,temp,humidity,`pm2.5` pm25 from `$tableName` where id = (select MAX(id) from `$tableName`)");
	    $return_data['status'] = 0;
        }catch(\Exception $e){
            $data = '无今日数据!';
            $return_data['status'] = 1;
	}
        $return_data['data'] = $data;
        $this->ajaxReturn($return_data,'JSON');
    }
    function test(){
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	$mobile_agents = Array("240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi","android","anywhereyougo.com","applewebkit/525","applewebkit/532","asus","audio","au-mic","avantogo","becker","benq","bilbo","bird","blackberry","blazer","bleu","cdm-","compal","coolpad","danger","dbtel","dopod","elaine","eric","etouch","fly ","fly_","fly-","go.web","goodaccess","gradiente","grundig","haier","hedy","hitachi","htc","huawei","hutchison","inno","ipad","ipaq","ipod","jbrowser","kddi","kgt","kwc","lenovo","lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo","mercator","meridian","micromax","midp","mini","mitsu","mmm","mmp","mobi","mot-","moto","nec-","netfront","newgen","nexian","nf-browser","nintendo","nitro","nokia","nook","novarra","obigo","palm","panasonic","pantech","philips","phone","pg-","playstation","pocket","pt-","qc-","qtek","rover","sagem","sama","samu","sanyo","samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens","sie-","softbank","sony","spice","sprint","spv","symbian","tablet","talkabout","tcl-","teleca","telit","tianyu","tim-","toshiba","tsm","up.browser","utec","utstar","verykool","virgin","vk-","voda","voxtel","vx","wap","wellco","wig browser","wii","windows ce","wireless","xda","xde","zte");
	$is_mobile = false;
	foreach ($mobile_agents as $device) {
		if (stristr($user_agent, $device)) {
			$is_mobile = true;
			break;
		}
	};
	return $is_mobile;	
}
    public function present_data_display(){
        $this->display();
    }
    public function weekly_average(){
        $this->display();
    }
    public function history_data(){
        $device = M("machine");
        $this->assign("device",$device->select());
        $this->display();
    }
    public function machine_manager(){
        $model = M('machine');
        $this->assign('list',$model->select());
        $this->show();
    }
    public function login(){
        if (IS_GET){
            $this->show();
            exit();
        }
        $model = M("login");
        $username=$_POST["username"];
        $password=$_POST["password"];
        if ($username=="") {
            //echo "<script>alert('用户名为空')</script>";
            echo "<script language=\"JavaScript\">";
            echo "alert(\"用户名为空\");\r\n";
            echo "history.back();\r\n";
            echo "</script>";
            exit;
        }
        else if ($password=="")
        {
            //echo "<script>alert('密码为空')</script>";
            echo "<script language=\"JavaScript\">";
            echo "alert(\"密码为空\");\r\n";
            echo "history.back();\r\n";

            echo "</script>";
            exit;
        }

        else {
            $result = $model->query("SELECT * FROM login WHERE username='$username' AND password='$password'");
            if ($result) {
                //$su = "登陆成功";
                //重定向浏览器
                session('username',$result[0]["nickname"]);  //设置session
                $this->success('登录成功', 'index/index');
                //确保重定向后，后续代码不会被执行
                exit;


            } else {
                echo "<script language=\"JavaScript\">";
                echo "alert(\"用户名或密码错误\");\r\n";
                echo "history.back();\r\n";
                echo "</script>";
                exit;
            }
        }
    }
    public function logout(){
        session("username",null);
        $this->success('退出成功！', 'index/login');
    }
}
