<?PHP
class ftp {
	/**
	 * @var string FTP Host
	 */
	private $_host;
	/**
	 * @var string FTP Port
	 */
	private $_port;
	/**
	 * @var string FTP User
	 */
	private $_user;
	/**
	 * @var string FTP Passwort
	 */
	private $_password;
	/**
	 * @var ressource FTP Verbindung
	 */
	private $_connection;
	/**
	 * @var bool befindet man sich im Lito Ordner (Root)
	 */
	public $lito_root = false; //Wenn true, kann der Paketmanager diese Verbindung verwenden
	/**
	* @var bool Steht die Verbindung?
	*/
	public $connected = false;
	/**
	 * @var string PHP or FTP
	 */
	private $_method = 'PHP';
	/**
	 * Daten angeben um eine Verbindung herzustellen
	 * @param string host
	 * @param string user
	 * @param string password
	 * @param string rootdir Verzeichniss auf dem Server
	 * @param int port
	 */
	/**
	 * @var string rootdir for phpmode
	 */
	private $rootdir = './';
	public function ftp($host = '', $user = '', $password = '', $rootdir = './', $port = 21) {
		if(defined('C_FTP_METHOD')){

			$this->_method = C_FTP_METHOD;
		}
		if($this->_method == 'PHP'){
			
			$rootdir=LITO_ROOT_PATH;
			if(!is_dir($rootdir))
			return false;
			$this->rootdir = preg_replace('!\/$!', '', $rootdir);
			$this->connected = true;
			$this->lito_root = true;
			//echo 'Useing PHP as a workaround!';
		} else {
			$this->_host = $host;
			$this->_port = $port;
			$this->_user = $user;
			$this->_password = $password;
			if (!@ $this->_connection = ftp_connect($this->_host, $this->_port))
			return false;
			if (!@ ftp_login($this->_connection, $this->_user, $this->_password))
			return false;
			$this->connected = true;
			ftp_chdir($this->_connection, $rootdir);
			//if ($this->exists('litotex.php'))
			$this->lito_root = true;
		}
	}
	/**
	 * Erstellt ein Verzeichniss
	 * @param string dir Verzeichnissname
	 */
	public function mk_dir($dir) {

		if (!$this->connected)
		return false;
		if ($this->exists($dir)){
		
		return true;
		}
		if($this->_method == 'PHP')
		return  mkdir($this->rootdir.'/'.$dir);
		else
		return @ ftp_mkdir($this->_connection, $dir);
	}
	/**
	 * L�scht ein Verzeichniss
	 * @param string dir Verzeichnissname
	 */
	public function rm_dir($dir) {
		$dir = preg_replace('!^\/!', '', $dir);
		if (!$this->connected)
		return false;
		if (!$this->exists($dir))
		return false;
		if($this->_method == 'PHP'){
				$this->_php_all_delete($this->_rootdir.'/'.$dir);
		} else {
			$list = $this->list_files($dir);
			if (is_array($list)) {
				foreach ($list as $file) {
					if (!is_array($this->list_files($file)) || in_array($file, $this->list_files($file))) {
						$this->rm_file($file);
					} else {
						$this->rm_dir($file);
					}
				}
			}
			return @ ftp_rmdir($this->_connection, $dir);
		}
	}
	/**
	 * Verschiebt ein Verzeichniss oder eine Datei
	 * @param string file Verzeichnissname/Dateiname
	 * @param string dest Ziel
	 */
	public function mv($file, $dest) {
		if (!$this->connected)
		return false;
		if (!$this->exists($file))
		return false;
		if($this->_method == 'PHP'){
			return @ rename($this->rootdir.'/'.$file, $this->rootdir.'/'.$dest);
		} else 
			return @ftp_rename($this->_connection, $file, $dest);
	}
	/**
	 * Listet den Inhalt eines Verzeichnisses auf
	 * @param string dir Verzeichnissname
	 */
	public function list_files($dir = './') {
		if (!$this->connected)
		return false;
		if($this->_method == 'PHP'){
			if(!is_dir($this->rootdir.'/'.$dir))
				return false;
			$dir = opendir($this->rootdir.'/'.$dir);
			$return = array();
			while($file = readdir($dir)){
				if($file == '.' || $file == '..')
					continue;
				$return[] = $file;
			}
			return $return;
		} else {
			$dir = preg_replace('!^\/!', '', $dir);
			$return = @ ftp_nlist($this->_connection, './'.$dir);
			if(!is_array($return))
			return false;
			foreach($return as $i => $param){
				$return[$i] = preg_replace('!^'.str_replace("/", "\\/", $dir).'\\/!', '', $param);
			}
			return $return;
		}
	}
	/**
	 * L�scht eine Datei
	 * @param string file Dateiname
	 */
	public function rm_file($file) {
		if (!$this->connected)
		return false;
		if (!$this->exists($file))
		return true;
		if($file == '.' || $file == '..')
		return false;
		if($this->_method == 'PHP'){
			if(!is_file($this->rootdir.'/'.$file))
				return false;
			return @unlink($this->rootdir.'/'.$file);
		} else
			return @ftp_delete($this->_connection, $file);
	}
	/**
	 * Setzt Berechtigungen
	 * @param string ch Berechtigungen
	 * @param string file Dateiname
	 */
	public function chown_perm($ch, $file) {
	
		if (!$this->connected)
		return false;
		if (!$this->exists($file)){
		return false;
		}
	
		if($this->_method == 'PHP'){
		
			return @chmod($this->rootdir.'/'.$file, $ch);
		} else
			return @ftp_chmod($this->_connection, $ch, $file);
	}
	/**
	 * Gibt den Inhalt einer Datei zur�ck
	 * @param string file Dateiname
	 */
	public function get_contents($file) {
		if (!$this->connected)
		return false;
		if (!$this->exists($file))
		return false;
		if($this->_method == 'PHP'){
			if(!is_file($this->rootdir.'/'.$file))
				return false;
			return file_get_contents($this->rootdir.'/'.$file);
		} else {
			$time = time();
			$local_cache = fopen(LITO_ROOT_PATH . 'cache/ftpcache.php' . $time, 'w');
			ftp_fget($this->_connection, $local_cache, $file, FTP_BINARY);
			$return = file_get_contents(LITO_ROOT_PATH . 'cache/ftpcache.php' . $time);
			fclose($local_cache);
			unlink(LITO_ROOT_PATH . 'cache/ftpcache.php' . $time);
			return $return;
		}
	}
	/**
	 * Schreibt in eine Datei
	 * @param string file Dateiname
	 * @param string new Neuer Inhalt
	 */
	public function write_contents($file, $new, $overwrite = true) {
		if (!$this->connected)
		return false;
		if ($overwrite && $this->exists($file))
		return false;
		if($this->_method == 'PHP'){
			$file_h = fopen($this->rootdir.'/'.$file, 'w');
			fwrite($file_h, $new);
			return @fclose($file_h);
		} else {
			$time = time();
			$local_cache = fopen(LITO_ROOT_PATH . 'cache/ftpcache.php' . $time, 'w');
			fwrite($local_cache, $new);
			fclose($local_cache);
			$local_cache = fopen(LITO_ROOT_PATH . 'cache/ftpcache.php' . $time, 'r');
			@$return = ftp_fput($this->_connection, $file, $local_cache, FTP_BINARY);
			fclose($local_cache);
			unlink(LITO_ROOT_PATH . 'cache/ftpcache.php' . $time);
			return $return;
		}
	}
	/**
	 * Kopiert dateien per FTP
	 * @param string file Zu kopierende Datei
	 * @param string dest Ziel
	 */
	public function copy_file($file, $dest){
		$file_c = $this->get_contents($file);
		$this->write_contents($dest, $file_c);
	}
	/**
	 * Kopiert Dateien und Ordner rekursiv per FTP
	 * @param string source Zu kopierendes Verzeichniss
	 * @param string dest Ziel
	 */
	public function copy_req($source, $dest){
		$source = preg_replace("!\/$!", '', $source);
		if(!$this->list_files($source))
		return false;
		if(!$this->exists($dest))
		if(!$this->mk_dir($dest))
		return false;
		$source_files = $this->list_files($source);
		foreach($source_files as $file){
			if($file == '.' || $file == '..' || $file == $source.'/.' || $file == $source.'/..')
			continue;
			if(!preg_match('!'.preg_replace('!^\/!', '', $source).'!', $file))
			$file = $source.'/'.$file;
			if($this->isdir($file))
			$this->copy_req($file, str_replace($source, $dest, $file));
			else{
				$this->copy_file($file, str_replace($source, $dest, $file));
			}
		}
	}
	/**
	 * Löscht ein Verzeichniss rekursiv
	 * @param string dir Verzeichniss
	 */
	public function req_remove($dir){
		$dir = preg_replace('!\/$!', '', $dir);
		if(!$this->exists($dir)){
			return true;
		}
		if($dir == '.' || $dir == '..')
		return false;
		if(!$this->isdir($dir)){
			return $this->rm_file($dir);
		}
		if($this->_method == 'PHP'){
			$this->_php_all_delete($this->rootdir.'/'.$dir);
		} else {
			$files = $this->list_files($dir);
			foreach($files as $file){
				if($file == '.' || $file == '..')
				continue;
				$file = $dir.'/'.$file;
				$this->req_remove($file);
			}
			$this->rm_dir($dir);
		}
	}
	public function isdir($file){
		if(!$this->exists($file))
		return false;
		if($this->_method == 'PHP'){
			return is_dir($this->rootdir.'/'.$file);
		} else {
			if(ftp_size($this->_connection, $file) == -1)
			return true;
			return false;
		}
	}
	/**
	 * Schließt die FTP Verbindung
	 */
	public function disconnect() {
		if($this->_method == 'PHP')
			$this->connected = false;
		else{
			if (!$this->connected)
			return false;
			ftp_close($this->_connection);
			$this->connected = false;
		}
	}
	/**
	 * �berpr�ft ob die angegebene Datei/das Verzeichniss existiert
	 * @param string file
	 * @return bool
	 */
	public function exists($file) {
		if($this->_method == 'PHP'){
		
			return file_exists($this->rootdir.'/'.$file);
		} else {
		$dir = dirname($file);
		$file = basename($file);
		if($dir == '.')
		$dir = '';
		if($dir == '' && count($this->list_files($dir)) == 0)
		$dir = '.';
		if (@ in_array($file, @ $this->list_files($dir), '/'))
		return true;
		else
		return false;
		}
	}
	/**
	 * Deletes a Directory recursivly
	 * @param $verz Direcotory
	 * @param $folder foldersarray (ignore!)
	 * @return array, deletet folders
	 */
	private function _php_dir_delete($verz, $folder = array())
	{
		$folder[] = $verz;
		$fp = opendir($verz);
		while ($dir_file = readdir($fp))
		{
			if (($dir_file == '.') || ($dir_file == '..'))
			continue;
			$neu_file = $verz . '/' . $dir_file;
			if (is_dir($neu_file))
			$folder = $this->_php_dir_delete($neu_file, $folder);
			else
			unlink($neu_file);
		}
		closedir($fp);
		return $folder;
	}
	/**
	 * Deletes all (dirs and files)
	 * @param $dir_file dir or file to delete
	 * @return bool
	 */
	private function _php_all_delete($dir_file)
	{
		if (is_dir($dir_file))
		{
			$array = $this->_php_dir_delete($dir_file);
			$array = array_reverse($array);
			foreach ($array as $elem)
			rmdir($elem);
		}
		elseif (is_file($dir_file))
		unlink($dir_file);
		else
		return false;
	}
}
?>