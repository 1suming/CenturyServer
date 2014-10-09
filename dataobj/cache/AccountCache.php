<?php

require_once PATH_MODEL . 'AccountModel.php';
/**
 +----------------------------------------------------------
 *    AccountCache
 +----------------------------------------------------------
 *   获取修改添加用户信息
 *
 +----------------------------------------------------------
 *  @author     Wenson
 *  @version    2012-12-30
 *  @package    dataobj
 +----------------------------------------------------------
 */
class AccountCache extends AccountModel{
	public function getAccountByGameuid($gameuid) {
		$key = $this->getUserCacheKey ( $gameuid );
		$ret = $this->getFromCache ( $key );
		if (empty ( $ret )) {
			$ret = parent::getAccountByGameuid ( $gameuid );
			if(!empty($ret)){
				$this->setToCache($key, $ret);
			}
		}
		if(!empty($ret)){
			$ret['gameuid']=$ret['_id'];
			$ret['username']=isset($ret['username'])?$ret['username']:"";
			$ret['photo']=isset($ret['photo'])?$ret['photo']:"";
			$ret['pushcount']=$this->getPushCount($gameuid);
			//加上推送的数量取得
		}
		return $ret;
	}
	
	public function getPushCount($gameuid){
		$likeKey = "PUSH_COUNT";
		return $this->getRedisHash ( $likeKey,$gameuid);
	}
	public function resetPushCount($gameuid,$count=0){
		$likeKey = "PUSH_COUNT";
		return $this->setRedisHash ( $likeKey, $gameuid, $count );
	}
	
	public function getAccountByUid($uid) {
		$gameuid=$this->getGameuid($uid);
		return $this->getAccountByGameuid($gameuid	);
	}
	
	protected function updateUserName($name,$photo) {
		parent::updateUserName ( $name,$photo );
		$key = $this->getUserCacheKey ( $this->gameuid );
		$this->delFromCache ( $key );
		return true;
	}
	
	
	private function getUserCacheKey($gameid) {
		return sprintf ( CACHE_KEY_USER, $gameid );
	}
}