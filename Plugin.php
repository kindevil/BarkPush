<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * 基于Bark的评论消息推送插件
 *
 * @package BarkPush
 * @author chenai
 * @version 1.0.0
 * @link https://kindevil.com
 */
 
 class BarkPush_Plugin implements Typecho_Plugin_Interface{
	public static function activate() {
		Typecho_Plugin::factory('Widget_Feedback')->finishComment = array('BarkPush_Plugin', 'render');
		return '插件启用成功，请设置Bark服务器和Key';
	}
	
	public static function deactivate(){//插件禁用方法
		return '禁用成功';
	}
	
	public static function config(Typecho_Widget_Helper_Form $form){
		$serverAddr = new Typecho_Widget_Helper_Form_Element_Text('server',null,'','服务器地址');
		$form->addInput($serverAddr);
		
		$key = new Typecho_Widget_Helper_Form_Element_Text('key',null,'','KEY');
		$form->addInput($key);
	}
	
	public static function personalConfig(Typecho_Widget_Helper_Form $form){
    }
    
    public static function render($comment){
		$config = Helper::options();
		
		$server = $config->plugin('BarkPush')->server;
		$server = $server.'/push';
		
		$key = $config->plugin('BarkPush')->key;
		
		$body = $comment->author." 在文章《".$comment->title."》评论说：".$comment->text;
		
		$curl = curl_init();
		
		curl_setopt_array($curl, [
			CURLOPT_URL => $server,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => '{
			  "body": "'.$body.'",
			  "device_key": "'.$key.'",
			  "title": "评论通知",
			  "badge": 1,
			  "category": "评论通知",
			  "sound": "minuet.caf",
			  "icon": "https://kindevil.com/favicon.ico",
			  "group": "评论通知",
			  "url": "'.$comment->permalink.'"
			}',
			CURLOPT_HTTPHEADER => [
				'Content-Type: application/json; charset=utf-8',
			],
		]);
		$response = curl_exec($curl);
		curl_close($curl);
		
		return $response;
    }
 }