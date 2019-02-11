<?php
include_once ROOT . "common/gm/gmUtil.php";
class gm extends single
{
	protected static $_instance = null;

	public function dynamicStore()
	{
        $function = gmUtil::getParam("function", gmUtil::TYPE_INT);
        $sel_function1 = (1 == $function) ? "selected":"";
        $sel_function2 = (2 == $function) ? "selected":"";

        $key = gmUtil::getParam('key', gmUtil::TYPE_STRING);
        $keyArray = array(
            'test1'=>array('show'=>"测试1",'key'=>'testID', 'value'=>'testV'),
            'test2'=>array('show'=>"测试2",'key'=>'roleID'),
        );
		gmUtil::printTable($keyArray);
		$keyOptionStr = gmUtil::printOption($keyArray);

        print_r("<form method='post' action=''>
            类型<select name='key'>$keyOptionStr</select><br><br>
            操作<select name='function'>
            <option value='1' $sel_function1>增加</option>
            <option value='2' $sel_function2>删除</option>
            </select><br><br>

            key<input  style='width:400px;' name='field'><br><br>
            value<input  style='width:400px;' name='value'><br><br>
            password<input type='pwd' name='pwd' required maxlength='18'><br><br>
            <input type='submit' value='提交'/></form>");

        $field = strtolower(gmUtil::getParam('field', gmUtil::TYPE_STRING));
        $value = gmUtil::getParam('value', gmUtil::TYPE_STRING);
        if ($function == 1 || $function == 2)
        {
			gmUtil::checkPWD();
		}
        if ($key == "-1")
        {    
            die("类型必选");
        }

        if (strlen($key) > 3) 
        {    
            if ($function == 2) {
                //删hash
				redisAgent::instance()->query("hdel", "public", $key, $field);
            }
            elseif ($function == 1) {
                //加hash
				redisAgent::instance()->query("hset", "public", $key, $field, $value);
            }
        }
        echo "<hr>";
        echo "<pre>";
        if (empty($key))
        {
            foreach($keyArray as $key => $show_info)
            {
				$cfgs = redisAgent::instance()->query("hgetall", "public", $key);
                echo "$key 数量：" . count($cfgs) . "<br>";
                echo "store list：";
                print_r($cfgs);
                echo "<hr>";
            }
        }
        else
        {
			$cfgs = redisAgent::instance()->query("hgetall", "public", $key);
            echo "$key 数量"  . count($cfgs) . "<br>";
            echo "store list：";
            print_r($cfgs);
        }
        echo "</pre>";
		return diyType::commonRet(diyType::SUCCESS);
	}

	public function diyStats()
	{
        echo "<html>";
        echo '<head>                  <meta http-equiv=Content-Type content="text/html;charset=utf-8">';
        echo '</head>';
        $localtime = date('Y-m-d', time()) . "T" . date('H:i', time());
        echo "当前当地时间: $localtime";
        $end_str = $localtime;
        $from_time = time() - 3600;
        $from_str = date('Y-m-d', $from_time) . "T" . date('H:i', $from_time);

        echo "<hr>参数及意义"; 
        $reasonDefine = array(
            array("eventid"=>101, "param1"=>"游戏时间", "param2"=>"最终得分"),
            array("eventid"=>201, "param1"=>"ping返回时间"),
        );
        gmUtil::printTable($reasonDefine);

        $template = gmUtil::getParam('template', gmUtil::TYPE_STRING);
        switch($template)
        {    
        case "ping_delay":
            $reason = 201; 
            $group = "param2";
            $filter1 = "param1";
            $value1 = 1; 
            break;
        default:
            $step = gmUtil::getParam('step', gmUtil::TYPE_STRING);
            $filter1 = gmUtil::getParam('filter1', gmUtil::TYPE_STRING);
            $value1 = gmUtil::getParam('value1', gmUtil::TYPE_STRING);
            $filter2 = gmUtil::getParam('filter2', gmUtil::TYPE_STRING);
            $value2 = gmUtil::getParam('value2', gmUtil::TYPE_STRING);
            $order_field = gmUtil::getParam('order_field', gmUtil::TYPE_STRING);
            if (!empty($_REQUEST['from_time']))
            {
                $from_str = gmUtil::getParam('from_time', gmUtil::TYPE_STRING);
                $end_str = gmUtil::getParam('end_time', gmUtil::TYPE_STRING);
            }

            $group = gmUtil::getParam('group', gmUtil::TYPE_STRING);
            $reason = gmUtil::getParam('reason', gmUtil::TYPE_INT);
            $dis = gmUtil::getParam('dis', gmUtil::TYPE_INT);
            break;
        }

        $groupOptions = array(
			"no_group"=>array('show'=>"不分类"), 
			"role_id"=>array('show'=>"role_id"),
			"param1"=>array('show'=>"param1"),
			'param2'=>array('show'=>"param2"),
			'param3'=>array('show'=>"param3"),
			'param4'=>array('show'=>"param4"),
			'param5'=>array('show'=>"param5")
		);
        $group_option_str = gmUtil::printOption($groupOptions, $group);

        $templateOptions = array(
            "nooo" => array('show'=>"无模板"),
            "ping_delay" => array('show'=>"客户端ping上报延迟"),
        );
        $template_option_str = gmUtil::printOption($templateOptions);

        $sel_dis0 = ("0" == $dis)? "selected":"";
        $sel_dis1 = ("1" == $dis)? "selected":"";
        $sel_dis2 = ("2" == $dis)? "selected":"";

        $from_time = strtotime($from_str);
        $end_time = strtotime($end_str);
        $html=<<<EOF
            <form method='post' action=''>
                <hr>简易模板
                    分类项统计<select name='template' value='$template'>$template_option_str
                    </select><br>
                <hr>
                <hr>必填项<br>
                reason（必填）<input  name='reason' value='$reason'/> <br>
                开始时间<input type='datetime-local', name='from_time' value=$from_str>
                结束时间<input type='datetime-local', name='end_time'间距最大12个小时 value=$end_str><br><br>

                去重<select name='dis'>
                <option value='0' $sel_dis0>不去重</option>
                <option value='1' $sel_dis1>根据上报role_id</option>
                <option value='2' $sel_dis2>根据param1</option>
                </select><br>
                分类项统计<select name='group' id='group' value='$group'>$group_option_str</select>如果选不分类，则不支持去重<br>
                <hr>
                <hr>可选项<br>
                分类值按区间合并排列，区间<input  name='step' value='$step'/>仅当分类项是数字时可用，填数字<br>
                过滤参数名1<input  name='filter1' value='$filter1'/>填param1, param2, param3, role_id等，只查此参数的参数值等于某个值的数据<br>
                过滤参数值1<input  name='value1' value='$value1'/>对应参数的值<br>
                过滤参数名2<input  name='filter2' value='$filter2'/>填param1, param2, param3, role_id等，只查此参数的参数值等于某个值的数据<br>
                过滤参数值2<input  name='value2' value='$value2'/>对应参数的值<br>
                排序参<input  name='order_field' value='$order_field'/>默认按统计数量排序，可自定义排序参<br>
                <br>

                <input type='submit' value='提交'/></form>
EOF;

        if ($dis == 0)
        {
            $dis = "count(*) as count, ";
        }
        elseif ($dis == 1)
        {
            $dis = "count(distinct(role_id)) as count, ";
            $order_cond = "order by count desc";
		}
        elseif ($dis == 2)
        {
            $dis = "count(distinct(param1)) as count, ";
        }

        if ($end_time - $from_time > 3600 * 24)
        {
            die("时间要在24小时以内");
            $end_time = $from_time + 3600 * 24;
        }
        $time_range = " logtime > $from_time and logtime < $end_time";
        echo "<br>";

        if (strlen(gmUtil::getParam('filter1', gmUtil::TYPE_STRING) > 1))
        {
            $filter_cond = " and $filter1 = '$value1'";
            if (strlen($_POST['filter2']) > 1)
            {
                $filter_cond .= " and $filter2 = '$value2'";
            }
        }
        if (strlen(gmUtil::getParam('order_field', gmUtil::TYPE_STRING) > 1))
        {
            $order_value = "convert($order_field, decimal(18, 2)) as order_value,";
            $order_cond = "order by order_value desc";
        }
        if (!$group || $group == "no_group")
        {
            $limit_cond = "limit 100";
            $group_cond = "";
            $dis = "";
        }
        else
        {
            $limit_cond = "";
            $group_cond = "group by self_group";
            $group_value = "$group as self_group, ";
            if ($step > 0)
            {
                $group_value = "round( convert($group, decimal(18,2)) / $step, 1) as self_group, ";
                $order_value = "";
                $order_cond = "order by self_group desc";
            }
        }
        print_r($html);
        if ($reason > 0)
        {
            $tb_name = log::getTbByReason($reason);
            $client_err_sql = "select *, $group_value $order_value $dis from_unixtime(logtime) as time from $tb_name where reason = $reason $filter_cond and $time_range $group_cond $order_cond $limit_cond";
            echo "sql: $client_err_sql <br><br>";
            $result = dbAgent::instance()->db("log")->query($client_err_sql);

            $all_num = 0;
            $big_num = 0;
            if (isset($result[0]['count']))
            {
                foreach($result as $one)
                {
                    $all_num += $one['count'];
                    if ($step > 0 && $one["self_group"] >= 1)
                    {
                        $big_num += $one['count'];
                    }
                }
                $msg = "统计总和：$all_num";
                if ($big_num > 0)
                {
                    $msg .= ", bigger than $step num: $big_num <br>";
                }
                echo $msg;
            }
            gmUtil::printTable($result);
        }
		return diyType::commonRet(diyType::SUCCESS);
	}

	public function test()
	{
		redisAgent::instance()->query("hset", "public", "store1", "aaa", "111");
		return diyType::commonRet(diyType::SUCCESS);
	}
}
