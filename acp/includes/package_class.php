<?php
/*
************************************************************
Litotex BrowsergameEngine
http://www.Litotex.de
http://www.freebg.de

Copyright (c) 2008 FreeBG Team
************************************************************
Hinweis:
Diese Software ist urheberechtlich geschützt.

Für jegliche Fehler oder Schäden, die durch diese Software
auftreten könnten, übernimmt der Autor keine Haftung.

Alle Copyright - Hinweise Innerhalb dieser Datei
dürfen NICHT entfernt und NICHT verändert werden.
************************************************************
Released under the GNU General Public License
************************************************************
*/

class package {
	/**
	* @var string FTP oder PHP zum installieren/deinstallieren... nutzen [FTP ist immer sinnvoll, außer es funktioniert nicht, PHP kann unter umständen dazu führen, dass einige Dateien nicht mehr per FTP gelöscht werden können]
	*/
	//	private $_method = 'FTP'; PHP wurde deaktiviert, da diese Funktion nicht sinnvoll wäre. Es kann nur noch FTP verwendet werden.
	/**
	* @var Object FTP Verbindungsklasse
	*/
	private $_ftp_connection; //Do not change
	/**
	* @var bool Steht eine FTP Verbindung?
	*/
	private $_ftp_connection_initialized = false; //Do not change
	private $_debug = '';
	/**
	* @var string Ordner, in dem Die Scripts liegen
	*/
	private $_resource = 'acp/tmp';
	/**
	* @var string Ordner in dem die Scripts liegen (muss per FTP erreichbar sein)
	*/
	private $_ftp_resource = 'acp/tmp';
	/**
	* @var string Paketname
	*/
	private $_pack_name = '';
	/**
	* @var bool Initialisiert?
	*/
	public $initialized = false;
	/**
	* @var bool Bei true werden alle Aktionen nur simuliert.
	*/
	private $_noaction = false;
	/**
	* Kopiert Dateien aus dem Ordner src in den Zielordner
	* @param string dir Ordner zum kopieren (im src Ordner)
	* @param string dest Speicherordner, wird falls nicht existent angelegt
	* @param bool Simulation bei true
	* @return bool
	*/
	public function package($package_name, $ftp, $noaction = false) {
		$this->_resource = LITO_ROOT_PATH . $this->_resource;
		if ($ftp !== false) {
			$this->_ftp_connection = $ftp;
			if (!$this->_ftp_connection->lito_root)
			return false;
			else
			$this->_ftp_connection_initialized = true;
		}
		if (!is_dir($this->_resource . '/' . $package_name))
		return false;
		$this->_pack_name = $package_name;
		$this->initialized = true;
		$this->_noaction = $noaction;
		return true;
	}
	/**
	* Erstellt ein Verzeichniss, sollte das Vaterverzeichniss nicht existieren, wird dieses vorher erstellt
	* @param string dir Verzeichnissname
	* @return bool
	*/
	private function _req_mkdir($dir) {
		if (!$this->_ftp_connection_initialized)
		return false;
		$dir = preg_replace('!^\.\/!', '', $dir);
		if ($this->_ftp_connection->exists($dir))
		return true;
		if ($this->_ftp_connection->exists(dirname($dir)) || dirname($dir) == '.')
		return $this->_ftp_connection->mk_dir($dir);
		if ($this->_req_mkdir(dirname($dir)))
		return $this->_ftp_connection->mk_dir($dir);
	}
	/**
	* Verschiebt Dateien aus dem src Ordner
	* @param string Datei zum kopieren
	* @param string Zieldatei
	* @return bool
	*/
	private function _move_file($file, $dest) {
		if (!$this->_ftp_connection->exists($this->_ftp_resource . '/' . $this->_pack_name . '/' . $file))
		return false;
		$dest_dir = dirname($dest);
		if (!$this->_ftp_connection->exists($dest_dir))
		$this->_req_mkdir($dest_dir);
		if (!$this->_ftp_connection->exists($dest_dir))
		return false;
		if ($this->_ftp_connection->exists($dest)){
			$this->_ftp_connection->rm_file($dest);
		}
		return $this->_ftp_connection->mv($this->_ftp_resource . '/' . $this->_pack_name . '/' . $file, $dest);
	}
	public function install() {
		global $db, $n;
		if (!is_dir($this->_resource . '/' . $this->_pack_name))
		return false;
		if (!file_exists($this->_resource . '/' . $this->_pack_name . '/setup.php'))
		return false;
		$error = 0;
		if ($this->_noaction == true)
		$this->_debug .= '<p>Die Installation wird nur <b>simuliert</b> dabei werden Fehler ausgegeben, Datenbank&auml;nderungen werden allerdings <b>nicht</b> getestet!</p>';
		$setup = parse_ini_file($this->_resource . '/' . $this->_pack_name . '/setup.php');
		if (!isset ($setup['modul_name']) || !preg_match('!^[0-9a-z_\-]*$!', $setup['modul_name'])) {
			$this->_debug .= 'Der Modulname ist nicht valide, oder existiert nicht! Er darf nur aus kleien Buchstabe (a-z), Zahlen, Minus "-" und Unterstrich "_" bestehen. Die Installation kann nicht vortgesetzt werden!';
			$error++;
		}
		if (!isset ($setup['modul_version']) || !preg_match('!^[0-9]*\.[0-9]*\.[0-9]*$!', $setup['modul_version'])) {
			$this->_debug .= 'Die Versionsangabe des Moduls ist nicht valide, oder existiert nicht! Sie darf nur aus Zahlen, getrennt durch Punkte bestehen, diese Zahlen müssen in 3 Gruppen unterteilt sein (Beispiel: 0.3.11)';
			$error++;
		}
		if ($setup['modul_acp'] == 1)
		$prefx = 'acp/';
		else
		$prefx = '';
		$backup = date('d.m.Y_H_i_s', time());
		$backup_done = false;
		$copyed = array ();
		if (!$error && (isset($setup['move_core']) || $this->_ftp_connection->exists($prefx . 'modules/' . $setup['modul_name']) || $this->_ftp_connection->exists($prefx . 'images/standard/' . $setup['modul_name']) || $this->_ftp_connection->exists($prefx . 'themes/standard/' . $setup['modul_name']) || $this->_ftp_connection->exists($prefx . 'lang/' . $setup['modul_name']))) {
			$this->_debug .= '<p>Das Modul wurde installiert, es wird eine Sicherheitskopie unter /backup/' . $setup['modul_name'] . '-' . $backup . ' angelegt.</p>';
			if (!$this->_ftp_connection->exists('backup'))
			$this->_ftp_connection->mk_dir('backup');
			if (!$this->_ftp_connection->exists('backup'))
			return false;
			$this->_ftp_connection->mk_dir('backup/' . $setup['modul_name'] . '-' . $backup);
			if (!$this->_ftp_connection->exists('backup/' . $setup['modul_name'] . '-' . $backup))
			return false;
			@ $this->_ftp_connection->copy_req($prefx . 'modules/' . $setup['modul_name'], 'backup/' . $setup['modul_name'] . '-' . $backup . '/modules');
			@ $this->_ftp_connection->copy_req($prefx . 'images/standard/' . $setup['modul_name'], 'backup/' . $setup['modul_name'] . '-' . $backup . '/images');
			@ $this->_ftp_connection->copy_req($prefx . 'themes/standard/' . $setup['modul_name'], 'backup/' . $setup['modul_name'] . '-' . $backup . '/templates');
			@ $this->_ftp_connection->copy_req($prefx . 'lang/' . $setup['modul_name'], 'backup/' . $setup['modul_name'] . '-' . $backup . '/languages');
			$backup_done = true;
		}
		if (!$error) {
			if (isset ($setup['move_module_file'])) {
				if (is_array($setup['move_module_file'])) {
					foreach ($setup['move_module_file'] as $mod) {
						if ($this->_move_file('modules/' . $setup['modul_name'] . '/' . $mod, $prefx . 'modules/' . $setup['modul_name'] . '/' . $mod)) {
							$this->_debug .= "\n" . '<p>Folgende Moduldatei ' . $mod . ' wurde in den Modulordner verschoben';
							$copyed[$mod] = 'module';
						} else {
							$this->_debug .= "\n" . '<p>Folgende Moduldatei ' . $mod . ' wurde wurde aufgrund eines Fehlers nicht in den Modulordner verschoben, &uuml;berpr&uuml;fen sie die existenz der Datei.</p>';
							$error++;
						}
					}
				} else {
					if ($this->_move_file('modules/' . $setup['modul_name'] . '/' . $setup['move_module_file'], 'modules/' . $setup['modul_name'] . '/' . $setup['move_module_file'])) {
						$this->_debug .= "\n" . '<p>Folgende Moduldatei ' . $setup['move_module_file'] . ' wurde in den Modulordner verschoben';
						$copyed[$setup['move_module_file']] = 'module';
					} else {
						$this->_debug .= "\n" . '<p>Folgende Moduldatei ' . $setup['move_module_file'] . ' wurde wurde aufgrund eines Fehlers nicht in den Modulordner verschoben, &uuml;berpr&uuml;fen sie die existenz der Datei.</p>';
						$error++;
					}
				}
			}
			if (isset ($setup['move_template_file'])) {
				if (is_array($setup['move_template_file'])) {
					foreach ($setup['move_template_file'] as $mod) {
						if ($this->_move_file('themes/standard/' . $setup['modul_name'] . '/' . $mod, $prefx . 'themes/standard/' . $setup['modul_name'] . '/' . $mod)) {
							$this->_debug .= "\n" . '<p>Folgende Templatedatei ' . $mod . ' wurde in den Modulordner verschoben';
							$copyed[$mod] = 'template';
						} else {
							$this->_debug .= "\n" . '<p>Folgende Templatedatei ' . $mod . ' wurde wurde aufgrund eines Fehlers nicht in den Modulordner verschoben, &uuml;berpr&uuml;fen sie die existenz der Datei.</p>';
							$error++;
						}
					}
				} else {
					if ($this->_move_file('themes/standard/' . $setup['modul_name'] . '/' . $setup['move_template_file'], 'themes/standard/' . $setup['modul_name'] . '/' . $setup['move_module_file'])) {
						$this->_debug .= "\n" . '<p>Folgende Templatedatei ' . $setup['move_template_file'] . ' wurde in den Modulordner verschoben';
						$copyed[$setup['move_template_file']] = 'template';
					} else {
						$this->_debug .= "\n" . '<p>Folgende Templatedatei ' . $setup['move_template_file'] . ' wurde wurde aufgrund eines Fehlers nicht in den Modulordner verschoben, &uuml;berpr&uuml;fen sie die existenz der Datei.</p>';
						$error++;
					}
				}
			}
			if (isset ($setup['move_lang_file'])) {
				if (is_array($setup['move_lang_file'])) {
					foreach ($setup['move_lang_file'] as $mod) {
						if ($this->_move_file('lang/' . $setup['modul_name'] . '/' . $mod, $prefx . 'lang/' . $setup['modul_name'] . '/' . $mod)) {
							$this->_debug .= "\n" . '<p>Folgende Sprachdatei ' . $mod . ' wurde in den Modulordner verschoben';
							$copyed[$mod] = 'lang';
						} else {
							$this->_debug .= "\n" . '<p>Folgende Sprachdatei ' . $mod . ' wurde wurde aufgrund eines Fehlers nicht in den Modulordner verschoben, &uuml;berpr&uuml;fen sie die existenz der Datei.</p>';
							$error++;
						}
					}
				} else {
					if ($this->_move_file('lang/' . $setup['modul_name'] . '/' . $setup['move_lang_file'], 'lang/' . $setup['modul_name'] . '/' . $setup['move_module_file'])) {
						$this->_debug .= "\n" . '<p>Folgende Sprachdatei ' . $setup['move_lang_file'] . ' wurde in den Modulordner verschoben';
						$copyed[$setup['move_lang_file']] = 'lang';
					} else {
						$this->_debug .= "\n" . '<p>Folgende Sprachdatei ' . $setup['move_lang_file'] . ' wurde wurde aufgrund eines Fehlers nicht in den Modulordner verschoben, &uuml;berpr&uuml;fen sie die existenz der Datei.</p>';
						$error++;
					}
				}
			}
			if (isset ($setup['move_image_file'])) {
				if (is_array($setup['move_image_file'])) {
					foreach ($setup['move_image_file'] as $mod) {
						if ($this->_move_file('images/standard/' . $setup['modul_name'] . '/' . $mod, $prefx . 'images/standard/' . $setup['modul_name'] . '/' . $mod)) {
							$this->_debug .= "\n" . '<p>Folgende Bilddatei ' . $mod . ' wurde in den Modulordner verschoben';
							$copyed[$mod] = 'image';
						} else {
							$this->_debug .= "\n" . '<p>Folgende Bilddatei ' . $mod . ' wurde wurde aufgrund eines Fehlers nicht in den Modulordner verschoben, &uuml;berpr&uuml;fen sie die existenz der Datei.</p>';
							$error++;
						}
					}
				} else {
					if ($this->_move_file('images/standard/' . $setup['modul_name'] . '/' . $setup['move_image_file'], 'images/' . $setup['modul_name'] . '/' . $setup['move_module_file'])) {
						$this->_debug .= "\n" . '<p>Folgende Bilddatei ' . $setup['move_image_file'] . ' wurde in den Modulordner verschoben';
						$copyed[$setup['move_image_file']] = 'image';
					} else {
						$this->_debug .= "\n" . '<p>Folgende Bilddatei ' . $setup['move_image_file'] . ' wurde wurde aufgrund eines Fehlers nicht in den Modulordner verschoben, &uuml;berpr&uuml;fen sie die existenz der Datei.</p>';
						$error++;
					}
				}
			}
			if(isset($setup['move_core'])){
				$this->_debug .= '<p><b>Es werden Dateien des Core Systems ver&auml;ndert!</b></p>';
				if(is_array($setup['move_core'])){
					foreach ($setup['move_core'] as $mod) {
						if($this->_ftp_connection->exists($mod)){
							$this->_ftp_connection->mk_dir('backup/' . $setup['modul_name'] . '-' . $backup . '/core');
							$mod_arr = explode('/', $mod);
							$now = '';
							foreach($mod_arr as $i => $dir){
								if($i >= count($mod_arr) -1)
								continue;
								$now .= '/'.$dir;
								$this->_ftp_connection->mk_dir('backup/' . $setup['modul_name'] . '-' . $backup . '/core' . $now);
							}
							$this->_ftp_connection->mv($mod, 'backup/' . $setup['modul_name'] . '-' . $backup . '/core/'.$mod);
						}
						if ($this->_ftp_connection->mv('acp/tmp/' . $this->_pack_name . '/' . $mod, $mod)) {
							$this->_debug .= "\n" . '<p>Folgende Core-Datei ' . $mod . ' wurde in den Modulordner verschoben';
							$copyed[$mod] = 'core';
						} else {
							$this->_debug .= "\n" . '<p>Folgende Core-Datei ' . $mod . ' wurde wurde aufgrund eines Fehlers nicht in den Modulordner verschoben, &uuml;berpr&uuml;fen sie die existenz der Datei.</p>';
							$error++;
							$copyed[$mod] = 'core';
						}
					}
				}
			}
			if (isset ($setup['db_update']) && !$error) {
				$this->_debug .= "\n" . '<p>Folgende Datenbankupdates wurden ausgef&uuml;hrt:</p>';
				$ver_q = $db->query("SELECT `current_version` FROM `cc".$n."_modul_admin` WHERE `modul_name` = '".$setup['modul_name']."'");
				if($db->num_rows($ver_q) == 0)
				$ver = '0.0.0';
				else{
				$ver_arr = $db->fetch_array($ver_q);
				$ver = $ver_arr['current_version'];
			}
			if (is_array($setup['db_update'])) {
				//Aktuelle Versionsnummer laden
				foreach ($setup['db_update'] as $mod) {
					if(compare_versions_sinus($ver, preg_replace("!^([0-9]\.[0-9]\.[0-9]);.*!", "$1", $mod)) != 1)
					continue;
					$mod = preg_replace("!^([0-9]\.[0-9]\.[0-9]);!", "", $mod);
					if ($this->_noaction)
					$this->_debug .= "\n" . '<p>' . $mod . '</p>';
					else {
						if (mysql_query($mod))
						$this->_debug .= "\n" . '<p>' . $mod . '</p>';
						else {
							$this->_debug .= "\n" . '<p>Es ist ein <b>Fehler</b> bei folgendem Query "' . $mod . '" aufgetreten, Mysql meldet: "' . mysql_error() . '"</p>';
							$error++;
						}
					}
				}
			} else {
				if(compare_versions_sinus($ver, preg_replace("!^([0-9]\.[0-9]\.[0-9]);.*!", "$1", $setup['db_update'])) != 1)
				continue;
				$setup['db_update'] = preg_replace("!^([0-9]\.[0-9]\.[0-9]);!", "", $setup['db_update']);
				if ($this->_noaction)
				$this->_debug .= "\n" . '<p>' . $setup['db_update'] . '</p>';
				else {
					if ($db->query($setup['db_update']))
					$this->_debug .= "\n" . '<p>' . $setup['db_update'] . '</p>';
					else {
						$this->_debug .= "\n" . '<p>Es ist ein <b>Fehler</b> bei folgendem Query "' . $setup['db_update'] . '" aufgetreten, Mysql meldet: "' . $db->error() . '"</p>';
						$error++;
					}
				}
			}
		} else
		if ($error) {
			$this->_debug .= '<p>Da bereits Fehler aufgetreten sind, wird kein MySQl Befehl ausgef&uuml;hrt!</p>';
		}
		if ($error) {
			$this->_debug .= '<p>Da bereits Fehler aufgetreten sind, wird Das Modul nicht in der Datenbank gespeichert!</p>';
		} else {
			if ($this->_noaction) {
				$this->_debug .= '<p>Registriere das Spiel in der Datenbank.</p>';
			} else {
				$this->_debug .= '<p>Registriere das Spiel in der Datenbank.</p>';
				if($prefx == 'acp/'){
					$is_installed = $db->query("SELECT `modul_type` FROM `cc".$n."_modul_admin` WHERE `modul_name` = '".$setup['modul_name']."'");
				} else {
					$is_installed = $db->query("SELECT `modul_type` FROM `cc".$n."_modul_admin` WHERE `modul_type` = '".$setup['modul_game_type']."'");
				}
				if($db->num_rows($is_installed) == 0){
					$db->query("INSERT INTO `cc".$n."_modul_admin` (`modul_name`, `modul_description`, `disable_allowed`, `activated`, `startfile`, `current_version`, `acp_modul`, `show_error_msg`, `modul_type`, `new_upd_available`, `perm_lvl`) VALUES ('".$setup['modul_name']."', '".$setup['modul_description']."', '".$setup['modul_disable_allowed']."', '0', '".$setup['modul_filename']."', '".$setup['modul_version']."', '".$setup['modul_acp']."', '".$setup['modul_show_error_msg']."', '".$setup['modul_game_type']."', '0', '".$setup['modul_permission_level']."')");
				} else
				if($prefx == 'acp/'){
					$done = $db->query("UPDATE `cc".$n."_modul_admin` SET `modul_description` = '".$setup['modul_description']."', `current_version` = '".$setup['modul_version']."' WHERE `modul_name` = '".$setup['modul_name']."'");
				} else {
					$done = $db->query("UPDATE `cc".$n."_modul_admin` SET `modul_description` = '".$setup['modul_description']."', `current_version` = '".$setup['modul_version']."' WHERE `modul_type` = '".$setup['modul_game_type']."'");
				}
			}
		}
	}
	if ($error > 0 || $this->_noaction) {
		if ($error > 0)
		$this->_debug .= '<p><b>Es sind <u>' . $error . '</u> Fehler aufgetreten! Bitte kontaktieren sie den Entwickler des Moduls "' . $setup['modul_autor'] . '" per E-Mail an "<a href="mailto:' . $setup['modul_autor_mail'] . '">' . $setup['modul_autor_mail'] . '</a>" falls sie die Dateien nicht manipuliert haben.</b></p>';
		if ($backup_done) {
			$this->_debug .= '<p>Da ein Backup der alten Daten erstellt wurde, wird dieses nun zur&uuml;ckgespielt.</p>';
			foreach ($copyed as $file => $type) {
				switch ($type) {
					case 'module' :
					$this->_ftp_connection->mv($prefx . 'modules/' . $setup['modul_name'] . '/' . $file, 'acp/tmp/' . $this->_pack_name . '/modules/' . $setup['modul_name'] . '/' . $file);
					break;
					case 'image' :
					$this->_ftp_connection->mv($prefx . 'images/standard/' . $setup['modul_name'] . '/' . $file, 'acp/tmp/' . $this->_pack_name . '/images/standard/' . $setup['modul_name'] . '/' . $file);
					break;
					case 'lang' :
					$this->_ftp_connection->mv($prefx . 'lang/' . $setup['modul_name'] . '/' . $file, 'acp/tmp/' . $this->_pack_name . '/lang/' . $setup['modul_name'] . '/' . $file);
					break;
					case 'template' :
					$this->_ftp_connection->mv($prefx . 'themes/standard/' . $setup['modul_name'] . '/' . $file, 'acp/tmp/' . $this->_pack_name . '/themes/standard/' . $setup['modul_name'] . '/' . $file);
					break;
					case 'core' :
					$this->_ftp_connection->mv($file, 'acp/tmp/' . $this->_pack_name . '/' . $file);
					$this->_ftp_connection->mv('backup/' . $setup['modul_name'] . '-' . $backup . '/core/' . $file, $file);
					break;
				}
			}
			@ $this->_ftp_connection->req_remove($prefx . 'themes/standard/' . $setup['modul_name']);
			@ $this->_ftp_connection->req_remove($prefx . 'lang/' . $setup['modul_name']);
			@ $this->_ftp_connection->req_remove($prefx . 'images/standard/' . $setup['modul_name']);
			@ $this->_ftp_connection->req_remove($prefx . 'modules/' . $setup['modul_name']);
			$this->_ftp_connection->mv('backup/' . $setup['modul_name'] . '-' . $backup . '/modules', $prefx . 'modules/' . $setup['modul_name']);
			$this->_ftp_connection->mv('backup/' . $setup['modul_name'] . '-' . $backup . '/images', $prefx . 'images/standard/' . $setup['modul_name']);
			$this->_ftp_connection->mv('backup/' . $setup['modul_name'] . '-' . $backup . '/templates', $prefx . 'themes/standard/' . $setup['modul_name']);
			$this->_ftp_connection->mv('backup/' . $setup['modul_name'] . '-' . $backup . '/languages', $prefx . 'lang/' . $setup['modul_name']);
			$this->_ftp_connection->rm_dir('backup/' . $setup['modul_name'] . '-' . $backup);
		} else {
			$this->_debug .= '<p>Da kein Backup erstellt wurde, werden eventuell geschribene Daten zur&uuml;ckgesetzt, dazu z&auml;hlt nicht die Datenbank!</p>';
			foreach ($copyed as $file => $type) {
				switch ($type) {
					case 'module' :
					$this->_ftp_connection->mv($prefx . 'modules/' . $setup['modul_name'] . '/' . $file, 'acp/tmp/' . $this->_pack_name . '/modules/' . $setup['modul_name'] . '/' . $file);
					break;
					case 'image' :
					$this->_ftp_connection->mv($prefx . 'images/standard/' . $setup['modul_name'] . '/' . $file, 'acp/tmp/' . $this->_pack_name . '/images/standard/' . $setup['modul_name'] . '/' . $file);
					break;
					case 'lang' :
					$this->_ftp_connection->mv($prefx . 'lang/' . $setup['modul_name'] . '/' . $file, 'acp/tmp/' . $this->_pack_name . '/lang/' . $setup['modul_name'] . '/' . $file);
					break;
					case 'template' :
					$this->_ftp_connection->mv($prefx . 'themes/standard/' . $setup['modul_name'] . '/' . $file, 'acp/tmp/' . $this->_pack_name . '/themes/standard/' . $setup['modul_name'] . '/' . $file);
					break;
				}
			}
			@ $this->_ftp_connection->rm_dir($prefx . 'themes/standard/' . $setup['modul_name']);
			@ $this->_ftp_connection->rm_dir($prefx . 'lang/' . $setup['modul_name']);
			@ $this->_ftp_connection->rm_dir($prefx . 'images/standard/' . $setup['modul_name']);
			@ $this->_ftp_connection->rm_dir($prefx . 'modules/' . $setup['modul_name']);
		}
		return false;
	}
	if(!$error){
		$this->_debug .= '<p>Das Tempor&auml;re Paket wird gel&ouml;scht.</p>';
		@ $this->_ftp_connection->req_remove('acp/tmp/' . $this->_pack_name);
	}
}
/**
* Deinstalliert ein Modul
* @return bool
*/
public function deinstall() {
	if (!is_dir($this->_resource . '/' . $this->_pack_name))
	return false;
	if (!file_exists($this->_resource . '/' . $this->_pack_name . '/setup.php'))
	return false;
	$error = 0;
	if ($this->_noaction == true)
	$this->_debug .= '<p>Die Installation wird nur <b>simuliert</b> dabei werden Fehler ausgegeben, Datenbank&auml;nderungen werden allerdings <b>nicht</b> getestet!</p>';
	$setup = parse_ini_file($this->_resource . '/' . $this->_pack_name . '/setup.php');
	if (!isset ($setup['modul_name']) || !preg_match('!^[0-9a-z_\-]*$!', $setup['modul_name'])) {
		$this->_debug .= 'Der Modulname ist nicht valide, oder existiert nicht! Er darf nur aus kleien Buchstabe (a-z), Zahlen, Minus "-" und Unterstrich "_" bestehen. Die Installation kann nicht vortgesetzt werden!';
		$error++;
	}
	if (!isset ($setup['modul_filename']) || !preg_match('!^[0-9a-z_\-]*\.[a-zA-Z]*$!', $setup['modul_filename'])) {
		$this->_debug .= 'Der Dateiname des Moduls ist nicht valide, oder existiert nicht! Er darf nur aus kleien Buchstabe (a-z), Zahlen, Minus "-" und Unterstrich "_" bestehen. Die Installation kann nicht vortgesetzt werden!';
		$error++;
	}
	if (!isset ($setup['modul_version']) || !preg_match('!^[0-9]*\.[0-9]*\.[0-9]*$!', $setup['modul_version'])) {
		$this->_debug .= 'Die Versionsangabe des Moduls ist nicht valide, oder existiert nicht! Sie darf nur aus Zahlen, getrennt durch Punkte bestehen, diese Zahlen müssen in 3 Gruppen unterteilt sein (Beispiel: 0.3.11)';
		$error++;
	}
	if ($setup['modul_type'] == 'acp')
	$prefx = 'acp/';
	else
	$prefx = '';
	$backup = date('d.m.Y_H_i_s', time());
	if (!$this->_ftp_connection->exists('backup'))
	$this->_ftp_connection->mk_dir('backup');
	if (!$this->_ftp_connection->exists('backup'))
	return false;
	$this->_ftp_connection->mk_dir('backup/' . $setup['modul_name'] . '-' . $backup);
	if (!$this->_ftp_connection->exists('backup/' . $setup['modul_name'] . '-' . $backup))
	return false;
	@ $this->_ftp_connection->mv($prefx . 'modules/' . $setup['modul_name'], 'backup/' . $setup['modul_name'] . '-' . $backup . '/modules');
	@ $this->_ftp_connection->mv($prefx . 'images/standard/' . $setup['modul_name'], 'backup/' . $setup['modul_name'] . '-' . $backup . '/images');
	@ $this->_ftp_connection->mv($prefx . 'themes/standard/' . $setup['modul_name'], 'backup/' . $setup['modul_name'] . '-' . $backup . '/templates');
	@ $this->_ftp_connection->mv($prefx . 'lang/' . $setup['modul_name'], 'backup/' . $setup['modul_name'] . '-' . $backup . '/languages');
	if (isset ($setup['de_db_update']) && !$error) {
		$this->_debug .= "\n" . '<p>Folgende Datenbankupdates wurden ausgef&uuml;hrt:</p>';
		if (is_array($setup['de_db_update'])) {
			foreach ($setup['de_db_update'] as $mod) {
				if ($this->_noaction)
				$this->_debug .= "\n" . '<p>' . $mod . '</p>';
				else {
					if (mysql_query($mod))
					$this->_debug .= "\n" . '<p>' . $mod . '</p>';
					else {
						$this->_debug .= "\n" . '<p>Es ist ein <b>Fehler</b> bei folgendem Query "' . $mod . '" aufgetreten, Mysql meldet: "' . mysql_error() . '"</p>';
						$error++;
					}
				}
			}
		} else {
			if ($this->_noaction)
			$this->_debug .= "\n" . '<p>' . $setup['de_db_update'] . '</p>';
			else {
				if (mysql_query($setup['de_db_update']))
				$this->_debug .= "\n" . '<p>' . $setup['de_db_update'] . '</p>';
				else {
					$this->_debug .= "\n" . '<p>Es ist ein <b>Fehler</b> bei folgendem Query "' . $setup['de_db_update'] . '" aufgetreten, Mysql meldet: "' . mysql_error() . '"</p>';
					$error++;
				}
			}
		}
	} else
	if ($error) {
		$this->_debug .= '<p>Da bereits Fehler aufgetreten sind, wird kein MySQl Befehl ausgef&uuml;hrt!</p>';
	}
	if ($this->_noaction) {
		$this->_debug .= '<p>Deinstallation erfolgreich.</p><p>Daten wurden nicht ver&auml;ndert!</p>';
		$this->_ftp_connection->mv('backup/' . $setup['modul_name'] . '-' . $backup . '/modules', $prefx . 'modules/' . $setup['modul_name']);
		$this->_ftp_connection->mv('backup/' . $setup['modul_name'] . '-' . $backup . '/images', $prefx . 'images/standard/' . $setup['modul_name']);
		$this->_ftp_connection->mv('backup/' . $setup['modul_name'] . '-' . $backup . '/templates', $prefx . 'themes/standard/' . $setup['modul_name']);
		$this->_ftp_connection->mv('backup/' . $setup['modul_name'] . '-' . $backup . '/languages', $prefx . 'lang/' . $setup['modul_name']);
		$this->_ftp_connection->rm_dir('backup/' . $setup['modul_name'] . '-' . $backup);
		return true;
	} else {
		$this->_ftp_connection->rm_dir('/acp/tmp/' . $this->_pack_name);
		$this->_debug .= '<p>Deinstallation erfolgreich.</p>';
	}
}
public function debug() {
	return $this->_debug;
}
}
?>

