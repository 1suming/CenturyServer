<?php 
$idFirst = $_REQUEST ['gameid'];
include_once PATH_HANDLER . 'GameHandler.php';
$game = new GameHandler ( $uid, 'WEB' );
if (empty ( $idFirst )) {
	$gameList = $game->getGameList ( 1 );
	$idFirst = $gameList [0] ['_id'];
}
$gamecontent=$game->getOne($idFirst);
$likeInfo=$game->getLikeInfo($idFirst);

?>
