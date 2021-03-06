<?php
// 新建一条记录
include_once 'BaseCommand.php';
include_once 'handler/AccountHandler.php';
include_once 'handler/BehaveHandler.php';
include_once 'handler/MailHandler.php';
class UserGetInfo extends BaseCommand {
	protected function executeEx($params) {
		$uid = $params ['uid'];
		if (empty ( $uid )) {
			$uid=$this->uid;
		}
		
		$account = new AccountHandler ( $this->uid );
		$ret= $account->getAccountByUid($uid);
		$mail = new MailHandler ( $this->uid );
		$retmail = $mail->getOneMail ();
		
		if (! empty ( $retmail )) {
			$ret ['mail'] = array (
					$retmail
			);
		}
		
		
// 		$account->sendJPush($account->getUidFromGameuid(838), "测试gameuid");
		
		$behavea = new BehaveHandler ( $this->uid );
		$behavea->newBehave ( "getUserInfo","","" );
		
		return $this->reutrnDate ( COMMAND_ENPTY ,$ret);
	}
}