<?php
	namespace think\core;
	class Auth{
		public function beforeAction($request){

		
			$noNeedLogin = C('DEFAULT_NO_LOGIN');
			$mod = APP_NAME.'/'.MODULE_NAME;
			$uri = APP_NAME.'/'.MODULE_NAME.'/'.ACTION_NAME;
			if( in_array($mod,$noNeedLogin)  || in_array($uri,$noNeedLogin) || in_array(APP_NAME,$noNeedLogin) ){
				$res = ['status'=>1];
				$mid = session('mid');
				if($mid){
					$res['__set']=['mid'=>$mid,'user'=>model('User')->getDetail(session('mid'))];
				}
				return $res;
				
			}
			$isLogged = model('Passport')->isLogged();
			if($isLogged){
				return ['status'=>1,'__set'=>['mid'=>session('mid'),'user'=>model('User')->getDetail(session('mid'))]];
			}
			
			$LOGIN_URL = C('LOGIN_URL');
			if($LOGIN_URL){
				$exp = strpos($LOGIN_URL,'?') ? '&':'?';
				$url = $LOGIN_URL.$exp.'redirect_uri='.urlencode(SITE_URL.$_SERVER['query_url']);
				return ['status'=>0,'jumpUrl'=>$url,'info'=>L('NEED_LOGIN')];
			}else{
				return ['status'=>0,'jumpUrl'=>U(C('DEFAULT_LOGIN_URL')),'info'=>L('NEED_LOGIN')];	
			}
			
		}

		public function beforeWidget($request){
			//目前认证流程和action一致
			return $this->beforeAction($request);
		}
	}
?>