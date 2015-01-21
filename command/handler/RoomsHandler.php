<?php
/**
 * 玩家房间基本类
 */
require_once PATH_CACHE . 'RoomsCache.php';
class RoomsHandler extends RoomsCache{
	public function NewRoom(){
		//网络房间基数
		return parent::NewRoom();
	}
	
	
	/* 
	 * 玩家加入某个房间
	 * @see RoomsModel::JoinRoom()
	 */
	public function JoinRoom($roomid){
		$ret=parent::addToRoom ( $roomid );
		if ($ret>0) {
			include_once PATH_HANDLER . 'AccountHandler.php';
			$account = new AccountHandler ( $this->uid );
			$userInfo = $account->getAccountByUid($this->uid);
			$content = $userInfo ['username'] . "已经加入到游戏中";
			$account->sendPushByGameuid ( $roomid, $content );
			return 1;
		}
		return $ret;
	}
	
	public function PunishSomeOne($gameuidarr){
		$roomid=$this->gameuid;
		$ret=$this->GetRoomInfo($roomid);
		$gameuid_name=array();
		foreach ($ret['room_user'] as $key=>$value){
			$gameuid_name[$value['gameuid']]['username']=$value['username'];
			$gameuid_name[$value['gameuid']]['photo']=$value['photo'];
		}
		include_once PATH_HANDLER . 'AccountHandler.php';
		$account = new AccountHandler ( $this->uid );
		
		include_once PATH_HANDLER . 'PunishHandler.php';
		$punish = new PunishHandler ( $this->uid );
		//先判断一下玩家是否都在房间里，如果不在房间里，则
		$result=array();
		foreach ($gameuidarr as $key=>$value){
			if(key_exists($value, $gameuid_name)||in_array($value, array(-1,-2,-3,-4,-5))){
				$temPublish=$punish->getRandomOne(1);
// 				file_put_contents(PATH_LOG."punish".date("Y-m-d").".log", print_r($temPublish),FILE_APPEND);
				$content="惩罚：".$temPublish['content'];
				$username = $gameuid_name [$value]['username'];
				$photo=isset($gameuid_name [$value]['photo'])?$gameuid_name [$value]['photo']:"";
				if ($value < 0) {
					$username = "NO." . abs ( $value );
				}
				$result [] = array (
						'username' => $username,
						'photo' => $photo,
						'content' => $content,
						'gameuid' => $value 
				);
				
				if($value>0){
					$this->setUserContent($value, $content);
					if($value!=$this->gameuid){
						$account->sendPushByGameuid($value, $content);						
					}
				}
			}
		}
		foreach ($gameuid_name as $key=>$value){
			if(!in_array($key, $gameuidarr)){
				$content="游戏胜利【".$ret['content'].'】';
				$this->setUserContent($key, $content);
				//$account->sendPushByGameuid($key, $content);
			}
		}
		
		return $result;
	}
	
	public function delSomeOne($gameuid){
		$roomInfo = $this->getRoomUserInfo ( $this->gameuid );
		if (empty ( $roomInfo )) {
			return false;
		}
		$ret = parent::removeSomeOne ( $roomInfo ['roomid'], $gameuid );
		if ($ret) {
			include_once PATH_HANDLER . 'AccountHandler.php';
			$account = new AccountHandler ( $this->uid );
			$content = '您被管理员移出房间';
			$account->sendPushByGameuid ( $gameuid, $content );
		}
		return $ret;
	}
	/**
	 * 
	 * @param unknown_type $type 1,谁是卧底 2，杀人游戏
	 */
	public function StartGame($type,$addPeople){
		$roomInfo=$this->GetRoomInfo($addPeople);
		$userCount=count($roomInfo['room_user']);
// 		$userCount=10;
		$roomcontent='';
		$gamename="";
		if ($type == 1) {
			if($userCount<4){
// 						return false;
			}
			include_once PATH_HANDLER . 'UnderCoverRoomHandler.php';
			$ucroom = new UnderCoverRoomHandler ( $this->uid );
			$roomContent = $ucroom->initcontent ( $userCount );
			$roomcontent="平民：".$roomContent['father']." 卧底：".$roomContent['son'];
		}
		else if($type==2){
			//杀人游戏分配身份
			include_once PATH_HANDLER . 'UnderCoverRoomHandler.php';
			$ucroom = new UnderCoverRoomHandler ( $this->uid );
			$roomContent=$ucroom->initKiller($userCount);
			$roomcontent="警察：".$roomContent['police']."人 平民：".$roomContent['killer'].'人';
		}
		$this->setRoomType($roomInfo['_id'],$type,$roomcontent);
	

		//准备发送推送
		include_once PATH_HANDLER . 'AccountHandler.php';
		$account = new AccountHandler ( $this->uid );
		foreach ($roomInfo['room_user'] as $key=>$value){
			$content="身份：".$roomContent['content'][$key];
			$roomInfo['room_user'][$key]['content']=$roomContent['content'][$key];
			if(!isset($value['gameuid'])||$value['gameuid']<0){
				continue;
			}
			$this->setUserContent($value['gameuid'], $content);
			if($this->gameuid!=$value['gameuid']&&$value['gameuid']>0){				
				$account->sendPushByGameuid($value['gameuid'], $content);
			}
		}
// 		$account->sendPushByGameuid($this->gameuid, "我爱你，我超爱你");

		if ($type == 1) {
			$gamename = "谁是卧底";
		} else if ($type == 2) {
			$gamename = "杀人游戏";
		}
		$roomInfo['content']=$gamename;
		$roomInfo['room_contente']=$roomContent;
		$roomInfo['roomtype']=$type;
		return $roomInfo;
	}
	
	
	public function GetRoomInfoOne(){
		$ret= parent::getRoomUserInfo($this->gameuid);
		if ($ret ['roomid'] > 0) {
			$roomInfo = $this->getInfo ( $ret ['roomid'] );
			$roomInfo ['name'] = isset ( $roomInfo ['name'] ) ? $roomInfo ['name'] : "";
			include_once PATH_HANDLER . '/LotteryHandler.php';
			$lottery = new LotteryHandler ( $this->uid );
			$shackret = $lottery->shake ( $ret ['roomid'] );
			$ret ['shackinfo'] = $shackret;
			if($shackret['clickcount']>0){
				$ret['content']=$ret['content']."[+".$shackret['clickcount']."]";
			}
			$ret ['roominfo'] = $roomInfo;
		}
		return $ret;
	}
	
	public function LevelRoom(){
		$userRoomInfo=$this->getRoomUserInfo($this->gameuid);
		$roomid=$userRoomInfo['roomid'];
		if($roomid>0){
			$roomInfo=$this->getInfo($roomid);
			if($roomInfo['gameuid']==$this->gameuid){
				//代表是自己创建了这个
				$retuser=parent::distroyRoom ($roomid);
				include_once PATH_HANDLER . 'AccountHandler.php';
				$account = new AccountHandler ( $this->uid );
				$userInfo = $account->getAccountByUid ( $this->uid );
				$str = $userInfo ['username'] . "解散了房间" . $this->gameuid;
				foreach ( $retuser as $key => $value ) {
					$temgameuid = $value;
					$account->sendPushByGameuid ( $temgameuid, $str );
				}
			}
			else{
				$userInfo = $account->getAccountByUid ( $this->uid );
				$content = $userInfo ['username'] . "离开了房间";
				$account->sendPushByGameuid( $roomid, $content );
				return true;
			}
		}
		else{
			return -1;
		}
	} 
	
	public function getRoomUserList($roomid){
		return parent::getRoomUserList($roomid);
	}
	
	/**
	 *	这个是主持人取得信息的方式
	 */
	public function GetRoomInfo($addPeople=0,$isLottery=false) {
		//先取下这个用户对应的roomid
// 		$userRoomInfo=$this->getRoomUserInfo($this->gameuid);
		$roomid=$this->gameuid;
		$ret = $this->getInfo ( $roomid );
		$roomUserList = $this->getRoomUserList ( $roomid );
		
		$retpeople=array();
		//添加两个多余的玩家
		for($i=1;$i<=$addPeople;$i++){
			$retpeople[]=array('username'=>"NO. $i",'gameuid'=>"-".$i,'photo'=>"");
		}
		
		include_once PATH_HANDLER . '/LotteryHandler.php';
		$lottery = new LotteryHandler ( $this->uid );
		$shackret = $lottery->shake ( $roomid );
		
		$ret['isshake']=$lottery->isRoomShake();

		$shackCountInfo=$lottery->getUserShackCount($roomUserList);
		if($isLottery){
			$lotteryInfo=$lottery->getSetting($this->gameuid);
			$lotteryarr=$lotteryInfo['content'];
			$lotteryRet=array();
			foreach ($lotteryarr as $key=>$value){
				$lotteryRet[$value['id']]=$value;
			}
			$hasLottery=$lottery->getHasLottery();
		}
		
		
		include_once PATH_HANDLER . 'AccountHandler.php';
		$account = new AccountHandler ( $this->uid );
		foreach ($roomUserList as $key=>$value){
			$tem=$account->getAccountByGameuid($value);
			if($isLottery){
				$tem['shakecount']=isset($shackCountInfo[$value])?$shackCountInfo[$value]:0;
				$tem['lotterycontent']=isset($hasLottery[$value])?$lotteryRet[$hasLottery[$value]]:array('empty'=>true);
			}
			$retpeople[]=$tem;
		}
		$ret['room_user']=$retpeople;
		return $ret;
	} 
	
	public function setUserContent($gameuid,$content){
		return parent::setUserContent($gameuid, $content);
	}
	
}