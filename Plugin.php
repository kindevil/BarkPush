<?php
namespace TypechoPlugin\BarkPush;

use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Form\Element\Text;
use Typecho\Widget\Helper\Form\Element\Radio;
use Widget\Options;

if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * 基于Bark的评论消息推送插件
 *
 * @package BarkPush
 * @author chenai
 * @version 1.0.0
 * @link https://github.com/kindevil/BarkPush
 */
 
 class Plugin implements PluginInterface{
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     */
	public static function activate() {
		\Typecho\Plugin::factory('Widget_Feedback')->finishComment = array('BarkPush_Plugin', 'render');
		return '插件启用成功，请设置Bark服务器和Key';
	}
	
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     */
	public static function deactivate(){//插件禁用方法
		return '禁用成功';
	}
	
    /**
     * 获取插件配置面板
     *
     * @param Form $form 配置面板
     */
	public static function config(Form $form){
		$serverAddr = new Text('server',null,'','服务器地址');
		$form->addInput($serverAddr);
		
		$key = new Text('key',null,'','KEY');
		$form->addInput($key);
		
		$push = new Radio('push',
		array(1=>_t('发送'),0=>_t('不发送')),0,_t('用户登录之后的评论否发送通知'),_t('选择“否”之后用户登录后台之后发出的评论不会推送通知到APP'));
		$form->addInput($push);
	}

    /**
     * 个人用户的配置面板
     *
     * @param Form $form
     */
    public static function personalConfig(Form $form)
    {
    }

    /**
     * 插件实现方法
     *
     * @access public
     * @return void
     */
    public static function render($comment){
		$config = Options::alloc();
		
		//判断用户是否登录，且插件是否设置为用户登录之后不推送通知
		if (\Widget\User::alloc()->hasLogin() && $config->plugin('BarkPush')->push == 0) {
			return "";
		}
		
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