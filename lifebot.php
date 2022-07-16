<?php
/**
* Plugin Name: 生命规划局 (WordPress版)
* Description: 在你跌落凡尘之时为您自动发送告别文章，在你自己的blog上。
* Author: 暗梦先生呀~ (LuminousDream)
* Version: 2022-v1
* Author URI: https://darkace.xyz
* Github:https://github.com/luminousdream
**/

// * tip:仅供于家人或朋友之间的情感传递，不具有财产遗嘱等效力。 *

//ini_set('display_errors', 1);

/**
 * 获取指定行内容
 *
 *
 */
function getLine($file, $line, $length = 4096){
    $returnTxt = null; // 初始化返回
    $i = 1; // 行数
 
    $handle = @fopen($file, "r");
    if ($handle) {
        while (!feof($handle)) {
            $buffer = fgets($handle, $length);
            if($line == $i) $returnTxt = $buffer;
            $i++;
        }
        fclose($handle);
    }
    return $returnTxt;
}

//读取配置文件
function readconfig()
{
	$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
	$result = array(getLine("$DOCUMENT_ROOT/lifebot.config",1),getLine("$DOCUMENT_ROOT/lifebot.config",2));
	return($result);
}

//设置面板
function lifebot_settings_menu() {
    add_menu_page(__('生命规划局设置'), __('生命规划局设置'), 'administrator',  __FILE__, 'lifebot_menu', false, 100);
}

//获取状态
function get_status($timepart)
{
	 $status_array = array("存活","宽限","已逝");
	 $status = "";
	 if(calc_after_day($timepart) > 0)
	 {
		 $status = $status_array[0];
	 }
	 else if (calc_after_day($timepart) <= -15){
		 $status = $status_array[2];
	 }
	 else if(calc_after_day($timepart) <= 0)
	 {
		 $status = $status_array[1];
	 }
	 return($status);
}

function lifebot_menu() {
  //读取配置到设置面板
  ini_set('display_errors', 1);
  $timepart = readconfig()[1]; //读取预设时间段
  echo '<form method="post">
  <h2>生命规划局(WordPress版) 插件设置</h2>
  
  神爱世人,甚至将他的独生子赐给他们,叫一切信他的,不至灭亡,反得永生。
  </br>取自 约翰福音3章16节
  
  <p>自动发送告别文章：</p>
    <div class="timeinfo">
      最近一次更新：'.date('Y-n-j',strtotime(get_lastpostdate('server','post'))).'</br>
	  当前时间：'.date("Y-m-d").'</br>
	  宽限时间：'.get_end_day($timepart).' [在给你发送邮件后15天的期限，如果您登录了你的blog，时间将重新计算并退出宽限] </br>
	  告别时间：'.date("Y-m-d",strtotime(get_end_day($timepart)." + 15 day")).' [在给你发送邮件后超过15天，则自动发送告别文章]</br>
	  状态：'.get_status($timepart).'</br>
	</div> <!-- 通过判断状态在后台显示指定消息，这里可以自定义 -->
	'; if(get_status($timepart) == "宽限"){ echo "<font color='pink'>你来啦，小可爱，你怎么啦，别不开心嘛，来分享你的快乐时刻。</font>";} echo'
    <div class="settitle">
	告别文章标题：<input type="textbox" name="byetitle" value="'.readconfig()[0].'" />
	</div>
	<div class="settimepart">
	设定时间段：
    <label class="active">';
	if($timepart == "1month")
    {
	  echo '<input type="radio" checked="checked" name="timeparts" value="1month"/>
        1个月</label>';
    }else{
		echo '<input type="radio" name="timeparts" value="1month"/>
        1个月</label><label>';
	}
	
	if($timepart == "3month")
    {
	  echo '<input type="radio" checked="checked" name="timeparts" value="3month"/>
        3个月</label><label>';
    }else{
		echo '<input type="radio" name="timeparts" value="3month"/>
        3个月</label><label>';
	}
	
	if($timepart == "6month")
    {
	  echo '<input type="radio" checked="checked" name="timeparts" value="6month"/>
        6个月</label><label>';
    }else{
		echo '<input type="radio" name="timeparts" value="6month"/>
        6个月</label><label>';
	}
	
	if($timepart == "12month")
    {
	  echo '<input type="radio" checked="checked" name="timeparts" value="12month"/>
        12个月</label>';
    }else{
		echo '<input type="radio" name="timeparts" value="12month"/>
        12个月</label>';
	}

	echo '
	
		</br></br>
	    * Usage： 新建一篇文章并添加分类目录为告别文章，并在您的服务器上新建一个html文档[lifebot.html]，此插件将自动计划以文章的形式发送 *
		</br>
		* 在设置的时间段内若您未更新文章，则会向您发送确认邮件，发送15天后若您仍未更新则自动为您发送告别文章 *
		</br>
		';
		echo '<font color="green">* 好极了，祝你拥有快乐的每一天。 *</font>';
		
		//保存设置后写入配置
		if(isset($_POST['button1'])) {
			echo "</br> * 您的设置已保存，请刷新页面 *";
			$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
			$fp = fopen("$DOCUMENT_ROOT/lifebot.config",'w');
			fwrite($fp,$_POST['byetitle']);
			fwrite($fp,"\n".$_POST['timeparts']);
			fclose($fp);
			chmod("$DOCUMENT_ROOT/lifebot.config",0755); //设置文件权限避免无法读取配置
		}else if(isset($_POST['button_test2'])) {
			echo "</br> * 告别文章已发送 *";
			endline();
		}
		
		echo '
	</div>
  </br></br>
		<input type="submit" name="button1" class="button-primary"  value="保存更改" />';
  echo '
		<input type="submit" name="button_test2" class="button-primary"  value="手动发送告别文章测试" />
  </form>';

}


function check_post_update($timepart) //判断在指定时间段内是否更新过文章
{
	$result="True";
	$lastupdate = date('Y-n-j',strtotime(get_lastpostdate('server','post'))); //获取上次文章更新时间
	$localtime=date("Y-m-d"); //获取现在的时间
	$resulttime=$localtime; 
    if($timepart == "1month")
	{
		$resulttime=date("Y-m-d",strtotime("$lastupdate + 1 month")); //计算设定后的时间
	}else if($timepart == "3month")
	{
		$resulttime=date("Y-m-d",strtotime("$lastupdate + 3 month")); //计算设定后的时间
	}else if($timepart == "6month")
	{
		$resulttime=date("Y-m-d",strtotime("$lastupdate + 6 month")); //计算设定后的时间
	}else if($timepart == "12month")
	{
		$resulttime=date("Y-m-d",strtotime("$lastupdate + 1 year")); //计算设定后的时间
	}
	
		$diff_seconds=strtotime($resulttime)-strtotime($localtime);
	$tmp = floor(($diff_seconds)/86400);
	if($tmp <= 0) //时间到之后返回False
	{
		$result="False";
	}
	
	return($result);
}

function get_end_day($timepart) //计算截止日期
{
	$lastupdate = date('Y-n-j',strtotime(get_lastpostdate('server','post'))); //获取上次文章更新时间
	$localtime=date("Y-m-d"); //获取现在的时间
	$resulttime=$localtime; 
    if($timepart == "1month")
	{
		$resulttime=date("Y-m-d",strtotime("$lastupdate + 1 month"));
	}else if($timepart == "3month")
	{
		$resulttime=date("Y-m-d",strtotime("$lastupdate + 3 month")); 
	}else if($timepart == "6month")
	{
		$resulttime=date("Y-m-d",strtotime("$lastupdate + 6 month"));
	}else if($timepart == "12month")
	{
		$resulttime=date("Y-m-d",strtotime("$lastupdate + 1 year")); 
	}
	
	$result =$resulttime;
	return($result);
}


function calc_after_day($timepart) //计算距离多少天
{
	$lastupdate = date('Y-n-j',strtotime(get_lastpostdate('server','post'))); //获取上次文章更新时间
	$localtime=date("Y-m-d"); //获取现在的时间
	$resulttime=$localtime; 
    if($timepart == "1month")
	{
		$resulttime=date("Y-m-d",strtotime("$lastupdate + 1 month"));
	}else if($timepart == "3month")
	{
		$resulttime=date("Y-m-d",strtotime("$lastupdate + 3 month")); 
	}else if($timepart == "6month")
	{
		$resulttime=date("Y-m-d",strtotime("$lastupdate + 6 month"));
	}else if($timepart == "12month")
	{
		$resulttime=date("Y-m-d",strtotime("$lastupdate + 1 year")); 
	}
	
	$diff_seconds=strtotime($resulttime)-strtotime($localtime);
	
	$result = floor(($diff_seconds)/86400);
	return($result);
}

function endline() //发送告别文章
{
	$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
	$content='这个人什么都没说，就跌落于凡尘，愿天堂能够守护你的平安，阿门！';
    if(file_exists("$DOCUMENT_ROOT/lifebot.html"))
	{
		 $content=file_get_contents("$DOCUMENT_ROOT/lifebot.html");
	}
	
	$my_post = array (
    'post_title' => readconfig()[0], //获取告别文章标题
    'post_content' => $content,
    'post_status' => 'publish', //如果要发布计划 填写future
    'post_author' => 1,
    'post_category' => array(0), //此处未分类
    'post_date'=>date('Y-m-d H:i:s'));
			
	$postID=wp_insert_post( $my_post );
}

//添加面板到管理员后台
add_action('init','lifebot_settings_menu');

//初始化配置
$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT']; //获取网站根目录
if(file_exists("$DOCUMENT_ROOT/lifebot.config") == false)
{
	$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
	$fp = fopen("$DOCUMENT_ROOT/lifebot.config",'w');
	fwrite($fp,"告别了，我的挚爱。");
	fwrite($fp,"\n1month");
	fclose($fp);
	chmod("$DOCUMENT_ROOT/lifebot.config",0755); //设置文件权限避免无法读取配置
}


//添加自定义计划类型 [Wp-cron]
add_filter( 'cron_schedules', 'lifebot_every_time' );
function lifebot_every_time( $schedules ) {
    $schedules['every_time'] = array(
        'interval'  => 5,
        'display'   => __( '每隔5秒钟', 'salong' )
    );
    return $schedules;
}
if ( ! wp_next_scheduled( 'lifebot_every_time' ) ) {
    wp_schedule_event( time(), 'every_time', 'lifebot_every_time' );
}

//移除无效计划 [Wp-cron]
add_action('wpjam_remove_invild_crons', 'wpjam_remove_invild_crons');
function wpjam_remove_invild_crons(){
	global $wp_filter;

	$wp_crons = _get_cron_array();

	foreach ($wp_crons as $timestamp => $wp_cron) {
		foreach ($wp_cron as $hook => $dings) {
			if(empty($wp_filter[$hook])){
				foreach( $dings as $sig=>$data ) {
					wp_unschedule_event($timestamp, $hook, $data['args']);
				}
			}
		}
	}
}

if(!wp_next_scheduled('wpjam_remove_invild_crons')) {
	wp_schedule_event( time(), 'daily', 'wpjam_remove_invild_crons' );
}

//程序主功能

add_action( 'lifebot_every_time', 'lifebot_average');
function lifebot_average() {
	//判断配置文件是否存在
	$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT']; //获取网站根目录
	if(file_exists("$DOCUMENT_ROOT/lifebot.config") == false)
	{
		$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
		$fp = fopen("$DOCUMENT_ROOT/lifebot.config",'w');
		fwrite($fp,"告别了，我的挚爱。");
		fwrite($fp,"\n1month");
		fclose($fp);
		chmod("$DOCUMENT_ROOT/lifebot.config",0755); //设置文件权限避免无法读取配置
	}
    
        if(get_status($timepart) == "已逝"){endline();}
        //获取状态判断时间段内并度过宽限期后15天仍未更新文章则自动发送告别文章
}

//EOF

?>
