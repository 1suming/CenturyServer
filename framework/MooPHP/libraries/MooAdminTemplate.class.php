<?php
/*
	More & Original PHP Framwork
	Copyright (c) 2007 - 2008 IsMole Inc.

	$Id: MooAdminTemplate.class.php 315 2008-06-05 03:02:24Z kimi $
*/

!defined('IN_MOOPHP') && exit('Access Denied');

class MooAdminTemplate {
	/**
	 * ������ͷ���˵�ҳ��
	 *
	 * @param array $topMenu �� �˵���������
	 * @return void stdout
	 */
	function topMenu($topMenu) {
		//note ���������ֱ���˳�
		if(!is_array($topMenu)) {
			exit('topMenu must be array');
		}

		//note ����������
		$menuLinks = $menuKeys = $comma = '';
		$i = 0;
		foreach ($topMenu as $key=>$val) {

			$menuLinks .= '<li><span><a href="#" onclick="sethighlight('.$i.'); togglemenu(\''.$key.'\'); parent.main.location=\''.$val['iniUrl'].'\';return false;">'.$val['name'].'</a></span></li>'."\n";

			$menuKeys .= $comma." '$key'";
			$comma = ',';
			$i++;
		}

		$param = array('menuKeys' => $menuKeys, 'menuLinks' => $menuLinks);
		echo $this->getHTML('topHtml', $param);
		exit();
	}

	/**
	 * ���������˵�ҳ��
	 *
	 * @param array $leftMenu �� �˵���������
	 * @return void stdout
	 */
	function leftMenu($leftMenu) {
		if(!is_array($leftMenu)) {
			exit('leftMenu must be array');
		}

		$menuLinks = '';
		$menucount = 0;
		$collapse = isset($_COOKIE['collapse']) ? $_COOKIE['collapse'] : '';
		$topMenu = array_keys($leftMenu);

		if(is_array($topMenu)) {
			foreach($topMenu as $key=>$menuDiv) {
				$menuLinks .= $key ? '</div><div id="'.$menuDiv.'" style="display: none">' : '<div id="'.$menuDiv.'">';
				$subMenu = array_keys($leftMenu[$menuDiv]);

				foreach($subMenu as $subMenuKey) {

					$menuLinks .= '<table width="146" border="0" cellspacing="0" align="center" cellpadding="0" class="leftmenulist" style="margin-bottom: 5px;">';
					$menus = $leftMenu[$menuDiv][$subMenuKey]['array'];

					$menucount++;
					$collapsed = preg_match("/\[$menucount\]/", $collapse);

					$menuLinks .= '<tr class="leftmenutext"><td><a href="###" onclick="collapse_change('.$menucount.')"><img id="menuimg_'.$menucount.'" src="./'.MOOPHP_ADMIN_DIR.'/images/menu_'.($collapsed ? 'add' : 'reduce').'.gif" border="0"/></a>&nbsp;'.
							'<a href="###" onclick="collapse_change('.$menucount.')">'.$leftMenu[$menuDiv][$subMenuKey]['name'].'</a></td></tr>'.
							'<tbody id="menu_'.$menucount.'" style="display:'.($collapsed ? 'none' : '').'">'.
							'<tr class="leftmenutd"><td><table border="0" cellspacing="0" cellpadding="0" class="leftmenuinfo">';

					foreach($menus as $menuData) {
						$menuLinks .= $menuData['title'] ? '<tr><td><a href="'.$menuData['url'].'" target="main">'.$menuData['title'].'</a></td></tr>' : '';
					}
					$menuLinks .= '</table></td></tr></tbody>';

					$menuLinks .= "</table>\n";
				}

			}
			$menuLinks .= '</div>';
		}

		$param = array('menuLinks'=>$menuLinks);
		echo $this->getHTML('leftHtml', $param);
		exit;
	}

	/**
	 * �����̨��ҳ���ҳͷ
	 *
	 * @return void stdout
	 */
	function adminHeader() {

		$param = array();
		echo $this->getHTML('adminHeaderHtml', $param);
	}

	/**
	 * �����̨��ҳ���ҳ��
	 *
	 * @return void stdout
	 */
	function adminFooter() {
		$param = array('version'=>MOOPHP_VERSION);
		echo $this->getHTML('adminFooterHtml', $param);
		exit;
	}

	/**
	 * ������ҳ��
	 *
	 * @param string $topUrl �� ͷ��ҳ���url
	 * @param string $leftUrl �� ���ҳ���url
	 * @param string $mainUrl �� ��ҳ���url
	 * @return void stdout
	 */
	function frame($topUrl, $leftUrl, $mainUrl) {

		$param = array('topUrl' => $topUrl, 'leftUrl' => $leftUrl, 'mainUrl' => $mainUrl);
		echo $this->getHTML('frameHtml', $param);
		exit();
	}

	function showNav($navs) {
		$navs = isset($GLOBALS['adminLang'][$navs]) ? $GLOBALS['adminLang'][$navs] : $navs;
		$headerSystem = !empty($GLOBALS['adminLang']['headerSystem']) ? $GLOBALS['adminLang']['headerSystem'] : '';
		echo '<table width="100%" border="0" cellpadding="0" cellspacing="0" class="guide">'.
			'<tr><td><a href="#" onClick="parent.menu.location=\'admincp.php?action=menu\'; parent.main.location=\'admincp.php?action=home\';return false;">'.$headerSystem.'</a>&nbsp;&raquo;&nbsp;'.$navs.'</td></tr></table><br />';
	}

	/**
	 * ���ɱ���Ϣ
	 *
	 * @param string $setName �� ���õı�������
	 * @param string $varName �� �����ı�����������
	 * @param string $value �� �����ı���ʼֵ
	 * @param string $type �� �����ı�����
	 * @param string $width �������ռ�Ŀ�ȱ���
	 * @param string $disabled �� �Ƿ��ֹ�ύ��������Ϊֻ��(readonly)
	 * @param string $hidden �� �Ƿ��������ص��۵���
	 * @param string $comment �� ���õ�˵��
	 * @param array $lang �� ���԰�����
	 * @return void stdout
	 */
	function showForm($setName, $varName, $value, $type = 'radio', $width = '45%', $disabled = '', $hidden = 0, $comment = '', $lang = array()) {
		$check = array();
		$check['default'] = isset($check['default']) ? $check['default'] : '';
		$check['true'] = isset($check['true']) ? $check['true'] : '';
		$check['false'] = isset($check['false']) ? $check['false'] : '';
		$lang = !empty($lang) ? $lang : array('yes'=>'yes', 'no'=>'no', 'default'=>'default');

		$check['disabled'] = $disabled ? ' disabled' : '';
		$width = !$width ? '45%' : $width;

		$aligntop = $type == "textarea" || $width != "45%" ?  "valign=\"top\"" : NULL;
		echo "<tr><td width=\"$width\" class=\"altbg1\" $aligntop>".
			'<b>'.$setName.'</b>'.($comment ? '<br /><span class="smalltxt">'.$comment.'</span>' : NULL).
			($disabled ? '<br /><span class="smalltxt" style="color:#FF0000">'.$lang[$setName.'_disabled'].'</span>' : NULL).'</td>'.
			'<td class="altbg2">';

		if($type == 'radio') {
			$value ? $check['true'] = "checked" : $check['false'] = "checked";
			$value ? $check['false'] = '' : $check['true'] = '';
			$check['hidden1'] = $hidden ? 'onclick="$(\'hidden_'.$setName.'\').style.display = \'\';"' : '';
			$check['hidden0'] = $hidden ? 'onclick="$(\'hidden_'.$setName.'\').style.display = \'none\';"' : '';
			echo "<input class=\"radio\" type=\"radio\" name=\"$varName\" value=\"1\" $check[true] $check[hidden1] $check[disabled]> {$lang['yes']} &nbsp; &nbsp; \n".
				"<input class=\"radio\" type=\"radio\" name=\"$varName\" value=\"0\" $check[false] $check[hidden0] $check[disabled]> {$lang['no']}\n";
		} elseif ($type == 'radioPlus') {
			$value == -1 ? $check['default'] = 'checked' : ($value ? $check['true'] = 'checked' : $check['false'] = 'checked');
			echo "<input class=\"radio\" type=\"radio\" name=\"$varName\" value=\"-1\" {$check['default']}> ".$lang['default']." &nbsp; &nbsp; \n".
				"<input class=\"radio\" type=\"radio\" name=\"$varName\" value=\"1\" {$check['true']}> {$lang['yes']} &nbsp; &nbsp; \n".
				"<input class=\"radio\" type=\"radio\" name=\"$varName\" value=\"0\" {$check['false']}> {$lang['no']}\n";
		} elseif ($type{0} == 'm') {
			if(substr($type, 1) == 'radio') {
				$radiocheck = array($value => ' checked');
				$split = count($varName[1]) > 2 ? '<br />' : ' &nbsp; &nbsp; ';
				foreach($varName[1] as $varary) {
					$onclick = '';
					if(!empty($varary[2])) {
						foreach($varary[2] as $ctrlid => $display) {
							$onclick .= '$(\''.$ctrlid.'\').style.display = \''.$display.'\';';
						}
					}
					$varary[0] = isset($varary[0]) ? $varary[0] : '';
					$varary[1] = isset($varary[1]) ? $varary[1] : '';
					$varName[0] = isset($varName[0]) ? $varName[0] : '';
					if(!empty($varary[0])) {
						$radiochecked = !empty($radiocheck[$varary[0]]) ? $radiocheck[$varary[0]]  : '';
					}else {
						$radiochecked = '';
					}
					$onclick = $onclick ? ' onclick="'.$onclick.'"' : '';
					echo '<input class="radio" type="radio" name="'.$varName[0].'" value="'.$varary[0].'"'.$radiochecked.$check['disabled'].$onclick.'> '.$varary[1].$split;
				}
			} else {
				$checkboxs = count($varName[1]);
				$value = sprintf('%0'.$checkboxs.'b', $value);$i = 1;
				foreach($varName[1] AS $key => $var) {
					echo '<input class="checkbox" type="checkbox" name="'.$varName[0].'['.$i.']" value="1"'.($value{$checkboxs - $i} ? ' checked' : '').' '.(!empty($varName[2][$key]) ? $varName[2][$key] : '').'> '.$var.'<br />';
					$i++;
				}
			}
		} elseif ($type == 'text' || $type == 'password') {
			echo "<input type=\"$type\" size=\"50\" name=\"$varName\" value=\"".MooHtmlspecialchars($value)."\" $check[disabled]>\n";
		} elseif ($type == 'calendar') {
			echo "<input type=\"$type\" size=\"50\" name=\"$varName\" value=\"".MooHtmlspecialchars($value)."\" onclick=\"showcalendar(event, this)\">\n";
		} elseif ($type == 'textarea') {
			$readonly = $disabled ? 'readonly' : '';
			echo "<img src=\"./".MOOPHP_ADMIN_DIR."/images/zoomin.gif\" onmouseover=\"this.style.cursor='pointer'\" onclick=\"zoomtextarea('$varName', 1)\"> <img src=\"./".MOOPHP_ADMIN_DIR."/images/zoomout.gif\" onmouseover=\"this.style.cursor='pointer'\" onclick=\"zoomtextarea('$varName', 0)\"><br /><textarea $readonly rows=\"6\" name=\"$varName\" id=\"$varName\" cols=\"50\">".MooHtmlspecialchars($value)."</textarea>";
		} elseif ($type == 'select') {
			echo '<select name="'.$varName[0].'" style="width: 55%">';
			foreach($varName[1] as $option) {
				$selected = $option[0] == $value ? 'selected' : '';
				echo "<option value=\"$option[0]\" $selected>".$option[1]."</option>\n";
			}
			echo '</select>';
		} else {
			echo $type;
		}
		echo '</td></tr>';
		if($hidden) {
			echo '</tbody><tbody class="sub" id="hidden_'.$setName.'" style="display: '.($value ? '' : 'none').'">';
		}
		echo "\n\n";
	}

	function showForms($name, $type = '', $value='') {
		if($type == 'formheader') {
			echo '<form method="post" name="'.$name.'" id="'.$name.'" action="'.$value.'">';
		} elseif ($type == 'formfooter') {
			echo '<br /><center><input class="button" type="submit" name="'.$name.'" value="'.$value.'"></center></form>';
		}
	}

	function showType($name, $type = '', $submit = '', $colspan = 2) {
		$name = isset($GLOBALS['adminLang'][$name]) ? $GLOBALS['adminLang'][$name] : $name;
		$id = substr(md5($name), 16);
		$submitHtml = $submit ? '<center><input class="button" type="submit" name="'.$submit.'" value="'.$GLOBALS['adminLang']['submit'].'"></center>' : '';
		if($type != 'bottom') {
			if(!$type) {
				echo '</table><br />';
			}
			if(!$type || $type == 'top') {
				echo '<a name="'.$id.'"></a><table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder"><tr class="header"><td colspan="'.$colspan.'">'.$name.'<a href="###" onclick="collapse_change(\''.$id.'\')"><img id="menuimg_'.$id.'" src="./'.MOOPHP_ADMIN_DIR.'/images/menu_reduce.gif" border="0" style="float: right; margin-top: -12px; margin-right: 8px;" /></a></td></tr><tbody id="menu_'.$id.'" style="display: yes">';
			}
		} else {
			echo '</tbody></table>'.$submitHtml;
		}
	}

	/**
	 * �����Ҫ��html����
	 *
	 * @param string $type ����Ҫ��õ�html���������
	 * @param string $param ����Ҫ�ڻ�õ�html�����н����ı�����ɵ�����
	 * @return string
	 */
	function getHTML($type, $param) {
		foreach(array('topUrl', 'leftUrl', 'mainUrl', 'menuLinks', 'menuKeys', 'version', 'message') as $valuekey) {
			if(empty($param[$valuekey])) {
				$param[$valuekey] = '';
			}
		}
		$param['adminDir'] = MOOPHP_ADMIN_DIR;

		include  MOOPHP_ROOT.'/libraries/MooAdminTemplateGetHtml.inc.php';
		return $$type;
	}

	/**
	 * ��ʾ��ʾ��Ϣ
	 *
	 * @param string $message ����ʾ����
	 * @param string $forward ����ʾ���ص�ַ
	 * @param intval $delay ����ת��ʱ����λΪ�룬��Ϊ0��ʱ���ڵ�ǰҳ��ֹͣ
	 * @param string $extra ����չ��Ϣ
	 * @return void
	 */
	function showMessage($message, $forward = '', $delay = 3, $extra = '') {
		if($forward) {
			$message .= "<br /><br /><br /><a href=\"$forward\">{$GLOBALS['adminLang']['redirect']}</a>";
			$message .= $delay ? "<script>setTimeout(\"document.location='$forward';\", $delay * 1000);</script>" : '';
		}
		$message = "<br /><br /><br />$message$extra<br /><br />";
		$this->adminHeader();
		echo $this->getHTML('adminShowMessageHtml', array('message'=>$message));
		$this->adminFooter();
	}
}
